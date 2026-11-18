<div 
    x-data="{ 
        toastMessage: '', 
        toastOpen: false, 
        savedVouchers: @js($savedVoucherCode ? [$savedVoucherCode => true] : (object) []),
        showToast(msg) {
            this.toastMessage = msg;
            this.toastOpen = true;
            setTimeout(() => { this.toastOpen = false; }, 2800);
        },
        saveVoucher(code, title) {
            this.savedVouchers[code] = true;
            $wire.saveVoucher(code);
            this.showToast('Đã lưu mã &ldquo;' + title + '&rdquo; thành công!');
        }
    }" 
    class="min-h-screen bg-[#f8faf9] pb-16 selection:bg-red-100 selection:text-[#CE171F]"
>
    <!-- Page SEO Meta Tags -->
    <livewire:seo.home />

    <!-- Toast Notification -->
    <div 
        x-show="toastOpen" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed top-20 right-5 z-50 bg-[#181112] text-white px-5 py-3 rounded-2xl shadow-2xl border border-red-600/40 flex items-center gap-3"
        style="display: none;"
    >
        <span class="w-7 h-7 rounded-full bg-red-500/20 text-[#FFC700] flex items-center justify-center">
            <i class="fa-solid fa-check text-sm"></i>
        </span>
        <span class="text-sm font-semibold" x-html="toastMessage"></span>
    </div>

    <!-- Main Container -->
    @php
        $home = $setting?->interface_settings['home'] ?? [];

        // Slider & Side banners
        $sliderBanners = array_values(array_filter($home['slider_banners'] ?? [], fn($b) => !empty($b['image'])));
        $side1Img = get_curator_media_url($home['side_banner_1_image'] ?? null);
        $side2Img = get_curator_media_url($home['side_banner_2_image'] ?? null);
        $hasSlider = count($sliderBanners) > 0;
        $hasSide = $side1Img || $side2Img;

        // Banners Khối (Mặc định tắt banner nền để giao diện thoáng nhẹ; có thể bật trong Admin -> Banners Khối)
        $flashSaleBg = !empty($home['show_flash_sale_bg']) ? get_curator_media_url($home['flash_sale_bg'] ?? null) : null;
        $newArrivalsBg = !empty($home['show_new_arrivals_bg']) ? get_curator_media_url($home['new_arrivals_bg'] ?? null) : null;
        $blogsBg = !empty($home['show_blogs_bg']) ? get_curator_media_url($home['blogs_bg'] ?? null) : null;

        // About THV
        $hasAboutBadge = !empty($home['about_badge']);
        $hasAboutTitle = !empty($home['about_title']);
        $hasAboutDesc = !empty($home['about_description']);
        $aboutFeatures = array_values(array_filter($home['about_features'] ?? [], fn($f) => !empty($f['title']) || !empty($f['description'])));
        $hasAboutBottom = !empty($home['about_bottom_address']) || !empty($home['about_bottom_hotline']) || !empty($home['about_bottom_hours']);
        $hasAboutSection = ($home['about_active'] ?? true) && ($hasAboutBadge || $hasAboutTitle || $hasAboutDesc || count($aboutFeatures) > 0 || $hasAboutBottom);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 sm:pt-7">

        <!-- 1. SEO H1 Heading Bar -->
        @php
            $cleanHomeH1 = !empty($home['h1_title'])
                ? preg_replace('/\bDOAN\s+H\s+NGHIỆP/iu', 'DOANH NGHIỆP', $home['h1_title'])
                : null;
        @endphp
        @if (!empty($cleanHomeH1) || !empty($home['h1_subtitle']))
            <div class="mb-3 sm:mb-5 text-left border-b border-slate-100 pb-2.5 sm:pb-3">
                @if (!empty($cleanHomeH1))
                    <h1 class="text-[15px] sm:text-lg md:text-[21px] font-bold text-[#991B1B] tracking-tight leading-snug">
                        {{ $cleanHomeH1 }}
                    </h1>
                @endif
                @if (!empty($home['h1_subtitle']))
                    <p class="text-[11.5px] sm:text-xs md:text-[13.5px] text-[#52606d] mt-1 font-normal leading-relaxed">
                        {{ $home['h1_subtitle'] }}
                    </p>
                @endif
            </div>
        @endif

        <!-- 2. Hero Banners Section (Slider + 2 Side Banners) -->
        @if ($hasSlider || $hasSide)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-5 mb-8 sm:mb-10">
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

                        @if (count($sliderBanners) > 1)
                            <!-- Navigation Controls -->
                            <button 
                                @click="prev()" 
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-slate-800 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-md hover:scale-105 cursor-pointer"
                                aria-label="Previous Slide"
                            >
                                <i class="fa-solid fa-chevron-left text-sm"></i>
                            </button>
                            <button 
                                @click="next()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-slate-800 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-md hover:scale-105 cursor-pointer"
                                aria-label="Next Slide"
                            >
                                <i class="fa-solid fa-chevron-right text-sm"></i>
                            </button>

                            <!-- Indicator Dots -->
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-black/30 backdrop-blur-xs px-3 py-1.5 rounded-full z-10">
                                <template x-for="i in totalSlides" :key="i">
                                    <button 
                                        @click="activeSlide = i - 1"
                                        class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                        :class="activeSlide === (i - 1) ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'"
                                    ></button>
                                </template>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Right Sub-banners (4 Cols, 2 Rows, exact same height as Left Slider) -->
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

        <!-- 2.5. VOUCHER & MÃ GIẢM GIÁ DNV (Exclusive Vouchers Bar) -->
        @if (!empty($home['show_vouchers']) && !empty($coupons) && $coupons->isNotEmpty())
            <section id="dnv-vouchers-section" class="mb-8 sm:mb-10">
                <div class="bg-gradient-to-r from-[#991B1B] via-[#CE171F] to-[#b0131a] rounded-2xl p-3 sm:p-5 shadow-sm text-white">
                    <div class="flex flex-wrap items-center justify-between pb-3 mb-3 border-b border-white/20 gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-amber-300 text-sm shadow-inner shrink-0">
                                <i class="fa-solid fa-tags"></i>
                            </span>
                            <div>
                                <h2 class="text-sm sm:text-base font-black tracking-wide uppercase flex items-center gap-2">
                                    <span>{{ $home['vouchers_title'] ?? 'MÃ GIẢM GIÁ & VOUCHER DNV' }}</span>
                                    @if (!empty($home['vouchers_badge']))
                                        <span class="text-[9.5px] bg-amber-400 text-red-950 font-black px-2 py-0.5 rounded-full uppercase tracking-wider hidden sm:inline-block">{{ $home['vouchers_badge'] }}</span>
                                    @endif
                                </h2>
                                @if (!empty($home['vouchers_subtitle']))
                                    <p class="text-[11px] sm:text-xs text-red-100 font-normal">
                                        {{ $home['vouchers_subtitle'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('cart') }}" wire:navigate.hover class="text-xs font-semibold text-amber-200 hover:text-white flex items-center gap-1 transition">
                            <span>{{ $home['vouchers_link_text'] ?? 'Xem Giỏ Hàng & Voucher' }}</span>
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>

                    <!-- 6 Horizontal Voucher Cards Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
                        @foreach ($coupons as $c)
                            <div class="bg-white rounded-xl p-2.5 sm:p-3 text-slate-800 flex flex-col justify-between shadow-xs border border-white/40 hover:shadow-md transition-all group relative overflow-hidden">
                                <!-- Top Ribbon / Badge -->
                                <div class="flex items-center justify-between mb-1.5 gap-1">
                                    <span class="text-[8.5px] sm:text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-red-50 text-[#CE171F] border border-red-100 truncate">
                                        {{ $c->badge ?: 'ƯU ĐÃI' }}
                                    </span>
                                    <span class="text-[10px] font-bold text-amber-600 shrink-0">
                                        {{ $c->code }}
                                    </span>
                                </div>

                                <!-- Voucher AI Image -->
                                <div class="w-full aspect-square max-w-[85px] sm:max-w-[95px] mx-auto my-1 flex items-center justify-center overflow-hidden">
                                    @if (!empty($c->image_url))
                                        <img 
                                            src="{{ curator_url($c->image_url) ?: asset($c->image_url) }}" 
                                            alt="{{ $c->title }}" 
                                            class="w-full h-full object-contain group-hover:scale-108 transition-transform duration-300 drop-shadow-2xs"
                                        >
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-red-50 text-[#CE171F] flex items-center justify-center">
                                            <i class="{{ $c->icon ?: 'fa-solid fa-ticket' }} text-xl"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Title & Condition -->
                                <div class="text-center my-1">
                                    <div class="text-xs font-bold text-slate-900 line-clamp-1 group-hover:text-[#CE171F] transition-colors" title="{{ $c->title }}">
                                        {{ $c->title }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-0.5 line-clamp-1">
                                        {{ $c->description ?: ('Đơn từ ' . number_format($c->min_order_amount, 0, ',', '.') . 'đ') }}
                                    </div>
                                </div>

                                <!-- Save / Applied Button with Alpine.js -->
                                <button 
                                    @click="saveVoucher('{{ $c->code }}', '{{ addslashes($c->title) }}')"
                                    type="button" 
                                    class="w-full mt-1.5 py-1.5 rounded-lg text-[11px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 shadow-2xs"
                                    :class="savedVouchers['{{ $c->code }}'] ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-[#CE171F] text-white hover:bg-[#b0131a] active:scale-98'"
                                >
                                    <template x-if="savedVouchers['{{ $c->code }}']">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-check text-[10px]"></i> Đã lưu
                                        </span>
                                    </template>
                                    <template x-if="!savedVouchers['{{ $c->code }}']">
                                        <span>Lưu mã</span>
                                    </template>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- 3. DNV MALL Section (matching screenshot with DNV branding) -->
        @if ($home['mall_active'] ?? true)
        <section id="dnv-mall-section" class="mb-10 sm:mb-14">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 overflow-hidden">
                <!-- Header matching Mall bar -->
                <div class="flex flex-wrap items-center justify-between px-4 sm:px-6 py-3.5 border-b border-slate-100 gap-y-2">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 md:gap-6 text-xs sm:text-sm">
                        <span class="text-base sm:text-lg font-black text-[#d0011b] tracking-wide uppercase">{{ $home['mall_title'] ?? 'DNV MALL' }}</span>
                        <span class="text-slate-300 hidden sm:inline">|</span>
                        @if (!empty($home['mall_badge_1']))
                        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                            <i class="fa-solid fa-rotate-left text-[#d0011b] text-sm"></i>
                            <span>{{ $home['mall_badge_1'] }}</span>
                        </div>
                        @endif
                        @if (!empty($home['mall_badge_2']))
                        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                            <i class="fa-solid fa-shield-halved text-[#d0011b] text-sm"></i>
                            <span>{{ $home['mall_badge_2'] }}</span>
                        </div>
                        @endif
                        @if (!empty($home['mall_badge_3']))
                        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                            <i class="fa-solid fa-truck-fast text-[#d0011b] text-sm"></i>
                            <span>{{ $home['mall_badge_3'] }}</span>
                        </div>
                        @endif
                    </div>
                    <a href="{{ $home['mall_link_url'] ?? route('category') }}" wire:navigate.hover class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#d0011b] hover:opacity-85 transition group">
                        <span>{{ $home['mall_link_text'] ?? 'Xem Tất Cả' }}</span>
                        <span class="w-4 h-4 rounded-full bg-[#d0011b] text-white flex items-center justify-center text-[9px] group-hover:translate-x-0.5 transition-transform">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    </a>
                </div>

                <!-- Main Body: Left AI Banner + 4x2 Deals Grid -->
                <div class="p-3 sm:p-4">
                    <div class="flex flex-col lg:flex-row items-stretch gap-3 sm:gap-4 relative" x-data="{
                        scrollRight() {
                            this.$refs.dealsGrid?.scrollBy({ left: 320, behavior: 'smooth' });
                        }
                    }">
                        <!-- Left Promotional Banner -->
                        @php
                            $mallBannerImg = !empty($home['mall_banner']) ? (get_curator_media_url($home['mall_banner']) ?: asset($home['mall_banner'])) : asset('images/mall/banner.png');
                        @endphp
                        <div class="w-full lg:w-[320px] xl:w-[350px] shrink-0 rounded-xl overflow-hidden shadow-2xs group relative aspect-square lg:aspect-auto">
                            <a href="{{ $home['mall_banner_link'] ?? route('category') }}" wire:navigate.hover class="block w-full h-full">
                                <img 
                                    src="{{ $mallBannerImg }}" 
                                    alt="Săn Deal Siêu Hot - {{ $home['mall_title'] ?? 'DNV Mall' }}" 
                                    class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300"
                                >
                            </a>
                        </div>

                        <!-- Right 4x2 Products Grid -->
                        @php
                            $mallDealsList = [];
                            if (!empty($home['mall_deals']) && is_array($home['mall_deals']) && count($home['mall_deals']) > 0) {
                                foreach ($home['mall_deals'] as $item) {
                                    $imgUrl = !empty($item['image']) ? (get_curator_media_url($item['image']) ?: asset($item['image'])) : '';
                                    $mallDealsList[] = [
                                        'img' => $imgUrl,
                                        'brand' => $item['brand'] ?? '',
                                        'promo' => $item['promo'] ?? '',
                                        'link' => !empty($item['link']) ? $item['link'] : route('category'),
                                    ];
                                }
                            }
                        @endphp

                        <div class="flex-1 relative">
                            <div 
                                x-ref="dealsGrid"
                                class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 h-full items-stretch"
                            >
                                @foreach ($mallDealsList as $deal)
                                    <a 
                                        href="{{ $deal['link'] }}" 
                                        wire:navigate.hover
                                        class="flex flex-col items-center justify-between p-2 sm:p-2.5 rounded-xl hover:bg-slate-50/90 transition-all duration-200 group border border-transparent hover:border-slate-200/70"
                                    >
                                        <div class="w-full aspect-square max-w-[125px] sm:max-w-[140px] flex items-center justify-center overflow-hidden mb-1.5">
                                            <img 
                                                src="{{ $deal['img'] }}" 
                                                alt="{{ $deal['brand'] }}" 
                                                class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300 drop-shadow-2xs"
                                            >
                                        </div>
                                        <div class="w-full text-center flex flex-col items-center gap-1">
                                            <span class="inline-block px-3 py-0.5 rounded-full bg-white border border-slate-200/80 shadow-2xs text-[10px] sm:text-[11px] font-bold text-slate-800 tracking-wide uppercase group-hover:border-[#d0011b]/40 transition-colors">
                                                {{ $deal['brand'] }}
                                            </span>
                                            <span class="text-xs sm:text-[13px] font-semibold text-[#d0011b] leading-tight line-clamp-1">
                                                {{ $deal['promo'] }}
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <!-- Right Arrow Carousel Button matching screenshot -->
                            <button 
                                @click="scrollRight()"
                                type="button" 
                                class="absolute -right-2 sm:-right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white shadow-md border border-slate-200/90 flex items-center justify-center text-slate-400 hover:text-slate-700 transition cursor-pointer z-10 hidden sm:flex"
                                aria-label="Next deals"
                            >
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- 3.5. DANH MỤC Section (matching Image 2) -->
        @if ($home['categories_active'] ?? true)
        <section id="categories-section" class="mb-10 sm:mb-14">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden" x-data="{
                scrollLeft() {
                    this.$refs.homeCatSlider.scrollBy({ left: -280, behavior: 'smooth' });
                },
                scrollRight() {
                    this.$refs.homeCatSlider.scrollBy({ left: 280, behavior: 'smooth' });
                }
            }">
                <div class="px-4 sm:px-6 py-3.5 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm sm:text-base font-bold text-slate-800 tracking-wider uppercase flex items-center gap-2">
                        <span>{{ $home['categories_title'] ?? 'DANH MỤC' }}</span>
                        @if (!empty($home['categories_subtitle']))
                            <span class="text-xs text-slate-400 font-normal normal-case hidden sm:inline">| {{ $home['categories_subtitle'] }}</span>
                        @endif
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
                @endphp

                <div class="relative group/cat">
                    <div 
                        x-ref="homeCatSlider"
                        class="grid grid-rows-2 grid-flow-col auto-cols-[100px] sm:auto-cols-[115px] md:auto-cols-[125px] lg:auto-cols-[minmax(0,1fr)] overflow-x-auto no-scrollbar scroll-smooth p-3 sm:p-4 gap-2 sm:gap-3"
                    >
                        <a 
                            href="{{ route('category') }}" 
                            wire:navigate.hover
                            class="flex flex-col items-center justify-start p-2 rounded-xl transition-all duration-200 group/item border border-transparent hover:border-slate-200 hover:bg-slate-50/80"
                        >
                            <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-full bg-red-50 text-[#CE171F] flex items-center justify-center transition-all duration-200 group-hover/item:scale-105 shadow-2xs border border-red-100 shrink-0">
                                <i class="fa-solid fa-layer-group text-lg sm:text-xl"></i>
                            </div>
                            <span class="text-[11px] sm:text-xs text-center line-clamp-2 mt-2 leading-tight text-slate-700 font-medium group-hover/item:text-[#CE171F] transition-colors">
                                Tất cả
                            </span>
                        </a>

                        @foreach ($categories as $c)
                            @php
                                $cStyle = $colorMap[$c->slug] ?? ['bg' => 'bg-slate-50', 'text' => 'text-[#CE171F]', 'border' => 'border-slate-200'];
                            @endphp
                            <a 
                                href="{{ route('category', $c->slug) }}" 
                                wire:navigate.hover
                                class="flex flex-col items-center justify-start p-2 rounded-xl transition-all duration-200 group/item border border-transparent hover:border-slate-200 hover:bg-slate-50/80"
                            >
                                <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-full {{ $cStyle['bg'] }} flex items-center justify-center transition-all duration-200 group-hover/item:scale-105 shadow-2xs border {{ $cStyle['border'] }} shrink-0 overflow-hidden">
                                    @php
                                        $catImgUrl = !empty($c->image_url) ? (get_curator_media_url($c->image_url) ?: curator_url($c->image_url, 150, 150)) : null;
                                    @endphp
                                    @if ($catImgUrl)
                                        <img src="{{ $catImgUrl }}" alt="{{ $c->name }}" class="w-full h-full object-cover rounded-full group-hover/item:scale-110 transition-transform duration-200">
                                    @else
                                        <i class="{{ $c->icon ?: 'fa-solid fa-box' }} {{ $cStyle['text'] }} text-lg sm:text-xl"></i>
                                    @endif
                                </div>
                                <span class="text-[11px] sm:text-xs text-center line-clamp-2 mt-2 leading-tight text-slate-700 font-medium group-hover/item:text-[#CE171F] transition-colors">
                                    {{ $c->name }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- 4. Section: FLASH SALE -->
        @if ($flashSaleProducts->isNotEmpty())
        <section id="flash-sale" class="mb-8 sm:mb-10 relative">
            @if ($flashSaleBg)
                <!-- Header Banner (Non-overlapping) -->
                <div class="w-full rounded-3xl overflow-hidden shadow-xs mb-4">
                    <img 
                        src="{{ $flashSaleBg }}" 
                        alt="{{ $home['flash_sale_title'] ?? 'Flash Sale' }}" 
                        class="w-full h-[180px] sm:h-[260px] md:h-[320px] lg:h-[360px] object-cover object-center select-none"
                    >
                </div>
            @endif

            <!-- Card Box with Products -->
            <div class="relative z-10 bg-white rounded-3xl p-4 sm:p-6 lg:p-7 shadow-xs border border-slate-200/80">
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100/80">
                    <h3 class="text-base sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        @if (!empty($home['flash_sale_title']))
                            <span>{{ $home['flash_sale_title'] }}</span>
                        @endif
                        @if (!empty($home['flash_sale_icon']))
                            <span class="text-rose-500">{{ $home['flash_sale_icon'] }}</span>
                        @endif
                    </h3>

                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center gap-1.5">
                            <button class="w-8 h-8 rounded-full border border-slate-200/90 bg-white/90 hover:bg-white hover:border-[#CE171F] text-slate-600 hover:text-[#CE171F] flex items-center justify-center transition shadow-2xs">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button class="w-8 h-8 rounded-full border border-slate-200/90 bg-white/90 hover:bg-white hover:border-[#CE171F] text-slate-600 hover:text-[#CE171F] flex items-center justify-center transition shadow-2xs">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                        @if (!empty($home['flash_sale_link_text']))
                            <a href="{{ $home['flash_sale_link_url'] ?? route('category') }}" wire:navigate.hover class="text-xs sm:text-sm font-semibold text-[#CE171F] hover:text-[#3b654a] transition ml-1">
                                {{ $home['flash_sale_link_text'] }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Products Grid (6 Cols on XL, matching screenshot 2 rows of 6) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                    @foreach ($flashSaleProducts as $prod)
                        <div wire:key="flash-prod-{{ $prod->id }}" class="bg-white rounded-2xl border border-slate-100 hover:border-emerald-300 p-2.5 sm:p-3 flex flex-col justify-between group hover:shadow-lg transition-all duration-200 relative">
                            <!-- Quick Add Button in Top Right -->
                            <button 
                                wire:click="addToCart({{ $prod->id }})"
                                type="button"
                                class="absolute top-2.5 right-2.5 w-7 h-7 rounded-lg bg-[#3b654a] hover:bg-[#2d503a] active:scale-95 text-white flex items-center justify-center transition shadow-xs z-10 cursor-pointer"
                                title="Thêm vào giỏ"
                            >
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>

                            <!-- Product Image: Full block without padding! -->
                            <div class="w-full aspect-square relative flex items-center justify-center overflow-hidden rounded-xl mb-1">
                                <a href="{{ route('product.detail', $prod->slug) }}" wire:navigate.hover class="w-full h-full flex items-center justify-center">
                                    <img 
                                        src="{{ curator_url($prod->image_url, 400, 400) ?: asset('storage/logo/favicon-dnv.png') }}" 
                                        alt="{{ $prod->name }}" 
                                        class="w-full h-full object-contain p-0 group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    >
                                </a>
                            </div>

                            <!-- Discount Tag Bar -->
                            <div class="w-full bg-[#B24A3E] text-white text-[11px] font-bold py-1 px-2 rounded-lg flex items-center justify-center gap-1 shadow-xs my-1.5">
                                <span>🔥</span>
                                <span>{{ $prod->discount_label ?? '-10%' }}</span>
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-1 truncate">
                                        {{ $prod->brand?->name ?? 'DNV' }} @if(!empty($prod->unit)) • {{ $prod->unit }} @endif
                                    </div>
                                    <h4 class="text-xs font-semibold text-slate-800 line-clamp-2 leading-snug group-hover:text-[#CE171F] transition min-h-[32px]">
                                        <a href="{{ route('product.detail', $prod->slug) }}" wire:navigate.hover>
                                            {{ $prod->name }}
                                        </a>
                                    </h4>
                                </div>

                                <div class="mt-2">
                                    <!-- Price Row -->
                                    <div class="flex items-baseline gap-1.5 flex-wrap">
                                        <span class="text-xs sm:text-sm font-bold text-[#B24A3E]">
                                            {{ number_format((float) $prod->sale_price > 0 ? $prod->sale_price : $prod->base_price, 0, ',', '.') }}đ
                                        </span>
                                        @if($prod->sale_price && $prod->sale_price < $prod->base_price)
                                            <span class="text-[10.5px] text-slate-400 line-through font-normal">
                                                {{ number_format($prod->base_price, 0, ',', '.') }}đ
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Sold / Stock Warning -->
                                    <div class="text-[10.5px] text-slate-400 mt-1 flex items-center justify-between">
                                        <span>{{ $prod->weight ?? $prod->unit ?? '' }}</span>
                                        @if(!empty($prod->stock_warning))
                                            <span class="text-rose-500 font-medium">{{ $prod->stock_warning }}</span>
                                        @elseif($prod->sold_count > 0)
                                            <span class="text-slate-400">Đã bán {{ $prod->sold_count }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- 5. Section: NEW ARRIVALS -->
        @if ($newArrivalProducts->isNotEmpty())
        <section id="new-arrivals" class="mb-8 sm:mb-10 relative">
            @if ($newArrivalsBg)
                <!-- Header Banner (Non-overlapping) -->
                <div class="w-full rounded-3xl overflow-hidden shadow-xs mb-4">
                    <img 
                        src="{{ $newArrivalsBg }}" 
                        alt="{{ $home['new_arrivals_title'] ?? 'New Arrivals' }}" 
                        class="w-full h-[180px] sm:h-[260px] md:h-[320px] lg:h-[360px] object-cover object-center select-none"
                    >
                </div>
            @endif

            <!-- Card Box with Products -->
            <div class="relative z-10 bg-white rounded-3xl p-4 sm:p-6 lg:p-7 shadow-xs border border-slate-200/80">
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                    <h3 class="text-base sm:text-xl font-bold text-slate-800 flex items-center gap-2">
                        @if (!empty($home['new_arrivals_title']))
                            <span>{{ $home['new_arrivals_title'] }}</span>
                        @endif
                        @if (!empty($home['new_arrivals_icon']))
                            <span class="text-amber-500">{{ $home['new_arrivals_icon'] }}</span>
                        @endif
                    </h3>

                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex items-center gap-1.5">
                            <button class="w-8 h-8 rounded-full border border-slate-200 bg-white/80 hover:bg-white hover:border-[#CE171F] text-slate-600 hover:text-[#CE171F] flex items-center justify-center transition shadow-2xs">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button class="w-8 h-8 rounded-full border border-slate-200 bg-white/80 hover:bg-white hover:border-[#CE171F] text-slate-600 hover:text-[#CE171F] flex items-center justify-center transition shadow-2xs">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                        @if (!empty($home['new_arrivals_link_text']))
                            <a href="{{ $home['new_arrivals_link_url'] ?? route('category') }}" wire:navigate.hover class="text-xs sm:text-sm font-semibold text-[#CE171F] hover:text-[#3b654a] flex items-center gap-1 ml-2 transition">
                                <span>{{ $home['new_arrivals_link_text'] }}</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Products Grid (6 Cols on XL, 2 rows) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                    @foreach ($newArrivalProducts as $item)
                        <div wire:key="new-prod-{{ $item->id }}" class="bg-white rounded-2xl border border-slate-100 hover:border-emerald-300 p-2.5 sm:p-3 flex flex-col justify-between group hover:shadow-lg transition-all duration-200 relative">
                            <!-- Category Badge -->
                            <span class="absolute top-2.5 left-2.5 z-10 {{ $item->badge_color ?? 'bg-emerald-600' }} text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs">
                                {{ $item->badge ?? 'Mới Về' }}
                            </span>

                            <!-- Quick Add Button -->
                            <button 
                                wire:click="addToCart({{ $item->id }})"
                                type="button"
                                class="absolute top-2.5 right-2.5 z-10 w-7 h-7 rounded-lg bg-[#3b654a] hover:bg-[#2d503a] active:scale-95 text-white flex items-center justify-center transition shadow-xs cursor-pointer"
                                title="Thêm vào giỏ"
                            >
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>

                            <!-- Product Image: Full block without padding! -->
                            <div class="w-full aspect-square relative flex items-center justify-center overflow-hidden rounded-xl mb-2">
                                <a href="{{ route('product.detail', $item->slug) }}" wire:navigate.hover class="w-full h-full flex items-center justify-center">
                                    <img 
                                        src="{{ curator_url($item->image_url, 400, 400) ?: asset('storage/logo/favicon-dnv.png') }}" 
                                        alt="{{ $item->name }}" 
                                        class="w-full h-full object-contain p-0 group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    >
                                </a>
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wide mb-1 truncate">
                                        {{ $item->brand?->name ?? 'DNV' }}
                                    </div>
                                    <h4 class="text-xs font-semibold text-slate-800 line-clamp-2 leading-snug group-hover:text-[#CE171F] transition h-8">
                                        <a href="{{ route('product.detail', $item->slug) }}" wire:navigate.hover>
                                            {{ $item->name }}
                                        </a>
                                    </h4>
                                </div>

                                <div class="mt-2.5">
                                    <!-- Price Row -->
                                    <div class="flex items-baseline justify-between gap-1 flex-wrap">
                                        <span class="text-xs sm:text-sm font-bold text-[#CE171F]">
                                            {{ number_format((float) $item->sale_price > 0 ? $item->sale_price : $item->base_price, 0, ',', '.') }}đ
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-normal">
                                            {{ $item->unit }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-[#991B1B] font-medium bg-red-50 px-1.5 py-0.5 rounded-md mt-1.5 inline-block">
                                        ✓ Sẵn sàng giao
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- 6. Section: NEW BLOG POSTS -->
        @if ($latestArticles->isNotEmpty())
        <section id="blog-section" class="mb-8 sm:mb-10 relative">
            @if ($blogsBg)
                <!-- Header Banner (Non-overlapping) -->
                <div class="w-full rounded-3xl overflow-hidden shadow-xs mb-4">
                    <img 
                        src="{{ $blogsBg }}" 
                        alt="{{ $home['blogs_title'] ?? 'Blogs THV' }}" 
                        class="w-full h-[180px] sm:h-[260px] md:h-[320px] lg:h-[360px] object-cover object-center select-none"
                    >
                </div>
            @endif

            <!-- Card Box with Blogs -->
            <div class="relative z-10 bg-white rounded-3xl p-4 sm:p-6 lg:p-7 shadow-xs border border-slate-200/80">
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                        @if (!empty($home['blogs_title']))
                            <span>{{ $home['blogs_title'] }}</span>
                        @endif
                        @if (!empty($home['blogs_icon']))
                            <span>{{ $home['blogs_icon'] }}</span>
                        @endif
                    </h3>

                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex items-center gap-1">
                            <button class="w-7 h-7 rounded-full border border-slate-200 hover:border-[#CE171F] text-slate-600 flex items-center justify-center transition">
                                <i class="fa-solid fa-chevron-left text-[11px]"></i>
                            </button>
                            <button class="w-7 h-7 rounded-full border border-slate-200 hover:border-[#CE171F] text-slate-600 flex items-center justify-center transition">
                                <i class="fa-solid fa-chevron-right text-[11px]"></i>
                            </button>
                        </div>
                        @if (!empty($home['blogs_link_text']))
                            <a href="{{ $home['blogs_link_url'] ?? route('blog') }}" wire:navigate.hover class="text-xs sm:text-sm font-semibold text-[#CE171F] hover:text-[#3b654a] flex items-center gap-1 ml-2 transition">
                                <span>{{ $home['blogs_link_text'] }}</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 3 Blog Cards Grid -->
                <!-- 3 Blog Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($latestArticles as $art)
                        <article class="bg-white rounded-2xl border border-slate-100 hover:border-emerald-300 overflow-hidden group hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                            <div>
                                <a href="{{ route('blog.detail', $art->slug) }}" wire:navigate.hover class="block relative aspect-[16/10] overflow-hidden bg-slate-100">
                                    <img 
                                        src="{{ curator_url($art->image_url, 600, 400) ?: asset('storage/roots/blogs/blog_1.png') }}" 
                                        alt="{{ $art->title }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    >
                                    <span class="absolute top-3 left-3 bg-[#CE171F] text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-full shadow-md">
                                        {{ $art->contentCategory?->name ?? 'Tin Sức Khỏe' }}
                                    </span>
                                </a>
                                <div class="p-4 sm:p-5">
                                    <div class="text-[11px] text-slate-400 font-medium mb-2 flex items-center gap-1.5">
                                        <i class="fa-regular fa-calendar"></i> {{ $art->created_at?->format('d/m/Y') ?? '29/09/2026' }}
                                    </div>
                                    <h4 class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-[#CE171F] transition-colors leading-snug line-clamp-2 mb-2">
                                        <a href="{{ route('blog.detail', $art->slug) }}" wire:navigate.hover>
                                            {{ $art->title }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-slate-500 font-normal line-clamp-2 leading-relaxed">
                                        {{ $art->summary }}
                                    </p>
                                </div>
                            </div>
                            <div class="px-4 sm:px-5 pb-4 pt-1">
                                <a href="{{ route('blog.detail', $art->slug) }}" wire:navigate.hover class="text-xs font-semibold text-[#CE171F] group-hover:translate-x-1 inline-flex items-center gap-1 transition-transform">
                                    Xem chi tiết <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- 7. Section: GIỚI THIỆU DNV (Configurable via InterfaceSettings) -->
        @if ($hasAboutSection)
        <section id="about-dnv-section" class="mb-14 sm:mb-20">
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-10 lg:p-12 text-white shadow-xl border border-slate-700/80 relative overflow-hidden">
                <!-- Background decorative glow -->
                <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-red-600/15 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-4xl mx-auto text-center space-y-3 mb-10">
                    @if ($hasAboutBadge)
                        <span class="inline-block px-3.5 py-1 rounded-full bg-[#CE171F] text-white text-xs font-bold uppercase tracking-wider shadow-sm">
                            {{ $home['about_badge'] }}
                        </span>
                    @endif
                    @if ($hasAboutTitle)
                        <h2 class="text-xl sm:text-3xl lg:text-4xl font-black text-white uppercase tracking-tight">
                            {{ $home['about_title'] }}
                        </h2>
                    @endif
                    @if ($hasAboutDesc)
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-3xl mx-auto">
                            {{ $home['about_description'] }}
                        </p>
                    @endif
                </div>

                @if (count($aboutFeatures) > 0)
                    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
                        @foreach ($aboutFeatures as $feat)
                            <div class="bg-white/5 hover:bg-white/10 rounded-2xl p-5 border border-white/10 transition-all duration-300 group hover:-translate-y-1">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#CE171F] to-[#991B1B] text-white flex items-center justify-center text-lg mb-3 shadow-md group-hover:scale-105 transition-transform">
                                    <i class="{{ $feat['icon'] ?? 'fa-solid fa-shield-halved' }}"></i>
                                </div>
                                <h3 class="text-sm font-bold text-white mb-1.5 group-hover:text-amber-400 transition-colors">
                                    {{ $feat['title'] ?? '' }}
                                </h3>
                                <p class="text-xs text-slate-300 leading-relaxed font-normal">
                                    {{ $feat['description'] ?? '' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($hasAboutBottom)
                    <div class="relative z-10 pt-6 border-t border-slate-700/80 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-300">
                        @if (!empty($home['about_bottom_address']))
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-[#CE171F] text-sm"></i>
                                <span>{{ $home['about_bottom_address'] }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-5 flex-wrap">
                            @if (!empty($home['about_bottom_hotline']))
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-phone text-amber-400 text-sm"></i>
                                    <a href="tel:{{ preg_replace('/[^0-9]/', '', $home['about_bottom_hotline']) }}" class="font-bold text-white hover:text-amber-300 transition">
                                        {{ $home['about_bottom_hotline'] }}
                                    </a>
                                </div>
                            @endif
                            @if (!empty($home['about_bottom_hours']))
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock text-emerald-400 text-sm"></i>
                                    <span>{{ $home['about_bottom_hours'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @endif

    </div>

    <!-- Floating Toast Notification (Client-side timer, no extra server roundtrip) -->
    <div 
        x-data="{ show: false, msg: '', timer: null }"
        x-on:home-cart-toast.window="msg = $event.detail.message; show = true; clearTimeout(timer); timer = setTimeout(() => { show = false }, 3200)"
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
