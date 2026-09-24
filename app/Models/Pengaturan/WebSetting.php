<?php

namespace App\Models\Pengaturan;
// USE SYSTEM
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasLogAktivitas;
// USE MODELS

class WebSetting extends Model
{
    use SoftDeletes, HasLogAktivitas;
    
    protected $table = 'web_settings';
    protected $guarded = [];

    public function getSchoolLogoHoriAttribute($value)
    {
        return stit_storage_image_url('images/logo', $value ?: 'logo-hori.png', 'images/logo/logo-hori.png');
    }
    public function getSchoolLogoVertAttribute($value)
    {
        return stit_storage_image_url('images/logo', $value ?: 'logo-vert.png', 'images/logo/logo-vert.png');
    }
}
