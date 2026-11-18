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
            $cartSeo = $setting->interface_settings['cart'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $metaTitle = !empty($cartSeo['meta_title']) ? $cartSeo['meta_title'] : ('Giỏ Hàng Của Bạn - ' . $siteName);
            $metaDesc = !empty($cartSeo['meta_description']) ? $cartSeo['meta_description'] : ('Xem lại danh sách sản phẩm thực phẩm organic và tiêu dùng đã chọn trong giỏ hàng tại ' . $siteName . '. Miễn phí vận chuyển và giao hỏa tốc 1 giờ.');
            $metaKeywords = !empty($cartSeo['meta_keywords']) ? $cartSeo['meta_keywords'] : 'giỏ hàng, thanh toán, mua sắm online, DOANH NGHIỆP VIỆT, dnv1.biz';
            $metaImage = !empty($cartSeo['meta_image']) ? curator_url($cartSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');
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
