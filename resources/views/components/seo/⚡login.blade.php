<?php

use Livewire\Component;
use App\Models\Setting;

new class extends Component {
    public $setting;
    public $title;

    public function mount($title = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->title = $title;
    }
};
?>

<div>
    @push('seo')
        @php
            $loginSeo = $setting->interface_settings['login'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = !empty($loginSeo['meta_title']) ? $loginSeo['meta_title'] : (($title ?: (auth()->check() ? 'Quản Lý Tài Khoản & Đơn Hàng' : 'Đăng Nhập / Đăng Ký Tài Khoản')) . ' - ' . $siteName);
            $metaDesc = !empty($loginSeo['meta_description']) ? $loginSeo['meta_description'] : ('Trang tài khoản khách hàng ' . $siteName . '. Quản lý thông tin cá nhân, tra cứu lịch sử mua hàng và theo dõi tình trạng giao hàng nhanh chóng.');
            $metaKeywords = !empty($loginSeo['meta_keywords']) ? $loginSeo['meta_keywords'] : 'tài khoản, đăng nhập, đơn hàng, DOANH NGHIỆP VIỆT, dnv1.biz';
            $metaImage = !empty($loginSeo['meta_image']) ? curator_url($loginSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDesc }}">
        <meta name="robots" content="noindex, follow">
        <meta name="author" content="{{ $siteName }}">
        <link rel="canonical" href="{{ request()->url() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDesc }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ request()->url() }}">
        @if ($metaImage)
            <meta property="og:image" content="{{ $metaImage }}">
            <meta property="og:image:alt" content="{{ $metaTitle }}">
        @endif
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:locale" content="vi_VN">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDesc }}">
        @if ($metaImage)
            <meta name="twitter:image" content="{{ $metaImage }}">
        @endif
    @endpush
</div>
