<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasLogAktivitas;
use App\Models\Mahasiswa;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\KrsDetail;
use App\Models\Akademik\TahunAkademik;

class Nilai extends Model
{
    use SoftDeletes, HasLogAktivitas;

    protected $table = 'nilais';
    protected $guarded = [];

    protected $casts = [
        'tugas_1' => 'decimal:2',
        'tugas_2' => 'decimal:2',
        'tugas_3' => 'decimal:2',
        'quiz_1' => 'decimal:2',
        'quiz_2' => 'decimal:2',
        'uts' => 'decimal:2',
        'uas' => 'decimal:2',
        'praktikum' => 'decimal:2',
        'kehadiran' => 'decimal:2',
        'bobot_tugas' => 'decimal:2',
        'bobot_quiz' => 'decimal:2',
        'bobot_uts' => 'decimal:2',
        'bobot_uas' => 'decimal:2',
        'bobot_praktikum' => 'decimal:2',
        'bobot_kehadiran' => 'decimal:2',
        'nilai_angka' => 'decimal:2',
        'nilai_mutu' => 'decimal:2',
        'mutu_x_sks' => 'decimal:2',
        'nilai_remidi' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    const NILAI_HURUF_MAP = [
        'A' => ['min' => 85, 'max' => 100, 'mutu' => 4.00],
        'A-' => ['min' => 80, 'max' => 84.99, 'mutu' => 3.67],
        'B+' => ['min' => 75, 'max' => 79.99, 'mutu' => 3.33],
        'B' => ['min' => 70, 'max' => 74.99, 'mutu' => 3.00],
        'B-' => ['min' => 65, 'max' => 69.99, 'mutu' => 2.67],
        'C+' => ['min' => 60, 'max' => 64.99, 'mutu' => 2.33],
        'C' => ['min' => 55, 'max' => 59.99, 'mutu' => 2.00],
        'C-' => ['min' => 50, 'max' => 54.99, 'mutu' => 1.67],
        'D+' => ['min' => 45, 'max' => 49.99, 'mutu' => 1.33],
        'D' => ['min' => 40, 'max' => 44.99, 'mutu' => 1.00],
        'E' => ['min' => 0, 'max' => 39.99, 'mutu' => 0.00],
    ];

    public function getStatusAttribute($value)
    {
        return ['Draft' => 'Draft', 'Published' => 'Published', 'Locked' => 'Locked'][$value] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'Draft' => 'badge bg-secondary',
            'Published' => 'badge bg-success',
            'Locked' => 'badge bg-warning'
        ][$this->attributes['status']] ?? 'badge bg-secondary';
    }

    public function getIsEditableAttribute()
    {
        return $this->attributes['status'] === 'Draft';
    }

    public function getIsLulusAttribute()
    {
        return $this->nilai_mutu >= 2.00;
    }

    public function getRataTugasAttribute()
    {
        $tugas = collect([$this->tugas_1, $this->tugas_2, $this->tugas_3])->filter(fn($nilai) => $nilai !== null);
        return $tugas->isEmpty() ? 0 : $tugas->avg();
    }

    public function getRataQuizAttribute()
    {
        $quiz = collect([$this->quiz_1, $this->quiz_2])->filter(fn($nilai) => $nilai !== null);
        return $quiz->isEmpty() ? 0 : $quiz->avg();
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'matkul_id');
    }

    public function krsDetail()
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'taka_id');
    }

    public function kehadiranMahasiswa()
    {
        return $this->hasMany(KehadiranMahasiswa::class, 'nilai_id');
    }

    public function scopeByMahasiswa($query, $mahasiswaId) { return $query->where('mahasiswa_id', $mahasiswaId); }
    public function scopeByMatakuliah($query, $matkulId) { return $query->where('matkul_id', $matkulId); }
    public function scopeBySemester($query, $semester) { return $query->where('semester', $semester); }
    public function scopeByTahunAkademik($query, $takaId) { return $query->where('taka_id', $takaId); }
    public function scopePublished($query) { return $query->where('status', 'Published'); }
    public function scopeLulus($query) { return $query->where('nilai_mutu', '>=', 2.00); }

    /**
     * Hitung nilai akhir dengan komposisi akademik 85% + kehadiran 15%.
     * Bobot akademik yang tersimpan tetap digunakan secara proporsional agar
     * penambahan komponen kehadiran tidak membuat total bobot > 100%.
     */
    public function hitungNilaiAkhir()
    {
        $bobotAkademik = collect([
            (float) $this->bobot_tugas,
            (float) $this->bobot_quiz,
            (float) $this->bobot_uts,
            (float) $this->bobot_uas,
            (float) $this->bobot_praktikum,
        ])->sum();

        $nilaiAkademik = 0;
        $komponen = [
            'tugas' => $this->rata_tugas,
            'quiz' => $this->rata_quiz,
            'uts' => $this->uts,
            'uas' => $this->uas,
            'praktikum' => $this->praktikum,
        ];
        $bobotKomponen = [
            'tugas' => (float) $this->bobot_tugas,
            'quiz' => (float) $this->bobot_quiz,
            'uts' => (float) $this->bobot_uts,
            'uas' => (float) $this->bobot_uas,
            'praktikum' => (float) $this->bobot_praktikum,
        ];
        if ($bobotAkademik > 0) {
            foreach ($komponen as $key => $nilai) {
                if ($nilai !== null) {
                    $nilaiAkademik += ((float) $nilai * $bobotKomponen[$key] / $bobotAkademik) * 0.85;
                }
            }
        }

        $nilaiKehadiran = $this->kehadiran !== null ? (float) $this->kehadiran : 0;
        $nilaiAkhir = $nilaiAkademik + ($nilaiKehadiran * 0.15);
        $this->bobot_kehadiran = 15;
        $this->nilai_angka = round($nilaiAkhir, 2);
        $this->updateNilaiHurufDanMutu();
        $this->mutu_x_sks = $this->nilai_mutu * $this->sks;
        $this->saveQuietly();
        return $this->nilai_angka;
    }

    private function updateNilaiHurufDanMutu()
    {
        foreach (self::NILAI_HURUF_MAP as $huruf => $range) {
            if ($this->nilai_angka >= $range['min'] && $this->nilai_angka <= $range['max']) {
                $this->nilai_huruf = $huruf;
                $this->nilai_mutu = $range['mutu'];
                break;
            }
        }
    }

    public function publish() { $this->update(['status' => 'Published', 'published_at' => now()]); }
    public function lock() { $this->update(['status' => 'Locked']); }
    public function unlock() { $this->update(['status' => 'Published']); }

    protected static function booted()
    {
        static::saving(function ($nilai) {
            if ($nilai->isDirty(['tugas_1','tugas_2','tugas_3','quiz_1','quiz_2','uts','uas','praktikum','kehadiran'])) {
                $nilai->hitungNilaiAkhir();
            }
        });
    }
}
