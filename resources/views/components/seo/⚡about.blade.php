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
            $aboutSeo = $setting->interface_settings['about'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = !empty($aboutSeo['meta_title']) ? $aboutSeo['meta_title'] : ('Về Chúng Tôi - ' . $siteName);
            $metaDesc = !empty($aboutSeo['meta_description']) ? $aboutSeo['meta_description'] : 'Hồ sơ năng lực, hành trình xuất khẩu nông sản sang Hoa Kỳ và dự án Thực phẩm An Sinh Xã Hội của Công ty Cổ phần Đầu tư và Xúc tiến Thương mại Doanh nghiệp Việt (DNV).';
            $metaKeywords = !empty($aboutSeo['meta_keywords']) ? $aboutSeo['meta_keywords'] : 'Giới thiệu DNV, Doanh Nghiệp Việt, nông sản xuất khẩu Mỹ, thực phẩm an sinh xã hội, DNV Food, dnv1.biz';
            $metaImage = !empty($aboutSeo['meta_image']) ? curator_url($aboutSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
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
