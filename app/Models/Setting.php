<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'general_settings' => 'json',
        'interface_settings' => 'json',
    ];

    private static ?self $systemSettingCache = null;

    /**
     * Lấy dữ liệu Setting có sẵn bộ nhớ đệm (Cache RAM / File)
     * Tránh việc gọi SQL 'select * from settings limit 1' lặp đi lặp lại nhiều lần trong 1 request.
     */
    public static function getSystemSetting(): ?self
    {
        if (self::$systemSettingCache !== null) {
            return self::$systemSettingCache;
        }

        try {
            self::$systemSettingCache = Cache::remember('site_system_setting', 86400, function () {
                return rescue(fn () => static::first(), null, false);
            });
        } catch (\Throwable $e) {
            self::$systemSettingCache = rescue(fn () => static::first(), null, false);
        }

        return self::$systemSettingCache;
    }

    /**
     * Xóa cache Setting khi Admin chỉnh sửa cấu hình
     */
    public static function clearSystemSettingCache(): void
    {
        self::$systemSettingCache = null;
        Cache::forget('site_system_setting');
    }

    protected static function booted()
    {
        static::saved(function () {
            static::clearSystemSettingCache();
        });
        static::deleted(function () {
            static::clearSystemSettingCache();
        });
    }
}
