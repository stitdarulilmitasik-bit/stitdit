<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;

class KehadiranMahasiswa extends Model
{
    protected $table = 'kehadiran_mahasiswas';
    protected $guarded = [];

    protected $casts = [
        'semester' => 'integer',
        'pertemuan' => 'integer',
    ];

    public function nilai()
    {
        return $this->belongsTo(Nilai::class, 'nilai_id');
    }
}