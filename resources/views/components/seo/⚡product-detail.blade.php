<?php

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component {
    public $setting;
    public $slug;
    public $product;

    public function mount($slug = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug;
        $this->product = $slug
            ? Product::with(['brand', 'category', 'categories'])->sellable()->where('slug', $slug)->first()
            : null;
    }
};
?>

<div>
    @push('seo')
        @php
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $prodName = $product?->name ?? 'Chi tiết sản phẩm';
            $seoTitle = !empty($product?->seo_title) ? $product->seo_title : $prodName;
            $metaTitle = $seoTitle . ' | ' . $siteName;

            $rawDesc = !empty($product?->seo_description)
                ? $product->seo_description
                : strip_tags($product?->description ?: ($product?->content ?? ''));
            $cleanDesc = trim(preg_replace('/\s+/', ' ', $rawDesc));

            $actualPrice = (float) (($product?->sale_price && $product->sale_price > 0)
                ? $product->sale_price
                : ($product?->base_price ?? 0));
            $formattedPrice = $actualPrice > 0 ? (number_format($actualPrice, 0, ',', '.') . 'đ') : '';

            $metaDesc = !empty($cleanDesc)
                ? Str::limit($cleanDesc, 160)
                : ('Đặt mua ' . $prodName . ' chính hãng, chất lượng cao tại ' . $siteName . ($formattedPrice ? ' với giá ' . $formattedPrice : '') . '.');

            $productBrand = $product?->brand?->name ?? 'DNV';
            $primaryCategoryName = ($product?->category ?? $product?->categories?->first())?->name;
            $productImage = ($product && !empty($product->image_url))
                ? (curator_url($product->image_url, 800, 800) ?: asset('storage/logo/seo-dnv.jpg'))
                : asset('storage/logo/seo-dnv.jpg');

            $metaKeywords = !empty($product?->seo_keywords)
                ? $product->seo_keywords
                : implode(', ', array_filter([$prodName, $productBrand, $primaryCategoryName, $siteName]));

            $productSchema = [
                '@context' => 'https://schema.org/',
                '@type' => 'Product',
                'name' => $prodName,
                'image' => $productImage,
                'description' => $metaDesc,
                'sku' => $product?->sku ?: (string) ($product?->id ?? ''),
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $productBrand,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => request()->url(),
                    'priceCurrency' => 'VND',
                    'price' => $actualPrice,
                    'availability' => 'https://schema.org/InStock',
                    'seller' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
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
        <meta property="og:type" content="product">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:image" content="{{ $productImage }}">
        <meta property="og:image:alt" content="{{ $prodName }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:locale" content="vi_VN">

        <!-- Product Meta -->
        <meta property="product:brand" content="{{ $productBrand }}">
        @if ($actualPrice > 0)
            <meta property="product:price:amount" content="{{ $actualPrice }}">
            <meta property="product:price:currency" content="VND">
        @endif
        <meta property="product:availability" content="in stock">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDesc }}">
        <meta name="twitter:image" content="{{ $productImage }}">

        <!-- Schema.org Product JSON-LD -->
        <script type="application/ld+json">
        {!! json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
</div>
