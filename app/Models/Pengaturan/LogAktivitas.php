<?php

namespace App\Models\Pengaturan;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogAktivitas extends Model
{
    // LogAktivitas adalah target audit, bukan objek yang diaudit.
    // Jangan gunakan HasLogAktivitas di sini agar tidak terjadi recursive logging.
    use SoftDeletes, HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'user_type',
        'action',
        'model_type',
        'model_id',
        'changes',
        'ip_address',
        'user_agent',
        'description'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function changesDetails()
    {
        return $this->hasMany(ActivityLogChange::class, 'activity_log_id');
    }

    public function scopeByUserType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function getFormattedChangesAttribute()
    {
        $changes = $this->changes ?? [];
        $formatted = [];

        if ($this->action === 'update') {
            foreach (($changes['old'] ?? []) as $key => $oldValue) {
                $formatted[] = [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $changes['new'][$key] ?? null
                ];
            }
        } elseif ($this->action === 'create') {
            foreach (($changes['new'] ?? []) as $key => $value) {
                $formatted[] = [
                    'field' => $key,
                    'new' => $value
                ];
            }
        } elseif ($this->action === 'delete') {
            foreach (($changes['old'] ?? []) as $key => $value) {
                $formatted[] = [
                    'field' => $key,
                    'old' => $value
                ];
            }
        }

        return $formatted;
    }

    public function getActionDescriptionAttribute()
    {
        return [
            'create' => 'Membuat',
            'update' => 'Mengubah',
            'delete' => 'Menghapus'
        ][$this->action] ?? ucfirst($this->action);
    }

    public function getUserTypeDescriptionAttribute()
    {
        return [
            'user' => 'Administrator',
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'guest' => 'Tamu'
        ][$this->user_type] ?? ucfirst($this->user_type);
    }
}
