<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) ?? 'vi' }}">

@php
    $setting = $setting ?? \App\Models\Setting::getSystemSetting() ?? new \App\Models\Setting();
@endphp

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon & App Icons (DNV Chuáº©n) -->
    @php
        $siteFavicon = !empty($setting->general_settings['site_icon']) 
            ? curator_url($setting->general_settings['site_icon']) 
            : asset('storage/logo/favicon-dnv.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $siteFavicon }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ $siteFavicon }}">
    <meta property="og:site_name" content="{{ $setting->general_settings['site_name'] }}">
    <meta name="application-name" content="{{ $setting->general_settings['site_name'] }}">
    <meta name="apple-mobile-web-app-title" content="{{ $setting->general_settings['site_name'] }}">
    @stack('seo')


    <!-- Preconnect Fonts: Be Vietnam Pro & Montserrat (Theo chuáº©n ASXH Doanh Nghiá»‡p Viá»‡t) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 (Loaded directly for instant icon rendering & wire:navigate stability) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Swiper 11 Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" data-navigate-once>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" data-navigate-once></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    {!! $setting->general_settings['scripts']['header'] ?? '' !!}
    @if ($setting->general_settings['security']['block_copy'] ?? false)
        <style>
            body {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
        </style>
    @endif
    <style>
        :root {
            --font-sans: 'Be Vietnam Pro', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-heading: 'Montserrat', system-ui, sans-serif;
        }
        body {
            font-family: var(--font-sans) !important;
        }
        h1, h2, h3, .font-heading {
            font-family: var(--font-heading), var(--font-sans) !important;
        }
    </style>
</head>

<body class="bg-[#FAFAFA] font-sans text-slate-800 antialiased min-h-screen flex flex-col justify-between selection:bg-red-100 selection:text-[#CE171F]">
    {!! $setting->general_settings['scripts']['body'] ?? '' !!}

    <!-- Shared Header Component -->
    <livewire:inc.header />

    <!-- Main Page Content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Shared Footer Component -->
    <livewire:inc.footer />

    <!-- Global Draggable Local Dev Toolkit Floating Widget (Only in app()->isLocal()) -->
    


    @livewireScripts
    {!! $setting->general_settings['scripts']['footer'] ?? '' !!}
    @if (
        ($setting->general_settings['security']['block_inspect'] ?? false) ||
            ($setting->general_settings['security']['block_copy'] ?? false))
        <script>
            @if ($setting->general_settings['security']['block_copy'] ?? false)
                document.addEventListener('contextmenu', event => event.preventDefault());
            @endif
            @if ($setting->general_settings['security']['block_inspect'] ?? false)
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'F12' || e.keyCode === 123) {
                        e.preventDefault();
                        return false;
                    }
                    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                        e.preventDefault();
                        return false;
                    }
                    if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                        e.preventDefault();
                        return false;
                    }
                    if (e.ctrlKey && e.key === 'U') {
                        e.preventDefault();
                        return false;
                    }
                });
            @endif
        </script>
    @endif
</body>

</html>
