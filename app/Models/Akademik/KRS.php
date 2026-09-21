<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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
    public function getStatusBadgeAttribute()
    {
        return match ($this->attributes['status'] ?? null) {
            'Draft' => 'badge bg-secondary',
            'Diajukan' => 'badge bg-warning text-dark',
            'Disetujui' => 'badge bg-success',
            'Ditolak' => 'badge bg-danger',
            'Dikunci' => 'badge bg-dark',
            'Dicetak' => 'badge bg-primary',
            default => 'badge bg-secondary',
        };
    }

    public function getIsEditableAttribute()
    {
        // Status lama dapat tersimpan dengan variasi huruf besar/kecil.
        // Normalisasi agar KRS Draft tetap dapat diedit/dibatalkan.
        $status = strtolower(trim((string) ($this->attributes['status'] ?? $this->status ?? '')));

        return in_array($status, ['draft', 'ditolak'], true);
    }

    public function getIsApprovableAttribute()
    {
        $status = strtolower(trim((string) ($this->attributes['status'] ?? $this->status ?? '')));
        return $status === 'diajukan';
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

    /**
     * Pastikan setiap detail KRS yang aktif dan sudah disetujui
     * mempunyai record Nilai sebagai sumber nilai dan kehadiran.
     */
    public function syncNilai()
    {
        $this->loadMissing('details');

        foreach ($this->details as $detail) {
            if (!in_array($detail->status, ['Aktif', 'Mengulang'], true)) {
                continue;
            }

            Nilai::firstOrCreate(
                ['krs_detail_id' => $detail->id],
                [
                    'code' => 'NIL-' . Str::upper(Str::random(12)),
                    'mahasiswa_id' => $this->mahasiswa_id,
                    'matkul_id' => $detail->matkul_id,
                    'taka_id' => $this->taka_id,
                    'semester' => $this->semester,
                    'sks' => $detail->sks,
                    'status' => 'Draft',
                    'bobot_tugas' => 20,
                    'bobot_quiz' => 10,
                    'bobot_uts' => 25,
                    'bobot_uas' => 25,
                    'bobot_praktikum' => 5,
                    'bobot_kehadiran' => 15,
                ]
            );
        }

        return $this;
    }

    public function approve($dosenPaId = null, $notes = null)
    {
        $this->update([
            'status' => 'Disetujui',
            'dosen_pa_id' => $dosenPaId ?? $this->dosen_pa_id,
            'approved_at' => now(),
            'notes' => $notes
        ]);

        $this->syncNilai();
    }

    public function reject($notes = null)
    {
        $this->update([
            'status' => 'Ditolak',
            'notes' => $notes
        ]);
    }

    /**
     * Jika seluruh mata kuliah dalam KRS sudah dibatalkan/dihapus,
     * KRS induk boleh digunakan kembali untuk pengisian ulang.
     *
     * KRS yang sudah Dipublish/Dikunci tidak pernah di-reset otomatis.
     */
    public function resetIfEmpty()
    {
        $jumlahAktif = $this->details()
            ->whereIn('status', ['Aktif', 'Mengulang'])
            ->count();

        if (
            $jumlahAktif === 0 &&
            in_array(strtolower(trim((string) $this->status)), ['diajukan', 'disetujui'], true)
        ) {
            $this->update([
                'status' => 'Draft',
                'total_sks' => 0,
                'approved_at' => null,
                'notes' => null,
            ]);

            return true;
        }

        return false;
    }

    public function submit()
    {
        $this->loadMissing('details');
        $this->total_sks = (int) $this->details()
            ->whereIn('status', ['Aktif', 'Mengulang'])
            ->sum('sks');

        if ($this->total_sks > 0) {
            $this->update(['status' => 'Diajukan']);
            return true;
        }
        return false;
    }
}
