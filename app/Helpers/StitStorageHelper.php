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

if (! function_exists('stit_profile_image_url')) {
    /**
     * Resolve a user profile photo URL.
     *
     * ByetHost uses /htdocs as the document root while this Laravel app
     * keeps the uploaded files under /htdocs/storage. Therefore check both
     * /storage/images/profile and /storage/images.
     */
    function stit_profile_image_url(?string $filename): string
    {
        $filename = $filename ? basename(ltrim($filename, '/')) : '';

        if ($filename && filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        if ($filename) {
            $candidates = [
                'images/profile/' . $filename,
                'images/' . $filename,
            ];

            foreach ($candidates as $path) {
                $diskExists = false;

                try {
                    $diskExists = Storage::disk('public')->exists($path);
                } catch (\Throwable $e) {
                    // Continue with the physical ByetHost mirror.
                }

                if ($diskExists) {
                    return '/storage/' . $path . '?v=' . @filemtime(storage_path($path));
                }

                if (is_file(storage_path($path))) {
                    return '/storage/' . $path . '?v=' . @filemtime(storage_path($path));
                }
            }
        }

        foreach ([
            'images/profile/default.jpg',
            'images/profile/default.png',
            'images/default.jpg',
            'images/default.png',
        ] as $fallback) {
            if (is_file(storage_path($fallback))) {
                return '/storage/' . $fallback . '?v=' . @filemtime(storage_path($fallback));
            }

            try {
                if (Storage::disk('public')->exists($fallback)) {
                    return Storage::disk('public')->url($fallback);
                }
            } catch (\Throwable $e) {
                // Continue to the next fallback.
            }
        }

        return asset('images/profile/default.png');
    }
}
if (! function_exists('stit_gallery_image_url')) {
    function stit_gallery_image_url(?string $filename): string
    {
        $filename = $filename ? ltrim($filename, '/') : '';

        if ($filename && filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        $candidates = [];
        if ($filename) {
            $candidates = [
                'images/gallery/' . $filename,
                'images/galleries/' . $filename,
                'gallery/' . $filename,
                'galleries/' . $filename,
            ];
        }

        foreach ($candidates as $path) {
            try {
                if (Storage::disk('public')->exists($path)) {
                    return Storage::disk('public')->url($path);
                }
            } catch (\Throwable $e) {
                // Continue to public asset fallback.
            }

            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return function_exists('stit_image_url')
            ? stit_image_url(null)
            : asset('images/profile/default.png');
    }
}

if (! function_exists('stit_image_url')) {
    /**
     * Resolve a generic site image URL with a safe default fallback.
     */
    function stit_image_url(?string $filename): string
    {
        $filename = $filename ? ltrim($filename, '/') : '';

        if ($filename && filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        $candidates = [];

        if ($filename) {
            $candidates = [
                'images/' . $filename,
                'images/gallery/' . $filename,
                'images/galleries/' . $filename,
                'gallery/' . $filename,
                'galleries/' . $filename,
            ];
        }

        foreach ($candidates as $path) {
            try {
                if (Storage::disk('public')->exists($path)) {
                    return Storage::disk('public')->url($path);
                }
            } catch (\Throwable $e) {
                // Continue to public asset fallback.
            }

            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return asset('images/profile/default.png');
    }
}
