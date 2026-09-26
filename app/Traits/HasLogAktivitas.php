<?php

namespace App\Traits;

use App\Models\Pengaturan\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pengaturan\ActivityLogChange;
use App\Jobs\ProcessActivityLog;

trait HasLogAktivitas
{
    protected static function bootHasLogAktivitas()
    {
        static::created(function ($model) {
            static::logAktivitas($model, 'create');
        });

        static::updated(function ($model) {
            static::logAktivitas($model, 'update');
        });

        static::deleted(function ($model) {
            static::logAktivitas($model, 'delete');
        });
    }

    protected static function getClientIp()
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    protected static function logAktivitas($model, $action)
    {
        $authenticatedUser = null;
        $authenticatedUserId = null;
        $authenticatedUserType = 'guest';

        foreach (['mahasiswa', 'dosen', 'web'] as $guardName) {
            if (Auth::guard($guardName)->check()) {
                $authenticatedUser = Auth::guard($guardName)->user();
                $authenticatedUserId = $authenticatedUser->id ?? null;
                $authenticatedUserType = $guardName === 'web' ? 'user' : $guardName;
                break;
            }
        }

        if ($authenticatedUser === null || !$model->getKey()) {
            return;
        }

        $changesToLog = [];

        if ($action === 'update') {
            $dirty = $model->getDirty();
            $original = $model->getOriginal();

            $attributesToLog = $model->getGuarded() === []
                ? $dirty
                : array_intersect_key($dirty, array_flip($model->getFillable()));

            foreach ($attributesToLog as $key => $newValue) {
                $oldValue = $original[$key] ?? null;

                if ($oldValue != $newValue) {
                    $changesToLog[] = [
                        'field_name' => $key,
                        'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                        'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
                    ];
                }
            }
        } elseif ($action === 'create') {
            $attributesToLog = $model->getGuarded() === []
                ? $model->getAttributes()
                : array_intersect_key(
                    $model->getAttributes(),
                    array_flip($model->getFillable())
                );

            foreach ($attributesToLog as $key => $newValue) {
                if (!in_array($key, [
                    $model->getCreatedAtColumn(),
                    $model->getUpdatedAtColumn(),
                    $model->getDeletedAtColumn(),
                    'created_by',
                    'updated_by',
                    'deleted_by'
                ], true)) {
                    $changesToLog[] = [
                        'field_name' => $key,
                        'old_value' => null,
                        'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
                    ];
                }
            }
        } elseif ($action === 'delete') {
            $attributesToLog = $model->getGuarded() === []
                ? $model->getOriginal()
                : array_intersect_key(
                    $model->getOriginal(),
                    array_flip($model->getFillable())
                );

            foreach ($attributesToLog as $key => $oldValue) {
                if (!in_array($key, [
                    $model->getCreatedAtColumn(),
                    $model->getUpdatedAtColumn(),
                    $model->getDeletedAtColumn(),
                    'created_by',
                    'updated_by',
                    'deleted_by'
                ], true)) {
                    $changesToLog[] = [
                        'field_name' => $key,
                        'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                        'new_value' => null,
                    ];
                }
            }
        }

        if (!empty($changesToLog)) {
            // Shared hosting ByetHost tidak menjalankan queue worker secara permanen.
            // Jalankan sinkron agar log langsung tersimpan pada request yang sama.
            ProcessActivityLog::dispatchSync(
                $authenticatedUserId,
                $authenticatedUserType,
                $action,
                get_class($model),
                (int) $model->getKey(),
                $changesToLog,
                static::getClientIp(),
                Request::userAgent(),
                static::getLogDescription($model, $action)
            );
        }
    }

    protected static function getLogDescription($model, $action)
    {
        $modelName = class_basename($model);

        return [
            'create' => "Membuat data {$modelName} baru",
            'update' => "Mengubah data {$modelName}",
            'delete' => "Menghapus data {$modelName}",
        ][$action] ?? '';
    }

    public function activityLogs()
    {
        return $this->morphMany(LogAktivitas::class, 'model', 'model_type', 'model_id');
    }

    public function recentActivity($limit = 10)
    {
        return LogAktivitas::where('user_id', $this->id)
            ->select(['id', 'user_id', 'user_type', 'action', 'model_type', 'model_id', 'description', 'created_at'])
            ->with('changesDetails')
            ->latest()
            ->take($limit)
            ->get();
    }
}
