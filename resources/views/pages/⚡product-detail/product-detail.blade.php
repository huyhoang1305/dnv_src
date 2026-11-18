<div x-data="{ 
    cartToast: false, 
    toastMsg: '',
    descExpanded: false,
    showToast(msg) {
        this.toastMsg = msg;
        this.cartToast = true;
        setTimeout(() => this.cartToast = false, 2500);
    }
}">
    <!-- Page SEO Meta Tags -->
    <livewire:seo.product-detail :slug="$slug" />

    <!-- Breadcrumb Navigation -->
    @php
        $primaryCat = $product?->category ?? $product?->categories?->first();
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2.5 pb-1 sm:pt-4 sm:pb-2">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0 hidden sm:inline"></i>
            <a href="{{ route('category') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0 hidden sm:inline">Danh mục</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <a href="{{ route('category', $primaryCat?->slug ?? 'all') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0 max-w-[110px] sm:max-w-none truncate">{{ $primaryCat?->name ?? 'Sản phẩm' }}</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-medium min-w-0 flex-1 truncate">{{ $product?->name }}</span>
        </nav>
    </div>

    <!-- Toast Notification -->
    <div 
        x-show="cartToast" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="fixed bottom-6 right-6 z-50 bg-[#181112] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-red-600/40"
        style="display: none;"
    >
        <div class="w-7 h-7 rounded-full bg-[#CE171F] flex items-center justify-center text-white text-xs shrink-0">
            <i class="fa-solid fa-check"></i>
        </div>
        <div>
            <div class="text-xs font-semibold" x-text="toastMsg"></div>
            <div class="text-[10px] text-emerald-200">Đã cập nhật giỏ hàng hỏa tốc 1h</div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 space-y-6">

        <!-- Main Product Card (Gallery + Info) -->
        <article class="bg-white rounded-2xl border border-slate-100 p-5 sm:p-7 shadow-xs">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">

                <!-- Left Column: Gallery / Product Image (5 cols) -->
                @php
                    $mainImgUrl = $selectedImageUrl
                        ?: curator_url($product?->image_url, 600, 600)
                        ?: asset('storage/logo/favicon-dnv.png');
                @endphp
                <div class="md:col-span-5 flex flex-col gap-3">
                    <div class="relative w-full aspect-square bg-[#fbfdfc] border border-slate-100 rounded-2xl overflow-hidden flex items-center justify-center p-6 group">
                        <img 
                            src="{{ $mainImgUrl }}" 
                            alt="{{ $product?->name }}" 
                            class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300"
                        >
                        <span class="absolute top-3 left-3 text-[10px] font-bold px-2.5 py-1 rounded-lg {{ $product?->badge_color ?? 'bg-[#CE171F]' }} text-white shadow-2xs">
                            {{ $product?->badge ?? 'Hàng Chính Hãng' }}
                        </span>
                    </div>

                    <!-- Mini Thumbnails -->
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <button 
                            type="button"
                            wire:click="$set('selectedImageUrl', null)"
                            class="w-16 h-16 rounded-xl border-2 {{ empty($selectedImageUrl) ? 'border-[#CE171F]' : 'border-slate-200 hover:border-slate-300' }} bg-[#fbfdfc] p-1.5 flex items-center justify-center cursor-pointer transition"
                        >
                            <img src="{{ curator_url($product?->image_url, 150, 150) ?: asset('storage/logo/favicon-dnv.png') }}" alt="{{ $product?->name }}" class="w-full h-full object-contain">
                        </button>
                        @if(is_array($product?->gallery))
                            @foreach($product->gallery as $galMediaId)
                                @php $galUrl = curator_url($galMediaId, 600, 600); @endphp
                                @if($galUrl)
                                    <button 
                                        type="button"
                                        wire:click="$set('selectedImageUrl', '{{ $galUrl }}')"
                                        class="w-16 h-16 rounded-xl border-2 {{ $selectedImageUrl === $galUrl ? 'border-[#CE171F]' : 'border-slate-200 hover:border-slate-300' }} bg-[#fbfdfc] p-1.5 flex items-center justify-center cursor-pointer transition"
                                    >
                                        <img src="{{ curator_url($galMediaId, 150, 150) ?: $galUrl }}" alt="{{ $product?->name }}" class="w-full h-full object-contain">
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Right Column: Product Info & Actions (7 cols) -->
                <div class="md:col-span-7 flex flex-col justify-between space-y-5">
                    <div>
                        <!-- Brand Badge -->
                        <span class="inline-block text-[11px] font-bold uppercase tracking-wider text-[#CE171F] bg-[#eef7f1] px-3 py-0.5 rounded-full mb-2">
                            {{ $product?->brand?->name ?? 'DNV' }}
                        </span>

                        <!-- Product Title -->
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-900 leading-snug">
                            {{ $product?->name }}
                        </h1>

                        <!-- Ratings & Sold Count -->
                        @php
                            $prodSettings = $setting?->interface_settings['products'] ?? [];
                            $approvedReviews = $product?->reviews ?? collect();
                            $reviewCount = $approvedReviews->count();
                            $avgRating = $reviewCount > 0 ? number_format($approvedReviews->avg('rating'), 1) : '5.0';
                        @endphp
                        <div class="flex items-center gap-3 text-xs text-slate-500 pt-2 font-normal flex-wrap">
                            @if($reviewCount > 0)
                                <div class="flex items-center gap-1">
                                    <span class="font-bold text-slate-800">{{ $avgRating }}</span>
                                    <div class="text-amber-400 text-xs">
                                        {{ str_repeat('★', (int) round((float)$avgRating)) }}{{ str_repeat('☆', 5 - (int) round((float)$avgRating)) }}
                                    </div>
                                    <span class="text-slate-400">({{ $reviewCount }} đánh giá)</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1 text-slate-400">
                                    <span>Chưa có đánh giá</span>
                                </div>
                            @endif
                            <span class="text-slate-300">|</span>
                            <span>Đã bán <strong class="text-slate-800 font-semibold">{{ $product?->sold_count ?? 0 }}</strong></span>
                            @if(!empty($prodSettings['show_stock']))
                                <span class="text-slate-300">|</span>
                                <span>Hàng tồn <strong class="text-slate-800 font-semibold">{{ $product?->stock_quantity ?? 0 }}</strong></span>
                            @endif
                        </div>

                        <!-- Price Section (Dynamically updates with selected Variant) -->
                        <div class="mt-4 p-4 rounded-xl bg-slate-50/70 border border-slate-100 space-y-2">
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl sm:text-3xl font-bold text-[#CE171F] tracking-tight">
                                    {{ number_format($this->activePrice, 0, ',', '.') }}đ
                                </span>
                                @if(!$this->selectedVariant && $product?->sale_price && $product?->sale_price < $product?->base_price)
                                    <span class="text-xs text-slate-400 line-through font-normal">
                                        {{ number_format($product?->base_price, 0, ',', '.') }}đ
                                    </span>
                                @endif
                                @if($this->selectedVariant)
                                    <span class="text-xs font-semibold text-slate-500">/ {{ $this->selectedVariant->label }}</span>
                                @elseif(!empty($product?->unit))
                                    <span class="text-xs font-semibold text-slate-500">/ {{ $product->unit }}</span>
                                @endif
                            </div>

                            @if(!empty($product?->weight) || !empty($product?->unit))
                            <div class="flex flex-wrap items-center gap-2">
                                @if(!empty($product?->unit))
                                <div class="inline-flex items-center gap-1.5 bg-white px-3 py-1 rounded-lg border border-slate-200 text-xs text-slate-600 font-medium">
                                    <i class="fa-solid fa-tag text-slate-400 text-xs"></i>
                                    <span>Đơn vị tính: <strong class="text-slate-800 font-semibold">{{ $product->unit }}</strong></span>
                                </div>
                                @endif
                                @if(!empty($product?->weight))
                                <div class="inline-flex items-center gap-1.5 bg-white px-3 py-1 rounded-lg border border-slate-200 text-xs text-slate-600 font-medium">
                                    <i class="fa-solid fa-weight-hanging text-slate-400 text-xs"></i>
                                    <span>Trọng lượng / Thể tích: <strong class="text-slate-800 font-semibold">{{ $product->weight }}</strong></span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Variant Selector Block (Dung tích / Màu sắc / Phân loại) -->
                        @if($product?->variants && $product->variants->isNotEmpty())
                            <div class="mt-5 space-y-2">
                                <label class="text-xs font-semibold text-slate-700 block">
                                    Chọn phân loại / dung tích:
                                </label>
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach($product->variants as $variant)
                                        @php
                                            $isSelectedVar = (int) $selectedVariantId === (int) $variant->id;
                                        @endphp
                                        <button
                                            wire:key="prod-var-{{ $variant->id }}"
                                            wire:click="selectVariant({{ $variant->id }})"
                                            type="button"
                                            class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer flex items-center gap-2 {{ $isSelectedVar ? 'border-[#CE171F] bg-red-50 text-[#CE171F] ring-1 ring-[#CE171F]/30 shadow-2xs' : 'border-slate-200 bg-white text-slate-700 hover:border-red-300 hover:text-[#CE171F]' }}"
                                        >
                                            <span>{{ $variant->label }}</span>
                                            <span class="text-[11px] {{ $isSelectedVar ? 'text-[#CE171F] font-bold' : 'text-slate-400 font-normal' }}">
                                                ({{ number_format($variant->effective_price, 0, ',', '.') }}đ)
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Quantity Selector Block -->
                        <div class="mt-5 space-y-2">
                            <label class="text-xs font-semibold text-slate-700">Số lượng:</label>
                            <div class="flex items-center gap-3">
                                <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                                    <button 
                                        wire:click="decrementQty"
                                        type="button" 
                                        class="w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition cursor-pointer"
                                        aria-label="Giảm"
                                    >
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    </button>
                                    <span class="w-10 text-center text-xs font-bold text-slate-800">
                                        {{ $quantity }}
                                    </span>
                                    <button 
                                        wire:click="incrementQty"
                                        type="button" 
                                        class="w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition cursor-pointer"
                                        aria-label="Tăng"
                                    >
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                </div>

                                <div class="text-xs text-slate-400 font-normal">
                                    Sẵn hàng chính hãng tại DNV
                                </div>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                            <button 
                                wire:click="addToCart"
                                wire:loading.attr="disabled"
                                type="button" 
                                class="w-full sm:flex-1 py-3 px-6 rounded-full bg-[#CE171F] hover:bg-[#3d6b4d] text-white font-semibold text-xs flex items-center justify-center gap-2 transition-all shadow-xs cursor-pointer disabled:opacity-75"
                            >
                                <i wire:loading.remove wire:target="addToCart" class="fa-solid fa-cart-plus text-sm"></i>
                                <i wire:loading wire:target="addToCart" class="fa-solid fa-spinner fa-spin text-sm"></i>
                                <span>Thêm vào giỏ hàng</span>
                            </button>

                            <button 
                                wire:click="buyNow"
                                wire:loading.attr="disabled"
                                type="button" 
                                class="w-full sm:flex-1 py-3 px-6 rounded-full bg-[#181112] hover:bg-black text-white font-semibold text-xs flex items-center justify-center gap-2 transition-all shadow-xs cursor-pointer disabled:opacity-75"
                            >
                                <i wire:loading.remove wire:target="buyNow" class="fa-solid fa-bolt text-amber-400 text-sm"></i>
                                <i wire:loading wire:target="buyNow" class="fa-solid fa-spinner fa-spin text-sm"></i>
                                <span>Mua ngay hỏa tốc 1h</span>
                            </button>
                        </div>
                    </div>

                    <!-- Guarantee Bar -->
                    @php
                        $guaranteeBadges = $prodSettings['guarantee_badges'] ?? [];
                    @endphp
                    @if (!empty($guaranteeBadges) && is_array($guaranteeBadges))
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-5 border-t border-slate-100">
                        @foreach ($guaranteeBadges as $gb)
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="w-8 h-8 rounded-lg bg-red-100 text-[#CE171F] flex items-center justify-center text-sm shrink-0">
                                    <i class="{{ $gb['icon'] ?? 'fa-regular fa-circle-check' }}"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">{{ $gb['title'] ?? '' }}</div>
                                    <div class="text-[10.5px] text-slate-500 font-normal">{{ $gb['subtitle'] ?? '' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
        </article>

        <!-- Product Specifications & Details -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Specs & Description (8 cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Specs Section -->
                <section class="bg-white rounded-2xl border border-slate-100 p-5 sm:p-7 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-list-check text-[#CE171F]"></i>
                        <span>Thông tin sản phẩm</span>
                    </h2>

                    <div class="divide-y divide-slate-100 text-xs">
                        <div class="py-2.5 grid grid-cols-3">
                            <span class="text-slate-400 font-normal">Thương hiệu</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ $product?->brand?->name ?? 'DNV' }}</span>
                        </div>
                        <div class="py-2.5 grid grid-cols-3">
                            <span class="text-slate-400 font-normal">Xuất xứ</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ $product?->brand?->origin ?? 'Việt Nam' }}</span>
                        </div>
                        <div class="py-2.5 grid grid-cols-3">
                            <span class="text-slate-400 font-normal">Đơn vị</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ $product?->unit ?? 'Cái' }}</span>
                        </div>
                        <div class="py-2.5 grid grid-cols-3">
                            <span class="text-slate-400 font-normal">Quy cách / Khối lượng</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ $product?->weight ?? $product?->unit ?? '1 cái' }}</span>
                        </div>
                        <div class="py-2.5 grid grid-cols-3">
                            <span class="text-slate-400 font-normal">Mã sản phẩm</span>
                            <span class="col-span-2 font-semibold text-slate-800">{{ $product?->sku ?? 'SP-' . $product?->id }}</span>
                        </div>
                    </div>

                    @if ($product?->brand)
                        <div class="pt-2 flex items-center gap-3">
                            <a href="{{ route('category') }}?brand={{ $product->brand->slug }}" wire:navigate.hover class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#CE171F] hover:underline">
                                <span>Xem thêm sản phẩm {{ $product->brand->name }}</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    @endif
                </section>

                <!-- Description Section -->
                <section class="bg-white rounded-2xl border border-slate-100 p-5 sm:p-7 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fa-solid fa-circle-info text-[#CE171F]"></i>
                        <span>Mô tả sản phẩm</span>
                    </h2>

                    <div class="relative">
                        <div 
                            class="prose prose-slate max-w-none text-slate-600 text-xs sm:text-sm leading-relaxed space-y-3 font-normal overflow-hidden transition-all duration-300"
                            :class="descExpanded ? 'max-h-none' : 'max-h-48'"
                        >
                            @if (!empty($product?->content))
                                {!! $product->content !!}
                            @else
                                <p>{{ $product?->description ?? 'Đang cập nhật thông tin mô tả chi tiết sản phẩm.' }}</p>
                            @endif

                            @if (!empty($prodSettings['commitment_note']))
                                <p class="text-xs text-[#2d5d3f] font-semibold pt-2">
                                    {{ $prodSettings['commitment_note'] }}
                                </p>
                            @endif
                        </div>

                        <!-- Fade overlay when collapsed -->
                        <div 
                            x-show="!descExpanded" 
                            class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-white to-transparent pointer-events-none"
                        ></div>
                    </div>

                    <div class="text-center pt-2">
                        <button 
                            @click="descExpanded = !descExpanded"
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-[#4d835f] text-[#CE171F] hover:bg-[#CE171F] hover:text-white transition-all text-xs font-semibold cursor-pointer shadow-2xs"
                        >
                            <span x-text="descExpanded ? 'Thu gọn nội dung' : 'Xem chi tiết mô tả'"></span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="descExpanded ? 'rotate-180' : ''"></i>
                        </button>
                    </div>
                </section>

                <!-- Customer Reviews Section (Dynamic from Database) -->
                <section class="bg-white rounded-2xl border border-slate-100 p-5 sm:p-7 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                        @if ($reviewCount > 0)
                            <div class="flex items-center gap-2">
                                <span class="text-xl font-bold text-slate-800">{{ $avgRating }}</span>
                                <div class="text-amber-400 text-sm">
                                    {{ str_repeat('★', (int) round((float)$avgRating)) }}{{ str_repeat('☆', 5 - (int) round((float)$avgRating)) }}
                                </div>
                                <span class="text-xs text-slate-500 font-normal">Đánh giá sản phẩm ({{ $reviewCount }} nhận xét)</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-slate-700 font-semibold text-sm">
                                <i class="fa-regular fa-star text-amber-500"></i>
                                <span>Đánh giá sản phẩm</span>
                                <span class="text-xs text-slate-400 font-normal">(Chưa có đánh giá nào)</span>
                            </div>
                        @endif
                    </div>

                    @if ($approvedReviews->isNotEmpty())
                        <div class="divide-y divide-slate-100 space-y-4">
                            @foreach ($approvedReviews as $rev)
                                <div class="pt-3 first:pt-0 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-red-100 text-[#CE171F] flex items-center justify-center text-xs font-bold uppercase">
                                                {{ mb_substr($rev->customer_name ?: 'K', 0, 1) }}
                                            </div>
                                            <span class="text-xs font-semibold text-slate-800">{{ $rev->customer_name }}</span>
                                        </div>
                                        <div class="text-amber-400 text-[11px]">
                                            {{ str_repeat('★', max(1, min(5, (int) $rev->rating))) }}{{ str_repeat('☆', 5 - max(1, min(5, (int) $rev->rating))) }}
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-600 font-normal leading-relaxed">
                                        "{{ $rev->content }}"
                                    </p>
                                    <div class="text-[10px] text-slate-400">{{ $rev->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 bg-slate-50/70 rounded-xl border border-slate-100 space-y-1">
                            <i class="fa-regular fa-comment-dots text-xl text-slate-300"></i>
                            <p class="text-xs text-slate-500">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên nhận xét!</p>
                        </div>
                    @endif

                    <!-- Submit Review Form -->
                    <form wire:submit.prevent="submitReview" class="pt-4 border-t border-slate-100 space-y-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Gửi nhận xét của bạn</h3>

                        @if ($reviewMessage)
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                                <i class="fa-solid fa-circle-check mr-1"></i> {{ $reviewMessage }}
                            </div>
                        @endif

                        @if ($reviewError)
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $reviewError }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <input
                                    type="text"
                                    wire:model="reviewName"
                                    placeholder="Họ và tên của bạn *"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#CE171F]"
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-600 font-medium shrink-0">Đánh giá:</span>
                                <select
                                    wire:model="reviewRating"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-[#CE171F]"
                                >
                                    <option value="5">★★★★★ (5 sao - Rất hài lòng)</option>
                                    <option value="4">★★★★☆ (4 sao - Hài lòng)</option>
                                    <option value="3">★★★☆☆ (3 sao - Bình thường)</option>
                                    <option value="2">★★☆☆☆ (2 sao - Chưa tốt)</option>
                                    <option value="1">★☆☆☆☆ (1 sao - Kém)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <textarea
                                wire:model="reviewContent"
                                rows="2"
                                placeholder="Chia sẻ cảm nhận của bạn về chất lượng sản phẩm..."
                                class="w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-800 focus:outline-none focus:border-[#CE171F]"
                            ></textarea>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="px-5 py-2 rounded-full bg-[#CE171F] hover:bg-[#b0131a] text-white text-xs font-semibold transition cursor-pointer shadow-2xs"
                            >
                                Gửi đánh giá
                            </button>
                        </div>
                    </form>
                </section>

            </div>

            <!-- Right Column: Store Service & Related Products (4 cols) -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Fast Delivery Promo -->
                @if (!empty($prodSettings['promo_title']) || !empty($prodSettings['promo_desc']))
                <div class="bg-gradient-to-br from-[#1b3524] to-[#2d5d3f] rounded-2xl p-5 text-white shadow-xs space-y-3">
                    @if (!empty($prodSettings['promo_badge']))
                    <div class="flex items-center gap-2 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>{{ $prodSettings['promo_badge'] }}</span>
                    </div>
                    @endif
                    @if (!empty($prodSettings['promo_title']))
                    <h3 class="text-sm font-bold leading-snug">{{ $prodSettings['promo_title'] }}</h3>
                    @endif
                    @if (!empty($prodSettings['promo_desc']))
                    <p class="text-xs text-emerald-100/90 font-normal leading-relaxed">
                        {{ $prodSettings['promo_desc'] }}
                    </p>
                    @endif
                    <div class="pt-1">
                        @php
                            $prodHotline = $setting?->interface_settings['footer']['contact_phone'] ?? $setting?->interface_settings['header']['topbar_hotline'] ?? '';
                        @endphp
                        @if ($prodHotline)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $prodHotline) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-800 bg-white hover:bg-slate-100 px-4 py-2 rounded-full transition shadow-xs">
                            <i class="fa-solid fa-phone text-[#CE171F]"></i>
                            <span>Hotline: {{ $prodHotline }}</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Related Products Mini Grid -->
                <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-3 border-b border-slate-100">
                        Sản Phẩm Tương Tự
                    </h3>

                    <div class="space-y-3.5">
                        @forelse ($relatedProducts as $rel)
                            <a href="{{ route('product.detail', $rel->slug) }}" wire:navigate.hover class="flex items-center gap-3 group">
                                <div class="w-14 h-14 rounded-xl bg-[#fbfdfc] border border-slate-100 p-1.5 flex items-center justify-center shrink-0">
                                    <img src="{{ curator_url($rel->image_url, 150, 150) ?: asset('storage/logo/favicon-dnv.png') }}" alt="{{ $rel->name }}" class="w-full h-full object-contain">
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-xs font-semibold text-slate-800 line-clamp-2 leading-snug group-hover:text-[#CE171F] transition-colors">
                                        {{ $rel->name }}
                                    </h4>
                                    <div class="text-xs font-bold text-[#CE171F]">
                                        {{ number_format((float) $rel->sale_price > 0 ? $rel->sale_price : $rel->base_price, 0, ',', '.') }}đ
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-400">Không có sản phẩm tương tự.</p>
                        @endforelse
                    </div>
                </div>

            </aside>

        </div>

    </div>

    <!-- Floating Toast Notification -->
    <div 
        x-data="{ show: false, msg: '', timer: null }"
        x-on:prod-cart-toast.window="msg = $event.detail.message; show = true; clearTimeout(timer); timer = setTimeout(() => { show = false }, 3200)"
        x-show="show"
        style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 right-6 z-50 bg-[#181112] text-white px-5 py-3.5 rounded-2xl shadow-xl border border-emerald-500/30 flex items-center gap-3 max-w-sm"
    >
        <div class="w-8 h-8 rounded-full bg-[#CE171F]/20 text-[#FFC700] flex items-center justify-center shrink-0">
            <i class="fa-solid fa-check text-sm"></i>
        </div>
        <div class="flex-1">
            <div class="text-xs font-bold" x-text="msg"></div>
            <a href="{{ route('cart') }}" class="text-[11px] text-emerald-300 underline font-medium hover:text-white mt-0.5 inline-block">
                Xem giỏ hàng ngay &rarr;
            </a>
        </div>
        <button @click="show = false" type="button" class="text-slate-400 hover:text-white cursor-pointer text-xs">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>
