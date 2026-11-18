<?php

use Livewire\Component;
use App\Models\Setting;

new class extends Component {
    public $setting;

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
    }
};
?>

<div>
    @push('seo')
        @php
            $blogSeo = $setting->interface_settings['blog'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = !empty($blogSeo['meta_title']) ? $blogSeo['meta_title'] : ('Blog & Mẹo Hay Sống Khỏe - ' . $siteName);
            $metaDesc = !empty($blogSeo['meta_description']) ? $blogSeo['meta_description'] : 'Khám phá các bài viết cẩm nang dinh dưỡng, thông tin hoạt động và dự án an sinh xã hội từ Doanh Nghiệp Việt (DNV).';
            $metaKeywords = !empty($blogSeo['meta_keywords']) ? $blogSeo['meta_keywords'] : 'blog an sinh, thông tin dnv, doanh nghiệp việt, cẩm nang dinh dưỡng, thực phẩm an sinh xã hội, dnv1.biz';
            $metaImage = !empty($blogSeo['meta_image']) ? curator_url($blogSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDesc }}">
        <meta name="keywords" content="{{ $metaKeywords }}">
        <meta name="robots" content="index, follow">
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
