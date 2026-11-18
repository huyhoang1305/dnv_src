@php
    $iconMap = [
        'chinh-sach-ban-hang' => 'fa-solid fa-handshake',
        'chinh-sach-bao-mat' => 'fa-solid fa-shield-halved',
        'chinh-sach-giao-hang' => 'fa-solid fa-truck-fast',
        'chinh-sach-thanh-toan' => 'fa-solid fa-credit-card',
        'quy-trinh-mua-hang' => 'fa-solid fa-clipboard-check',
    ];

    $currentTitle = $policyContent?->title ?? 'Chính Sách & Quy Định';
    $currentDesc = $policyContent?->summary ?? '';
    $currentIcon = $iconMap[$slug] ?? 'fa-solid fa-file-shield';

    $footerCfg = $setting?->interface_settings['footer'] ?? [];
    $headerCfg = $setting?->interface_settings['header'] ?? [];
    $brandBadge = $headerCfg['brand_name'] ?? ($setting?->general_settings['site_name'] ?? 'DOANH NGHIỆP VIỆT (DNV)');
    $supportPhone = $footerCfg['contact_phone'] ?? '';
    $supportPhoneClean = preg_replace('/[^0-9+]/', '', $supportPhone);
    $supportEmail = $footerCfg['contact_email'] ?? '';
    $supportAddress = $footerCfg['contact_address'] ?? '';
    $supportOffice = $footerCfg['contact_office'] ?? '';
    $supportZalo = !empty($footerCfg['social_zalo'])
        ? $footerCfg['social_zalo']
        : ($supportPhoneClean ? 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $supportPhone) : '#');
@endphp

@push('seo')
    @php
        $siteName = $setting?->general_settings['site_name'] ?? config('app.name');
        $metaTitle = !empty($policyContent?->seo_title) ? $policyContent->seo_title : ($currentTitle . ' - ' . $siteName);
        $metaDesc = !empty($policyContent?->seo_description) ? $policyContent->seo_description : $currentDesc;
        $metaKeywords = $policyContent?->seo_keywords ?? '';
        $metaImage = !empty($policyContent?->image_url)
            ? (get_curator_media_url($policyContent->image_url) ?: asset('storage/logo/seo-dnv.jpg'))
            : (get_curator_media_url($setting?->general_settings['site_icon'] ?? null) ?: asset('storage/logo/seo-dnv.jpg'));
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
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="vi_VN">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
@endpush

