@php
    $footer = $setting?->interface_settings['footer'] ?? [];
    $footerLogoUrl = get_curator_media_url($setting?->interface_settings['header']['logo'] ?? ($footer['logo'] ?? null));

    $companyName = $footer['company_name'] ?? '';
    $contactPhone = $footer['contact_phone'] ?? '';
    $contactEmail = $footer['contact_email'] ?? '';
    $contactAddress = $footer['contact_address'] ?? '';
    $contactOffice = $footer['contact_office'] ?? '';
    $contactWebsite = $footer['contact_website'] ?? '';
    $taxCode = $footer['tax_code'] ?? '';
    $contactHours = $footer['contact_hours'] ?? '';
    $introText = $footer['intro_text'] ?? '';

    $dbPolicies = \App\Models\Content::where('is_active', true)
        ->whereHas('contentType', fn ($q) => $q->where('slug', 'chinh-sach'))
        ->orderBy('id', 'asc')
        ->get(['title', 'slug']);

    $policyLinks = $dbPolicies
        ->map(fn ($p) => [
            'label' => $p->title,
            'url' => route('policy', ['slug' => $p->slug]),
        ])
        ->toArray();

    $quickLinks = !empty($footer['quick_links']) && is_array($footer['quick_links']) && count($footer['quick_links']) > 0 
        ? $footer['quick_links'] 
        : $policyLinks;

    $ratings = !empty($footer['ratings']) && is_array($footer['ratings']) ? $footer['ratings'] : [];

    $showFloatingCall = !empty($footer['floating_call_active']) && !empty($footer['floating_call_phone']);
    $showFloatingZalo = !empty($footer['floating_zalo_active']) && !empty($footer['floating_zalo_link']);
@endphp

