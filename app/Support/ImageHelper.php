<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('stit_image_url')) {
    /**
     * Resolve application images from storage/app/public/images.
     * Images are exposed through the Laravel /media endpoint, so the site
     * does not depend on a public/storage symlink.
     */
    function stit_image_url(?string $path, string $fallback = 'images/placeholders/news-placeholder.svg'): string
    {
        $path = trim((string) $path);

        $normalize = static function (string $value): string {
            $value = trim(str_replace('\\', '/', $value));

            // Accept full URLs that point back to this application's media/storage
            // endpoints, while rejecting unrelated template/CDN image URLs.
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $parsed = parse_url($value);
                $value = (string) ($parsed['path'] ?? '');
            }

            $value = ltrim($value, '/');

            foreach (['media/', 'storage/', 'public/'] as $prefix) {
                if (str_starts_with($value, $prefix)) {
                    $value = substr($value, strlen($prefix));
                }
            }

            return ltrim($value, '/');
        };

        $path = $normalize($path);

        // Application image records are normally stored as images/foo.jpg.
        // For legacy bare filenames, also try images/<filename>.
        $candidates = [];
        if ($path !== '') {
            $candidates[] = $path;
            if (! str_starts_with($path, 'images/')) {
                $candidates[] = 'images/' . $path;
            }
        }

        foreach (array_unique($candidates) as $candidate) {
            if (str_starts_with($candidate, 'images/') && Storage::disk('public')->exists($candidate)) {
                return url('/media/' . ltrim($candidate, '/'));
            }
        }

        $fallback = $normalize($fallback);
        if (! str_starts_with($fallback, 'images/')) {
            $fallback = 'images/' . $fallback;
        }

        return Storage::disk('public')->exists($fallback)
            ? url('/media/' . ltrim($fallback, '/'))
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
        return stit_storage_image_url(
            'images/profile',
            $filename,
            'images/placeholders/profile-placeholder.svg'
        );
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
