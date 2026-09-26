<?php

namespace App\Http\Controllers\Private\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Throwable;

class MaintenanceController extends Controller
{
    /**
     * Jalankan pembersihan cache Laravel dari dashboard web-admin.
     * Hanya akun Web Administrator (type/raw_type = 0) yang diizinkan.
     */
    public function clearViews(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        try {
            Artisan::call('view:clear');

            return back()
                ->with('maintenance_success', 'Compiled view Laravel berhasil dibersihkan (php artisan view:clear).')
                ->with('maintenance_output', trim(Artisan::output()));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('maintenance_error', 'Compiled view Laravel gagal dibersihkan: ' . $e->getMessage());
        }
    }

    public function clearRoutes(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        try {
            Artisan::call('route:clear');

            return back()
                ->with('maintenance_success', 'Route cache Laravel berhasil dibersihkan (php artisan route:clear).')
                ->with('maintenance_output', trim(Artisan::output()));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('maintenance_error', 'Route cache Laravel gagal dibersihkan: ' . $e->getMessage());
        }
    }

    public function storageLink(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        try {
            /*
             * ByetHost/OpenResty tidak selalu mengizinkan pembuatan symbolic link
             * dan fungsi exec() dapat dinonaktifkan. Jangan memanggil
             * php artisan storage:link di server tersebut.
             *
             * Struktur aplikasi kita memakai /htdocs sebagai document root,
             * sehingga /storage/... harus tersedia secara fisik di:
             * /htdocs/storage/...
             *
             * Sinkronkan isi storage/app/public ke storage sebagai public mirror.
             */
            $source = storage_path('app/public');
            $target = storage_path();

            File::ensureDirectoryExists($source);
            File::ensureDirectoryExists($target);

            File::copyDirectory($source, $target);

            return back()
                ->with('maintenance_success', 'Storage publik berhasil disiapkan untuk ByetHost.')
                ->with('maintenance_output', 'Public storage mirror: ' . $source . ' -> ' . $target);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('maintenance_error', 'Storage publik gagal disiapkan: ' . $e->getMessage());
        }
    }

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


    /**
     * Jalankan migration database yang belum diterapkan.
     * Hanya Web Administrator (type 0).
     */
    public function migrate(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        try {
            Artisan::call('migrate', ['--force' => true]);

            return back()
                ->with('maintenance_success', 'Migration database berhasil dijalankan.')
                ->with('maintenance_output', trim(Artisan::output()));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('maintenance_error', 'Migration database gagal: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan terminal Composer khusus Administrator.
     */
    public function composerTerminal()
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        return view('central.maintenance-composer');
    }

    /**
     * Eksekusi perintah Composer dari web-admin.
     *
     * Hanya perintah Composer yang diizinkan; karakter shell seperti
     * ;, |, &, $, >, <, backtick dan newline ditolak agar input tidak
     * berubah menjadi arbitrary shell command.
     */
    public function runComposer(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        abort_unless($user && (int) $user->raw_type === 0, 403);

        $request->validate([
            'command' => ['required', 'string', 'max:1000', 'regex:/^[A-Za-z0-9_\.\/\\:\@\=\+\*\-\s]+$/'],
        ]);

        $input = trim($request->input('command'));
        $input = preg_replace('/^composer(?:\.phar)?\s*/i', '', $input);
        $input = preg_replace('/^php\s+(?:composer(?:\.phar)?)(?:\s+)/i', '', $input);

        if ($input === '') {
            return back()
                ->with('composer_error', 'Masukkan perintah Composer, contoh: composer dump-autoload -o.')
                ->withInput();
        }

        $parts = preg_split('/\s+/', $input);
        $commandName = strtolower($parts[0] ?? '');

        $allowedCommands = [
            'about', 'audit', 'check-platform-reqs', 'clear-cache', 'config',
            'diagnose', 'dump-autoload', 'dumpautoload', 'install', 'list',
            'outdated', 'remove', 'require', 'show', 'status', 'validate',
            'update', 'upgrade', 'why', 'depends',
        ];

        if (! in_array($commandName, $allowedCommands, true)) {
            return back()
                ->with('composer_error', 'Perintah Composer "' . $commandName . '" tidak diizinkan melalui terminal web.')
                ->withInput();
        }

        $root = base_path();
        $composerPhar = base_path('composer.phar');
        $composer = null;
        $prefix = [];

        if (is_file($composerPhar)) {
            $composer = PHP_BINARY;
            $prefix = [$composer, $composerPhar];
        } else {
            $candidates = [
                env('COMPOSER_BINARY'),
                '/usr/local/bin/composer',
                '/usr/bin/composer',
                'composer',
            ];

            foreach ($candidates as $candidate) {
                if (! $candidate) {
                    continue;
                }

                if ($candidate === 'composer' || is_file($candidate) || is_executable($candidate)) {
                    $composer = $candidate;
                    $prefix = [$candidate];
                    break;
                }
            }
        }

        if (! $composer) {
            return back()
                ->with('composer_error', 'Composer tidak ditemukan. Letakkan composer.phar di root aplikasi atau set COMPOSER_BINARY pada environment server.')
                ->withInput();
        }

        $args = array_values(array_filter($parts, static fn ($part) => $part !== ''));
        $processCommand = array_merge($prefix, $args);

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            $processCommand,
            $descriptorSpec,
            $pipes,
            $root,
            null,
            ['bypass_shell' => true]
        );

        if (! is_resource($process)) {
            return back()
                ->with('composer_error', 'Tidak dapat menjalankan proses Composer pada server.')
                ->withInput();
        }

        fclose($pipes[0]);
        stream_set_timeout($pipes[1], 120);
        stream_set_timeout($pipes[2], 120);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        $output = trim($stdout . ($stderr ? "\n\n" . $stderr : ''));

        if (strlen($output) > 30000) {
            $output = substr($output, -30000);
            $output = "[output dipotong; menampilkan 30.000 karakter terakhir]\n\n" . $output;
        }

        return back()
            ->with($exitCode === 0 ? 'composer_success' : 'composer_error',
                'composer ' . $input . ($exitCode === 0 ? ' berhasil dijalankan.' : ' gagal dijalankan (exit code ' . $exitCode . ').'))
            ->with('composer_output', $output)
            ->with('composer_command', 'composer ' . $input);
    }

}
