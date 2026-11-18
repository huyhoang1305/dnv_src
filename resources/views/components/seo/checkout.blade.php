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
            $checkoutSeo = $setting->interface_settings['checkout'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = !empty($checkoutSeo['meta_title']) ? $checkoutSeo['meta_title'] : ('Đặt Hàng & Thanh Toán An Toàn - ' . $siteName);
            $metaDesc = !empty($checkoutSeo['meta_description']) ? $checkoutSeo['meta_description'] : ('Hoàn tất đơn hàng thực phẩm sạch, nhập khẩu cao cấp an toàn, nhanh chóng tại ' . $siteName . '. Cam kết giao hỏa tốc 1 giờ tại TP.HCM.');
            $metaKeywords = !empty($checkoutSeo['meta_keywords']) ? $checkoutSeo['meta_keywords'] : 'thanh toán, đặt hàng, hoàn tất đơn, DOANH NGHIỆP VIỆT, dnv1.biz';
            $metaImage = !empty($checkoutSeo['meta_image']) ? curator_url($checkoutSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDesc }}">
        <meta name="robots" content="noindex, nofollow">
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
