<?php

namespace App\Http\Controllers\Private\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MaintenanceController extends Controller
{
    /**
     * Jalankan pembersihan cache Laravel dari dashboard web-admin.
     * Hanya akun Web Administrator (type/raw_type = 0) yang diizinkan.
     */
    public function clearCache(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        try {
            Artisan::call('optimize:clear');

            return back()
                ->with('maintenance_success', 'Cache dan optimasi Laravel berhasil dibersihkan.')
                ->with('maintenance_output', trim(Artisan::output()));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('maintenance_error', 'Cache Laravel gagal dibersihkan: ' . $e->getMessage());
        }
    }
}
