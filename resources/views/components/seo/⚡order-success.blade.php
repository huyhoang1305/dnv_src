<?php

use Livewire\Component;
use App\Models\Setting;

new class extends Component {
    public $setting;
    public $orderCode;

    public function mount($orderCode = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->orderCode = $orderCode;
    }
};
?>

<div>
    @push('seo')
        @php
            $osSeo = $setting->interface_settings['order_success'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = ($orderCode ? ('Đơn hàng #' . $orderCode . ' - ') : '') . (!empty($osSeo['meta_title']) ? $osSeo['meta_title'] : ('Đặt Hàng Thành Công - ' . $siteName));
            $metaDesc = !empty($osSeo['meta_description']) ? $osSeo['meta_description'] : ('Cảm ơn quý khách đã mua sắm tại ' . $siteName . '. Đơn hàng đang được đóng gói và giao hỏa tốc đến địa chỉ của bạn.');
            $metaKeywords = !empty($osSeo['meta_keywords']) ? $osSeo['meta_keywords'] : 'đơn hàng thành công, mua sắm online, DOANH NGHIỆP VIỆT, dnv1.biz';
            $metaImage = !empty($osSeo['meta_image']) ? curator_url($osSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
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
