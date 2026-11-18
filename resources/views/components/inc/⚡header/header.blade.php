@php
    $header = $setting?->interface_settings['header'] ?? [];
    $footer = $setting?->interface_settings['footer'] ?? [];
    $headerLogoUrl = get_curator_media_url($header['logo'] ?? null);
    $brandName = $header['brand_name'] ?? null;
    $slogan = $header['slogan'] ?? null;
@endphp
<header 
    x-data="{ 
        mobileOpen: false, 
        searchOpen: false,
        catModalOpen: false,
        policyModalOpen: false,
        catTimeout: null,
        policyTimeout: null,
        activeCatIndex: 0,
        catSearch: '',
        mobileCatOpen: false,
        mobilePolicyOpen: false,
        desktopSearchFocus: false,
        mobileSearchFocus: false,

        openCat() {
            clearTimeout(this.catTimeout);
            clearTimeout(this.policyTimeout);
            this.policyModalOpen = false;
            this.catModalOpen = true;
            this.$nextTick(() => {
                if (this.$refs.catGrid) {
                    this.$refs.catGrid.scrollTop = 0;
                }
            });
        },
        closeCat() {
            this.catTimeout = setTimeout(() => {
                this.catModalOpen = false;
            }, 350);
        },
        openPolicy() {
            clearTimeout(this.policyTimeout);
            clearTimeout(this.catTimeout);
            this.catModalOpen = false;
            this.policyModalOpen = true;
        },
        closePolicy() {
            this.policyTimeout = setTimeout(() => {
                this.policyModalOpen = false;
            }, 350);
        }
    }" 
    class="sticky top-0 z-50 w-full bg-white border-b border-slate-100 shadow-2xs transition-all duration-200"
