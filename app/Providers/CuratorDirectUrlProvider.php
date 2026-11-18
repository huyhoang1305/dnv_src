<?php

namespace App\Providers;

use Awcodes\Curator\Concerns\UrlProvider;
use Illuminate\Support\Facades\Storage;

class CuratorDirectUrlProvider implements UrlProvider
{
    public static function getThumbnailUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    public static function getMediumUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    public static function getLargeUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
