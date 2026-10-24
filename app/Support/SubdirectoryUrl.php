<?php

namespace App\Support;

final class SubdirectoryUrl
{
    public static function asset(string $path, string $appUrl, ?string $assetUrl = null): string
    {
        if ($path === '' || preg_match('~^(?:[a-z][a-z0-9+.-]*:|//|[?#])~i', $path)) {
            return $path;
        }

        $basePath = trim((string) parse_url($appUrl, PHP_URL_PATH), '/');
        $path = ltrim($path, '/');
        if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath) + 1);
        }

        $root = $assetUrl && $assetUrl !== '/' ? rtrim($assetUrl, '/') : rtrim($appUrl, '/');
        if (str_starts_with($root, '/') && ! str_starts_with($root, '//')) {
            $parts = parse_url($appUrl);
            $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'localhost');
            $origin .= isset($parts['port']) ? ':' . $parts['port'] : '';
            $root = $origin . $root;
        }

        return $root . '/' . $path;
    }
}
