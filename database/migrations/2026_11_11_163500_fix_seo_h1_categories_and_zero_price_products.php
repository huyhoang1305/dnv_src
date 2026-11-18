<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix typo "DOAN H NGHIỆP VIỆT" -> "DOANH NGHIỆP VIỆT" in settings
        if (Schema::hasTable('settings')) {
            foreach (Setting::all() as $setting) {
                $interface = $setting->interface_settings ?? [];
                $general = $setting->general_settings ?? [];
                $changed = false;

                array_walk_recursive($interface, function (&$val) use (&$changed) {
                    if (is_string($val) && preg_match('/DOAN\s+H\s+NGHIỆP/iu', $val)) {
                        $val = preg_replace('/DOAN\s+H\s+NGHIỆP/iu', 'DOANH NGHIỆP', $val);
                        $changed = true;
                    }
                });

                array_walk_recursive($general, function (&$val) use (&$changed) {
                    if (is_string($val) && preg_match('/DOAN\s+H\s+NGHIỆP/iu', $val)) {
                        $val = preg_replace('/DOAN\s+H\s+NGHIỆP/iu', 'DOANH NGHIỆP', $val);
                        $changed = true;
                    }
                });

                if ($changed) {
                    $setting->interface_settings = $interface;
                    $setting->general_settings = $general;
                    $setting->save();
                }
            }
            Cache::forget('system_settings');
        }

        // 2. Deactivate any existing 0đ products so customers cannot see or order 0đ items
        if (Schema::hasTable('products')) {
            Product::where(function ($q) {
                $q->whereNull('base_price')->orWhere('base_price', '<=', 0);
            })->update(['is_active' => false]);
        }

        // 3. Ensure all categories have clean Vietnamese seo_title matching their accented name
        if (Schema::hasTable('categories')) {
            foreach (Category::all() as $cat) {
                if (empty($cat->seo_title) || str_contains($cat->seo_title, 'An Sinh Xã Hội')) {
                    $cat->seo_title = $cat->name . ' | DOANH NGHIỆP VIỆT';
                    $cat->save();
                }
            }
        }
    }

    public function down(): void
    {
        // No-op data cleanup migration
    }
};
