<div class="min-h-screen bg-[#F8FAFC] py-8 sm:py-12 selection:bg-[#CE171F] selection:text-white" x-data="{ copied: false }">
    <!-- Page SEO Meta Tags -->
    <livewire:seo.order-success :orderCode="$orderCode" />

    @php
        $os = $setting?->interface_settings['order_success'] ?? [];
        $osTitle = !empty($os['title']) ? $os['title'] : 'Đặt Hàng Thành Công!';
        $timelineSteps = !empty($os['timeline_steps']) && is_array($os['timeline_steps']) ? $os['timeline_steps'] : [
            ['title' => 'Đã nhận đơn', 'sub' => 'Vừa xong'],
            ['title' => 'Đang đóng gói', 'sub' => 'Hiện tại'],
            ['title' => 'Đang giao hàng', 'sub' => 'Vận chuyển'],
            ['title' => 'Giao thành công', 'sub' => 'Hoàn tất'],
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (empty($orderData))
            <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200/80 shadow-sm text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Không tìm thấy thông tin đơn hàng</h1>
                <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                    Đơn hàng không tồn tại hoặc đã hết phiên giao dịch. Quý khách có thể quay lại trang chủ để tiếp tục mua sắm hoặc liên hệ Hotline để được hỗ trợ.
                </p>
                <div class="pt-2 flex items-center justify-center gap-3">
                    <a href="{{ route('home') }}" wire:navigate.hover class="px-6 py-2.5 rounded-full bg-[#CE171F] hover:bg-[#b0131a] text-white text-xs font-semibold transition">
                        Về Trang Chủ
                    </a>
                    <a href="{{ route('category') }}" wire:navigate.hover class="px-6 py-2.5 rounded-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                        Xem Danh Mục Sản Phẩm
                    </a>
                </div>
            </div>
        @else

        <!-- 1. Top Celebratory Success Card & Order Stepper -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm text-center relative overflow-hidden">
            <!-- Decorative Ambient Glows -->
            <div class="absolute -top-24 -left-24 w-56 h-56 bg-emerald-100/40 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-56 h-56 bg-amber-100/40 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Crisp Animated Success Checkmark Icon -->
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto text-3xl shadow-lg mb-4 transition-transform hover:scale-105" style="background-color: #10B981; color: #ffffff; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 0 0 8px #ECFDF5;">
                <i class="fa-solid fa-check" style="color: #ffffff;"></i>
            </div>

            <!-- Title -->
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                {{ $osTitle }}
            </h1>

            <!-- Subtitle -->
            @if(!empty($os['thank_you_message']))
                <p class="text-xs sm:text-sm text-slate-600 font-normal mt-2.5 max-w-xl mx-auto leading-relaxed">
                    {!! nl2br(e($os['thank_you_message'])) !!}
                </p>
            @else
                <p class="text-xs sm:text-sm text-slate-600 font-normal mt-2.5 max-w-xl mx-auto leading-relaxed">
                    Cảm ơn Quý khách đã mua sắm tại <strong>Doanh Nghiệp Việt (DNV)</strong>. Đơn hàng của bạn đã được ghi nhận và đang được chuẩn bị để giao hàng trong thời gian sớm nhất.
                </p>
            @endif

            <!-- Order Code Pill with Copy -->
            <div class="mt-4 inline-flex items-center gap-3 bg-red-50/70 border border-red-200/80 rounded-2xl px-5 py-2.5 shadow-2xs">
                <div class="text-left">
                    <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Mã đơn hàng</div>
                    <div class="text-sm sm:text-base font-extrabold text-[#CE171F] font-mono tracking-tight">#{{ $orderData['order_number'] }}</div>
                </div>
                <div class="h-8 w-px bg-red-200"></div>
                <button 
                    @click="navigator.clipboard.writeText('{{ $orderData['order_number'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-red-200 hover:bg-[#CE171F] hover:text-white text-[#CE171F] text-xs font-semibold transition cursor-pointer shadow-2xs group"
                    title="Sao chép mã đơn hàng"
                >
                    <i :class="copied ? 'fa-solid fa-check text-emerald-600 group-hover:text-white' : 'fa-regular fa-copy'"></i>
                    <span x-text="copied ? 'Đã chép!' : 'Sao chép'">Sao chép</span>
                </button>
            </div>

            <!-- Modern Horizontal Delivery Stepper -->
            <div class="mt-8 pt-8 border-t border-slate-100 max-w-2xl mx-auto">
                <div class="relative">
                    <!-- Background Connector Line -->
                    <div class="hidden sm:block absolute top-5 left-[12%] right-[12%] h-1 z-0 rounded-full" style="background-color: #E2E8F0;"></div>
                    <!-- Active Progress Fill (Steps 1 to 2) -->
                    <div class="hidden sm:block absolute top-5 left-[12%] w-[25%] h-1 z-0 rounded-full" style="background-color: #10B981;"></div>

                    <!-- 4 Steps in Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-2 relative z-10">
                        <!-- Step 1: Complete -->
                        <div class="flex flex-col items-center text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm shadow-md mb-2" style="background-color: #10B981; color: #ffffff; box-shadow: 0 0 0 4px #ffffff;">
                                <i class="fa-solid fa-check" style="color: #ffffff;"></i>
                            </div>
                            <div class="text-xs font-bold text-slate-800">Đã nhận đơn</div>
                            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5 leading-snug">
                                @php
                                    $rawDate = $orderData['created_at'] ?? now()->format('H:i d/m/Y');
                                    $dateParts = explode(' ', trim($rawDate));
                                @endphp
                                @if(count($dateParts) >= 2)
                                    <span>{{ $dateParts[0] }}</span> <span class="text-slate-400 font-normal">({{ $dateParts[1] }})</span>
                                @else
                                    <span>{{ $rawDate }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Step 2: Processing (Active) -->
                        <div class="flex flex-col items-center text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm shadow-md mb-2 animate-pulse" style="background-color: #F59E0B; color: #ffffff; box-shadow: 0 0 0 4px #FEF3C7;">
                                <i class="fa-solid fa-box-open" style="color: #ffffff;"></i>
                            </div>
                            <div class="text-xs font-bold text-amber-700">Đang đóng gói</div>
                            <div class="text-[11px] text-amber-600 font-medium mt-0.5">Kho chuẩn bị hàng</div>
                        </div>

                        <!-- Step 3: Shipping (Pending) -->
                        <div class="flex flex-col items-center text-center opacity-70">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm mb-2" style="background-color: #F1F5F9; color: #64748B; box-shadow: 0 0 0 4px #ffffff;">
                                <i class="fa-solid fa-truck-fast" style="color: #64748B;"></i>
                            </div>
                            <div class="text-xs font-medium text-slate-600">Đang giao hàng</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Bàn giao shipper</div>
                        </div>

                        <!-- Step 4: Complete (Pending) -->
                        <div class="flex flex-col items-center text-center opacity-70">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm mb-2" style="background-color: #F1F5F9; color: #64748B; box-shadow: 0 0 0 4px #ffffff;">
                                <i class="fa-solid fa-circle-check" style="color: #64748B;"></i>
                            </div>
                            <div class="text-xs font-medium text-slate-600">Giao thành công</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Nhận hàng & thanh toán</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Transfer Details (If Selected VietQR) -->
        @if (($orderData['payment_method'] ?? 'cod') === 'bank_transfer')
            <div class="bg-blue-50/70 border border-blue-200/90 rounded-3xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-center gap-2.5 text-blue-900 font-bold text-sm mb-4">
                    <i class="fa-solid fa-qrcode text-lg text-blue-600"></i>
                    <span>Thông tin chuyển khoản ngân hàng (VietQR)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                    @php
                        $paymentSetting = $setting?->general_settings['payment'] ?? [];
                        $bankName = !empty($paymentSetting['bank_name']) ? $paymentSetting['bank_name'] : 'Ngân hàng TMCP Quốc Tế Việt Nam (VIB)';
                        $accNum = !empty($paymentSetting['account_number']) ? $paymentSetting['account_number'] : '475475';
                        $accName = !empty($paymentSetting['account_name']) ? $paymentSetting['account_name'] : 'CONG TY CP ĐẦU TƯ & XÚC TIẾN THƯƠNG MẠI DOANH NGHIỆP VIỆT';
                    @endphp
                    <div class="space-y-2 text-xs text-slate-700">
                        <div class="flex items-center justify-between py-1.5 border-b border-blue-100">
                            <span class="text-slate-500">Ngân hàng:</span>
                            <strong class="text-slate-900">{{ $bankName }}</strong>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-blue-100">
                            <span class="text-slate-500">Số tài khoản:</span>
                            <strong class="text-blue-700 font-mono text-sm">{{ $accNum }}</strong>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-blue-100">
                            <span class="text-slate-500">Chủ tài khoản:</span>
                            <strong class="text-slate-900">{{ $accName }}</strong>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-blue-100">
                            <span class="text-slate-500">Số tiền:</span>
                            <strong class="text-rose-600 font-bold text-sm">{{ number_format($orderData['total'] ?? 0, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-slate-500">Nội dung chuyển khoản:</span>
                            <strong class="text-[#CE171F] font-mono text-xs bg-white px-2 py-0.5 rounded border border-blue-200">{{ $orderData['order_number'] }}</strong>
                        </div>
                    </div>

                    <div class="text-center sm:text-right">
                        <div class="inline-block p-3 bg-white rounded-2xl border border-blue-200 shadow-xs text-center">
                            <img 
                                src="https://api.vietqr.io/image/970436-{{ preg_replace('/[^0-9]/', '', $accNum) }}-compact2.png?amount={{ $orderData['total'] ?? 0 }}&addInfo={{ $orderData['order_number'] }}&accountName={{ urlencode($accName) }}" 
                                alt="Mã VietQR Thanh Toán" 
                                class="w-40 h-40 object-contain mx-auto rounded-lg"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Quét mã QR qua ứng dụng ngân hàng</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. Order Information & Items Grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            
            <!-- Left Info (5 cols) -->
            <div class="md:col-span-5 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-[#CE171F]"></i>
                    <span>Thông tin nhận hàng</span>
                </h3>

                <div class="space-y-3.5 text-xs text-slate-600">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Người nhận:</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $orderData['customer_name'] ?? 'Quý khách hàng' }}</strong>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Số điện thoại:</span>
                        <strong class="text-slate-800 font-semibold flex items-center gap-1.5 mt-0.5">
                            <i class="fa-solid fa-phone text-[10px] text-[#CE171F]"></i>
                            <span>{{ $orderData['customer_phone'] ?? 'Chưa cập nhật' }}</span>
                        </strong>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Địa chỉ giao hàng:</span>
                        <div class="text-slate-800 font-medium leading-relaxed mt-0.5 flex items-start gap-1.5">
                            <i class="fa-solid fa-location-dot text-[11px] text-[#CE171F] mt-0.5 shrink-0"></i>
                            <span>{{ $orderData['shipping_address'] ?? 'Tại cửa hàng' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">Hình thức thanh toán:</span>
                        <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ ($orderData['payment_method'] ?? 'cod') === 'cod' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                            @if (($orderData['payment_method'] ?? 'cod') === 'cod')
                                <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i>
                                <span>Thanh toán khi nhận hàng (COD)</span>
                            @else
                                <i class="fa-solid fa-qrcode text-blue-600"></i>
                                <span>Chuyển khoản ngân hàng (VietQR)</span>
                            @endif
                        </span>
                    </div>

                    @if (!empty($orderData['order_notes']))
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Ghi chú cho shipper:</span>
                            <span class="text-slate-700 italic">"{{ $orderData['order_notes'] }}"</span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-slate-100">
                        <div class="p-3 bg-red-50/60 border border-red-100 rounded-xl text-xs text-[#991B1B] flex items-start gap-2 leading-relaxed">
                            <i class="fa-solid fa-headset text-[#CE171F] mt-0.5 shrink-0"></i>
                            <span>Nhân viên DNV sẽ gọi điện hoặc nhắn tin để thông báo thời gian giao hàng cụ thể cho Quý khách.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Items List (7 cols) -->
            <div class="md:col-span-7 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping text-[#CE171F]"></i>
                        <span>Sản phẩm trong đơn hàng</span>
                    </span>
                    @if (!empty($orderData['items']))
                        <span class="text-[11px] font-bold text-[#CE171F] bg-red-50 px-2 py-0.5 rounded-full">{{ count($orderData['items']) }} sản phẩm</span>
                    @endif
                </h3>

                @if (!empty($orderData['items']) && count($orderData['items']) > 0)
                    <div class="divide-y divide-slate-100 text-xs max-h-80 overflow-y-auto pr-1">
                        @foreach ($orderData['items'] as $item)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 p-1 shrink-0 overflow-hidden">
                                        <img src="{{ $item['image'] ?? asset('storage/logo/favicon-dnv.png') }}" alt="{{ $item['name'] ?? 'Sản phẩm' }}" class="w-full h-full object-contain">
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-slate-800 line-clamp-1 leading-snug">{{ $item['name'] ?? ($item['full_name'] ?? 'Sản phẩm') }}</h4>
                                        <div class="text-slate-400 text-[11px] mt-0.5">
                                            <span>{{ number_format($item['price'] ?? 0, 0, ',', '.') }}đ</span>
                                            <span class="text-slate-300 mx-1">×</span>
                                            <span class="font-bold text-slate-700">{{ $item['qty'] ?? 1 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-900 shrink-0 text-xs sm:text-sm">
                                    {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 0, ',', '.') }}đ
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 text-center text-xs text-slate-500">
                        <i class="fa-solid fa-box-check text-2xl text-emerald-500 mb-2 block"></i>
                        Đơn hàng đã được lưu thành công trên hệ thống.
                    </div>
                @endif

                <!-- Price Totals -->
                <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600 font-normal">
                        <span>Tạm tính hàng hóa:</span>
                        <span class="font-semibold text-slate-800">{{ number_format($orderData['subtotal'] ?? 0, 0, ',', '.') }}đ</span>
                    </div>

                    @if (($orderData['discount'] ?? 0) > 0)
                        <div class="flex items-center justify-between text-[#CE171F] font-medium">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-ticket text-[10px]"></i>
                                <span>Giảm giá voucher:</span>
                            </span>
                            <span class="font-bold">-{{ number_format($orderData['discount'], 0, ',', '.') }}đ</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-slate-600 font-normal">
                        <span>Phí vận chuyển:</span>
                        @if (($orderData['shipping_fee'] ?? 0) === 0)
                            <span class="text-emerald-600 font-bold uppercase">Miễn phí giao hàng</span>
                        @else
                            <span class="font-semibold text-slate-800">{{ number_format($orderData['shipping_fee'], 0, ',', '.') }}đ</span>
                        @endif
                    </div>

                    <div class="pt-2.5 border-t-2 border-slate-100 flex items-baseline justify-between">
                        <div>
                            <span class="text-sm font-extrabold text-slate-900 block">Tổng thanh toán:</span>
                            <span class="text-[10.5px] text-slate-400 font-normal">(Đã bao gồm VAT & phí ship)</span>
                        </div>
                        <span class="text-xl sm:text-2xl font-black text-[#CE171F] tracking-tight">
                            {{ number_format($orderData['total'] ?? 0, 0, ',', '.') }}đ
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. Action Buttons & Support -->
        <div class="flex flex-wrap items-center justify-center gap-3 pt-3">
            <a 
                href="{{ route('category') }}" 
                wire:navigate.hover 
                class="w-full sm:w-auto px-7 py-3.5 rounded-full bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition shadow-md hover:shadow-lg hover:scale-105"
            >
                <i class="fa-solid fa-basket-shopping text-sm"></i>
                <span>Tiếp Tục Mua Sắm</span>
            </a>

            <a 
                href="{{ route('home') }}" 
                wire:navigate.hover 
                class="w-full sm:w-auto px-6 py-3.5 rounded-full bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-semibold flex items-center justify-center gap-2 transition shadow-xs"
            >
                <i class="fa-solid fa-house text-xs"></i>
                <span>Về Trang Chủ</span>
            </a>

            @if(!empty($os['support_hotline']))
                <a 
                    href="tel:{{ preg_replace('/[^0-9]/', '', $os['support_hotline']) }}" 
                    class="w-full sm:w-auto px-5 py-3.5 rounded-full bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-center gap-2 transition"
                >
                    <i class="fa-solid fa-phone text-xs text-emerald-600"></i>
                    <span>Hotline: {{ $os['support_hotline'] }}</span>
                </a>
            @else
                <a 
                    href="tel:0903856861" 
                    class="w-full sm:w-auto px-5 py-3.5 rounded-full bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-center gap-2 transition"
                >
                    <i class="fa-solid fa-phone text-xs text-emerald-600"></i>
                    <span>Hotline: 0903.856.861</span>
                </a>
            @endif

            <a 
                href="https://zalo.me/0903856861" 
                target="_blank"
                rel="noopener"
                class="w-full sm:w-auto px-5 py-3.5 rounded-full bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-semibold flex items-center justify-center gap-2 transition"
            >
                <img src="{{ asset('images/zalo-icon.webp') }}" alt="Zalo" class="w-4 h-4 object-contain">
                <span>Hỗ Trợ Zalo</span>
            </a>
        </div>

        @endif

    </div>
</div>
