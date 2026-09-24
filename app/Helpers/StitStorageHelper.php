<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('stit_storage_image_url')) {
    /**
     * Resolve an image URL from the public storage disk with a public fallback.
     */
    function stit_storage_image_url(
        string $directory,
        string $filename,
        ?string $fallback = null
    ): string {
        $directory = trim($directory, '/');
        $filename = ltrim($filename, '/');
        $path = $directory . '/' . $filename;

        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }

            if ($fallback) {
                $fallback = ltrim($fallback, '/');

                if (Storage::disk('public')->exists($fallback)) {
                    return Storage::disk('public')->url($fallback);
                }
            }
        } catch (\Throwable $e) {
            // Fall through to the public asset fallback.
        }

        if ($fallback && file_exists(public_path($fallback))) {
            return asset($fallback);
        }

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset($fallback ?: $path);
    }
}
