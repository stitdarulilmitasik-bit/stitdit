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
     */
    function stit_profile_image_url(?string $filename): string
    {
        $filename = $filename ? ltrim($filename, '/') : 'default.png';

        // Keep already absolute/external URLs untouched.
        if (filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        $candidates = [
            'images/profile/' . $filename,
            'images/profiles/' . $filename,
            'images/user/' . $filename,
            'images/users/' . $filename,
            'profile/' . $filename,
            'profiles/' . $filename,
            'users/' . $filename,
        ];

        foreach ($candidates as $path) {
            try {
                if (Storage::disk('public')->exists($path)) {
                    return Storage::disk('public')->url($path);
                }
            } catch (\Throwable $e) {
                // Continue to the next candidate.
            }

            if (file_exists(public_path($path))) {
                return asset($path);
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
