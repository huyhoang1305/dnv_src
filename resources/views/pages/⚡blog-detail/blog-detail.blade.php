<div>
    <!-- Page SEO Meta Tags -->
    <livewire:seo.blog-detail :slug="$slug" :title="$article?->title" :description="$article?->summary" :image="curator_url($article?->image_url)" :category="$article?->contentCategory?->name ?? 'Tin tức'" />

    @php
        $blogSettings = $setting?->interface_settings['blog'] ?? [];
        $authorName = !empty($blogSettings['author_name'])
            ? $blogSettings['author_name']
            : ($setting?->interface_settings['header']['brand_name'] ?? $setting?->general_settings['site_name'] ?? 'Ban Biên Tập DNV');
        $authorRole = !empty($blogSettings['author_role'])
            ? $blogSettings['author_role']
            : ($setting?->interface_settings['header']['slogan'] ?? 'Cẩm Nang & Tin Tức');
        $authorBio = !empty($blogSettings['author_bio'])
            ? $blogSettings['author_bio']
            : ($setting?->interface_settings['footer']['intro_text'] ?? '');
        $articleTags = !empty($article?->seo_keywords)
            ? array_filter(array_map('trim', explode(',', $article->seo_keywords)))
            : (!empty($article?->contentCategory?->name) ? [$article->contentCategory->name] : []);
    @endphp

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2.5 pb-1 sm:pt-4 sm:pb-2">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <a href="{{ route('blog') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Blog & Mẹo hay</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-medium min-w-0 flex-1 truncate">{{ $article?->title ?? 'Chi tiết bài viết' }}</span>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 sm:py-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Main Article Column (8 cols) -->
            <article class="lg:col-span-8 bg-white rounded-2xl border border-slate-100 p-4 sm:p-8 shadow-xs space-y-4 sm:space-y-6">

                <!-- Article Header -->
                <div class="space-y-2.5 sm:space-y-3 pb-4 sm:pb-5 border-b border-slate-100">
                    @if ($article?->contentCategory)
                        <span class="inline-block text-[10.5px] sm:text-[11px] font-bold uppercase tracking-wider text-[#4d835f] bg-[#eef7f1] px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full">
                            {{ $article->contentCategory->name }}
                        </span>
                    @endif
                    <h1 class="text-lg sm:text-2xl md:text-3xl font-bold text-slate-900 leading-snug">
                        {{ $article?->title }}
                    </h1>
                    <div class="flex items-center gap-4 text-xs text-slate-500 flex-wrap pt-1 font-normal">
                        @if ($article?->created_at)
                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar text-slate-400"></i> {{ $article->created_at->format('d/m/Y') }}</span>
                            <span class="text-slate-300">•</span>
                        @endif
                        <span class="flex items-center gap-1.5"><i class="fa-regular fa-user text-slate-400"></i> <strong class="text-slate-700">{{ $authorName }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span class="flex items-center gap-1.5"><i class="fa-regular fa-eye text-slate-400"></i> {{ number_format($article?->view_count ?? 0) }} lượt xem</span>
                    </div>
                </div>

                @if (!empty($article?->summary))
                <!-- Summary Quote Callout -->
                <div class="bg-[#f7faf8] border-l-4 border-[#4d835f] p-4 sm:p-5 rounded-r-xl text-slate-600 text-xs sm:text-sm italic leading-relaxed">
                    "{{ $article->summary }}"
                </div>
                @endif

                <!-- Featured Image -->
                @if (!empty($article?->image_url))
                <div class="rounded-2xl overflow-hidden shadow-xs">
                    <img 
                        src="{{ get_curator_media_url($article->image_url) ?: curator_url($article->image_url, 800, 500) }}" 
                        alt="{{ $article->title }}" 
                        class="w-full h-auto max-h-[460px] object-cover"
                    >
                    <p class="text-[11px] text-slate-400 text-center italic py-2">
                        {{ $article->title }}
                    </p>
                </div>
                @endif

                <!-- Article Body Typography -->
                <div class="prose prose-slate max-w-none text-slate-700 text-xs sm:text-sm leading-relaxed space-y-4 font-normal">
                    {!! $article?->body !!}
                </div>

                <!-- Social Share & Tags -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if (count($articleTags) > 0)
                            <span class="text-xs font-semibold text-slate-700">Chủ đề:</span>
                            @foreach ($articleTags as $tag)
                                <a href="{{ route('blog', ['search' => $tag]) }}" wire:navigate.hover class="text-[11px] bg-slate-100 hover:bg-emerald-50 hover:text-[#4d835f] text-slate-600 px-2.5 py-1 rounded-md transition">
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-700">Chia sẻ:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs hover:opacity-90">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        @if (!empty($setting?->interface_settings['footer']['social_zalo']))
                            <a href="{{ $setting->interface_settings['footer']['social_zalo'] }}" target="_blank" class="w-7 h-7 rounded-full overflow-hidden hover:opacity-90 flex items-center justify-center shadow-2xs" title="Chia sẻ qua Zalo">
                                <img src="{{ asset('images/zalo-icon.webp') }}" alt="Zalo" class="w-full h-full object-cover">
                            </a>
                        @endif
                    </div>
                </div>

            </article>

            <!-- Sidebar Column (4 cols) -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Author / Editorial Profile Card -->
                @if (!empty($authorName))
                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-xs text-center space-y-3">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-[#4d835f] flex items-center justify-center text-2xl font-bold mx-auto border-2 border-white shadow-xs uppercase">
                        {{ mb_substr($authorName, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">{{ $authorName }}</h3>
                        @if (!empty($authorRole))
                            <p class="text-xs text-slate-400 font-normal">{{ $authorRole }}</p>
                        @endif
                    </div>
                    @if (!empty($authorBio))
                        <p class="text-xs text-slate-500 font-normal leading-relaxed">
                            {{ $authorBio }}
                        </p>
                    @endif
                </div>
                @endif

                <!-- Related Articles in Sidebar -->
                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-book-open text-[#4d835f] text-sm"></i>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Bài Viết Cùng Chủ Đề</h3>
                    </div>

                    <div class="space-y-3.5">
                        @forelse ($relatedArticles as $rel)
                            <a href="{{ route('blog.detail', $rel->slug) }}" wire:navigate.hover class="flex items-start gap-3 group">
                                <img 
                                    src="{{ curator_url($rel->image_url, 150, 100) ?: asset('storage/roots/blogs/blog_1.png') }}" 
                                    alt="{{ $rel->title }}" 
                                    class="w-16 h-12 object-cover rounded-lg shrink-0 group-hover:opacity-90 transition"
                                >
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-slate-800 leading-snug group-hover:text-[#4d835f] transition-colors line-clamp-2">
                                        {{ $rel->title }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $rel->created_at?->format('d/m/Y') }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-400">Không có bài viết liên quan.</p>
                        @endforelse
                    </div>
                </div>

            </aside>

        </div>
    </div>
</div>
