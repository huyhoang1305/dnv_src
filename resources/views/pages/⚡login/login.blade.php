<div>
    <!-- Page SEO Meta Tags -->
    <livewire:seo.login />

    @php
        $loginSettings = $setting?->interface_settings['login'] ?? [];
    @endphp

    @auth
    <!-- LOGGED IN USER: Orders List & Account Management Page -->
    <div class="min-h-[85vh] bg-[#f8faf9] pt-3 pb-8 sm:py-8 selection:bg-[#CE171F] selection:text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumb Navigation & Back -->
            <div class="flex items-center justify-between gap-2 sm:gap-4 mb-3.5 sm:mb-5">
                <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 font-normal min-w-0 whitespace-nowrap overflow-hidden">
                    <a href="{{ route('home') }}" wire:navigate.hover class="hover:text-[#CE171F] transition-colors shrink-0">
                        Trang chủ
                    </a>
                    <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-300 shrink-0"></i>
                    <span class="text-slate-800 font-medium min-w-0 truncate">Tài khoản & Đơn hàng</span>
                </nav>

                <button 
                    wire:click="logout" 
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200 text-xs font-medium transition cursor-pointer shadow-2xs"
                    title="Đăng xuất"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>
                    <span class="hidden sm:inline">Đăng xuất</span>
                </button>
            </div>

            <!-- User Header Card -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-red-50 text-[#CE171F] border border-red-200/80 flex items-center justify-center text-xl font-bold uppercase shadow-2xs shrink-0">
                            {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-sm sm:text-lg font-bold text-slate-900 tracking-tight">
                                    {{ auth()->user()->name }}
                                </h1>
                                @if(!empty($loginSettings['customer_badge']))
                                <span class="bg-red-100/80 text-[#CE171F] text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap shrink-0">
                                    {{ $loginSettings['customer_badge'] }}
                                </span>
                                @endif
                            </div>
                            <p class="text-[11px] sm:text-xs text-slate-500 font-normal mt-0.5 flex items-center gap-x-2.5 gap-y-1 flex-wrap">
                                <span><i class="fa-regular fa-envelope text-[11px] text-slate-400 mr-1"></i> {{ auth()->user()->email }}</span>
                                @if ($accountPhone)
                                    <span>•</span>
                                    <span><i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i> {{ $accountPhone }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Main Navigation Tabs: Đơn hàng vs Thông tin tài khoản (Reload page to avoid state conflict) -->
                    <div class="flex items-center gap-2 bg-slate-100/80 p-1.5 rounded-2xl shrink-0">
                        <a 
                            href="{{ route('orders') }}" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 {{ $tab === 'orders' ? 'bg-white text-[#CE171F] shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            <i class="fa-solid fa-receipt text-xs"></i>
                            <span>Đơn hàng</span>
                        </a>
                        <a 
                            href="{{ route('account') }}" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 {{ $tab === 'profile' ? 'bg-white text-[#CE171F] shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                        >
                            <i class="fa-regular fa-user text-xs"></i>
                            <span>Quản lý tài khoản</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- TAB 1: DANH SÁCH ĐƠN HÀNG (MATCHING SCREENSHOT) -->
            @if ($tab === 'orders')
                <!-- Status Pills Filter Bar (Tất cả, Chờ xử lý, Soạn hàng, Đang giao, Hoàn thành, Đã hủy) -->
                <div class="bg-white rounded-2xl p-2 sm:p-2.5 border border-slate-200/80 shadow-xs mb-6 overflow-x-auto no-scrollbar">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-max">
                        <button 
                            wire:click="setStatus('all')" 
                            type="button" 
                            class="px-5 py-2 rounded-full text-xs font-semibold transition cursor-pointer {{ $status === 'all' ? 'border border-[#CE171F] text-[#CE171F] bg-red-50' : 'bg-[#f1f3f2] text-slate-600 hover:bg-slate-200' }}"
                        >
                            Tất cả
                        </button>
                        <button 
                            wire:click="setStatus('pending')" 
                            type="button" 
                            class="px-5 py-2 rounded-full text-xs font-semibold transition cursor-pointer {{ $status === 'pending' ? 'border border-[#CE171F] text-[#CE171F] bg-red-50' : 'bg-[#f1f3f2] text-slate-600 hover:bg-slate-200' }}"
                        >
                            Chờ xử lý
                        </button>
                        <button 
                            wire:click="setStatus('processing')" 
                            type="button" 
                            class="px-5 py-2 rounded-full text-xs font-semibold transition cursor-pointer {{ $status === 'processing' ? 'border border-[#CE171F] text-[#CE171F] bg-red-50' : 'bg-[#f1f3f2] text-slate-600 hover:bg-slate-200' }}"
                        >
                            Soạn hàng
                        </button>
                        <button 
                            wire:click="setStatus('shipping')" 
                            type="button" 
                            class="px-5 py-2 rounded-full text-xs font-semibold transition cursor-pointer {{ $status === 'shipping' ? 'border border-[#CE171F] text-[#CE171F] bg-red-50' : 'bg-[#f1f3f2] text-slate-600 hover:bg-slate-200' }}"
                        >
                            Đang giao
                        </button>
                        <button 
                            wire:click="setStatus('completed')" 
                            type="button" 
                            class="px-5 py-2 rounded-full text-xs font-semibold transition cursor-pointer {{ $status === 'completed' ? 'border border-[#CE171F] text-[#CE171F] bg-red-50' : 'bg-[#f1f3f2] text-slate-600 hover:bg-slate-200' }}"
                        >
                            Hoàn thành
                        </button>
                        <button 
                            wire:click="setStatus('cancelled')" 
                            type="button" 
                            class="px-5 py-2 rounded-full text-xs font-semibold transition cursor-pointer {{ $status === 'cancelled' ? 'border border-[#CE171F] text-[#CE171F] bg-red-50' : 'bg-[#f1f3f2] text-slate-600 hover:bg-slate-200' }}"
                        >
                            Đã hủy
                        </button>
                    </div>
                </div>

                <!-- ORDERS CONTENT: Empty state vs Order items -->
                @if ($this->orders->isEmpty())
                    <!-- Exact empty state from screenshot -->
                    <div class="bg-white rounded-3xl p-12 sm:p-20 border border-slate-200/80 text-center shadow-xs my-4">
                        <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-20 h-20" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 32L40 18L64 32L40 46L16 32Z" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2.5" stroke-linejoin="round"/>
                                <path d="M16 32V56L40 68V46L16 32Z" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2.5" stroke-linejoin="round"/>
                                <path d="M64 32V56L40 68V46L64 32Z" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2.5" stroke-linejoin="round"/>
                                <path d="M16 32L8 27V42L16 46V32Z" fill="#CBD5E1"/>
                                <path d="M64 32L72 27V42L64 46V32Z" fill="#CBD5E1"/>
                                <path d="M28 12L40 18L28 25L16 18L28 12Z" fill="#CBD5E1"/>
                                <path d="M52 12L64 18L52 25L40 18L52 12Z" fill="#94A3B8"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">
                            Chưa có hoạt động nào
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-400 font-normal mt-1 max-w-sm mx-auto">
                            Đặt ngay món ngon để lấp đầy lịch sử nhé!
                        </p>
                        <div class="mt-6">
                            <a 
                                href="{{ route('category') }}" 
                                wire:navigate.hover 
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                            >
                                <i class="fa-solid fa-basket-shopping text-xs"></i>
                                <span>Khám phá sản phẩm ngay</span>
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Orders List Cards -->
                    <div class="space-y-4">
                        @foreach ($this->orders as $order)
                            <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs hover:border-red-200 transition space-y-4">
                                <!-- Order Header -->
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold font-mono text-[#CE171F] bg-red-50 px-2.5 py-1 rounded-lg">
                                            #{{ $order->order_number }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            {{ $order->created_at ? $order->created_at->format('H:i d/m/Y') : now()->format('H:i d/m/Y') }}
                                        </span>
                                    </div>

                                    <div>
                                        @if (in_array($order->status, ['pending', 'Chờ xử lý']))
                                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="fa-regular fa-clock mr-1"></i> Chờ xử lý
                                            </span>
                                        @elseif (in_array($order->status, ['processing', 'Soạn hàng']))
                                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fa-solid fa-box-archive mr-1"></i> Soạn hàng
                                            </span>
                                        @elseif (in_array($order->status, ['shipping', 'Đang giao', 'delivering']))
                                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                <i class="fa-solid fa-motorcycle mr-1"></i> Đang giao hỏa tốc
                                            </span>
                                        @elseif (in_array($order->status, ['completed', 'Hoàn thành']))
                                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-[#991B1B] border border-red-200">
                                                <i class="fa-regular fa-circle-check mr-1"></i> Hoàn thành
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class="fa-solid fa-xmark mr-1"></i> Đã hủy
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Order Items List -->
                                <div class="divide-y divide-slate-100">
                                    @forelse ($order->items as $it)
                                        @php
                                            $orderItemImg = $it->product_image
                                                ?: ($it->product?->image_url ? (get_curator_media_url($it->product->image_url) ?: curator_url($it->product->image_url, 150, 150)) : null);
                                        @endphp
                                        <div class="py-3 flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 p-1 flex items-center justify-center shrink-0">
                                                    <img src="{{ $orderItemImg ?: asset('storage/logo/favicon-dnv.png') }}" alt="{{ $it->product_name ?? 'Sản phẩm' }}" class="w-full h-full object-contain">
                                                </div>
                                                <div>
                                                    <h4 class="text-xs font-semibold text-slate-800 line-clamp-1">
                                                        {{ $it->product_name ?? 'Sản phẩm ASXH' }}
                                                    </h4>
                                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                                        Số lượng: x{{ $it->quantity ?? 1 }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-xs font-semibold text-slate-800 text-right">
                                                {{ number_format(($it->price ?? 0) * ($it->quantity ?? 1), 0, ',', '.') }}đ
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-2 text-xs text-slate-500">
                                            {{ $order->shipping_address ?? 'Đơn hàng nhu yếu phẩm ASXH' }}
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Order Footer & Actions -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
                                    <div class="text-xs text-slate-500">
                                        Tổng tiền: <strong class="text-sm font-bold text-[#CE171F]">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if (in_array($order->status, ['pending', 'Chờ xử lý']))
                                            <button 
                                                wire:click="cancelOrder({{ $order->id }})" 
                                                wire:confirm="Bạn có chắc chắn muốn hủy đơn hàng này không?"
                                                type="button" 
                                                class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-medium transition cursor-pointer"
                                            >
                                                Hủy đơn
                                            </button>
                                        @endif
                                        <a 
                                            href="{{ route('order.success', $order->order_number) }}" 
                                            wire:navigate.hover 
                                            class="px-4 py-1.5 rounded-xl bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs font-semibold transition cursor-pointer shadow-2xs"
                                        >
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- TAB 2: QUẢN LÝ THÔNG TIN TÀI KHOẢN -->
            @if ($tab === 'profile')
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Left: Profile details form (7 cols) -->
                    <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">
                                    Thông tin cá nhân
                                </h2>
                                <p class="text-xs text-slate-400 font-normal mt-0.5">
                                    Cập nhật thông tin giao hàng & liên hệ của bạn
                                </p>
                            </div>
                            <i class="fa-solid fa-user-pen text-slate-400"></i>
                        </div>

                        <!-- Feedback Alerts -->
                        @if ($profileMessage)
                            <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-[#CE171F] text-xs flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-[#CE171F] shrink-0"></i>
                                <span>{{ $profileMessage }}</span>
                            </div>
                        @endif

                        @if ($profileError)
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                                <span>{{ $profileError }}</span>
                            </div>
                        @endif

                        <form wire:submit.prevent="updateProfile" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Họ và tên <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    wire:model="accountName" 
                                    type="text" 
                                    class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-medium focus:border-[#CE171F] focus:outline-none transition"
                                    placeholder="Nhập họ và tên"
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Số điện thoại
                                    </label>
                                    <input 
                                        wire:model="accountPhone" 
                                        type="tel" 
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-medium focus:border-[#CE171F] focus:outline-none transition"
                                        placeholder="0988 888 888"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Email
                                    </label>
                                    <input 
                                        wire:model="accountEmail" 
                                        type="email" 
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-medium focus:border-[#CE171F] focus:outline-none transition"
                                        placeholder="email@example.com"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Địa chỉ giao hàng mặc định
                                </label>
                                <textarea 
                                    wire:model="accountAddress" 
                                    rows="3" 
                                    class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-normal focus:border-[#CE171F] focus:outline-none transition"
                                    placeholder="Số nhà, tên đường, phường/xã, quận/huyện, TP.HCM..."
                                ></textarea>
                            </div>

                            <div class="pt-2">
                                <button 
                                    type="submit" 
                                    class="px-6 py-2.5 rounded-full bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs font-semibold transition cursor-pointer shadow-xs"
                                >
                                    Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Security & Password change (5 cols) -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                            <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">
                                        Bảo mật & Mật khẩu
                                    </h2>
                                    <p class="text-xs text-slate-400 font-normal mt-0.5">
                                        Đổi mật khẩu định kỳ để an toàn
                                    </p>
                                </div>
                                <i class="fa-solid fa-shield-halved text-slate-400"></i>
                            </div>

                            <form wire:submit.prevent="changePassword" class="space-y-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Mật khẩu hiện tại
                                    </label>
                                    <input 
                                        wire:model="currentPassword" 
                                        type="password" 
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:border-[#CE171F] focus:outline-none"
                                        placeholder="••••••••"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Mật khẩu mới
                                    </label>
                                    <input 
                                        wire:model="newPassword" 
                                        type="password" 
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:border-[#CE171F] focus:outline-none"
                                        placeholder="Tối thiểu 6 ký tự"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Xác nhận mật khẩu mới
                                    </label>
                                    <input 
                                        wire:model="newPasswordConfirmation" 
                                        type="password" 
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:border-[#CE171F] focus:outline-none"
                                        placeholder="Nhập lại mật khẩu mới"
                                    >
                                </div>

                                <div class="pt-2">
                                    <button 
                                        type="submit" 
                                        class="w-full py-2.5 rounded-full bg-slate-800 hover:bg-black text-white text-xs font-semibold transition cursor-pointer"
                                    >
                                        Cập nhật mật khẩu
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Store Support Card -->
                        @if(!empty($loginSettings['support_title']) || !empty($loginSettings['support_phone']) || !empty($loginSettings['support_zalo']))
                        <div class="bg-red-50/60 rounded-3xl p-5 border border-red-200/80 text-xs text-slate-600 space-y-2.5">
                            @if(!empty($loginSettings['support_title']))
                            <div class="font-bold text-[#CE171F] flex items-center gap-2">
                                <i class="fa-solid fa-headset text-sm"></i>
                                <span>{{ $loginSettings['support_title'] }}</span>
                            </div>
                            @endif
                            @if(!empty($loginSettings['support_desc']))
                            <p class="leading-relaxed">
                                {{ $loginSettings['support_desc'] }}
                            </p>
                            @endif
                            @if(!empty($loginSettings['support_phone']) || !empty($loginSettings['support_zalo']))
                            <div class="pt-1 flex items-center gap-3">
                                @if(!empty($loginSettings['support_phone']))
                                <a href="tel:{{ preg_replace('/[^0-9]/', '', $loginSettings['support_phone']) }}" class="font-bold text-[#CE171F] hover:underline">
                                    <i class="fa-solid fa-phone mr-1"></i> {{ $loginSettings['support_phone'] }}
                                </a>
                                @endif
                                @if(!empty($loginSettings['support_phone']) && !empty($loginSettings['support_zalo']))
                                <span>•</span>
                                @endif
                                @if(!empty($loginSettings['support_zalo']))
                                <a href="{{ $loginSettings['support_zalo'] }}" target="_blank" class="font-semibold text-blue-600 hover:underline">
                                    Chat Zalo
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
@else
    <!-- GUEST: LOGIN / REGISTER FORMS -->
    <div class="min-h-[85vh] flex items-center justify-center py-10 px-4 sm:px-6 bg-[#f8faf9] selection:bg-[#CE171F] selection:text-white" x-data="{ showPassword: false, showConfirmPassword: false }">
        <div class="w-full max-w-[460px] bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/60 border border-slate-100 relative">
            
            <!-- Back Arrow Button (Top-Left) -->
            <a 
                href="{{ route('home') }}" 
                wire:navigate.hover 
                class="absolute top-6 left-6 w-9 h-9 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 flex items-center justify-center transition shadow-2xs"
                title="Về trang chủ"
            >
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>

            <!-- Centered Logo -->
            @php
                $loginLogoUrl = !empty($setting?->interface_settings['header']['logo']) 
                    ? get_curator_media_url($setting->interface_settings['header']['logo']) 
                    : (!empty($loginSettings['login_logo']) 
                        ? get_curator_media_url($loginSettings['login_logo']) 
                        : null);
            @endphp
            @if($loginLogoUrl)
            <div class="text-center pt-1 mb-3">
                <a href="{{ route('home') }}" wire:navigate.hover class="inline-block">
                    <img 
                        src="{{ $loginLogoUrl }}" 
                        alt="{{ $loginSettings['login_title'] ?? 'Logo' }}" 
                        class="h-16 w-auto mx-auto object-contain"
                    >
                </a>
            </div>
            @endif

            <!-- Heading -->
            <div class="text-center mb-5">
                <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-1">
                    {{ !empty($loginSettings['login_title']) ? $loginSettings['login_title'] : 'Đăng nhập hoặc Đăng ký TV mới' }}
                </h2>
                <p class="text-xs text-slate-500 font-normal">
                    @if ($mode === 'register')
                        Điền thông tin bên dưới để đăng ký Thành viên mới
                    @else
                        {{ !empty($loginSettings['login_hint']) ? $loginSettings['login_hint'] : 'Nhập số điện thoại hoặc email để đăng nhập' }}
                    @endif
                </p>
            </div>

            <!-- Mode Switcher Tabs: Đăng nhập | Đăng ký TV mới (Full page reload to avoid input conflict) -->
            <div class="grid grid-cols-2 gap-1.5 bg-slate-100 p-1.5 rounded-2xl mb-5">
                <a
                    href="{{ route('login.vi') }}"
                    class="py-2.5 px-3 rounded-xl text-xs sm:text-[13px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 {{ $mode === 'login' ? 'bg-white text-[#CE171F] shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                    <span>Đăng nhập</span>
                </a>
                <a
                    href="{{ route('register.vi') }}"
                    class="py-2.5 px-3 rounded-xl text-xs sm:text-[13px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 {{ $mode === 'register' ? 'bg-[#CE171F] text-white shadow-xs' : 'text-slate-600 hover:text-[#CE171F]' }}"
                >
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Đăng ký TV mới</span>
                </a>
            </div>

            <!-- Error Notification -->
            @if ($errorMessage)
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-700 text-xs flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0 mt-0.5"></i>
                    <span>{{ $errorMessage }}</span>
                </div>
            @endif

            @if ($successMessage)
                <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200/80 text-[#CE171F] text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-[#CE171F] shrink-0"></i>
                    <span>{{ $successMessage }}</span>
                </div>
            @endif

            <!-- TAB 1: FORM ĐĂNG NHẬP -->
            @if ($mode === 'login')
                <form wire:submit.prevent="submitLogin" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Số điện thoại hoặc Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <input 
                                wire:model="phone" 
                                type="text" 
                                autofocus
                                placeholder="Nhập số điện thoại hoặc email của bạn" 
                                class="w-full pl-10 pr-4 py-3 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition shadow-2xs font-medium"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Mật khẩu <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input 
                                wire:model="password" 
                                :type="showPassword ? 'text' : 'password'" 
                                placeholder="Nhập mật khẩu đăng nhập" 
                                class="w-full pl-10 pr-10 py-3 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition shadow-2xs font-medium"
                            >
                            <button 
                                @click="showPassword = !showPassword" 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                            >
                                <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3 bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs sm:text-sm font-semibold rounded-2xl shadow-sm hover:shadow transition-all duration-200 cursor-pointer"
                    >
                        Đăng nhập
                    </button>
                </form>

                <!-- Callout for New Member Registration -->
                <div class="mt-5 pt-4 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500 mb-2.5">
                        Bạn là Thành viên mới chưa có tài khoản?
                    </p>
                    <a
                        href="{{ route('register.vi') }}"
                        class="w-full py-2.5 px-4 rounded-2xl border-2 border-[#CE171F] text-[#CE171F] hover:bg-red-50 text-xs sm:text-sm font-bold transition cursor-pointer flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        <span>Mở Form Đăng ký TV mới</span>
                    </a>
                </div>

            <!-- TAB 2: FORM ĐĂNG KÝ THÀNH VIÊN MỚI (TV MỚI) -->
            @else
                <form wire:submit.prevent="submitRegister" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Họ và tên thành viên <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input 
                                wire:model="name" 
                                type="text" 
                                autofocus
                                placeholder="Nhập đầy đủ họ và tên của bạn" 
                                class="w-full pl-10 pr-4 py-2.5 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition font-medium"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Số điện thoại <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <input 
                                    wire:model="phone" 
                                    type="tel" 
                                    placeholder="VD: 0903856861" 
                                    class="w-full pl-9 pr-3 py-2.5 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition font-medium"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Email <span class="text-[10px] font-normal text-slate-400">(Tùy chọn)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <input 
                                    wire:model="registerEmail" 
                                    type="email" 
                                    placeholder="email@domain.com" 
                                    class="w-full pl-9 pr-3 py-2.5 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition font-medium"
                                >
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Địa chỉ liên hệ / nhận hàng
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <input 
                                wire:model="registerAddress" 
                                type="text" 
                                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành..." 
                                class="w-full pl-10 pr-4 py-2.5 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition font-medium"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Mật khẩu <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <input 
                                    wire:model="password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    placeholder="Tối thiểu 6 ký tự" 
                                    class="w-full pl-9 pr-8 py-2.5 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition font-medium"
                                >
                                <button 
                                    @click="showPassword = !showPassword" 
                                    type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                                >
                                    <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Nhập lại mật khẩu <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <input 
                                    wire:model="password_confirmation" 
                                    :type="showConfirmPassword ? 'text' : 'password'" 
                                    placeholder="Xác nhận mật khẩu" 
                                    class="w-full pl-9 pr-8 py-2.5 bg-[#f8faf9] border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#CE171F] focus:outline-none transition font-medium"
                                >
                                <button 
                                    @click="showConfirmPassword = !showConfirmPassword" 
                                    type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
                                >
                                    <i :class="showConfirmPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3 bg-[#CE171F] hover:bg-[#B91C1C] text-white text-xs sm:text-sm font-semibold rounded-2xl shadow-sm hover:shadow transition-all duration-200 cursor-pointer mt-2 flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-user-check text-xs"></i>
                        <span>Hoàn tất Đăng ký Thành viên mới</span>
                    </button>
                </form>

                <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                    <span class="text-xs text-slate-500">Đã có tài khoản thành viên?</span>
                    <a
                        href="{{ route('login.vi') }}"
                        class="text-xs font-bold text-[#CE171F] hover:underline ml-1 cursor-pointer"
                    >
                        Đăng nhập ngay
                    </a>
                </div>
            @endif

        </div>
    </div>
@endauth
</div>
