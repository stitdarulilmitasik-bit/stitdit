<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('stit_image_url')) {
    /**
     * Resolve all application images from storage/app/public/images.
     * The public web URL is exposed only through /storage after storage:link.
     */
    function stit_image_url(?string $path, string $fallback = 'images/placeholders/news-placeholder.svg'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return url('/media/' . ltrim($fallback, '/'));
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        // Normalize common values that were historically stored in the database.
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }
        if (str_starts_with($path, 'images/')) {
            // Keep all application image files under storage/app/public/images.
            $path = ltrim($path, '/');
        }

        // Never make the public site depend on an old/template image host.
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return url('/media/' . ltrim($fallback, '/'));
        }

        if (Storage::disk('public')->exists($path)) {
        return asset('storage/' . $path);
        }

        // Do not fall back to public/images. All application images are stored
        // in storage/app/public/images and exposed through /storage.
        $fallback = str_replace('public/', '', ltrim($fallback, '/'));
        if (str_starts_with($fallback, 'storage/')) {
            $fallback = substr($fallback, 8);
        }
        return Storage::disk('public')->exists($fallback)
            ? asset('storage/' . $fallback)
            : url('/media/images/placeholders/news-placeholder.svg');
    }
}

if (! function_exists('stit_storage_image_url')) {
    function stit_storage_image_url(?string $directory, ?string $filename, string $fallback = 'images/placeholders/news-placeholder.svg'): string
    {
        $directory = trim((string) $directory, '/');
        $filename = trim((string) $filename);

        return stit_image_url(
            $filename === '' ? null : ($directory !== '' ? $directory . '/' . ltrim($filename, '/') : ltrim($filename, '/')),
            $fallback
        );
    }
}

if (! function_exists('stit_profile_image_url')) {
    function stit_profile_image_url(?string $filename): string
    {
        $fallback = 'images/placeholders/profile-placeholder.svg';
        return stit_storage_image_url('images/profile', $filename, $fallback);
    }
}

if (! function_exists('stit_news_image_url')) {
    function stit_news_image_url(?string $filename): string
    {
        return stit_storage_image_url('images/berita', $filename);
    }
}

if (! function_exists('stit_announcement_image_url')) {
    function stit_announcement_image_url(?string $filename): string
    {
        return stit_storage_image_url('images/pengumuman', $filename);
    }
}

if (! function_exists('stit_gallery_image_url')) {
    function stit_gallery_image_url(?string $filename): string
    {
        return stit_storage_image_url('images/galeri', $filename);
    }
}

if (! function_exists('stit_gallery_photo_url')) {
    function stit_gallery_photo_url(?string $filename): string
    {
        return stit_storage_image_url('images/galeri/foto', $filename);
    }
}
