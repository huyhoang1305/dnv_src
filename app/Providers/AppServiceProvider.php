<?php

namespace App\Providers;

use App\Models\Setting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cấu hình toàn cục cho CuratorPicker trong toàn bộ trang Admin Panel
        CuratorPicker::configureUsing(function (CuratorPicker $picker): void {
            // Tự động kích hoạt live() để giữ liên kết Livewire DOM khi nằm trong Repeater
            $picker->live();
        });

        // Cấu hình toàn cục cho RichEditor: tăng giới hạn file đính kèm lên 50MB để tránh chặn trước khi nén
        \Filament\Forms\Components\RichEditor::configureUsing(function (\Filament\Forms\Components\RichEditor $richEditor): void {
            $richEditor->fileAttachmentsMaxSize(51200);
        });

        // Tự động nén ảnh client-side xuống dưới 1MB trước khi upload cho RichEditor và Curator
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): \Illuminate\Contracts\View\View => view('filament.components.rich-editor-image-compressor')
        );

        try {
            // Chia sẻ biến $setting cho toàn bộ các màn hình Blade (Layout, SEO, v.v...)
            $setting = Setting::first();
            ViewFacade::share('setting', $setting);
        } catch (\Exception $e) {
            // Bỏ qua nếu database chưa chạy migration
        }
    }
}
