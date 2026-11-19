<div class="min-h-screen bg-[#f8faf9] pt-2.5 pb-10 sm:py-7">
    <!-- Page SEO Meta Tags -->
    <livewire:seo.store />

    @php
        $store = $setting?->interface_settings['store'] ?? [];
        $bannerUrl = !empty($store['banner_image']) ? get_curator_media_url($store['banner_image']) : null;
        $hasDetails = !empty($store['branch_name']) || !empty($store['address']) || !empty($store['hours']) || !empty($store['phone']) || !empty($store['email']) || !empty($store['delivery_text']) || !empty($store['hotline_call_url']) || !empty($store['google_map_url']);

        $rawMapUrl = trim($store['map_iframe_url'] ?? '');
        $resolvedMapIframeUrl = '';
        if (!empty($rawMapUrl)) {
            if (preg_match('/src=["\']([^"\']+)["\']/i', $rawMapUrl, $m)) {
                $rawMapUrl = html_entity_decode($m[1], ENT_QUOTES);
            }
            if (str_contains($rawMapUrl, '/maps/embed') || str_contains($rawMapUrl, 'output=embed')) {
                $resolvedMapIframeUrl = $rawMapUrl;
            } elseif (preg_match('~/maps/place/([^/@?]+)~i', $rawMapUrl, $pm)) {
                $place = urldecode(str_replace('+', ' ', $pm[1]));
                $resolvedMapIframeUrl = 'https://maps.google.com/maps?q=' . urlencode($place) . '&hl=vi&z=17&output=embed';
            } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $rawMapUrl, $cm)) {
                $resolvedMapIframeUrl = 'https://maps.google.com/maps?q=' . $cm[1] . ',' . $cm[2] . '&hl=vi&z=17&output=embed';
            } elseif (!empty($store['address'])) {
                $resolvedMapIframeUrl = 'https://maps.google.com/maps?q=' . urlencode($store['address']) . '&hl=vi&z=17&output=embed';
            }
        }
        $hasMap = !empty($resolvedMapIframeUrl);
    @endphp

    @php
        $isContactRoute = request()->routeIs('contact') || request()->is('lien-he*');
        $pageHeading = $isContactRoute ? 'Liên Hệ Ban Quản Trị & Hỗ Trợ Khách Hàng' : (!empty($store['title']) ? $store['title'] : 'Hệ Thống Cửa Hàng & Điểm Phân Phối');
        $pageSubheading = $isContactRoute ? 'Quý khách vui lòng để lại thông tin hoặc liên hệ Hotline để được hỗ trợ nhanh nhất.' : (!empty($store['subtitle']) ? $store['subtitle'] : 'Thông tin chi nhánh và kênh liên hệ chính thức của DNV');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 whitespace-nowrap overflow-hidden mb-2.5 sm:mb-4">
            <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition shrink-0">Trang chủ</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
            <span class="font-medium sm:font-semibold text-slate-800 min-w-0 truncate">{{ $isContactRoute ? 'Liên hệ' : (!empty($store['title']) ? $store['title'] : 'Cửa hàng') }}</span>
        </nav>

        <!-- Store / Contact Heading -->
        <div class="mb-3 sm:mb-5 text-left border-b border-slate-100 pb-2.5 sm:pb-3">
            <h1 class="text-[15px] sm:text-lg md:text-[21px] font-bold text-[#991B1B] tracking-tight leading-snug">
                {{ $pageHeading }}
            </h1>
            <p class="text-[11.5px] sm:text-xs md:text-[13.5px] text-[#52606d] mt-1 font-normal leading-relaxed">
                {{ $pageSubheading }}
            </p>
        </div>

        @if($bannerUrl)
            <section class="relative rounded-3xl overflow-hidden shadow-sm border border-slate-200/70 mb-6 sm:mb-8">
                <img 
                    src="{{ $bannerUrl }}" 
                    alt="{{ $store['title'] ?? 'Liên hệ Doanh Nghiệp Việt (DNV)' }}" 
                    class="w-full h-auto max-h-[380px] object-cover object-center block"
                >
            </section>
        @endif

        <!-- Store Info Grid (Card + Map) -->
        @if($hasDetails || $hasMap)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 mb-12 sm:mb-16">
            <!-- Details Card -->
            @if($hasDetails)
            <div class="{{ $hasMap ? 'lg:col-span-5' : 'lg:col-span-12' }} bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 flex flex-col justify-between">
                <div>
                    <!-- Badge Title -->
                    @if(!empty($store['branch_name']))
                    <div class="flex items-center gap-2.5 text-base sm:text-lg font-bold text-slate-900 pb-3 border-b border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-red-50 text-[#CE171F] flex items-center justify-center text-sm">
                            <i class="fa-solid fa-store"></i>
                        </span>
                        <span>{{ $store['branch_name'] }}</span>
                    </div>
                    @endif

                    <!-- Store Details List -->
                    <div class="space-y-4 text-xs sm:text-[13px] text-slate-600 mt-5">
                        <!-- Address -->
                        @if(!empty($store['address']))
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-[#CE171F] text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-slate-800 font-semibold block mb-0.5">Địa chỉ:</strong>
                                <span>{{ $store['address'] }}</span>
                            </div>
                        </div>
                        @endif

                        <!-- Hours -->
                        @if(!empty($store['hours']))
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-clock text-[#CE171F] text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-slate-800 font-semibold block mb-0.5">Giờ mở cửa:</strong>
                                <span>{{ $store['hours'] }}</span>
                            </div>
                        </div>
                        @endif

                        <!-- Phone -->
                        @if(!empty($store['phone']))
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-phone text-[#CE171F] text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-slate-800 font-semibold block mb-0.5">Điện thoại:</strong>
                                <a href="tel:{{ preg_replace('/[^0-9]/', '', $store['phone']) }}" class="text-[#CE171F] font-bold hover:underline">{{ $store['phone'] }}</a>
                            </div>
                        </div>
                        @endif

                        <!-- Email -->
                        @if(!empty($store['email']))
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-envelope text-[#CE171F] text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-slate-800 font-semibold block mb-0.5">Email:</strong>
                                <a href="mailto:{{ $store['email'] }}" class="text-slate-600 hover:text-[#CE171F] transition">{{ $store['email'] }}</a>
                            </div>
                        </div>
                        @endif

                        <!-- Website -->
                        @php
                            $storeWebsite = $store['website'] ?? $setting?->interface_settings['footer']['contact_website'] ?? $setting?->interface_settings['about']['contact_website'] ?? null;
                            $storeWebsiteHref = $storeWebsite && !str_starts_with($storeWebsite, 'http') ? 'https://' . $storeWebsite : $storeWebsite;
                        @endphp
                        @if(!empty($storeWebsite))
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-globe text-[#CE171F] text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-slate-800 font-semibold block mb-0.5">Website chính thức:</strong>
                                <a href="{{ $storeWebsiteHref }}" target="_blank" rel="noopener" class="text-[#CE171F] font-bold hover:underline">{{ preg_replace('#^https?://#', '', rtrim($storeWebsite, '/')) }}</a>
                            </div>
                        </div>
                        @endif

                        <!-- Delivery -->
                        @if(!empty($store['delivery_text']))
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-truck-fast text-[#CE171F] text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-slate-800 font-semibold block mb-0.5">Dịch vụ giao hàng:</strong>
                                <span>{{ $store['delivery_text'] }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                @if(!empty($store['hotline_call_url']) || !empty($store['google_map_url']))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-7 pt-5 border-t border-slate-100">
                    @if(!empty($store['hotline_call_url']))
                    @php
                        $hotlineHref = $store['hotline_call_url'];
                        if (!str_starts_with($hotlineHref, 'tel:') && !str_starts_with($hotlineHref, 'http')) {
                            $hotlineHref = 'tel:' . preg_replace('/[^0-9+]/', '', $hotlineHref);
                        }
                    @endphp
                    <a 
                        href="{{ $hotlineHref }}" 
                        class="border border-[#CE171F] text-[#CE171F] hover:bg-[#CE171F] hover:text-white rounded-xl py-2.5 px-3 text-center text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-2xs"
                    >
                        <i class="fa-solid fa-phone text-xs"></i>
                        <span>Gọi điện tư vấn</span>
                    </a>
                    @endif
                    @if(!empty($store['google_map_url']))
                    <a 
                        href="{{ $store['google_map_url'] }}" 
                        target="_blank" 
                        rel="noopener"
                        class="bg-[#CE171F] hover:bg-[#B91C1C] text-white rounded-xl py-2.5 px-3 text-center text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-2xs"
                    >
                        <i class="fa-solid fa-location-arrow text-xs"></i>
                        <span>Chỉ đường Maps</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
            @endif

            <!-- Map Card -->
            @if($hasMap)
            <div class="{{ $hasDetails ? 'lg:col-span-7' : 'lg:col-span-12' }} bg-white rounded-3xl overflow-hidden shadow-xs border border-slate-200/80 min-h-[360px] lg:min-h-full">
                <iframe
                    src="{{ $resolvedMapIframeUrl }}"
                    class="w-full h-full min-h-[380px] border-0"
                    allowfullscreen="" 
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Bản đồ chỉ đường {{ $store['branch_name'] ?? 'Doanh Nghiệp Việt (DNV)' }}"
                ></iframe>
            </div>
            @endif
        </div>
        @endif

        <!-- Store Features Section -->
        @if(!empty($store['features']) && is_array($store['features']) && count($store['features']) > 0)
        <section class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs mb-8">
            @if(!empty($store['features_title']))
            <div class="text-center max-w-xl mx-auto mb-8 sm:mb-10">
                <h2 class="text-lg sm:text-2xl font-bold text-slate-800 tracking-tight inline-block relative pb-2.5 after:content-[''] after:absolute after:bottom-0 after:left-1/2 after:-translate-x-1/2 after:w-12 after:h-0.5 after:bg-[#CE171F] after:rounded-full">
                    {{ $store['features_title'] }}
                </h2>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($store['features'] as $feat)
                <div class="bg-slate-50/70 hover:bg-red-50/50 border border-slate-200/70 hover:border-red-300 rounded-2xl p-6 text-center transition-all group">
                    @php
                        $featIcon = $feat['icon'] ?? 'fa-solid fa-shield-halved';
                        if (str_contains($featIcon, 'shield-check')) {
                            $featIcon = 'fa-solid fa-shield-halved';
                        }
                    @endphp
                    <div class="w-12 h-12 rounded-2xl bg-red-100/70 text-[#CE171F] mx-auto flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="{{ $featIcon }}"></i>
                    </div>
                    @if(!empty($feat['title']))
                    <h3 class="text-sm font-bold text-slate-800 mb-2 group-hover:text-[#CE171F] transition">
                        {{ $feat['title'] }}
                    </h3>
                    @endif
                    @if(!empty($feat['description']))
                    <p class="text-xs text-slate-500 font-normal leading-relaxed">
                        {{ $feat['description'] }}
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Online Contact Form Section (Configurable via InterfaceSettings) -->
        @if($store['contact_form_active'] ?? true)
        <section id="contact-form-section" class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-xs mb-10">
            <div class="max-w-3xl mx-auto">
                <div class="text-center space-y-2 mb-8">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-[#CE171F] font-bold text-xs uppercase tracking-wider">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Liên Hệ Trực Tuyến
                    </span>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 uppercase tracking-tight">
                        {{ $store['contact_form_title'] ?? 'GỬI YÊU CẦU LIÊN HỆ & HỢP TÁC' }}
                    </h2>
                    @if(!empty($store['contact_form_subtitle']))
                    <p class="text-xs sm:text-sm text-slate-600 max-w-xl mx-auto">
                        {{ $store['contact_form_subtitle'] }}
                    </p>
                    @endif
                </div>

                @if($isSubmitted)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 sm:p-8 text-center space-y-4 animate-fade-in">
                        <div class="w-14 h-14 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto text-2xl shadow-md">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h3 class="text-lg font-bold text-emerald-900">Gửi Thông Tin Thành Công!</h3>
                        <p class="text-sm text-emerald-700 max-w-md mx-auto leading-relaxed">
                            {{ $successMessage }}
                        </p>
                        <div class="pt-2">
                            <button 
                                wire:click="resetForm" 
                                type="button" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer"
                            >
                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                Gửi thêm yêu cầu khác
                            </button>
                        </div>
                    </div>
                @else
                    <form wire:submit.prevent="submitContact" class="space-y-4 sm:space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Họ và tên -->
                            <div>
                                <label for="contact-name" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="contact-name"
                                        wire:model.defer="name" 
                                        placeholder="Nguyễn Văn A" 
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#CE171F] focus:ring-1 focus:ring-[#CE171F] transition @error('name') border-red-500 @enderror"
                                        required
                                    >
                                </div>
                                @error('name')
                                    <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Số điện thoại -->
                            <div>
                                <label for="contact-phone" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input 
                                        type="tel" 
                                        id="contact-phone"
                                        wire:model.defer="phone" 
                                        placeholder="0939 393 657" 
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#CE171F] focus:ring-1 focus:ring-[#CE171F] transition @error('phone') border-red-500 @enderror"
                                        required
                                    >
                                </div>
                                @error('phone')
                                    <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Email -->
                            <div>
                                <label for="contact-email" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Địa chỉ Email
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input 
                                        type="email" 
                                        id="contact-email"
                                        wire:model.defer="email" 
                                        placeholder="email@example.com" 
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#CE171F] focus:ring-1 focus:ring-[#CE171F] transition @error('email') border-red-500 @enderror"
                                    >
                                </div>
                                @error('email')
                                    <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Chủ đề -->
                            <div>
                                <label for="contact-subject" class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Chủ đề liên hệ
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-tag"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="contact-subject"
                                        wire:model.defer="subject" 
                                        placeholder="Tư vấn chương trình An Sinh Xã Hội" 
                                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#CE171F] focus:ring-1 focus:ring-[#CE171F] transition"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Nội dung tin nhắn -->
                        <div>
                            <label for="contact-message" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nội dung tin nhắn / Nhu cầu tư vấn <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="contact-message"
                                wire:model.defer="message" 
                                rows="4" 
                                placeholder="Vui lòng để lại nội dung chi tiết để chúng tôi có thể chuẩn bị thông tin hỗ trợ chu đáo nhất..." 
                                class="w-full p-3 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#CE171F] focus:ring-1 focus:ring-[#CE171F] transition @error('message') border-red-500 @enderror"
                                required
                            ></textarea>
                            @error('message')
                                <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nút gửi -->
                        <div class="text-center pt-2">
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-[#CE171F] hover:bg-[#b0131a] active:scale-98 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition-all shadow-md shadow-red-900/20 inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="submitContact" class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>{{ $store['contact_form_button_text'] ?? 'Gửi Thông Tin Liên Hệ' }}</span>
                                </span>
                                <span wire:loading wire:target="submitContact" class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                    <span>Đang gửi thông tin...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </section>
        @endif
    </div>
</div>
