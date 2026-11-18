<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Content;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $setting;
    public $categories;
    public $navCategories = [];
    public $navPolicies = [];
    public $navBrands = [];
    public $cartCount = 0;
    public $searchQuery = '';

    #[On('cart-updated')]
    public function updateCartCount()
    {
        $this->cartCount = CartService::count();
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
    }

    public function mount()
    {
        if (!$this->setting) {
            $this->setting = Setting::getSystemSetting();
        }
        $this->cartCount = CartService::count();
        $this->categories = Category::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        // Load root categories with active subcategories from DB
        $dbCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order', 'asc');
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        $this->navCategories = $dbCategories->map(function ($cat) {
            $imgUrl = !empty($cat->image_url)
                ? (get_curator_media_url($cat->image_url) ?: curator_url($cat->image_url, 80, 80))
                : null;

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'icon' => $cat->icon ?: 'fa-solid fa-box',
                'image' => $imgUrl,
                'allUrl' => route('category', $cat->slug),
                'subcategories' => $cat->children->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'slug' => $sub->slug,
                    ];
                })->toArray(),
            ];
        })->toArray();

        // Load active policies & process from DB (type: chinh-sach)
        $policyIcons = [
            'chinh-sach-ban-hang' => 'fa-solid fa-handshake',
            'chinh-sach-bao-mat' => 'fa-solid fa-shield-halved',
            'chinh-sach-giao-hang' => 'fa-solid fa-truck-fast',
            'chinh-sach-thanh-toan' => 'fa-solid fa-credit-card',
            'quy-trinh-mua-hang' => 'fa-solid fa-clipboard-check',
        ];

        $this->navPolicies = Content::where('is_active', true)
            ->whereHas('contentType', fn ($q) => $q->where('slug', 'chinh-sach'))
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($p) use ($policyIcons) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'icon' => $policyIcons[$p->slug] ?? 'fa-solid fa-file-shield',
                    'url' => route('policy', ['slug' => $p->slug]),
                ];
            })->toArray();

        // Load active brands from DB
        $dbBrands = Brand::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $this->navBrands = $dbBrands->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
                'key' => $b->slug,
                'origin' => $b->origin,
                'url' => route('category') . '?brand=' . urlencode($b->slug),
            ];
        })->toArray();
    }

    public function getSearchResultsProperty()
    {
        $query = trim($this->searchQuery);
        if (mb_strlen($query) < 2) {
            return [
                'products' => [],
                'categories' => [],
                'brands' => [],
                'total' => 0,
            ];
        }

        $products = Product::sellable()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhereHas('brand', function ($bq) use ($query) {
                      $bq->where('name', 'like', "%{$query}%");
                  });
            })
            ->with('brand')
            ->take(6)
            ->get()
            ->map(function ($p) {
                $price = (float) (((float) $p->sale_price > 0) ? $p->sale_price : $p->base_price);
                $originalPrice = (float) $p->base_price;
                $img = curator_url($p->image_url, 120, 120) ?: asset('storage/logo/favicon-dnv.png');

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'brand' => $p->brand?->name ?? 'DNV',
                    'price' => $price,
                    'original_price' => $originalPrice,
                    'has_discount' => $p->sale_price && $p->sale_price < $p->base_price,
                    'image' => $img,
                    'url' => route('product.detail', $p->slug),
                ];
            })->toArray();

        $articles = Content::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            })
            ->with(['contentCategory', 'contentType'])
            ->take(4)
            ->get()
            ->map(function ($art) {
                $img = curator_url($art->image_url, 140, 100) ?: asset('storage/roots/about/about_story.jpg');
                $isPolicy = $art->contentType?->slug === 'chinh-sach';

                return [
                    'id' => $art->id,
                    'title' => $art->title,
                    'slug' => $art->slug,
                    'category' => $art->contentCategory?->name ?? ($isPolicy ? 'Chính sách' : 'Tin tức & Mẹo hay'),
                    'date' => $art->created_at?->format('d/m/Y') ?? '',
                    'image' => $img,
                    'url' => $isPolicy ? route('policy', $art->slug) : route('blog.detail', $art->slug),
                ];
            })->toArray();

        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->take(4)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'url' => route('category', $c->slug),
                ];
            })->toArray();

        $brands = Brand::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->take(4)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'url' => route('category') . '?brand=' . urlencode($b->slug),
                ];
            })->toArray();

        return [
            'products' => $products,
            'articles' => $articles,
            'categories' => $categories,
            'brands' => $brands,
            'total' => count($products) + count($articles) + count($categories) + count($brands),
        ];
    }
};
