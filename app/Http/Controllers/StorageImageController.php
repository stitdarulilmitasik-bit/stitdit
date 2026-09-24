<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class StorageImageController extends Controller
{
    /**
     * Serve application images directly from storage/app/public/images.
     * This avoids depending on a public/storage symlink on shared hosting.
     */
    public function show(string $path): Response
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_if($path === '' || str_contains($path, '..'), 404);
        abort_unless(str_starts_with($path, 'images/'), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        $fullPath = $disk->path($path);

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
