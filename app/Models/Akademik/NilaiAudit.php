<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;

class NilaiAudit extends Model
{
    protected $table = 'nilai_audits';

    protected $guarded = [];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];

    public function nilai()
    {
        return $this->belongsTo(Nilai::class, 'nilai_id');
    }
}
