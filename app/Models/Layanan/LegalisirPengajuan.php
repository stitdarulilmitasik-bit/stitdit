<?php

namespace App\Models\Layanan;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalisirPengajuan extends Model
{
    use SoftDeletes;

    protected $table = 'legalisir_pengajuans';
    protected $guarded = [];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_legalisir' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
