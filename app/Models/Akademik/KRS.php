<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasLogAktivitas;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Akademik\TahunAkademik;
use App\Models\Akademik\KrsDetail;

class KRS extends Model
{
    use SoftDeletes, HasLogAktivitas;

    protected $table = 'k_r_s';
    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'ipk_sebelumnya' => 'decimal:2',
    ];

    // ACCESSOR METHODS
    public function getStatusAttribute($value)
    {
        $statuses = [
            'Draft' => 'draft',
            'draft' => 'draft',
            'Diajukan' => 'submitted',
            'diajukan' => 'submitted',
            'submitted' => 'submitted',
            'Disetujui' => 'approved',
            'disetujui' => 'approved',
            'approved' => 'approved',
            'Ditolak' => 'rejected',
            'ditolak' => 'rejected',
            'rejected' => 'rejected',
            'Dipublish' => 'published',
            'dipublish' => 'published',
            'published' => 'published',
            'Dikunci' => 'locked',
            'dikunci' => 'locked',
            'locked' => 'locked'
        ];

        return $statuses[$value] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge bg-secondary',
            'submitted' => 'badge bg-warning',
            'approved' => 'badge bg-success',
            'rejected' => 'badge bg-danger',
            'published' => 'badge bg-primary',
            'locked' => 'badge bg-dark'
        ];

        return $badges[$this->attributes['status']] ?? 'badge bg-secondary';
    }

    public function getIsEditableAttribute()
    {
        return in_array($this->status, ['draft', 'submitted', 'rejected']);
    }

    public function getIsApprovableAttribute()
    {
        return $this->status === 'submitted';
    }

    public function getBatasSksAttribute()
    {
        // Gunakan batas yang tersimpan pada KRS. KRS mahasiswa baru
        // dibuat dengan max_sks 24, sedangkan KRS lama tetap punya
        // fallback berdasarkan IPK.
        if ((int) $this->max_sks > 0) {
            return (int) $this->max_sks;
        }

        $ipk = (float) $this->ipk_sebelumnya;

        if ($ipk >= 3.50) return 24;
        if ($ipk >= 3.00) return 22;
        if ($ipk >= 2.50) return 20;
        if ($ipk >= 2.00) return 18;

        return 15;
    }

    public function getSisaSksAttribute()
    {
        $terpakai = (int) $this->details()
            ->whereIn('status', ['Aktif', 'Mengulang'])
            ->sum('sks');

        return max(0, $this->batas_sks - $terpakai);
    }

    // RELATIONSHIP METHODS
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'taka_id');
    }

    public function dosenPA()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pa_id');
    }

    public function details()
    {
        return $this->hasMany(KrsDetail::class, 'krs_id');
    }

    public function detailsAktif()
    {
        return $this->hasMany(KrsDetail::class, 'krs_id')->where('status', 'Aktif');
    }

    // SCOPE METHODS
    public function scopeByMahasiswa($query, $mahasiswaId)
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }

    public function scopeByTahunAkademik($query, $takaId)
    {
        return $query->where('taka_id', $takaId);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // BUSINESS LOGIC METHODS
    public function hitungTotalSks()
    {
        $this->total_sks = $this->detailsAktif()->sum('sks');
        $this->save();
        return $this->total_sks;
    }

    public function canAddMatakuliah($sks)
    {
        // total_sks bisa belum tersinkron dengan detail KRS.
        $terpakai = (int) $this->details()
            ->whereIn('status', ['Aktif', 'Mengulang'])
            ->sum('sks');

        return ($terpakai + (int) $sks) <= $this->batas_sks;
    }

    public function approve($dosenPaId = null, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'dosen_pa_id' => $dosenPaId,
            'approved_at' => now(),
            'notes' => $notes
        ]);
    }

    public function reject($notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $notes
        ]);
    }

    public function submit()
    {
        if ($this->total_sks > 0) {
            $this->update(['status' => 'submitted']);
            return true;
        }
        return false;
    }
}
