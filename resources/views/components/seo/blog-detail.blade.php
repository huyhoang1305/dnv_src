<?php

use App\Models\Content;
use App\Models\Setting;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component {
    public $setting;
    public $slug;
    public $article;

    public function mount($slug = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug;
        $this->article = $slug
            ? Content::with('contentCategory')->where('slug', $slug)->first()
            : null;
    }
};
?>

<div>
    @push('seo')
        @php
            $siteName = $setting->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT';
            $title = !empty($article?->seo_title) ? $article->seo_title : ($article?->title ?? 'Thông tin hoạt động');
            $metaTitle = $title . ' | ' . $siteName;

            $rawDesc = !empty($article?->seo_description)
                ? $article->seo_description
                : ($article?->summary ?: strip_tags($article?->body ?? ''));
            $metaDesc = !empty(trim($rawDesc))
                ? Str::limit(trim(preg_replace('/\s+/', ' ', $rawDesc)), 160)
                : ($title . ' - Tin tức và thông tin hoạt động tại ' . $siteName . '.');

            $category = $article?->contentCategory?->name ?? 'Tin tức';
            $image = ($article && !empty($article->image_url))
                ? (curator_url($article->image_url, 1200, 630) ?: asset('storage/logo/seo-dnv.jpg'))
                : asset('storage/logo/seo-dnv.jpg');
            $metaKeywords = !empty($article?->seo_keywords)
                ? $article->seo_keywords
                : ($title . ', ' . $category . ', ' . $siteName . ', DNV');

            $blogSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $title,
                'image' => $image,
                'description' => $metaDesc,
                'articleSection' => $category,
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => request()->url(),
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
        <meta property="og:type" content="article">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:image" content="{{ $image }}">
        <meta property="og:image:alt" content="{{ $title }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:locale" content="vi_VN">
        <meta property="article:section" content="{{ $category }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDesc }}">
        <meta name="twitter:image" content="{{ $image }}">

        <!-- Schema.org BlogPosting JSON-LD -->
        <script type="application/ld+json">
        {!! json_encode($blogSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
</div>
