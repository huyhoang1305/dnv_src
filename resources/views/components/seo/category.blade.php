<?php

use App\Models\Category;
use App\Models\Setting;
use Livewire\Component;

new class extends Component {
    public $setting;
    public $slug = 'de-xuat';
    public $title = null;
    public $category = null;

    public function mount($slug = 'de-xuat', $title = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug ?? 'de-xuat';
        $this->title = $title;

        if ($this->slug && !in_array($this->slug, ['de-xuat', 'all', '', 'danh-muc'])) {
            $this->category = Category::where('slug', $this->slug)->first();
        }
    }
};
?>

<div>
    @push('seo')
        @php
            $catSeo = $setting->interface_settings['products'] ?? $setting->interface_settings['category'] ?? [];
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $isRoot = in_array($slug, ['de-xuat', 'all', '', 'danh-muc']) && !$category;

            if ($category) {
                $displayName = $category->name;
                $customSeoTitle = (!empty($category->seo_title) && !str_contains($category->seo_title, 'An Sinh Xã Hội'))
                    ? $category->seo_title
                    : null;
                $customSeoDesc = (!empty($category->seo_description) && !str_contains($category->seo_description, 'An Sinh Xã Hội'))
                    ? $category->seo_description
                    : null;

                $metaTitle = $customSeoTitle ?: ($displayName . ' | ' . $siteName);
                $metaDesc = $customSeoDesc ?: (!empty($category->description)
                    ? $category->description
                    : ('Mua ' . $displayName . ' chính hãng, chất lượng cao, nguồn gốc minh bạch tại ' . $siteName . '. Đặt hàng tiện lợi, giao hàng nhanh chóng.'));
                $metaKeywords = !empty($category->seo_keywords)
                    ? $category->seo_keywords
                    : ($displayName . ', ' . $siteName . ', DNV, dnv1.biz');
                $metaImage = !empty($category->image_url)
                    ? (curator_url($category->image_url, 1200, 630) ?: asset('storage/logo/seo-dnv.jpg'))
                    : asset('storage/logo/seo-dnv.jpg');
            } else {
                $displayName = $title ?: 'Danh Mục Sản Phẩm';
                $metaTitle = ($isRoot && !empty($catSeo['meta_title']) && !str_contains($catSeo['meta_title'], 'An Sinh Xã Hội'))
                    ? $catSeo['meta_title']
                    : ($displayName . ' | ' . $siteName);
                $metaDesc = ($isRoot && !empty($catSeo['meta_description']))
                    ? $catSeo['meta_description']
                    : ('Khám phá toàn bộ danh mục sản phẩm chính hãng, chất lượng cao tại ' . $siteName . '.');
                $metaKeywords = !empty($catSeo['meta_keywords'])
                    ? $catSeo['meta_keywords']
                    : ($displayName . ', ' . $siteName . ', DNV, dnv1.biz');
                $metaImage = !empty($catSeo['meta_image'])
                    ? curator_url($catSeo['meta_image'], 1200, 630)
                    : asset('storage/logo/seo-dnv.jpg');
            }
            $hasProducts = true;
            if ($category) {
                $childIds = $category->children()->pluck('id')->toArray();
                $catIds = array_merge([$category->id], $childIds);
                $hasProducts = \App\Models\Product::sellable()
                    ->where(function ($q) use ($catIds) {
                        $q->whereIn('category_id', $catIds)
                          ->orWhereHas('categories', function ($cq) use ($catIds) {
                              $cq->whereIn('categories.id', $catIds);
                          });
                    })
                    ->exists();
            }
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDesc }}">
        <meta name="keywords" content="{{ $metaKeywords }}">
        @if ($hasProducts)
            <meta name="robots" content="index, follow">
        @else
            <meta name="robots" content="noindex, follow">
        @endif
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
