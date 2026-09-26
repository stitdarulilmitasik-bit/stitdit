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
        return $this->resolveLogoUrl($value, 'logo-hori.png');
    }

    public function getSchoolLogoVertAttribute($value)
    {
        return $this->resolveLogoUrl($value, 'logo-vert.png');
    }

    private function resolveLogoUrl(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $value = ltrim($value, '/');
        foreach (['storage/images/logo/', 'images/logo/'] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
                break;
            }
        }

        return stit_storage_image_url(
            'images/logo',
            $value ?: $fallback,
            'images/logo/' . $fallback
        );
    }
}