<footer class="w-full mt-10 relative font-sans">
    
    <!-- KHỐI TRÊN: NỀN VÀNG (Bố cục 4 cột rộng rãi như cũ) -->
    <div class="bg-[#FFC700] text-slate-900 py-10 sm:py-12 border-t-2 border-amber-400 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-8">
                
                <!-- Cột 1: Logo, Giới thiệu & Mạng xã hội (3 cột) -->
                <div class="lg:col-span-3 space-y-3.5">
                    @if ($footerLogoUrl)
                        <a href="{{ route('home') }}" wire:navigate.hover class="inline-flex items-center justify-center bg-white rounded-full p-1.5 shadow-sm border border-amber-300/80 hover:shadow-md hover:scale-105 transition-all">
                            <img 
                                src="{{ $footerLogoUrl }}" 
                                alt="{{ $setting?->interface_settings['header']['brand_name'] ?? 'DOANH NGHIỆP VIỆT' }}" 
                                class="h-14 w-14 object-contain rounded-full"
                            >
                        </a>
                    @endif
                    
                    @if ($introText)
                        <p class="text-xs text-slate-800 leading-relaxed font-normal">
                            {{ $introText }}
                        </p>
                    @endif

                    <!-- Các nút mạng xã hội tròn -->
                    <div class="flex items-center gap-2.5 pt-1">
                        @if (!empty($footer['social_facebook']))
                            <a 
                                href="{{ $footer['social_facebook'] }}" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="w-8 h-8 rounded-full bg-[#1877F2] hover:bg-[#166fe5] text-white flex items-center justify-center hover:scale-110 transition shadow-2xs" 
                                title="Facebook"
                            >
                                <i class="fa-brands fa-facebook-f text-xs"></i>
                            </a>
                        @endif
                        @if (!empty($footer['social_zalo']))
                            <a 
                                href="{{ $footer['social_zalo'] }}" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="w-8 h-8 rounded-full overflow-hidden hover:scale-110 transition shadow-2xs flex items-center justify-center" 
                                title="Zalo OA"
                            >
                                <img src="{{ asset('images/zalo-icon.webp') }}" alt="Zalo OA" class="w-full h-full object-cover">
                            </a>
                        @endif
                        @if ($contactPhone)
                            <a 
                                href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" 
                                class="w-8 h-8 rounded-full bg-[#10B981] hover:bg-[#059669] text-white flex items-center justify-center hover:scale-110 transition shadow-2xs" 
                                title="Gọi Hotline: {{ $contactPhone }}"
                            >
                                <i class="fa-solid fa-phone text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Cột 2: Thông tin liên hệ (4 cột trên lg) -->
                <div class="lg:col-span-4 space-y-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-[#991B1B] uppercase tracking-wider">
                            Thông tin liên hệ
                        </h3>
                        <div class="w-8 h-0.5 bg-[#CE171F] mt-1.5 rounded-full"></div>
                    </div>

                    @if ($companyName)
                        <div class="text-xs font-bold text-slate-900 leading-snug">
                            {{ $companyName }}
                        </div>
                    @endif

                    <ul class="space-y-2 text-xs text-slate-800 font-normal">
                        @if ($contactAddress)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-location-dot text-[#991B1B] text-xs mt-0.5 shrink-0"></i>
                                <span class="leading-snug"><strong>Địa chỉ trụ sở:</strong> {{ $contactAddress }}</span>
                            </li>
                        @endif
                        @if ($contactOffice)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-building text-[#991B1B] text-xs mt-0.5 shrink-0"></i>
                                <span class="leading-snug"><strong>Địa chỉ liên hệ:</strong> {{ $contactOffice }}</span>
                            </li>
                        @endif
                        @if ($contactPhone)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-phone text-[#991B1B] text-xs shrink-0"></i>
                                <span><strong>Điện thoại:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="font-bold text-[#991B1B] hover:underline">{{ $contactPhone }}</a></span>
                            </li>
                        @endif
                        @if ($contactEmail)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-envelope text-[#991B1B] text-xs shrink-0"></i>
                                <span><strong>Email:</strong> <a href="mailto:{{ $contactEmail }}" class="hover:underline font-medium">{{ $contactEmail }}</a></span>
                            </li>
                        @endif
                        @if ($contactWebsite)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-globe text-[#991B1B] text-xs shrink-0"></i>
                                <span><strong>Website:</strong> <a href="{{ str_starts_with($contactWebsite, 'http') ? $contactWebsite : 'https://' . $contactWebsite }}" target="_blank" rel="noopener" class="font-bold text-[#991B1B] hover:underline">{{ preg_replace('~https?://~', '', rtrim($contactWebsite, '/')) }}</a></span>
                            </li>
                        @endif
                        @if ($taxCode)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-id-card text-[#991B1B] text-xs shrink-0"></i>
                                <span><strong>Mã số thuế:</strong> <span class="font-semibold text-slate-900">{{ $taxCode }}</span></span>
                            </li>
                        @endif
                        @if ($contactHours)
                            <li class="flex items-center gap-2 text-slate-700 pt-0.5">
                                <i class="fa-solid fa-clock text-[#991B1B] text-xs shrink-0"></i>
                                <span>{{ $contactHours }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Cột 3: Liên kết nhanh (2 cột trên lg) -->
                <div class="lg:col-span-2 space-y-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-[#991B1B] uppercase tracking-wider">
                            Liên kết nhanh
                        </h3>
                        <div class="w-8 h-0.5 bg-[#CE171F] mt-1.5 rounded-full"></div>
                    </div>

                    <ul class="space-y-2 text-xs text-slate-800 font-normal">
                        @foreach ($quickLinks as $link)
                            @if (!empty($link['label']) && !empty($link['url']))
                                <li>
                                    <a href="{{ $link['url'] }}" wire:navigate.hover class="inline-flex items-center gap-2 hover:translate-x-1 hover:text-[#991B1B] transition font-medium">
                                        <i class="fa-solid fa-chevron-right text-[10px] text-[#991B1B]"></i>
                                        <span>{{ $link['label'] }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- Cột 4: Cam kết chất lượng (3 cột trên lg) -->
                <div class="lg:col-span-3 space-y-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-[#991B1B] uppercase tracking-wider">
                            Cam kết chất lượng
                        </h3>
                        <div class="w-8 h-0.5 bg-[#CE171F] mt-1.5 rounded-full"></div>
                    </div>

                    <div class="space-y-2.5">
                        <!-- Cam kết nguồn gốc & Kiểm định chất lượng -->
                        <div class="bg-white/90 border border-amber-300/80 rounded-xl p-3 space-y-1.5 shadow-2xs">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-[#991B1B]">
                                <i class="fa-solid fa-shield-halved text-emerald-600 text-sm"></i>
                                <span>100% Nguồn gốc rõ ràng</span>
                            </div>
                            <p class="text-[11px] text-slate-700 leading-relaxed font-normal">
                                Tất cả sản phẩm phân phối tại DNV đều có xuất xứ minh bạch, kiểm định an toàn thực phẩm và đầy đủ chứng từ nguồn gốc.
                            </p>
                            <a 
                                href="{{ route('gioi-thieu') }}" 
                                wire:navigate.hover 
                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#CE171F] hover:underline pt-1"
                            >
                                <span>Xem hồ sơ năng lực DNV</span>
                                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                            </a>
                        </div>

                        @if (count($ratings) > 0)
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                @foreach ($ratings as $r)
                                    <div class="bg-white/90 border border-amber-300/80 rounded-xl p-2 flex items-center gap-2 hover:bg-white transition shadow-2xs">
                                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm shrink-0">
                                            <i class="{{ !empty($r['icon']) ? $r['icon'] : 'fa-solid fa-star' }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-amber-700 leading-none">{{ $r['score'] ?? '5.0' }} <i class="fa-solid fa-star text-[9px] text-amber-500"></i></div>
                                            <div class="text-[9.5px] text-slate-600 truncate mt-0.5 font-medium">{{ $r['platform'] ?? 'Review' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- KHỐI DƯỚI: NỀN ĐỎ (Thanh bản quyền & chính sách gọn trên 1 hàng) -->
    <div class="bg-[#CE171F] text-white py-3.5 border-t border-[#B91C1C]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center justify-between text-xs text-white/95 gap-3 text-center lg:text-left font-normal">
            <div class="leading-snug lg:whitespace-nowrap">
                @if (!empty($footer['copyright']))
                    {!! $footer['copyright'] !!}
                @else
                    &copy; {{ date('Y') }} <strong class="text-white font-semibold">DOANH NGHIỆP VIỆT (DNV)</strong> • <a href="https://dnv1.biz" class="hover:text-amber-200 font-semibold text-white underline">dnv1.biz</a>. Bảo lưu mọi quyền.
                @endif
            </div>
            @if (count($policyLinks) > 0)
                <div class="flex items-center gap-2.5 text-xs font-medium text-white/95 shrink-0 flex-wrap justify-center lg:justify-end">
                    @foreach ($policyLinks as $pIdx => $pLink)
                        @if (!empty($pLink['label']) && !empty($pLink['url']))
                            @if ($pIdx > 0)
                                <span class="text-amber-300">•</span>
                            @endif
                            <a href="{{ $pLink['url'] }}" wire:navigate.hover class="hover:text-amber-200 transition whitespace-nowrap">{{ $pLink['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Nút liên hệ nổi (Floating quick contact) -->
    @if ($showFloatingCall)
        <a 
            href="tel:{{ preg_replace('/[^0-9+]/', '', $footer['floating_call_phone']) }}" 
            class="fixed bottom-6 left-6 z-40 bg-[#CE171F] hover:bg-[#B91C1C] text-white w-12 h-12 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110 group border-2 border-white"
            title="Gọi ngay"
        >
            <i class="fa-solid fa-phone text-base animate-bounce"></i>
        </a>
    @endif

    @if ($showFloatingZalo)
        <a 
            href="{{ $footer['floating_zalo_link'] }}" 
            target="_blank" 
            rel="noopener"
            class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110 group"
            title="Chat Zalo"
        >
            <img 
                src="{{ asset('images/zalo-icon.webp') }}" 
                alt="Chat Zalo" 
                class="w-full h-full object-contain drop-shadow-md rounded-full"
                width="48"
                height="48"
                loading="lazy"
            >
        </a>
    @endif

</footer>
