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
     * Uploaded profile photos live on the public filesystem disk
     * (storage/app/public). Shared hosting may not provide a public
     * storage symlink, so prefer the application's /media endpoint when
     * the file exists on the disk. The physical /storage mirror remains
     * as a fallback for deployments that copy public assets there.
     */
    function stit_profile_image_url(?string $filename): string
    {
        $filename = $filename ? ltrim($filename, '/') : '';

        if ($filename && filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        if ($filename) {
            $basename = basename($filename);
            $candidates = [
                'images/profile/' . $basename,
                'images/' . $basename,
            ];

            foreach ($candidates as $path) {
                try {
                    if (Storage::disk('public')->exists($path)) {
                        return url('/media/' . ltrim($path, '/'));
                    }
                } catch (\Throwable $e) {
                    // Continue with the physical ByetHost mirror.
                }

                if (is_file(storage_path($path))) {
                    return '/storage/' . $path . '?v=' . @filemtime(storage_path($path));
                }

                if (is_file(public_path($path))) {
                    return asset($path) . '?v=' . @filemtime(public_path($path));
                }
            }
        }

        foreach ([
            'images/profile/default.jpg',
            'images/profile/default.png',
            'images/default.jpg',
            'images/default.png',
        ] as $fallback) {
            try {
                if (Storage::disk('public')->exists($fallback)) {
                    return url('/media/' . ltrim($fallback, '/'));
                }
            } catch (\Throwable $e) {
                // Continue to the next fallback.
            }

            if (is_file(storage_path($fallback))) {
                return '/storage/' . $fallback . '?v=' . @filemtime(storage_path($fallback));
            }

            if (is_file(public_path($fallback))) {
                return asset($fallback) . '?v=' . @filemtime(public_path($fallback));
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