>
    <!-- Top Announcement Bar -->
    @if (!empty($header['topbar_active']))
        @php
            $hasTopbarContent = !empty($header['topbar_text']) || !empty($header['topbar_hotline']) || !empty($header['topbar_zalo']) || !empty($header['badge_1']) || !empty($header['badge_2']);
        @endphp
        @if ($hasTopbarContent)
            <div class="bg-gradient-to-r from-[#FFC700] via-[#FBBF24] to-[#F59E0B] text-[#991B1B] text-[10.5px] sm:text-[12px] py-1 sm:py-1.5 px-2.5 sm:px-4 font-medium border-b border-amber-300 shadow-2xs">
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0 flex items-center justify-center text-center">
                        {{-- Mobile view: Strict 1-line compact bar, no wrapping or orphan bullets --}}
                        @php
                            $mobileBadgeText = !empty($header['badge_2']) ? $header['badge_2'] : ($header['badge_1'] ?? '');
                            $hasMobilePhoneOrZalo = !empty($header['topbar_hotline']) || !empty($header['topbar_zalo']);
                        @endphp
                        <div class="sm:hidden flex items-center justify-center gap-1.5 w-full whitespace-nowrap overflow-hidden text-[10.5px] leading-tight">
                            @if (!empty($header['topbar_hotline']))
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $header['topbar_hotline']) }}" class="inline-flex items-center gap-1 font-bold text-[#CE171F] shrink-0">
                                    <i class="fa-solid fa-phone text-[9px]"></i>
                                    <span>{{ $header['topbar_hotline'] }}</span>
                                </a>
                            @endif
                            @if (!empty($header['topbar_zalo']))
                                @if (!empty($header['topbar_hotline']))
                                    <span class="text-amber-700/60 shrink-0">•</span>
                                @endif
                                <a href="{{ $header['topbar_zalo_link'] ?? 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $header['topbar_zalo']) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 shrink-0">
                                    <span class="text-[#991B1B]">Zalo:</span>
                                    <span class="font-bold text-[#CE171F]">{{ $header['topbar_zalo'] }}</span>
                                </a>
                            @endif
                            @if (!empty($mobileBadgeText))
                                @if ($hasMobilePhoneOrZalo)
                                    <span class="hidden min-[360px]:inline text-amber-700/60 shrink-0">•</span>
                                @endif
                                <a href="{{ route('gioi-thieu') }}" wire:navigate.hover class="{{ $hasMobilePhoneOrZalo ? 'hidden min-[360px]:inline' : 'inline' }} text-[#991B1B] truncate hover:underline">{{ $mobileBadgeText }}</a>
                            @endif
                        </div>

                        {{-- Desktop view --}}
                        <div class="hidden sm:inline-flex items-center gap-2 flex-wrap justify-center">
                            @if (!empty($header['topbar_text']))
                                <span class="text-[#991B1B] font-semibold">{{ $header['topbar_text'] }}</span>
                            @endif
                            @if (!empty($header['topbar_zalo']))
                                <span class="text-amber-600">•</span>
                                <span>Zalo: <a href="{{ $header['topbar_zalo_link'] ?? 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $header['topbar_zalo']) }}" target="_blank" rel="noopener" class="font-semibold text-[#CE171F] font-bold hover:text-white hover:underline transition">{{ $header['topbar_zalo'] }}</a></span>
                            @endif
                            @if (!empty($header['topbar_hotline']))
                                <span class="text-amber-600">•</span>
                                <span>Hotline: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $header['topbar_hotline']) }}" class="font-semibold text-[#CE171F] font-bold hover:text-white hover:underline transition">{{ $header['topbar_hotline'] }}</a></span>
                            @endif
                        </div>
                    </div>
                    @if (!empty($header['badge_1']) || !empty($header['badge_2']))
                        <div class="hidden md:flex items-center gap-4 text-[11px] text-[#991B1B] shrink-0">
                            @if (!empty($header['badge_1']))
                                <a href="{{ route('gioi-thieu') }}" wire:navigate.hover class="flex items-center gap-1 hover:underline hover:text-white transition" title="Xem hồ sơ pháp lý và cam kết chất lượng">
                                    <i class="fa-solid fa-shield-halved text-[10px]"></i> {{ $header['badge_1'] }}
                                </a>
                            @endif
                            @if (!empty($header['badge_1']) && !empty($header['badge_2']))
                                <span class="text-amber-500">|</span>
                            @endif
                            @if (!empty($header['badge_2']))
                                <span class="flex items-center gap-1">
                                    <i class="fa-solid fa-truck-fast text-[10px]"></i> {{ $header['badge_2'] }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <!-- Main Header Bar -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-1.5 sm:py-2.5">
        <div class="flex items-center justify-between gap-2 sm:gap-4 lg:gap-8">
            <!-- Brand Logo -->
            @if ($headerLogoUrl)
                <a href="{{ route('home') }}" wire:navigate.hover class="flex items-center shrink-0 group">
                    <img src="{{ $headerLogoUrl }}" alt="{{ $brandName ?? config('app.name') }}" class="h-9 sm:h-14 md:h-15 w-auto object-contain group-hover:scale-105 transition-transform duration-200">
                </a>
            @elseif ($brandName)
                <a href="{{ route('home') }}" wire:navigate.hover class="flex items-center shrink-0 group">
                    <span class="text-lg sm:text-2xl font-bold tracking-tight text-slate-800 leading-none group-hover:text-[#CE171F] transition-colors">
                        {{ $brandName }}
                    </span>
                </a>
            @endif

            <!-- Search Bar (Desktop) with Realtime Dropdown -->
            <div 
                class="hidden sm:flex flex-1 max-w-2xl relative"
                @click.outside="desktopSearchFocus = false"
            >
                <div class="w-full flex items-center bg-[#fbfcfb] hover:bg-white focus-within:bg-white border border-slate-200/90 hover:border-slate-300 focus-within:border-[#CE171F] rounded-full py-1.5 px-4 transition-all duration-200 shadow-2xs">
                    <i class="fa-solid fa-magnifying-glass text-[#CE171F] text-sm shrink-0 mr-2.5"></i>
                    <input 
                        wire:model.live.debounce.300ms="searchQuery"
                        @focus="desktopSearchFocus = true"
                        @keydown.escape="desktopSearchFocus = false; $wire.clearSearch()"
                        type="text" 
                        placeholder="{{ $header['search_placeholder'] ?? '' }}"
                        class="w-full bg-transparent text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none font-normal"
                    >
                    @if (!empty($searchQuery))
                        <button 
                            wire:click="clearSearch" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 p-1 text-xs cursor-pointer ml-1"
                            title="Xóa tìm kiếm"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>

                <!-- Desktop Real-time Search Dropdown -->
                @if (mb_strlen(trim($searchQuery)) >= 2)
                    <div 
                        x-show="desktopSearchFocus"
                        x-transition:enter="transition ease-out duration-150 transform"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="absolute top-[calc(100%+6px)] left-0 w-full bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-4 z-[9999] max-h-[480px] overflow-y-auto divide-y divide-slate-100"
                    >
                        <!-- Search Loading Indicator -->
                        <div wire:loading wire:target="searchQuery" class="py-6 text-center text-xs text-slate-500 font-medium">
                            <i class="fa-solid fa-circle-notch fa-spin text-[#CE171F] text-base mr-2"></i>
                            Đang tìm kiếm sản phẩm...
                        </div>

                        <div wire:loading.remove wire:target="searchQuery">
                            @php $res = $this->searchResults; @endphp

                            @if ($res['total'] === 0)
                                <div class="py-8 text-center space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-sm">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </div>
                                    <div class="text-xs text-slate-600">
                                        Không tìm thấy kết quả nào phù hợp với "<strong class="text-slate-800">{{ $searchQuery }}</strong>"
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Gợi ý: Thử từ khóa như "sữa", "bánh", "kẹo", "glade", "thớt"...
                                    </div>
                                </div>
                            @else
                                <!-- Matching Categories & Brands Tags -->
                                @if (count($res['categories']) > 0 || count($res['brands']) > 0)
                                    <div class="pb-3 space-y-2">
                                        @if (count($res['categories']) > 0)
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold uppercase text-slate-400 mr-1 flex items-center gap-1">
                                                    <i class="fa-solid fa-folder-tree text-[#CE171F]"></i> Danh mục:
                                                </span>
                                                @foreach ($res['categories'] as $cat)
                                                    <a 
                                                        href="{{ $cat['url'] }}" 
                                                        wire:navigate.hover 
                                                        @click="desktopSearchFocus = false"
                                                        class="text-xs bg-red-50 text-[#991B1B] hover:bg-[#CE171F] hover:text-white px-2.5 py-1 rounded-lg font-medium transition cursor-pointer"
                                                    >
                                                        {{ $cat['name'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (count($res['brands']) > 0)
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold uppercase text-slate-400 mr-1 flex items-center gap-1">
                                                    <i class="fa-solid fa-award text-amber-500"></i> Thương hiệu:
                                                </span>
                                                @foreach ($res['brands'] as $b)
                                                    <a 
                                                        href="{{ $b['url'] }}" 
                                                        wire:navigate.hover 
                                                        @click="desktopSearchFocus = false"
                                                        class="text-xs bg-amber-50 text-amber-900 border border-amber-200/80 hover:bg-amber-500 hover:text-white px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer uppercase tracking-wider"
                                                    >
                                                        {{ $b['name'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Matching Products -->
                                @if (count($res['products']) > 0)
                                    <div class="pt-3 pb-2 space-y-2">
                                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-box text-[#CE171F]"></i>
                                                <span>Sản phẩm gợi ý ({{ count($res['products']) }})</span>
                                            </span>
                                            <span class="text-[10px] text-[#CE171F] font-semibold">Chính hãng 100%</span>
                                        </div>
                                        <div class="space-y-1.5">
                                            @foreach ($res['products'] as $p)
                                                <a 
                                                    href="{{ $p['url'] }}" 
                                                    wire:navigate.hover 
                                                    @click="desktopSearchFocus = false"
                                                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition group"
                                                >
                                                    <div class="w-11 h-11 rounded-lg bg-[#fbfdfc] border border-slate-100 p-1 flex items-center justify-center shrink-0">
                                                        <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-xs font-semibold text-slate-800 truncate group-hover:text-[#CE171F] transition-colors">
                                                            {{ $p['name'] }}
                                                        </div>
                                                        <div class="text-[10.5px] text-slate-400 flex items-center gap-1.5">
                                                            <span class="font-bold text-[#CE171F]">{{ $p['brand'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <div class="text-xs font-bold text-[#CE171F]">
                                                            {{ number_format($p['price'], 0, ',', '.') }}đ
                                                        </div>
                                                        @if ($p['has_discount'])
                                                            <div class="text-[10px] text-slate-400 line-through">
                                                                {{ number_format($p['original_price'], 0, ',', '.') }}đ
                                                            </div>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Matching Articles / News (Tin tức) -->
                                @if (count($res['articles']) > 0)
                                    <div class="pt-3 pb-2 space-y-2 border-t border-slate-100">
                                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-newspaper text-blue-500"></i>
                                                <span>Tin tức & Mẹo hay ({{ count($res['articles']) }})</span>
                                            </span>
                                            <a 
                                                href="{{ route('blog') }}?search={{ urlencode($searchQuery) }}" 
                                                wire:navigate.hover 
                                                @click="desktopSearchFocus = false"
                                                class="text-[10px] text-blue-600 hover:underline font-semibold"
                                            >
                                                Xem thêm bài viết →
                                            </a>
                                        </div>
                                        <div class="space-y-1.5">
                                            @foreach ($res['articles'] as $art)
                                                <a 
                                                    href="{{ $art['url'] }}" 
                                                    wire:navigate.hover 
                                                    @click="desktopSearchFocus = false"
                                                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition group"
                                                >
                                                    <div class="w-14 h-11 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-200/70">
                                                        <img src="{{ $art['image'] }}" alt="{{ $art['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-xs font-semibold text-slate-800 line-clamp-1 group-hover:text-[#CE171F] transition-colors">
                                                            {{ $art['title'] }}
                                                        </div>
                                                        <div class="text-[10.5px] text-slate-400 flex items-center gap-2 mt-0.5">
                                                            <span class="text-[#CE171F] font-medium bg-red-50 px-1.5 py-0.2 rounded text-[9.5px]">
                                                                {{ $art['category'] }}
                                                            </span>
                                                            @if ($art['date'])
                                                                <span><i class="fa-regular fa-clock text-[9px] mr-0.5"></i>{{ $art['date'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-300 group-hover:text-[#CE171F] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Footer View All Links -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                                    <a 
                                        href="{{ route('category') }}?q={{ urlencode($searchQuery) }}" 
                                        wire:navigate.hover 
                                        @click="desktopSearchFocus = false"
                                        class="text-[#CE171F] hover:underline inline-flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid fa-basket-shopping text-[11px]"></i>
                                        <span>Tất cả sản phẩm ("{{ $searchQuery }}")</span>
                                    </a>
                                    <a 
                                        href="{{ route('blog') }}?search={{ urlencode($searchQuery) }}" 
                                        wire:navigate.hover 
                                        @click="desktopSearchFocus = false"
                                        class="text-blue-600 hover:underline inline-flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid fa-newspaper text-[11px]"></i>
                                        <span>Tất cả bài viết ("{{ $searchQuery }}")</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Actions: Search + Cart + Login Button + Hamburger Menu -->
            <div class="flex items-center gap-1 sm:gap-2.5 shrink-0">
                <!-- Search Icon on mobile -->
                <button 
                    @click="searchOpen = !searchOpen" 
                    class="sm:hidden w-8 h-8 flex items-center justify-center text-slate-600 hover:text-[#CE171F] rounded-full hover:bg-slate-100 transition cursor-pointer"
                    aria-label="Tìm kiếm"
                >
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </button>

                <!-- Đăng Nhập / Tài Khoản Button -->
                @if (!empty($header['show_account']))
                    @auth
                        <a 
                            href="{{ route('account') }}" 
                            wire:navigate.hover
                            class="border border-slate-200/90 bg-[#f8faf9] hover:bg-[#CE171F] hover:text-white hover:border-[#CE171F] text-slate-700 text-xs font-medium w-8 h-8 sm:w-auto sm:px-3.5 sm:py-1.5 rounded-full flex items-center justify-center gap-1.5 transition-all shadow-2xs group"
                            title="Tài khoản: {{ auth()->user()->name }}"
                        >
                            <i class="fa-solid fa-circle-user text-xs text-[#CE171F] group-hover:text-white transition"></i>
                            <span class="hidden md:inline max-w-[80px] truncate">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a 
                            href="{{ route('login') }}" 
                            wire:navigate.hover
                            class="border border-slate-200/90 bg-[#f8faf9] hover:bg-[#CE171F] hover:text-white hover:border-[#CE171F] text-slate-700 text-xs font-medium w-8 h-8 sm:w-auto sm:px-3.5 sm:py-1.5 rounded-full flex items-center justify-center gap-1.5 transition-all shadow-2xs group"
                            title="Đăng nhập"
                        >
                            <i class="fa-regular fa-user text-xs text-slate-600 group-hover:text-white transition"></i>
                            <span class="hidden md:inline">Đăng nhập</span>
                        </a>
                    @endauth
                @endif

                <!-- Cart Button -->
                @if (!empty($header['show_cart']))
                    <a 
                        href="{{ route('cart') }}" 
                        class="relative border border-slate-200/90 bg-[#f8faf9] hover:bg-[#CE171F] hover:text-white hover:border-[#CE171F] text-slate-700 text-xs font-medium px-2.5 sm:px-3 py-1.5 rounded-full flex items-center gap-1.5 transition-all shadow-2xs group"
                        title="Giỏ hàng của bạn"
                    >
                        <i class="fa-solid fa-basket-shopping text-xs text-[#CE171F] group-hover:text-white transition"></i>
                        <span class="hidden md:inline">Giỏ hàng</span>
                        <span class="min-w-4 h-4 px-1 rounded-full bg-[#CE171F] group-hover:bg-white text-white group-hover:text-[#CE171F] text-[9.5px] font-bold flex items-center justify-center transition">
                            {{ $cartCount }}
                        </span>
                    </a>
                @endif

                <!-- Hamburger Menu Trigger -->
                <button 
                    @click="mobileOpen = !mobileOpen" 
                    type="button" 
                    class="w-8 h-8 flex items-center justify-center text-slate-700 hover:text-[#CE171F] transition text-base cursor-pointer rounded-full hover:bg-slate-100"
                    aria-label="Menu"
                >
                    <i :class="mobileOpen ? 'fa-solid fa-xmark text-lg' : 'fa-solid fa-bars text-lg'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar Toggle with Realtime Dropdown -->
        <div 
            x-show="searchOpen" 
            x-transition 
            class="pt-2 sm:hidden relative" 
            @click.outside="mobileSearchFocus = false"
        >
            <div class="flex items-center bg-[#fbfcfb] border border-slate-200 rounded-full py-1.5 px-3.5 shadow-2xs">
                <i class="fa-solid fa-magnifying-glass text-[#CE171F] text-xs shrink-0 mr-2"></i>
                <input 
                    wire:model.live.debounce.300ms="searchQuery" 
                    @focus="mobileSearchFocus = true"
                    type="text" 
                    placeholder="{{ $header['search_placeholder'] ?? '' }}"
                    class="w-full bg-transparent text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none"
                >
                @if (!empty($searchQuery))
                    <button wire:click="clearSearch" type="button" class="text-slate-400 hover:text-slate-600 p-0.5 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                @endif
            </div>

            <!-- Mobile Search Results Dropdown -->
            @if (mb_strlen(trim($searchQuery)) >= 2)
                <div 
                    x-show="mobileSearchFocus" 
                    class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 p-3 z-[9999] max-h-[380px] overflow-y-auto space-y-3"
                >
                    <!-- Loading Indicator -->
                    <div wire:loading wire:target="searchQuery" class="py-4 text-center text-xs text-slate-500 font-medium">
                        <i class="fa-solid fa-circle-notch fa-spin text-[#CE171F] text-sm mr-2"></i>
                        Đang tìm kiếm...
                    </div>

                    <div wire:loading.remove wire:target="searchQuery">
                        @php $mRes = $this->searchResults; @endphp

                        @if ($mRes['total'] === 0)
                            <div class="py-4 text-center text-xs text-slate-500">
                                Không tìm thấy kết quả cho "{{ $searchQuery }}"
                            </div>
                        @else
                            <!-- Categories & Brands -->
                            @if (count($mRes['categories']) > 0 || count($mRes['brands']) > 0)
                                <div class="space-y-1.5 pb-2 border-b border-slate-100">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @foreach ($mRes['categories'] as $c)
                                            <a 
                                                href="{{ $c['url'] }}" 
                                                wire:navigate.hover 
                                                @click="searchOpen = false; mobileSearchFocus = false"
                                                class="text-[11px] bg-red-50 text-[#991B1B] px-2 py-0.5 rounded-md font-medium"
                                            >
                                                {{ $c['name'] }}
                                            </a>
                                        @endforeach
                                        @foreach ($mRes['brands'] as $b)
                                            <a 
                                                href="{{ $b['url'] }}" 
                                                wire:navigate.hover 
                                                @click="searchOpen = false; mobileSearchFocus = false"
                                                class="text-[11px] bg-amber-50 text-amber-900 border border-amber-200 px-2 py-0.5 rounded-md font-bold uppercase"
                                            >
                                                {{ $b['name'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Products -->
                            @if (count($mRes['products']) > 0)
                                <div class="space-y-1.5 pt-1">
                                    <div class="text-[10px] font-bold uppercase text-slate-400 flex items-center gap-1">
                                        <i class="fa-solid fa-box text-[#CE171F]"></i> Sản phẩm ({{ count($mRes['products']) }})
                                    </div>
                                    @foreach ($mRes['products'] as $p)
                                        <a 
                                            href="{{ $p['url'] }}" 
                                            wire:navigate.hover 
                                            @click="searchOpen = false; mobileSearchFocus = false"
                                            class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-slate-50"
                                        >
                                            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" class="w-9 h-9 object-contain rounded bg-slate-50 p-0.5 shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[11.5px] font-semibold text-slate-800 truncate">{{ $p['name'] }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $p['brand'] }}</div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="text-[11.5px] font-bold text-[#CE171F]">{{ number_format($p['price'], 0, ',', '.') }}đ</div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Articles / News (Tin tức) -->
                            @if (count($mRes['articles']) > 0)
                                <div class="space-y-1.5 pt-2 border-t border-slate-100">
                                    <div class="text-[10px] font-bold uppercase text-slate-400 flex items-center gap-1">
                                        <i class="fa-solid fa-newspaper text-blue-500"></i> Tin tức & Mẹo hay ({{ count($mRes['articles']) }})
                                    </div>
                                    @foreach ($mRes['articles'] as $art)
                                        <a 
                                            href="{{ $art['url'] }}" 
                                            wire:navigate.hover 
                                            @click="searchOpen = false; mobileSearchFocus = false"
                                            class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-slate-50"
                                        >
                                            <div class="w-12 h-9 rounded bg-slate-100 overflow-hidden shrink-0">
                                                <img src="{{ $art['image'] }}" alt="{{ $art['title'] }}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[11px] font-semibold text-slate-800 truncate">{{ $art['title'] }}</div>
                                                <div class="text-[9.5px] text-[#CE171F]">{{ $art['category'] }}</div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                                <a 
                                    href="{{ route('category') }}?q={{ urlencode($searchQuery) }}" 
                                    wire:navigate.hover 
                                    @click="searchOpen = false; mobileSearchFocus = false"
                                    class="text-[#CE171F] hover:underline"
                                >
                                    Tất cả sản phẩm →
                                </a>
                                <a 
                                    href="{{ route('blog') }}?search={{ urlencode($searchQuery) }}" 
                                    wire:navigate.hover 
                                    @click="searchOpen = false; mobileSearchFocus = false"
                                    class="text-blue-600 hover:underline"
                                >
                                    Tất cả bài viết →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sub-Navigation Bar (Desktop) -->
        <div class="hidden md:block mt-2 pt-2 border-t border-slate-100/90 relative">
            <nav class="flex items-center justify-between gap-6 py-0.5 text-sm font-medium text-[#52606d]">
                <!-- 1. Danh mục Dropdown (Click + Hover, hiển thị đủ 17 nhóm sản phẩm) -->
                <div 
                    class="relative"
                    @mouseenter="openCat()"
                    @mouseleave="closeCat()"
                    @click.outside="catModalOpen = false"
                >
                    <button 
                        type="button"
                        @click="catModalOpen = !catModalOpen"
                        class="flex items-center gap-1.5 py-1 text-sm font-semibold text-[#CE171F] hover:text-[#991B1B] transition-colors whitespace-nowrap cursor-pointer group"
                    >
                        <i class="fa-solid fa-basket-shopping text-sm text-[#CE171F]"></i>
                        <span>Danh Mục Sản Phẩm</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-[#CE171F] transition-transform duration-200" :class="catModalOpen ? 'rotate-180 text-[#CE171F]' : ''"></i>
                    </button>

                    <!-- Category Modal Container (17 nhóm sản phẩm) -->
                    <div 
                        x-show="catModalOpen" 
                        x-transition:enter="transition ease-out duration-150 transform"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100 transform"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                        @mouseenter="openCat()"
                        @mouseleave="closeCat()"
                        class="absolute top-full left-0 pt-2 z-[70] w-[94vw] sm:w-[680px] md:w-[760px] max-w-[780px]"
                        style="display: none;"
                    >
                        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-4">
                            <!-- Header & Search Box -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 mb-2.5 border-b border-slate-100 bg-white">
                                <div class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 shrink-0">
                                    <i class="fa-solid fa-layer-group text-[#CE171F]"></i>
                                    <span>Chọn Nhóm Sản Phẩm ({{ count($navCategories) }} nhóm)</span>
                                </div>
                                <div class="relative w-full sm:w-64">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input 
                                        x-model="catSearch" 
                                        type="text" 
                                        placeholder="Tìm nhanh nhóm sản phẩm..." 
                                        class="w-full pl-8 pr-7 py-1.5 bg-slate-50/80 border border-slate-200/90 rounded-xl text-xs text-slate-700 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition"
                                    >
                                    <button 
                                        x-show="catSearch" 
                                        @click="catSearch = ''" 
                                        type="button"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Grid of Product Categories -->
                            <div 
                                x-ref="catGrid"
                                class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-[min(82vh,620px)] overflow-y-auto pr-1 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full"
                            >
                                @foreach ($navCategories as $cat)
                                    <a 
                                        href="{{ $cat['allUrl'] }}"
                                        wire:navigate.hover
                                        @click="catModalOpen = false"
                                        x-show="!catSearch || '{{ mb_strtolower(addslashes($cat['name'])) }}'.includes(catSearch.toLowerCase().trim())"
                                        class="flex items-center gap-2.5 py-2 px-2.5 rounded-xl bg-[#f8faf9] hover:bg-red-50 text-slate-700 hover:text-[#CE171F] border border-slate-100 hover:border-red-200 transition cursor-pointer group/catitem shadow-2xs min-h-[46px]"
                                    >
                                        <div class="w-8.5 h-8.5 rounded-lg bg-white border border-slate-100 flex items-center justify-center shrink-0 overflow-hidden group-hover/catitem:border-red-200 transition">
                                            @if (!empty($cat['image']))
                                                <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" class="w-full h-full object-cover group-hover/catitem:scale-110 transition-transform">
                                            @else
                                                <i class="{{ $cat['icon'] }} text-[#CE171F] text-sm"></i>
                                            @endif
                                        </div>
                                        <span class="text-xs font-semibold leading-snug line-clamp-2 flex-1">{{ $cat['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>

                            <!-- Bottom Link: Xem tất cả sản phẩm -->
                            <div class="pt-2.5 mt-2.5 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Chọn nhóm hàng để xem các sản phẩm tương ứng</span>
                                <a 
                                    href="{{ route('category') }}" 
                                    wire:navigate.hover 
                                    @click="catModalOpen = false"
                                    class="text-xs font-semibold text-[#CE171F] hover:underline inline-flex items-center gap-1.5 transition"
                                >
                                    <span>Xem tất cả sản phẩm</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Chính Sách & Quy Trình Dropdown (4 chính sách + 1 quy trình) -->
                @if (count($navPolicies) > 0)
                    <div 
                        class="relative"
                        @mouseenter="openPolicy()"
                        @mouseleave="closePolicy()"
                        @click.outside="policyModalOpen = false"
                    >
                        <button 
                            type="button"
                            @click="policyModalOpen = !policyModalOpen"
                            class="flex items-center gap-1.5 py-1 text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer group {{ request()->routeIs('policy*') ? 'text-[#CE171F]' : 'text-slate-700 hover:text-[#CE171F]' }}"
                        >
                            <i class="fa-solid fa-file-shield text-xs {{ request()->routeIs('policy*') ? 'text-[#CE171F]' : 'text-slate-400 group-hover:text-[#CE171F]' }} transition-colors" :class="policyModalOpen ? 'text-[#CE171F]' : ''"></i>
                            <span>Chính Sách & Quy Trình</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-[#CE171F] transition-transform duration-200" :class="policyModalOpen ? 'rotate-180 text-[#CE171F]' : ''"></i>
                        </button>

                        <!-- Policy Dropdown Container -->
                        <div 
                            x-show="policyModalOpen" 
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                            @mouseenter="openPolicy()"
                            @mouseleave="closePolicy()"
                            class="absolute top-full left-0 pt-2 z-[70] w-72"
                            style="display: none;"
                        >
                            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-2.5 space-y-1">
                                <div class="px-3 py-1.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 mb-1">
                                    Chính sách & Quy trình DNV
                                </div>
                                @foreach ($navPolicies as $pol)
                                    <a 
                                        href="{{ $pol['url'] }}"
                                        wire:navigate.hover
                                        @click="policyModalOpen = false"
                                        class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-[#f8faf9] hover:bg-red-50 text-slate-700 hover:text-[#CE171F] border border-transparent hover:border-red-200/80 text-xs font-semibold transition group/politem"
                                    >
                                        <span class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-[#CE171F] shrink-0 group-hover/politem:bg-[#CE171F] group-hover/politem:text-white transition">
                                                <i class="{{ $pol['icon'] }} text-xs"></i>
                                            </span>
                                            <span>{{ $pol['title'] }}</span>
                                        </span>
                                        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300 group-hover/politem:text-[#CE171F] group-hover/politem:translate-x-0.5 transition-all"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Secondary Nav Links (Evenly distributed across the bar) -->
                @if (!empty($header['nav_links']) && is_array($header['nav_links']) && count($header['nav_links']) > 0)
                    @foreach ($header['nav_links'] as $nav)
                        @if (!empty($nav['name']) && !empty($nav['url']) && ($nav['url'] !== '/danh-muc'))
                            @php
                                $navUrl = $nav['url'];
                                $isCurrent = request()->is(trim($navUrl, '/')) || (url()->current() === url($navUrl));
                            @endphp
                            <a 
                                href="{{ $navUrl }}" 
                                {{ !empty($nav['target_blank']) ? 'target="_blank" rel="noopener"' : 'wire:navigate.hover' }}
                                class="flex items-center gap-1.5 py-1 transition-colors whitespace-nowrap {{ $isCurrent ? 'text-[#CE171F] font-semibold' : 'text-slate-600 hover:text-[#CE171F]' }}"
                            >
                                @if (!empty($nav['icon']))
                                    <i class="{{ $nav['icon'] }} text-xs {{ $isCurrent ? 'text-[#CE171F]' : 'text-slate-400' }}"></i>
                                @endif
                                <span>{{ $nav['name'] }}</span>
                            </a>
                        @endif
                    @endforeach
                @endif
            </nav>
        </div>
    </div>

    <!-- Mobile Slide Drawer with Dynamic Categories & Brands Rendering -->
    <template x-teleport="body">
        <div 
            x-show="mobileOpen" 
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[99999]"
            style="display: none;"
            @click="mobileOpen = false"
            @keydown.escape.window="mobileOpen = false"
        >
            <div 
                @click.stop
                class="fixed inset-y-0 right-0 w-4/5 max-w-sm h-full h-dvh bg-white shadow-2xl flex flex-col justify-between overflow-y-auto z-[100000]"
                x-show="mobileOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                <div class="p-5">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        @if ($headerLogoUrl)
                            <a href="{{ route('home') }}" wire:navigate.hover class="flex items-center">
                                <img src="{{ $headerLogoUrl }}" alt="{{ $brandName ?? config('app.name') }}" class="h-10 sm:h-12 w-auto object-contain">
                            </a>
                        @elseif ($brandName)
                            <span class="font-bold text-slate-800 text-base">{{ $brandName }}</span>
                        @endif
                        <button @click="mobileOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition cursor-pointer">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Navigation List with Categories & Brands Accordions -->
                    <div class="mt-4 space-y-1.5 text-sm font-medium">
                        <!-- 1. Expandable Categories Accordion -->
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <button 
                                @click="mobileCatOpen = !mobileCatOpen"
                                type="button" 
                                class="w-full flex items-center justify-between p-2.5 text-slate-800 bg-[#f8faf9] hover:bg-red-50 transition cursor-pointer"
                            >
                                <span class="flex items-center gap-2.5 font-semibold text-xs text-[#991B1B]">
                                    <i class="fa-solid fa-basket-shopping text-[#CE171F]"></i>
                                    <span>Danh mục sản phẩm ({{ count($navCategories) }})</span>
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="mobileCatOpen ? 'rotate-180 text-[#CE171F]' : ''"></i>
                            </button>
                            
                            <div x-show="mobileCatOpen" x-collapse class="p-2 space-y-1 bg-white max-h-[320px] overflow-y-auto">
                                @foreach ($navCategories as $cat)
                                    <a 
                                        @click="mobileOpen = false" 
                                        href="{{ $cat['allUrl'] }}" 
                                        wire:navigate.hover 
                                        class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:text-[#CE171F] hover:bg-red-50/60 transition"
                                    >
                                        <div class="w-7 h-7 rounded-md bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 overflow-hidden">
                                            @if (!empty($cat['image']))
                                                <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="{{ $cat['icon'] }} text-[#CE171F] text-xs"></i>
                                            @endif
                                        </div>
                                        <span>{{ $cat['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- 2. Expandable Policies Accordion (4 chính sách + 1 quy trình) -->
                        @if (count($navPolicies) > 0)
                            <div class="border border-slate-100 rounded-xl overflow-hidden">
                                <button 
                                    @click="mobilePolicyOpen = !mobilePolicyOpen"
                                    type="button" 
                                    class="w-full flex items-center justify-between p-2.5 text-slate-800 bg-[#f8faf9] hover:bg-red-50 transition cursor-pointer"
                                >
                                    <span class="flex items-center gap-2.5 font-semibold text-xs text-slate-700">
                                        <i class="fa-solid fa-file-shield text-[#CE171F]"></i>
                                        <span>Chính sách & Quy trình ({{ count($navPolicies) }})</span>
                                    </span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="mobilePolicyOpen ? 'rotate-180 text-[#CE171F]' : ''"></i>
                                </button>
                                
                                <div x-show="mobilePolicyOpen" x-collapse class="p-2 space-y-1 bg-white">
                                    @foreach ($navPolicies as $pol)
                                        <a 
                                            @click="mobileOpen = false" 
                                            href="{{ $pol['url'] }}" 
                                            wire:navigate.hover 
                                            class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:text-[#CE171F] hover:bg-red-50/60 transition"
                                        >
                                            <i class="{{ $pol['icon'] }} text-[#CE171F] text-xs w-4 text-center"></i>
                                            <span>{{ $pol['title'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Dynamic Nav Links -->
                        @if (!empty($header['nav_links']) && is_array($header['nav_links']))
                            @foreach ($header['nav_links'] as $nav)
                                @if (!empty($nav['name']) && !empty($nav['url']))
                                    <a 
                                        @click="mobileOpen = false" 
                                        href="{{ $nav['url'] }}" 
                                        {{ !empty($nav['target_blank']) ? 'target="_blank" rel="noopener"' : 'wire:navigate.hover' }}
                                        class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 hover:bg-red-50 hover:text-[#CE171F] transition text-xs font-semibold"
                                    >
                                        <i class="{{ !empty($nav['icon']) ? $nav['icon'] : 'fa-solid fa-link' }} text-[#CE171F] w-4"></i>
                                        <span>{{ $nav['name'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                        @if (!empty($header['show_cart']))
                            <a @click="mobileOpen = false" href="{{ route('cart') }}" class="flex items-center gap-3 p-2.5 rounded-xl text-slate-700 hover:bg-red-50 hover:text-[#CE171F] transition text-xs font-semibold">
                                <i class="fa-solid fa-basket-shopping text-[#CE171F] w-4"></i> Giỏ hàng ({{ $cartCount }})
                            </a>
                        @endif
                    </div>

                    @php
                        $drawerHotline = $header['topbar_hotline'] ?? ($footer['contact_phone'] ?? null);
                        $drawerHours = $footer['contact_hours'] ?? null;
                        $drawerAddress = $footer['contact_address'] ?? null;
                    @endphp
                    @if ($drawerHotline || $drawerHours || $drawerAddress)
                        <div class="mt-5 pt-4 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                            @if ($drawerHotline)
                                <p class="flex items-center gap-2"><i class="fa-solid fa-phone text-[#CE171F]"></i> Hotline: <strong class="text-slate-800">{{ $drawerHotline }}</strong></p>
                            @endif
                            @if ($drawerHours)
                                <p class="flex items-center gap-2"><i class="fa-solid fa-clock text-[#CE171F]"></i> {{ $drawerHours }}</p>
                            @endif
                            @if ($drawerAddress)
                                <p class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-[#CE171F]"></i> {{ $drawerAddress }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                @if (!empty($header['show_account']))
                    <div class="p-4 bg-slate-50 border-t border-slate-100">
                        @auth
                            <a @click="mobileOpen = false" href="{{ route('account') }}" wire:navigate.hover class="w-full py-2.5 bg-[#CE171F] text-white text-center text-xs font-semibold rounded-xl block hover:bg-[#B91C1C] transition">
                                Tài Khoản ({{ auth()->user()->name }})
                            </a>
                        @else
                            <a @click="mobileOpen = false" href="{{ route('login') }}" wire:navigate.hover class="w-full py-2.5 bg-[#CE171F] text-white text-center text-xs font-semibold rounded-xl block hover:bg-[#B91C1C] transition">
                                Đăng Nhập / Đăng Ký
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </template>
</header>
