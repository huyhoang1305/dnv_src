<div>
    <!-- Page SEO Meta Tags -->
    <livewire:seo.blog />

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2.5 pb-1 sm:pt-4 sm:pb-2">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-medium min-w-0 truncate">Blog & Mẹo hay</span>
        </nav>
    </div>

    @php
        $blog = $setting?->interface_settings['blog'] ?? [];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 sm:py-4 space-y-5 sm:space-y-6">

        <!-- Category Filter Carousel (Scrollable) -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2 border-b border-slate-100">
            <button 
                wire:click="setCategory('all')"
                type="button" 
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all border {{ $category === 'all' ? 'bg-emerald-50 text-[#4d835f] border-[#4d835f] shadow-2xs' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:text-slate-800' }}"
            >
                <span>Tất cả</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $category === 'all' ? 'bg-[#4d835f] text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ \App\Models\Content::where('is_active', true)->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'))->count() }}
                </span>
            </button>

            @foreach ($this->categories as $cat)
                <button 
                    wire:click="setCategory('{{ $cat->slug }}')"
                    type="button" 
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all border {{ $category === $cat->slug ? 'bg-emerald-50 text-[#4d835f] border-[#4d835f] shadow-2xs' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:text-slate-800' }}"
                >
                    <span>{{ $cat->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $category === $cat->slug ? 'bg-[#4d835f] text-white' : 'bg-slate-100 text-slate-500' }}">
                        {{ $cat->contents_count }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Main Blog Layout: 2 Columns on Desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: Articles Grid (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @forelse ($this->articles as $article)
                        <article class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-xs hover:shadow-md hover:border-emerald-200 transition-all duration-200 flex flex-col group">
                            <!-- Thumbnail -->
                            <a href="{{ route('blog.detail', $article->slug) }}" wire:navigate.hover class="relative block aspect-16/10 overflow-hidden bg-slate-100">
                                <img 
                                    src="{{ curator_url($article->image_url, 600, 400) ?: asset('storage/roots/blogs/blog_1.png') }}" 
                                    alt="{{ $article->title }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                                @if ($article->contentCategory)
                                <div class="absolute top-2.5 left-2.5 bg-slate-900/60 backdrop-blur-xs text-white text-[10px] font-medium px-2 py-0.5 rounded-md flex items-center gap-1">
                                    <i class="fa-regular fa-folder text-[9px] text-emerald-400"></i>
                                    <span>{{ $article->contentCategory->name }}</span>
                                </div>
                                @endif
                            </a>

                            <!-- Card Body -->
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-2">
                                    <!-- Meta -->
                                    <div class="flex items-center gap-3 text-[11px] text-slate-500 font-normal">
                                        <span><i class="fa-regular fa-calendar mr-1"></i> {{ $article->created_at?->format('d/m/Y') ?? '29/09/2026' }}</span>
                                        <span>•</span>
                                        <span class="text-[#4d835f] font-semibold"><i class="fa-regular fa-folder mr-1"></i> {{ $article->contentCategory?->name ?? 'Tin tức' }}</span>
                                    </div>

                                    <!-- Title -->
                                    <h2 class="text-sm sm:text-base font-bold text-slate-800 leading-snug group-hover:text-[#4d835f] transition-colors line-clamp-2">
                                        <a href="{{ route('blog.detail', $article->slug) }}" wire:navigate.hover>
                                            {{ $article->title }}
                                        </a>
                                    </h2>

                                    <!-- Excerpt -->
                                    <p class="text-xs text-slate-500 font-normal leading-relaxed line-clamp-3">
                                        {{ $article->summary ?? Str::limit(strip_tags($article->body), 150) }}
                                    </p>
                                </div>

                                <!-- Read More Link -->
                                <div class="pt-2 border-t border-slate-50">
                                    <a 
                                        href="{{ route('blog.detail', $article->slug) }}" 
                                        wire:navigate.hover
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#4d835f] hover:text-[#2d5d3f] transition-colors"
                                    >
                                        <span>Đọc tiếp</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-slate-100 p-8 space-y-3">
                            <i class="fa-regular fa-newspaper text-4xl text-slate-300"></i>
                            <h3 class="text-base font-semibold text-slate-700">Không tìm thấy bài viết nào</h3>
                            <p class="text-xs text-slate-500">Vui lòng thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác.</p>
                            <button wire:click="setCategory('all')" class="text-xs font-semibold text-[#4d835f] hover:underline">
                                Xem tất cả bài viết
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Dynamic Pagination -->
                @if ($this->articles->hasPages())
                    <div class="flex items-center justify-center gap-2 pt-4">
                        <button
                            wire:click="previousPage"
                            @disabled($this->articles->onFirstPage())
                            type="button"
                            class="w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-600 disabled:text-slate-300 disabled:cursor-not-allowed flex items-center justify-center text-xs hover:border-[#4d835f] hover:text-[#4d835f] transition-colors cursor-pointer"
                        >
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        </button>

                        @for ($page = 1; $page <= $this->articles->lastPage(); $page++)
                            <button
                                wire:click="gotoPage({{ $page }})"
                                type="button"
                                class="w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors cursor-pointer {{ $page === $this->articles->currentPage() ? 'bg-[#4d835f] text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:border-[#4d835f] hover:text-[#4d835f]' }}"
                            >
                                {{ $page }}
                            </button>
                        @endfor

                        <button
                            wire:click="nextPage"
                            @disabled(!$this->articles->hasMorePages())
                            type="button"
                            class="w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-600 disabled:text-slate-300 disabled:cursor-not-allowed flex items-center justify-center text-xs hover:border-[#4d835f] hover:text-[#4d835f] transition-colors cursor-pointer"
                        >
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Right Column: Sidebar (4 cols) -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Search Widget -->
                <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-xs">
                    <div class="relative">
                        <input 
                            wire:model.live.debounce.300ms="search"
                            type="text" 
                            placeholder="Tìm kiếm bài viết..."
                            class="w-full bg-[#f8faf9] border border-slate-200 rounded-xl py-2 pl-3.5 pr-9 text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#4d835f] font-normal"
                        >
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Trending / Popular Articles -->
                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-fire text-rose-500 text-sm"></i>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ !empty($blog['popular_title']) ? $blog['popular_title'] : 'Bài Viết Nổi Bật' }}</h3>
                    </div>

                    <div class="space-y-3.5">
                        @foreach ($this->popularArticles as $pop)
                            <a href="{{ route('blog.detail', $pop->slug) }}" wire:navigate.hover class="flex items-start gap-3 group">
                                <img 
                                    src="{{ curator_url($pop->image_url, 150, 100) ?: asset('storage/roots/blogs/blog_1.png') }}" 
                                    alt="{{ $pop->title }}" 
                                    class="w-16 h-12 object-cover rounded-lg shrink-0 group-hover:opacity-90 transition"
                                >
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-slate-800 leading-snug group-hover:text-[#4d835f] transition-colors line-clamp-2">
                                        {{ $pop->title }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $pop->created_at?->format('d/m/Y') ?? '' }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            </aside>

        </div>

    </div>
</div>
