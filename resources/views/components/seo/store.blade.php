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
            $storeSeo = $setting->interface_settings['store'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = !empty($storeSeo['meta_title']) ? $storeSeo['meta_title'] : ('Cửa Hàng Thực Phẩm & Tiêu Dùng - ' . $siteName);
            $metaDesc = !empty($storeSeo['meta_description']) ? $storeSeo['meta_description'] : 'Hệ thống kho vận và điểm kết nối phân phối hàng nhu yếu phẩm đến các Khu Công Nghiệp & Cụm Công Nghiệp của Doanh Nghiệp Việt (DNV).';
            $metaKeywords = !empty($storeSeo['meta_keywords']) ? $storeSeo['meta_keywords'] : 'cửa hàng ASXH, điểm cung ứng an sinh xã hội, doanh nghiệp việt, nhu yếu phẩm KCN, dnv1.biz';
            $metaImage = !empty($storeSeo['meta_image']) ? curator_url($storeSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
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