<div class="min-h-screen bg-[#f8fafc] pb-16 font-sans">
    
    <!-- Hero Header Banner -->
    <div class="bg-gradient-to-r from-[#991B1B] via-[#CE171F] to-[#b91c1c] text-white py-6 sm:py-14 relative overflow-hidden shadow-md">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-white pointer-events-none">
            <i class="fa-solid fa-file-shield text-9xl"></i>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-white/80 whitespace-nowrap overflow-hidden mb-2 sm:mb-3">
                <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-amber-200 transition shrink-0">Trang chủ</a>
                <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-white/60 shrink-0"></i>
                <a href="{{ route('policy') }}" wire:navigate.hover class="hover:text-amber-200 transition shrink-0">Chính sách</a>
                @if ($policyContent)
                    <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-white/60 shrink-0"></i>
                    <span class="text-white font-semibold min-w-0 truncate">{{ $currentTitle }}</span>
                @endif
            </nav>

            <div class="max-w-3xl">
                @if ($brandBadge)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider bg-white/20 backdrop-blur-xs text-amber-200 border border-white/20 mb-2 sm:mb-3">
                        <i class="{{ $currentIcon }}"></i> {{ $brandBadge }}
                    </span>
                @endif
                <h1 class="text-xl sm:text-3xl md:text-4xl font-extrabold tracking-tight leading-tight">
                    {{ $currentTitle }}
                </h1>
                @if ($currentDesc)
                    <p class="text-xs sm:text-sm text-white/90 mt-2 font-normal leading-relaxed">
                        {{ $currentDesc }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 sm:-mt-8 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            
            <!-- Left Sidebar: Policy Navigation & Company Info (4 Cols) -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- Policy List Navigation -->
                @if (!empty($policyList) && $policyList->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                            <h2 class="text-xs sm:text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-[#CE171F]"></i>
                                <span>DANH MỤC CHÍNH SÁCH</span>
                            </h2>
                        </div>
                        <div class="p-2 space-y-1">
                            @foreach ($policyList as $pItem)
                                @php
                                    $isActive = ($slug === $pItem->slug);
                                    $pIcon = $iconMap[$pItem->slug] ?? 'fa-solid fa-file-shield';
                                    $pUrl = route('policy', ['slug' => $pItem->slug]);
                                @endphp
                                <a 
                                    href="{{ $pUrl }}" 
                                    wire:navigate.hover
                                    class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group {{ $isActive ? 'bg-[#CE171F] text-white shadow-sm' : 'text-slate-700 hover:bg-red-50 hover:text-[#CE171F]' }}"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0 transition {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-red-100 group-hover:text-[#CE171F]' }}">
                                            <i class="{{ $pIcon }}"></i>
                                        </div>
                                        <span class="text-xs sm:text-sm font-bold truncate">{{ $pItem->title }}</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs transition-transform {{ $isActive ? 'text-white' : 'text-slate-300 group-hover:translate-x-1 group-hover:text-[#CE171F]' }}"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Company Support Card -->
                @if ($supportPhone || $supportEmail || $supportAddress || $supportOffice)
                    <div class="bg-gradient-to-br from-white to-amber-50/40 rounded-2xl p-5 shadow-sm border border-amber-200/80 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#CE171F] text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 uppercase">Hỗ Trợ Khách Hàng</div>
                                <div class="text-[11px] text-slate-500">{{ $footerCfg['contact_hours'] ?? 'Tư vấn trực tiếp 24/7' }}</div>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs text-slate-700 pt-2 border-t border-amber-200/60">
                            @if ($supportPhone)
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-phone text-[#CE171F] text-xs mt-0.5 shrink-0"></i>
                                    <div>
                                        <span class="font-bold text-slate-900">Hotline / Zalo:</span>
                                        <a href="tel:{{ $supportPhoneClean }}" class="font-extrabold text-[#CE171F] hover:underline ml-1">{{ $supportPhone }}</a>
                                    </div>
                                </div>
                            @endif
                            @if ($supportEmail)
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-envelope text-[#CE171F] text-xs mt-0.5 shrink-0"></i>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900">Email:</span>
                                        <a href="mailto:{{ $supportEmail }}" class="text-slate-700 hover:text-[#CE171F] hover:underline ml-1 break-all font-medium">{{ $supportEmail }}</a>
                                    </div>
                                </div>
                            @endif
                            @if ($supportAddress)
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-building text-[#CE171F] text-xs mt-0.5 shrink-0"></i>
                                    <div class="text-[11.5px] leading-relaxed">
                                        <span class="font-bold text-slate-900">Trụ sở:</span> {{ $supportAddress }}
                                    </div>
                                </div>
                            @endif
                            @if ($supportOffice)
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-location-dot text-[#CE171F] text-xs mt-0.5 shrink-0"></i>
                                    <div class="text-[11.5px] leading-relaxed">
                                        <span class="font-bold text-slate-900">Văn phòng liên hệ:</span> {{ $supportOffice }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($supportZalo && $supportZalo !== '#')
                            <div class="pt-2">
                                <a 
                                    href="{{ $supportZalo }}" 
                                    target="_blank" 
                                    rel="noopener"
                                    class="w-full py-2.5 px-4 rounded-xl bg-[#0068FF] hover:bg-[#0055d4] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-xs hover:shadow-md transition cursor-pointer"
                                >
                                    <img src="{{ asset('images/zalo-icon.webp') }}" alt="Zalo" class="w-4 h-4 object-contain"> Chat Hỗ Trợ Zalo
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            <!-- Right Content Area (8 Cols) -->
            <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 lg:p-10 shadow-sm border border-slate-200/90 text-slate-800">
                @if (!empty($policyContent?->body))
                    <div class="policy-rich-content text-xs sm:text-sm text-slate-700 leading-relaxed space-y-4
                        [&_h2]:text-xl sm:[&_h2]:text-2xl [&_h2]:font-black [&_h2]:text-[#991B1B] [&_h2]:uppercase [&_h2]:tracking-tight [&_h2]:border-b [&_h2]:border-slate-100 [&_h2]:pb-3 [&_h2]:mb-4
                        [&_h3]:text-base sm:[&_h3]:text-lg [&_h3]:font-bold [&_h3]:text-slate-900 [&_h3]:mt-6 [&_h3]:mb-2.5 [&_h3]:border-l-4 [&_h3]:border-[#CE171F] [&_h3]:pl-3
                        [&_h4]:text-sm sm:[&_h4]:text-base [&_h4]:font-bold [&_h4]:text-slate-800 [&_h4]:mt-4 [&_h4]:mb-2
                        [&_p]:mb-3 [&_p]:leading-relaxed
                        [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-2 [&_ul]:my-3 [&_ul]:bg-slate-50/70 [&_ul]:p-4 [&_ul]:pl-8 [&_ul]:rounded-2xl [&_ul]:border [&_ul]:border-slate-200/70
                        [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-2 [&_ol]:my-3 [&_ul]:bg-slate-50/70 [&_ol]:p-4 [&_ol]:pl-8 [&_ol]:rounded-2xl [&_ol]:border [&_ol]:border-slate-200/70
                        [&_li]:leading-relaxed
                        [&_strong]:text-slate-900 [&_strong]:font-bold
                        [&_a]:text-[#CE171F] [&_a]:font-bold hover:[&_a]:underline
                        [&_blockquote]:bg-amber-50/70 [&_blockquote]:border-l-4 [&_blockquote]:border-[#CE171F] [&_blockquote]:rounded-r-2xl [&_blockquote]:p-4 [&_blockquote]:my-4 [&_blockquote]:text-slate-700 [&_blockquote_p]:mb-1.5 last:[&_blockquote_p]:mb-0
                        [&_table]:w-full [&_table]:text-left [&_table]:border-collapse [&_table]:my-4 [&_table]:rounded-2xl [&_table]:overflow-hidden [&_table]:border [&_table]:border-slate-200
                        [&_thead]:bg-slate-100/90 [&_thead]:text-slate-900 [&_thead]:font-bold [&_thead]:border-b [&_thead]:border-slate-200
                        [&_th]:py-3 [&_th]:px-3.5 [&_th]:border [&_th]:border-slate-200/80
                        [&_td]:py-3 [&_td]:px-3.5 [&_td]:border [&_td]:border-slate-200/70 [&_td]:align-top
                        [&_tr:hover]:bg-slate-50/60
                        [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-2xl [&_img]:my-4">
                        {!! $policyContent->body !!}
                    </div>
                @else
                    <div class="py-12 text-center text-slate-400 text-sm">
                        Nội dung chính sách đang được cập nhật.
                    </div>
                @endif
            </div>

        </div>
    </div>

</div>
