<?php

namespace App\Traits;

use App\Models\Pengaturan\LogAktivitas;
use App\Models\Pengaturan\ActivityLogChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

trait HasLogAktivitas
{
    protected static function bootHasLogAktivitas()
    {
        static::created(fn ($model) => static::logAktivitas($model, 'create'));
        static::updated(fn ($model) => static::logAktivitas($model, 'update'));
        static::deleted(fn ($model) => static::logAktivitas($model, 'delete'));
    }

    protected static function getClientIp()
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]) : null)
            ?? ($_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? null));
    }

    protected static function logAktivitas($model, $action)
    {
        $user = null;
        $userType = 'guest';

        foreach (['web', 'mahasiswa', 'dosen'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $userType = $guard === 'web' ? 'user' : $guard;
                break;
            }
        }

        if (!$user || !$model->getKey()) {
            return;
        }

        $changes = [];

        if ($action === 'update') {
            foreach ($model->getDirty() as $field => $newValue) {
                $oldValue = $model->getOriginal($field);
                if ($oldValue != $newValue) {
                    $changes[] = [
                        'field_name' => $field,
                        'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                        'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
                    ];
                }
            }
        } elseif ($action === 'create') {
            foreach ($model->getAttributes() as $field => $value) {
                if (!in_array($field, ['created_at','updated_at','deleted_at','created_by','updated_by','deleted_by'], true)) {
                    $changes[] = [
                        'field_name' => $field,
                        'old_value' => null,
                        'new_value' => is_array($value) ? json_encode($value) : $value,
                    ];
                }
            }
        } elseif ($action === 'delete') {
            foreach ($model->getOriginal() as $field => $value) {
                if (!in_array($field, ['created_at','updated_at','deleted_at','created_by','updated_by','deleted_by'], true)) {
                    $changes[] = [
                        'field_name' => $field,
                        'old_value' => is_array($value) ? json_encode($value) : $value,
                        'new_value' => null,
                    ];
                }
            }
        }

        if (empty($changes)) {
            return;
        }

        DB::transaction(function () use ($user, $userType, $action, $model, $changes) {
            $log = LogAktivitas::create([
                'user_id' => $user->id,
                'user_type' => $userType,
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'changes' => null,
                'ip_address' => static::getClientIp(),
                'user_agent' => Request::userAgent(),
                'description' => static::getLogDescription($model, $action),
            ]);

            $rows = [];
            foreach ($changes as $change) {
                $rows[] = [
                    'activity_log_id' => $log->id,
                    'field_name' => $change['field_name'],
                    'old_value' => $change['old_value'] ?? null,
                    'new_value' => $change['new_value'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows) {
                ActivityLogChange::insert($rows);
            }
        });
    }

    protected static function getLogDescription($model, $action)
    {
        $name = class_basename($model);
        return [
            'create' => "Membuat data {$name} baru",
            'update' => "Mengubah data {$name}",
            'delete' => "Menghapus data {$name}",
        ][$action] ?? '';
    }

    public function activityLogs()
    {
        return $this->morphMany(LogAktivitas::class, 'model', 'model_type', 'model_id');
    }

    public function recentActivity($limit = 10)
    {
        return LogAktivitas::where('user_id', $this->id)
            ->select(['id','user_id','user_type','action','model_type','model_id','description','created_at'])
            ->with('changesDetails')->latest()->take($limit)->get();
    }
}
