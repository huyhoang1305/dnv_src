<div x-data="{ 
    filterModalOpen: false, 
    activeFilterTab: 'brand', 
    cartToast: false, 
    toastMsg: '',
    showToast(msg) {
        this.toastMsg = msg;
        this.cartToast = true;
        setTimeout(() => this.cartToast = false, 2500);
    }
}">
    <!-- Page SEO Meta Tags -->
    <livewire:seo.category :slug="$slug" :title="$this->currentCategory?->name" />

    <!-- Hero Banners Section -->
    @php
        $home = $setting?->interface_settings['home'] ?? [];
        $sliderBanners = array_values(array_filter($home['slider_banners'] ?? [], fn($b) => !empty($b['image'])));
        $side1Img = get_curator_media_url($home['side_banner_1_image'] ?? null);
        $side2Img = get_curator_media_url($home['side_banner_2_image'] ?? null);
        $hasSlider = count($sliderBanners) > 0;
        $hasSide = $side1Img || $side2Img;

        $catObj = $this->currentCategory;
        if ($catObj) {
            $categoryH1 = $catObj->name;
            $categorySub = !empty($catObj->description)
                ? $catObj->description
                : ('Khám phá các sản phẩm thuộc danh mục ' . $catObj->name . ' chất lượng, nguồn gốc rõ ràng tại Doanh Nghiệp Việt (DNV).');
        } elseif (!empty(trim($q ?? ''))) {
            $categoryH1 = 'Kết quả tìm kiếm: "' . trim($q) . '"';
            $categorySub = 'Danh sách sản phẩm phù hợp với từ khóa tìm kiếm của bạn tại Doanh Nghiệp Việt (DNV).';
        } else {
            $categoryH1 = 'Tất Cả Sản Phẩm';
            $categorySub = 'Khám phá toàn bộ danh mục sản phẩm chính hãng, đa dạng ngành hàng tại Doanh Nghiệp Việt (DNV).';
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2.5 sm:pt-6">
        <div class="mb-3 sm:mb-5 text-left border-b border-slate-100 pb-2.5 sm:pb-3">
            <h1 class="text-[15px] sm:text-lg md:text-[21px] font-bold text-[#991B1B] tracking-tight leading-snug">
                {{ $categoryH1 }}
            </h1>
            @if (!empty($categorySub))
                <p class="text-[11.5px] sm:text-xs md:text-[13.5px] text-[#52606d] mt-1 font-normal leading-relaxed">
                    {{ $categorySub }}
                </p>
            @endif
        </div>

        @if ($hasSlider || $hasSide)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-5 mb-2 sm:mb-4">
                <!-- Left Slider (8 or 12 Cols) -->
                @if ($hasSlider)
                    <div 
                        x-data="{ 
                            activeSlide: 0, 
                            totalSlides: {{ count($sliderBanners) }},
                            next() { this.activeSlide = (this.activeSlide + 1) % this.totalSlides; },
                            prev() { this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides; },
                            init() { if (this.totalSlides > 1) { setInterval(() => this.next(), 4500); } }
                        }"
                        class="{{ $hasSide ? 'lg:col-span-8' : 'lg:col-span-12' }} relative rounded-2xl overflow-hidden shadow-sm bg-white border border-slate-200/70 aspect-[16/9] sm:aspect-[16/8] lg:aspect-auto lg:h-[380px] group"
                    >
                        <!-- Slides Container -->
                        <div class="relative w-full h-full">
                            @foreach ($sliderBanners as $idx => $slide)
                                @php
                                    $slideImg = get_curator_media_url($slide['image']);
                                @endphp
                                @if ($slideImg)
                                    <div 
                                        x-show="activeSlide === {{ $idx }}"
                                        x-transition:enter="transition-opacity ease-out duration-500"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition-opacity ease-in duration-300"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="absolute inset-0 w-full h-full"
                                        @if ($idx > 0) style="display: none;" @endif
                                    >
                                        @if (!empty($slide['link']))
                                            <a href="{{ $slide['link'] }}" wire:navigate.hover class="block w-full h-full">
                                                <img 
                                                    src="{{ $slideImg }}" 
                                                    alt="{{ $slide['title'] ?? 'Banner THV' }}" 
                                                    class="w-full h-full object-cover object-center"
                                                >
                                            </a>
                                        @else
                                            <img 
                                                src="{{ $slideImg }}" 
                                                alt="{{ $slide['title'] ?? 'Banner THV' }}" 
                                                class="w-full h-full object-cover object-center"
                                            >
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Prev/Next Controls -->
                        @if (count($sliderBanners) > 1)
                            <button 
                                @click="prev()" 
                                type="button" 
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/70 hover:bg-white text-slate-800 flex items-center justify-center transition opacity-0 group-hover:opacity-100 cursor-pointer shadow-md backdrop-blur-xs z-10"
                                aria-label="Previous Slide"
                            >
                                <i class="fa-solid fa-chevron-left text-sm"></i>
                            </button>
                            <button 
                                @click="next()" 
                                type="button" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/70 hover:bg-white text-slate-800 flex items-center justify-center transition opacity-0 group-hover:opacity-100 cursor-pointer shadow-md backdrop-blur-xs z-10"
                                aria-label="Next Slide"
                            >
                                <i class="fa-solid fa-chevron-right text-sm"></i>
                            </button>

                            <!-- Pagination Dots -->
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-10 bg-black/20 backdrop-blur-xs px-2.5 py-1 rounded-full">
                                @foreach ($sliderBanners as $idx => $slide)
                                    <button 
                                        @click="activeSlide = {{ $idx }}" 
                                        type="button" 
                                        class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                                        :class="activeSlide === {{ $idx }} ? 'w-6 bg-white' : 'w-1.5 bg-white/50 hover:bg-white/75'"
                                        aria-label="Slide {{ $idx + 1 }}"
                                    ></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Right 2 Side Banners (4 Cols, exact same height as Left Slider) -->
                @if ($hasSide)
                    <div class="{{ $hasSlider ? 'lg:col-span-4' : 'lg:col-span-12' }} grid grid-cols-2 lg:grid-cols-1 {{ ($side1Img && $side2Img) ? 'lg:grid-rows-2' : 'lg:grid-rows-1' }} gap-4 lg:gap-4 lg:h-[380px]">
                        @if ($side1Img)
                            <a 
                                href="{{ $home['side_banner_1_link'] ?? '#' }}" 
                                wire:navigate.hover
                                class="block w-full h-full min-h-0 rounded-2xl overflow-hidden shadow-sm border border-slate-200/70 group hover:shadow-md transition-all relative aspect-[16/9] lg:aspect-auto"
                            >
                                <img 
                                    src="{{ $side1Img }}" 
                                    alt="Banner phụ 1" 
                                    class="w-full h-full object-cover object-center group-hover:scale-103 transition-transform duration-300"
                                >
                            </a>
                        @endif

                        @if ($side2Img)
                            <a 
                                href="{{ $home['side_banner_2_link'] ?? '#' }}" 
                                wire:navigate.hover
                                class="block w-full h-full min-h-0 rounded-2xl overflow-hidden shadow-sm border border-slate-200/70 group hover:shadow-md transition-all relative aspect-[16/9] lg:aspect-auto"
                            >
                                <img 
                                    src="{{ $side2Img }}" 
                                    alt="Banner phụ 2" 
                                    class="w-full h-full object-cover object-center group-hover:scale-103 transition-transform duration-300"
                                >
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1.5 sm:py-2">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            @if ($catObj)
                <a href="{{ route('category') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Danh mục</a>
                <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
                <span class="text-slate-800 font-medium min-w-0 truncate">{{ $catObj->name }}</span>
            @else
                <span class="text-slate-800 font-medium min-w-0 truncate">{{ $categoryH1 }}</span>
            @endif
        </nav>
    </div>

    @php
        $productSettings = $setting?->interface_settings['products'] ?? [];
    @endphp

    <!-- Toast Notification -->
    <div 
        x-show="cartToast" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="fixed bottom-6 right-6 z-50 bg-[#1b3524] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-emerald-600/40"
        style="display: none;"
    >
        <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center text-white text-xs shrink-0">
            <i class="fa-solid fa-check"></i>
        </div>
        <div>
            <div class="text-xs font-semibold" x-text="toastMsg"></div>
            @if(!empty($productSettings['toast_message_hint']))
            <div class="text-[10px] text-emerald-200">{{ $productSettings['toast_message_hint'] }}</div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 space-y-4">

        <!-- Active Filter Pills Bar (Giá) -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
            <!-- Price Sorting -->
            @if(!isset($productSettings['enable_price_sort']) || $productSettings['enable_price_sort'])
            <div class="relative" x-data="{ sortOpen: false }">
                <button 
                    @click="sortOpen = !sortOpen"
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition-all cursor-pointer {{ $sort !== 'default' ? 'bg-red-50 text-[#CE171F] border-[#CE171F]' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300' }}"
                >
                    <span>
                        Giá: 
                        @if ($sort === 'low_to_high') Thấp đến cao 
                        @elseif ($sort === 'high_to_low') Cao đến thấp 
                        @else Mặc định @endif
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform" :class="sortOpen ? 'rotate-180' : ''"></i>
                </button>

                <div 
                    x-show="sortOpen" 
                    @click.away="sortOpen = false" 
                    x-transition 
                    class="absolute top-full left-0 mt-1.5 w-44 bg-white border border-slate-200 rounded-xl shadow-lg p-2 z-40 space-y-1"
                    style="display: none;"
                >
                    <button wire:click="setSort('default')" @click="sortOpen = false" class="w-full text-left px-3 py-1.5 text-xs rounded-lg hover:bg-red-50 hover:text-[#CE171F] transition {{ $sort === 'default' ? 'font-bold text-[#CE171F]' : 'text-slate-700' }}">Mặc định</button>
                    <button wire:click="setSort('low_to_high')" @click="sortOpen = false" class="w-full text-left px-3 py-1.5 text-xs rounded-lg hover:bg-red-50 hover:text-[#CE171F] transition {{ $sort === 'low_to_high' ? 'font-bold text-[#CE171F]' : 'text-slate-700' }}">Giá thấp đến cao</button>
                    <button wire:click="setSort('high_to_low')" @click="sortOpen = false" class="w-full text-left px-3 py-1.5 text-xs rounded-lg hover:bg-red-50 hover:text-[#CE171F] transition {{ $sort === 'high_to_low' ? 'font-bold text-[#CE171F]' : 'text-slate-700' }}">Giá cao đến thấp</button>
                </div>
            </div>
            @endif

            <span class="text-xs text-slate-500 font-medium whitespace-nowrap px-2.5 py-1 bg-white border border-slate-200 rounded-full">
                {{ $this->totalProducts }} sản phẩm
            </span>

            @if ($brand !== 'all' || $sort !== 'default' || $subcat !== 'all')
                <button 
                    wire:click="$set('brand', 'all'); $set('sort', 'default'); $set('subcat', 'all');" 
                    class="text-[11px] text-rose-600 hover:text-rose-700 font-semibold px-2 py-1 flex items-center gap-1 cursor-pointer"
                >
                    <i class="fa-solid fa-rotate-left text-[10px]"></i> Xóa lọc
                </button>
            @endif
        </div>

        <!-- Shopee-style DANH MỤC Section (matching Image 2) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mb-3" x-data="{
            scrollLeft() {
                this.$refs.catSlider.scrollBy({ left: -280, behavior: 'smooth' });
            },
            scrollRight() {
                this.$refs.catSlider.scrollBy({ left: 280, behavior: 'smooth' });
            }
        }">
            <!-- Header Bar -->
            <div class="px-4 sm:px-6 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 tracking-wider uppercase flex items-center gap-2">
                    <span>DANH MỤC</span>
                </h2>
                <div class="flex items-center gap-1">
                    <button 
                        @click="scrollLeft()" 
                        type="button" 
                        class="w-7 h-7 rounded-full border border-slate-200 hover:border-[#CE171F] text-slate-500 hover:text-[#CE171F] flex items-center justify-center transition cursor-pointer"
                        aria-label="Previous categories"
                    >
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <button 
                        @click="scrollRight()" 
                        type="button" 
                        class="w-7 h-7 rounded-full border border-slate-200 hover:border-[#CE171F] text-slate-500 hover:text-[#CE171F] flex items-center justify-center transition cursor-pointer"
                        aria-label="Next categories"
                    >
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>

            @php
                $colorMap = [
                    'banh' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
                    'keo' => ['bg' => 'bg-pink-50', 'text' => 'text-pink-500', 'border' => 'border-pink-200'],
                    'nuoc-uong' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-500', 'border' => 'border-sky-200'],
                    'nong-san' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
                    'thuy-hai-san' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-600', 'border' => 'border-cyan-200'],
                    'ngu-coc' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'border' => 'border-yellow-200'],
                    'trai-cay-say' => ['bg' => 'bg-lime-50', 'text' => 'text-lime-600', 'border' => 'border-lime-200'],
                    'nguyen-lieu-nau-an-lam-banh' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
                    'nguyen-lieu-pha-che' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'border' => 'border-teal-200'],
                    'thuc-pham-cong-nghe' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
                    'nui-mi-bun-mien' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-500', 'border' => 'border-orange-200'],
                    'gia-vi' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-500', 'border' => 'border-rose-200'],
                    'my-pham' => ['bg' => 'bg-fuchsia-50', 'text' => 'text-fuchsia-500', 'border' => 'border-fuchsia-200'],
                    'hoa-my-pham' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-500', 'border' => 'border-indigo-200'],
                    'do-dung-gia-dinh' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'border' => 'border-teal-200'],
                ];
                $isAllActive = in_array($slug, ['all', 'de-xuat', 'danh-muc', '']);
            @endphp

            <!-- 2-Row Horizontal Scroll Grid matching Image 2 -->
            <div class="relative group/cat">
                <div 
                    x-ref="catSlider"
                    class="grid grid-rows-2 grid-flow-col auto-cols-[100px] sm:auto-cols-[115px] md:auto-cols-[125px] lg:auto-cols-[minmax(0,1fr)] overflow-x-auto no-scrollbar scroll-smooth p-3 sm:p-4 gap-2 sm:gap-3"
                >
                    <!-- All Products Item -->
                    <a 
                        href="{{ route('category') }}" 
                        wire:navigate.hover
                        class="flex flex-col items-center justify-start p-2 rounded-xl transition-all duration-200 group/item border {{ $isAllActive ? 'border-[#CE171F]/40 bg-red-50/50 shadow-2xs' : 'border-transparent hover:border-slate-200 hover:bg-slate-50/80' }}"
                    >
                        <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-full {{ $isAllActive ? 'bg-[#CE171F] text-white ring-2 ring-red-200' : 'bg-red-50 text-[#CE171F] group-hover/item:scale-105' }} flex items-center justify-center transition-all duration-200 shadow-2xs border border-red-100 shrink-0">
                            <i class="fa-solid fa-layer-group text-lg sm:text-xl"></i>
                        </div>
                        <span class="text-[11px] sm:text-xs text-center line-clamp-2 mt-2 leading-tight transition-colors {{ $isAllActive ? 'text-[#CE171F] font-bold' : 'text-slate-700 font-medium group-hover/item:text-[#CE171F]' }}">
                            Tất cả sản phẩm
                        </span>
                    </a>

                    <!-- Category Items -->
                    @foreach ($this->categories as $c)
                        @php
                            $isCatActive = ($slug === $c->slug);
                            $cStyle = $colorMap[$c->slug] ?? ['bg' => 'bg-slate-50', 'text' => 'text-[#CE171F]', 'border' => 'border-slate-200'];
                        @endphp
                        <a 
                            href="{{ route('category', $c->slug) }}" 
                            wire:navigate.hover
                            class="flex flex-col items-center justify-start p-2 rounded-xl transition-all duration-200 group/item border {{ $isCatActive ? 'border-[#CE171F]/40 bg-red-50/50 shadow-2xs' : 'border-transparent hover:border-slate-200 hover:bg-slate-50/80' }}"
                        >
                            <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-full {{ $isCatActive ? 'bg-white ring-2 ring-[#CE171F] shadow-sm' : $cStyle['bg'] . ' group-hover/item:scale-105' }} flex items-center justify-center transition-all duration-200 shadow-2xs border {{ $isCatActive ? 'border-[#CE171F]' : $cStyle['border'] }} shrink-0 overflow-hidden">
                                @php
                                    $catImgUrl = !empty($c->image_url) ? (get_curator_media_url($c->image_url) ?: curator_url($c->image_url, 150, 150)) : null;
                                @endphp
                                @if ($catImgUrl)
                                    <img src="{{ $catImgUrl }}" alt="{{ $c->name }}" class="w-full h-full object-cover rounded-full group-hover/item:scale-110 transition-transform duration-200">
                                @else
                                    <i class="{{ $c->icon ?: 'fa-solid fa-box' }} {{ $isCatActive ? 'text-[#CE171F]' : $cStyle['text'] }} text-lg sm:text-xl"></i>
                                @endif
                            </div>
                            <span class="text-[11px] sm:text-xs text-center line-clamp-2 mt-2 leading-tight transition-colors {{ $isCatActive ? 'text-[#CE171F] font-bold' : 'text-slate-700 font-medium group-hover/item:text-[#CE171F]' }}">
                                {{ $c->name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Subcategories Bar (if current category has children) -->
            @if ($this->currentCategory && $this->currentCategory->children->count() > 0)
                <div class="bg-slate-50/80 px-4 sm:px-6 py-2.5 border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs">
                    <span class="text-[11px] font-bold text-slate-400 shrink-0 mr-1">Nhóm hàng:</span>
                    <button 
                        wire:click="setSubcat('all')" 
                        type="button" 
                        class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $subcat === 'all' ? 'bg-[#CE171F] text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}"
                    >
                        Tất cả
                    </button>
                    @foreach ($this->currentCategory->children as $sub)
                        <button 
                            wire:click="setSubcat('{{ $sub->slug }}')" 
                            type="button" 
                            class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition cursor-pointer {{ $subcat === $sub->slug ? 'bg-[#CE171F] text-white shadow-2xs font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}"
                        >
                            {{ $sub->name }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Listing Grid (Hiển thị ngang 6 sản phẩm, dọc vô cùng) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3">
            @forelse ($this->products as $p)
                <div wire:key="cat-prod-{{ $p->id }}" class="bg-white rounded-2xl border border-slate-100 p-2.5 hover:border-red-200 hover:shadow-md transition-all duration-200 flex flex-col justify-between group relative">
                    <!-- Top Image Area: Full block without padding! -->
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden mb-2 flex items-center justify-center bg-slate-50/50">
                        <a href="{{ route('product.detail', $p->slug) }}" wire:navigate.hover class="w-full h-full flex items-center justify-center p-0">
                            <img 
                                src="{{ curator_url($p->image_url, 400, 400) ?: asset('storage/logo/favicon-dnv.png') }}" 
                                alt="{{ $p->name }}" 
                                onerror="this.onerror=null; this.src='{{ asset('storage/logo/favicon-dnv.png') }}';"
                                class="w-full h-full object-contain p-0 group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            >
                        </a>

                        @if ($p->badge)
                            <span class="absolute top-2 left-2 text-[9.5px] font-bold px-2 py-0.5 rounded-md {{ $p->badge_color ?? 'bg-[#CE171F]' }} text-white shadow-2xs">
                                {{ $p->badge }}
                            </span>
                        @endif

                        <!-- Add to Cart Plus Button -->
                        <button 
                            wire:click="addToCart({{ $p->id }})"
                            type="button" 
                            class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/95 hover:bg-[#CE171F] active:scale-95 text-slate-700 hover:text-white border border-slate-200 shadow-2xs flex items-center justify-center text-xs transition-all cursor-pointer"
                            aria-label="Thêm vào giỏ"
                            title="Thêm vào giỏ hàng"
                        >
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <!-- Meta Brand & Unit -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium">
                            <span class="truncate max-w-[90px]">{{ $p->brand?->name ?? 'DNV' }}</span>
                            <span>•</span>
                            <span>{{ $p->unit ?? 'Cái' }}</span>
                        </div>

                        <!-- Product Name -->
                        <h3 class="text-[11.5px] font-semibold text-slate-800 line-clamp-2 h-8 leading-snug group-hover:text-[#CE171F] transition-colors">
                            <a href="{{ route('product.detail', $p->slug) }}" wire:navigate.hover>
                                {{ $p->name }}
                            </a>
                        </h3>

                        <!-- Price & Specification -->
                        <div class="pt-1">
                            <div class="text-xs sm:text-sm font-bold text-[#CE171F] tracking-tight">
                                {{ number_format((float) $p->sale_price > 0 ? $p->sale_price : $p->base_price, 0, ',', '.') }}đ
                            </div>
                            <div class="text-[9.5px] text-slate-400 font-normal truncate">
                                {{ $p->weight ?? $p->unit ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-100 p-8 space-y-3">
                    <i class="fa-solid fa-box-open text-4xl text-slate-300"></i>
                    <h3 class="text-base font-semibold text-slate-700">Chưa có sản phẩm phù hợp với bộ lọc</h3>
                    <p class="text-xs text-slate-500">Hãy thử đổi thương hiệu hoặc chuyển sang danh mục khác.</p>
                    <button 
                        wire:click="$set('brand', 'all'); $set('sort', 'default'); $set('subcat', 'all');" 
                        class="text-xs font-semibold text-[#CE171F] hover:underline"
                    >
                        Đặt lại bộ lọc
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Infinite Scroll Sentinel / Load More -->
        @if ($this->products->isNotEmpty())
            @if ($this->hasMore)
                <div 
                    x-data="{
                        init() {
                            const observer = new IntersectionObserver((entries) => {
                                if (entries[0].isIntersecting) {
                                    $wire.loadMore();
                                }
                            }, { rootMargin: '350px' });
                            observer.observe(this.$el);
                        }
                    }"
                    class="py-10 flex flex-col items-center justify-center gap-3"
                >
                    <div wire:loading wire:target="loadMore" class="inline-flex items-center gap-2.5 text-xs font-semibold text-slate-600 bg-white px-5 py-2.5 rounded-full border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-spinner fa-spin text-[#CE171F]"></i>
                        <span>Đang tải thêm sản phẩm...</span>
                    </div>

                    <button 
                        wire:loading.remove
                        wire:target="loadMore"
                        wire:click="loadMore" 
                        type="button"
                        class="px-6 py-2.5 rounded-full bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 shadow-2xs hover:border-[#CE171F] hover:text-[#CE171F] transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <i class="fa-solid fa-arrow-down text-[11px]"></i>
                        <span>Tải thêm sản phẩm</span>
                    </button>
                </div>
            @else
                <div class="py-10 text-center text-xs text-slate-400 font-medium">
                    <span class="inline-block px-4 py-1.5 bg-slate-100 rounded-full">Đã hiển thị toàn bộ {{ $this->totalProducts }} sản phẩm</span>
                </div>
            @endif
        @endif

    </div>

    <!-- Floating Toast Notification -->
    <div 
        x-data="{ show: false, msg: '', timer: null }"
        x-on:cat-cart-toast.window="msg = $event.detail.message; show = true; clearTimeout(timer); timer = setTimeout(() => { show = false }, 3200)"
        x-show="show"
        style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 right-6 z-50 bg-[#181112] text-white px-5 py-3.5 rounded-2xl shadow-xl border border-red-500/30 flex items-center gap-3 max-w-sm"
    >
        <div class="w-8 h-8 rounded-full bg-red-500/20 text-[#FFC700] flex items-center justify-center shrink-0">
            <i class="fa-solid fa-check text-sm"></i>
        </div>
        <div class="flex-1">
            <div class="text-xs font-bold line-clamp-1" x-text="msg"></div>
            <a href="{{ route('cart') }}" class="text-[11px] text-amber-300 underline font-medium hover:text-white mt-0.5 inline-block">
                Xem giỏ hàng ngay &rarr;
            </a>
        </div>
        <button @click="show = false" type="button" class="text-slate-400 hover:text-white cursor-pointer text-xs">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>
