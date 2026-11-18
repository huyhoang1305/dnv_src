<div>
    <!-- Page SEO Meta Tags -->
    <livewire:seo.about />

    @php
        $about = $setting?->interface_settings['about'] ?? [];
        $footer = $setting?->interface_settings['footer'] ?? [];

        // 4 Giá trị cốt lõi
        $coreValuesBadge = $about['core_values_badge'] ?? '';
        $coreValuesTitle = $about['core_values_title'] ?? '';
        $coreValuesSubtitle = $about['core_values_subtitle'] ?? '';
        $coreValues = (!empty($about['core_values']) && is_array($about['core_values'])) ? $about['core_values'] : [];

        // Hồ sơ DNV
        $storyTitle = !empty($about['story_title']) ? $about['story_title'] : 'GIỚI THIỆU DOANH NGHIỆP VIỆT (DNV)';
        $storyLead = !empty($about['story_lead']) ? $about['story_lead'] : null;
        $storyContent = !empty($about['story_content']) && trim(strip_tags($about['story_content'], '<img>')) !== '' ? $about['story_content'] : null;

        // Sơ đồ Quy trình FDA
        $quyTrinhImg = !empty($about['quy_trinh_image'])
            ? (get_curator_media_url($about['quy_trinh_image']) ?: (filter_var($about['quy_trinh_image'], FILTER_VALIDATE_URL) ? $about['quy_trinh_image'] : asset($about['quy_trinh_image'])))
            : null;
        $quyTrinhBadge = $about['quy_trinh_badge'] ?? '';
        $quyTrinhTitle = $about['quy_trinh_title'] ?? '';
        $quyTrinhSubtitle = $about['quy_trinh_subtitle'] ?? '';

        // Thông tin liên hệ
        $contactCompany = !empty($about['contact_company']) ? $about['contact_company'] : ($footer['company_name'] ?? '');
        $contactAddress = !empty($about['contact_address']) ? $about['contact_address'] : ($footer['contact_address'] ?? '');
        $contactOffice = !empty($about['contact_office']) ? $about['contact_office'] : ($footer['contact_office'] ?? '');
        $contactPhone = !empty($about['contact_phone']) ? $about['contact_phone'] : ($footer['contact_phone'] ?? '');
        $contactPhone2 = $about['contact_phone_2'] ?? '';
        $contactEmail = !empty($about['contact_email']) ? $about['contact_email'] : ($footer['contact_email'] ?? '');
        $contactEmail2 = $about['contact_email_2'] ?? '';
        $contactWebsite = !empty($about['contact_website']) ? $about['contact_website'] : ($footer['contact_website'] ?? '');
        $taxCode = !empty($about['tax_code']) ? $about['tax_code'] : ($footer['tax_code'] ?? '');
        $contentManager = $about['content_manager'] ?? '';
    @endphp

    <!-- Breadcrumb Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2.5 pb-1 sm:pt-4 sm:pb-2">
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal whitespace-nowrap overflow-hidden">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-medium min-w-0 truncate">{{ $storyTitle }}</span>
        </nav>
    </div>

    <div class="space-y-8 sm:space-y-12 md:space-y-16 pb-16 bg-white pt-2 sm:pt-4">

        <!-- NỘI DUNG KHỐI GIỚI THIỆU (RICH CONTENT HỒ SƠ NĂNG LỰC DNV) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <article class="article-content space-y-6 text-sm sm:text-base text-slate-700 leading-relaxed text-justify 
                [&_p]:mb-4 
                [&_h2]:border-l-4 [&_h2]:border-[#CE171F] [&_h2]:pl-4 [&_h2]:py-1 [&_h2]:text-xl sm:[&_h2]:text-2xl [&_h2]:font-black [&_h2]:text-slate-900 [&_h2]:uppercase [&_h2]:tracking-wide [&_h2]:mb-6
                [&_h3]:text-lg sm:[&_h3]:text-xl [&_h3]:font-black [&_h3]:text-slate-900 [&_h3]:mt-8 [&_h3]:mb-3 
                [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-2xl [&_img]:mx-auto [&_img]:my-4 [&_img]:shadow-sm
                [&_figure]:my-6 
                [&_strong]:text-slate-900">
                
                <h2>{{ $storyTitle }}</h2>

                @if ($storyLead)
                    <p class="text-base sm:text-lg font-semibold text-slate-800 leading-relaxed border-b border-slate-100 pb-3">
                        {{ $storyLead }}
                    </p>
                @endif

                @if ($storyContent)
                    <div class="prose max-w-none text-slate-700">
                        {!! $storyContent !!}
                    </div>
                @endif

                @if ($quyTrinhImg || $quyTrinhTitle)
                    <div class="my-8 bg-gradient-to-br from-red-50/60 via-amber-50/30 to-white rounded-3xl p-5 sm:p-7 border border-red-200/80 shadow-xs text-center space-y-4">
                        <div class="space-y-1.5 max-w-2xl mx-auto">
                            @if (!empty($quyTrinhBadge))
                                <span class="inline-block px-3 py-0.5 rounded-full bg-[#CE171F] text-white text-[11px] font-bold uppercase tracking-wider">
                                    {{ $quyTrinhBadge }}
                                </span>
                            @endif
                            @if (!empty($quyTrinhTitle))
                                <h3 class="!text-lg sm:!text-xl !font-black !text-slate-900 !m-0 uppercase">
                                    {{ $quyTrinhTitle }}
                                </h3>
                            @endif
                            @if (!empty($quyTrinhSubtitle))
                                <p class="text-xs sm:text-sm text-slate-600 font-medium">
                                    {{ $quyTrinhSubtitle }}
                                </p>
                            @endif
                        </div>
                        @if ($quyTrinhImg)
                            <figure class="my-4">
                                <img 
                                    src="{{ $quyTrinhImg }}" 
                                    alt="{{ $quyTrinhTitle }}"
                                    class="w-full h-auto object-cover rounded-2xl shadow-sm mx-auto"
                                >
                            </figure>
                        @endif
                    </div>
                @endif
            </article>

            <!-- DÒNG SẢN PHẨM XUẤT KHẨU CHỦ LỰC -->
            @if (($about['export_products_active'] ?? true) && !empty($about['export_products_list']) && is_array($about['export_products_list']))
            <div class="my-12 pt-8 border-t border-slate-200">
                <div class="text-center max-w-3xl mx-auto mb-10 space-y-2">
                    @if (!empty($about['export_products_badge']))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-[#CE171F] font-bold text-xs uppercase tracking-wider">
                            <i class="fa-solid fa-earth-americas"></i>
                            {{ $about['export_products_badge'] }}
                        </span>
                    @endif
                    @if (!empty($about['export_products_title']))
                        <h3 class="!text-xl sm:!text-2xl !font-black !text-slate-900 !mt-2 !mb-1 uppercase tracking-tight">
                            {{ $about['export_products_title'] }}
                        </h3>
                    @endif
                    @if (!empty($about['export_products_subtitle']))
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">
                            {{ $about['export_products_subtitle'] }}
                        </p>
                    @endif
                </div>

                @php
                    $themeStyles = [
                        0 => ['from' => 'from-amber-50/40', 'border' => 'border-amber-200/80', 'text' => 'text-amber-700'],
                        1 => ['from' => 'from-red-50/30', 'border' => 'border-red-200/80', 'text' => 'text-[#CE171F]'],
                        2 => ['from' => 'from-orange-50/30', 'border' => 'border-orange-200/80', 'text' => 'text-orange-700'],
                        3 => ['from' => 'from-blue-50/30', 'border' => 'border-blue-200/80', 'text' => 'text-blue-800'],
                        4 => ['from' => 'from-emerald-50/30', 'border' => 'border-emerald-200/80', 'text' => 'text-emerald-800'],
                    ];
                @endphp

                <div class="space-y-10">
                    @foreach ($about['export_products_list'] as $idx => $prod)
                        @php
                            $t = $themeStyles[$idx % count($themeStyles)];
                            $pImg1 = !empty($prod['image']) ? (get_curator_media_url($prod['image']) ?: (filter_var($prod['image'], FILTER_VALIDATE_URL) ? $prod['image'] : asset($prod['image']))) : '';
                            $pImg2 = !empty($prod['image_2']) ? (get_curator_media_url($prod['image_2']) ?: (filter_var($prod['image_2'], FILTER_VALIDATE_URL) ? $prod['image_2'] : asset($prod['image_2']))) : '';
                        @endphp
                        <div class="bg-gradient-to-br {{ $t['from'] }} via-white to-white rounded-3xl p-5 sm:p-7 border {{ $t['border'] }} shadow-xs space-y-4">
                            <div class="text-center space-y-1">
                                @if (!empty($prod['badge']))
                                    <div class="text-xs font-bold {{ $t['text'] }} uppercase tracking-widest">{{ $prod['badge'] }}</div>
                                @endif
                                <h4 class="text-lg sm:text-xl font-black text-slate-900 uppercase">{{ $prod['title'] ?? '' }}</h4>
                                <div class="w-12 h-1 bg-[#CE171F] mx-auto rounded-full mt-1.5"></div>
                            </div>
                            @if ($pImg1 && $pImg2)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
                                    <figure class="text-center">
                                        <img 
                                            src="{{ $pImg1 }}" 
                                            alt="{{ $prod['title'] ?? '' }} - 1" 
                                            class="w-full h-auto object-cover rounded-2xl shadow-sm mx-auto"
                                            loading="lazy"
                                        >
                                    </figure>
                                    <figure class="text-center">
                                        <img 
                                            src="{{ $pImg2 }}" 
                                            alt="{{ $prod['title'] ?? '' }} - 2" 
                                            class="w-full h-auto object-cover rounded-2xl shadow-sm mx-auto"
                                            loading="lazy"
                                        >
                                    </figure>
                                </div>
                            @elseif ($pImg1)
                                <figure class="my-4 text-center">
                                    <img 
                                        src="{{ $pImg1 }}" 
                                        alt="{{ $prod['title'] ?? '' }}" 
                                        class="w-full max-h-[550px] object-contain rounded-2xl shadow-sm mx-auto bg-white"
                                        loading="lazy"
                                    >
                                </figure>
                            @endif
                            @if (!empty($prod['caption']))
                                <figcaption class="text-xs sm:text-sm text-slate-600 text-center italic mt-2.5">
                                    {{ $prod['caption'] }}
                                </figcaption>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- KHỐI DOANH NGHIỆP DNV & LIÊN HỆ -->
            @if ($contactCompany || $contactPhone || $contactEmail || $contactAddress)
            <div class="my-10 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-6 sm:p-8 lg:p-10 shadow-xl border border-slate-700/80 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-700/60">
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#CE171F] text-white font-bold text-xs tracking-wider uppercase shadow-xs">
                            DOANH NGHIỆP VIỆT - NÂNG TẦM NÔNG SẢN VIỆT
                        </span>
                        @if ($contactCompany)
                            <h3 class="text-xl sm:text-2xl font-black !text-white m-0 uppercase tracking-tight" style="color: #ffffff !important;">
                                {{ $contactCompany }}
                            </h3>
                        @endif
                    </div>

                    @php
                        $contactCtaText = !empty($about['contact_cta_text']) ? $about['contact_cta_text'] : 'LIÊN HỆ ĐẶT HÀNG';
                        $contactCtaUrl = !empty($about['contact_cta_url']) ? $about['contact_cta_url'] : route('contact');
                    @endphp
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                        <a wire:navigate.hover href="{{ $contactCtaUrl }}" class="px-5 py-3 rounded-2xl bg-[#CE171F] hover:bg-[#B91C1C] text-white font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-900/30 flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-headset"></i>
                            <span>{{ $contactCtaText }}</span>
                        </a>
                        <a wire:navigate.hover href="{{ route('category') }}" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider transition-all border border-white/20 flex items-center gap-2">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span>Xem Chi Tiết Sản Phẩm</span>
                        </a>
                    </div>
                </div>

                <!-- Grid thông tin liên hệ chính thức -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    @if ($contactPhone)
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-xl bg-[#CE171F]/30 text-red-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Hotline Đặt Hàng</div>
                        <div class="text-sm font-bold text-white flex flex-col gap-1">
                            <a href="tel:{{ preg_replace('/[^0-9]/', '', $contactPhone) }}" class="hover:text-amber-400 transition-colors">{{ $contactPhone }}</a>
                            @if (!empty($contactPhone2))
                                <a href="tel:{{ preg_replace('/[^0-9]/', '', $contactPhone2) }}" class="hover:text-amber-400 transition-colors">{{ $contactPhone2 }}</a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if ($contactEmail)
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Email Liên Hệ</div>
                        <div class="text-xs font-bold text-white flex flex-col gap-1">
                            <a href="mailto:{{ $contactEmail }}" class="hover:text-amber-400 transition-colors truncate">{{ $contactEmail }}</a>
                            @if (!empty($contactEmail2))
                                <a href="mailto:{{ $contactEmail2 }}" class="hover:text-amber-400 transition-colors truncate">{{ $contactEmail2 }}</a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if ($contactAddress)
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Trụ Sở Chính</div>
                        <div class="text-xs text-slate-200 leading-relaxed font-normal">
                            {{ $contactAddress }}
                        </div>
                    </div>
                    @endif

                    @if ($contactOffice || $contentManager)
                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">VPGD & Liên hệ</div>
                        <div class="text-xs text-slate-200 leading-relaxed font-normal">
                            {{ $contactOffice }}
                            @if ($contentManager)
                                <br><span class="text-slate-400 text-[11px]">Phụ trách nội dung: <strong class="!text-white font-semibold">{{ $contentManager }}</strong></span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                @if ($contactCompany)
                <div class="text-center pt-3 border-t border-slate-800 text-[11px] text-slate-400">
                    * Toàn bộ bản quyền thuộc {{ $contactCompany }}.
                </div>
                @endif
            </div>
            @endif
        </section>

        <!-- PHẦN: 4 GIÁ TRỊ CỐT LÕI & MỤC TIÊU PHÁT TRIỂN -->
        @if (count($coreValues) > 0)
        <section class="bg-gradient-to-b from-white via-amber-50/30 to-white py-12 sm:py-14 border-y border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-10">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    @if ($coreValuesBadge)
                        <span class="text-xs font-black uppercase tracking-wider text-[#CE171F]">
                            {{ $coreValuesBadge }}
                        </span>
                    @endif
                    @if ($coreValuesTitle)
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                            {{ $coreValuesTitle }}
                        </h2>
                    @endif
                    @if ($coreValuesSubtitle)
                        <p class="text-xs sm:text-sm text-slate-600">
                            {{ $coreValuesSubtitle }}
                        </p>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                    @foreach ($coreValues as $cv)
                        <div class="bg-white p-5 sm:p-6 rounded-3xl border-2 border-slate-200 hover:border-[#FFC700] shadow-xs space-y-3.5 hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-[#FFC700] text-[#991B1B] flex items-center justify-center text-xl font-black shadow-xs">
                                    {{ $cv['number'] ?? $loop->iteration }}
                                </div>
                                <h3 class="text-base font-bold text-slate-900 leading-snug">
                                    {{ $cv['title'] ?? '' }}
                                </h3>
                                <p class="text-xs text-slate-600 leading-relaxed font-normal">
                                    {{ $cv['description'] ?? '' }}
                                </p>
                            </div>
                            @if (!empty($cv['highlight']))
                                <div class="text-[11px] font-bold text-[#CE171F] flex items-center gap-1">
                                    <i class="fa-solid fa-check"></i>
                                    <span>{{ $cv['highlight'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- ĐIỀU HƯỚNG MUA HÀNG & LIÊN HỆ TƯ VẤN -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2">
            <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <a wire:navigate.hover href="{{ route('category') }}" class="px-5 py-2.5 rounded-full bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs font-bold transition-all shadow-xs inline-flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <span>Khám Phá Danh Mục Sản Phẩm</span>
                    </a>
                </div>

                <a wire:navigate.hover href="{{ route('store') }}" class="text-xs font-bold text-slate-600 hover:text-[#CE171F] transition-colors inline-flex items-center gap-1.5">
                    <span>Liên Hệ Gửi Yêu Cầu Tư Vấn</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

    </div>
</div>
