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
            $homeSeo = $setting->interface_settings['home'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT | Dự Án Thực Phẩm An Sinh Xã Hội';
            $metaTitle = !empty($homeSeo['meta_title']) ? $homeSeo['meta_title'] : $siteName;
            $metaDesc = !empty($homeSeo['meta_description']) ? $homeSeo['meta_description'] : 'Công ty CP Đầu tư và Xúc tiến Thương mại Doanh Nghiệp Việt (DNV) - Dự án Thực phẩm An sinh Xã hội, cung cấp hàng nhu yếu phẩm cho Công đoàn Cơ sở các KCN trên toàn quốc.';
            $metaKeywords = !empty($homeSeo['meta_keywords']) ? $homeSeo['meta_keywords'] : 'doanh nghiệp việt, DNV, Thực phẩm An sinh Xã hội, nông sản việt, dnv1.biz';
            $metaImage = !empty($homeSeo['meta_image']) ? curator_url($homeSeo['meta_image'], 1200, 630) : asset('storage/logo/seo-dnv.jpg');

            $schemaData = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'Organization',
                        '@id' => url('/') . '/#organization',
                        'name' => 'DOANH NGHIỆP VIỆT',
                        'legalName' => 'Công ty Cổ phần Đầu tư & Xúc tiến Thương mại Doanh Nghiệp Việt',
                        'url' => url('/'),
                        'logo' => $metaImage,
                        'telephone' => '0903.856.861',
                        'email' => 'ecom@asxh.org.vn',
                        'address' => [
                            '@type' => 'PostalAddress',
                            'streetAddress' => 'TP. Hồ Chí Minh',
                            'addressLocality' => 'Hồ Chí Minh',
                            'addressCountry' => 'VN',
                        ],
                        'sameAs' => [
                            'https://www.facebook.com/profile.php?id=61596505266495',
                        ],
                    ],
                    [
                        '@type' => 'WebSite',
                        '@id' => url('/') . '/#website',
                        'url' => url('/'),
                        'name' => $siteName,
                        'description' => $metaDesc,
                        'publisher' => [
                            '@id' => url('/') . '/#organization',
                        ],
                        'potentialAction' => [
                            '@type' => 'SearchAction',
                            'target' => url('/danh-muc') . '?search={search_term_string}',
                            'query-input' => 'required name=search_term_string',
                        ],
                    ],
                ],
            ];
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

        <!-- JSON-LD Structured Data: Organization & WebSite -->
        <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
</div>
