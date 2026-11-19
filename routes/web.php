<?php

use App\Http\Middleware\CheckSiteStatus;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware([CheckSiteStatus::class])->group(function () {
    Route::livewire('/', 'pages::home')->name('home');
    Route::livewire('/store', 'pages::store')->name('store');
    Route::livewire('/lien-he', 'pages::store')->name('contact');
    Route::livewire('/about', 'pages::about')->name('about');
    Route::livewire('/gioi-thieu', 'pages::about')->name('gioi-thieu');
    Route::livewire('/blog', 'pages::blog')->name('blog');
    Route::livewire('/blog/{slug}', 'pages::blog-detail')->name('blog.detail');
    // 301 Permanent Redirects for legacy / mismatched category slugs
    Route::redirect('/danh-muc/do-choi-tre-em', '/danh-muc/nguyen-lieu-nau-an-lam-banh', 301);
    Route::redirect('/do-choi-tre-em', '/danh-muc/nguyen-lieu-nau-an-lam-banh', 301);
    Route::redirect('/danh-muc/nong-san', '/danh-muc/ngu-coc', 301);
    Route::redirect('/nong-san', '/danh-muc/ngu-coc', 301);

    Route::livewire('/danh-muc/{slug?}', 'pages::category')->name('category');
    Route::livewire('/san-pham/{slug}', 'pages::product-detail')->name('product.detail');

    // Auth & Account Management
    Route::livewire('/login', 'pages::login')->name('login');
    Route::livewire('/dang-nhap', 'pages::login')->name('login.vi');
    Route::livewire('/register', 'pages::login')->name('register');
    Route::livewire('/dang-ky', 'pages::login')->name('register.vi');
    Route::livewire('/tai-khoan', 'pages::login')->name('account');
    Route::livewire('/don-hang', 'pages::login')->name('orders');

    // Cart, Checkout & Order Success
    Route::livewire('/cart', 'pages::cart')->name('cart');
    Route::livewire('/gio-hang', 'pages::cart')->name('cart.vi');
    Route::livewire('/checkout', 'pages::checkout')->name('checkout');
    Route::livewire('/thanh-toan', 'pages::checkout')->name('checkout.vi');
    Route::livewire('/order-success/{orderCode?}', 'pages::order-success')->name('order.success');
    Route::livewire('/don-hang-thanh-cong/{orderCode?}', 'pages::order-success')->name('order.success.vi');

    // Policy Pages
    Route::livewire('/chinh-sach/{slug?}', 'pages::policy')->name('policy');
});

// Dynamic XML Sitemap for Google Search Console
Route::get('/sitemap.xml', function () {
    $urls = [];
    $nowAtom = now()->toAtomString();

    // 1. Static Pages
    $staticPages = [
        ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => route('category'), 'priority' => '0.9', 'changefreq' => 'daily'],
        ['loc' => route('gioi-thieu'), 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => route('contact'), 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => route('blog'), 'priority' => '0.8', 'changefreq' => 'daily'],
    ];
    foreach ($staticPages as $sp) {
        $urls[] = $sp + ['lastmod' => $nowAtom];
    }

    // 2. Active Categories with products (avoid submitting thin/noindex content to GSC)
    $categories = \App\Models\Category::where('is_active', true)
        ->whereHas('products', fn ($q) => $q->where('is_active', true))
        ->orderBy('sort_order')
        ->get(['slug', 'updated_at']);
    foreach ($categories as $cat) {
        if (!empty($cat->slug)) {
            $urls[] = [
                'loc' => route('category', $cat->slug),
                'lastmod' => ($cat->updated_at ?? now())->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }
    }

    // 3. Active Sellable Products (price > 0)
    $products = \App\Models\Product::sellable()->orderBy('id', 'desc')->get(['slug', 'updated_at']);
    foreach ($products as $prod) {
        if (!empty($prod->slug)) {
            $urls[] = [
                'loc' => route('product.detail', $prod->slug),
                'lastmod' => ($prod->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }
    }

    // 4. Active Blogs & Policy Pages
    $contents = \App\Models\Content::with('contentType')->where('is_active', true)->latest()->get(['slug', 'content_type_id', 'updated_at']);
    foreach ($contents as $item) {
        if (empty($item->slug)) {
            continue;
        }
        $isPolicy = $item->contentType?->slug === 'chinh-sach';
        $urls[] = [
            'loc' => $isPolicy ? route('policy', ['slug' => $item->slug]) : route('blog.detail', $item->slug),
            'lastmod' => ($item->updated_at ?? now())->toAtomString(),
            'changefreq' => $isPolicy ? 'monthly' : 'weekly',
            'priority' => $isPolicy ? '0.6' : '0.7',
        ];
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $loc = htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $lastmod = htmlspecialchars($u['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$u['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
})->name('sitemap');
