<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống đang bảo trì | Doanh Nghiệp Việt (DNV)</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/logo/favicon-dnv.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAF9] text-slate-800 min-h-screen flex flex-col justify-between selection:bg-[#CE171F] selection:text-white">
    <header class="bg-white border-b border-slate-100 py-3 px-4 sm:px-8">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('storage/logo/favicon-dnv.png') }}" alt="DNV Logo" class="w-10 h-10 object-contain">
                <div>
                    <span class="text-sm font-black tracking-tight text-[#CE171F] block leading-tight">DOANH NGHIỆP VIỆT</span>
                    <span class="text-[10px] text-slate-500 font-medium block">Kênh Thương mại Điện tử DNV</span>
                </div>
            </a>
            <a href="tel:0903856861" class="text-xs font-semibold text-slate-600 hover:text-[#CE171F] flex items-center gap-1.5 transition">
                <i class="fa-solid fa-phone text-[#CE171F]"></i>
                <span class="hidden sm:inline">Hotline:</span> 0903.856.861
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-6">
        <div class="max-w-lg w-full text-center space-y-6">
            <div class="relative inline-block">
                <div class="text-8xl sm:text-9xl font-black text-slate-200 tracking-widest select-none">503</div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 border-4 border-white shadow-lg flex items-center justify-center text-3xl">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Hệ thống đang bảo trì & nâng cấp
                </h1>
                <p class="text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    Website đang được nâng cấp định kỳ để phục vụ quý khách tốt hơn. Chúng tôi sẽ sớm quay trở lại. Xin chân thành cảm ơn sự kiên nhẫn của Quý khách!
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 max-w-md mx-auto space-y-3 text-xs text-left shadow-xs">
                <div class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Liên hệ hỗ trợ khẩn cấp:</div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-[#CE171F]"></i>
                        <span>Hotline chăm sóc khách hàng:</span>
                    </span>
                    <a href="tel:0903856861" class="font-bold text-[#CE171F] hover:underline">0903.856.861</a>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-comment-dots text-blue-600"></i>
                        <span>Tư vấn qua Zalo:</span>
                    </span>
                    <a href="https://zalo.me/0903856861" target="_blank" class="font-bold text-blue-600 hover:underline">0903.856.861</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-100 bg-white">
        © {{ date('Y') }} DOANH NGHIỆP VIỆT (DNV). Giữ toàn quyền bảo lưu.
    </footer>
</body>
</html>
