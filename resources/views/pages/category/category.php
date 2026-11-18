<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public $setting;

    public $slug;
    public $toastMessage = '';
    public $showToast = false;

    #[Url]
    public $subcat = 'all';

    #[Url]
    public $brand = 'all';

    #[Url]
    public $sort = 'default';

    #[Url]
    public $q = '';

    #[Url]
    public $perPage = 24;

    public function mount($slug = 'de-xuat')
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug ?? 'de-xuat';

        // Trả HTTP 404 nếu danh mục không tồn tại trong hệ thống (Issue #17)
        if ($this->slug && !in_array($this->slug, ['de-xuat', 'all', 'danh-muc'])) {
            $catExists = Category::where('slug', $this->slug)->where('is_active', true)->exists();
            if (!$catExists) {
                abort(404, 'Không tìm thấy danh mục sản phẩm yêu cầu.');
            }
        }

        if (!request()->has('perPage')) {
            $this->perPage = (int)($this->setting?->interface_settings['products']['default_per_page'] ?? 24);
        }
    }

    public function setSubcat($sub)
    {
        $this->subcat = $sub;
        $this->perPage = 24;
    }

    public function setSort($order)
    {
        $this->sort = $order;
        $this->perPage = 24;
    }

    public function setBrand($b)
    {
        $this->brand = $b;
        $this->perPage = 24;
    }

    public function setPerPage($count)
    {
        $this->perPage = (int) $count;
    }

    public function updatedQ()
    {
        $this->perPage = 24;
    }

    public function loadMore()
    {
        $this->perPage += 24;
    }

    public function getCategoriesProperty()
    {
        return Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order', 'asc');
            }])
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function getCurrentCategoryProperty()
    {
        if ($this->slug && !in_array($this->slug, ['de-xuat', 'all', 'danh-muc'])) {
            return Category::where('slug', $this->slug)
                ->where('is_active', true)
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order', 'asc');
                }])
                ->first();
        }
        return null;
    }

    public function getBrandsProperty()
    {
        return Brand::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getFilteredQuery()
    {
        $query = Product::with('brand', 'category')
            ->sellable();

        // Filter by main category if not default / all
        if ($this->slug && !in_array($this->slug, ['de-xuat', 'all', 'danh-muc'])) {
            $cat = Category::where('slug', $this->slug)->first();
            if ($cat) {
                $childIds = $cat->children()->pluck('id')->toArray();
                $catIds = array_merge([$cat->id], $childIds);
                $query->where(function ($q) use ($catIds) {
                    $q->whereIn('category_id', $catIds)
                      ->orWhereHas('categories', function ($cq) use ($catIds) {
                          $cq->whereIn('categories.id', $catIds);
                      });
                });
            }
        }

        // Filter by subcategory
        if ($this->subcat && $this->subcat !== 'all') {
            $subCat = Category::where('slug', $this->subcat)->first();
            if ($subCat) {
                $query->where(function ($q) use ($subCat) {
                    $q->where('category_id', $subCat->id)
                      ->orWhereHas('categories', function ($cq) use ($subCat) {
                          $cq->where('categories.id', $subCat->id);
                      });
                });
            }
        }

        // Filter by brand
        if ($this->brand && $this->brand !== 'all') {
            $b = Brand::where('slug', $this->brand)->first();
            if ($b) {
                $query->where('brand_id', $b->id);
            }
        }

        // Filter by search query q
        if (!empty(trim($this->q))) {
            $searchTerm = trim($this->q);
            $query->where(function ($sq) use ($searchTerm) {
                $sq->where('name', 'like', "%{$searchTerm}%")
                   ->orWhere('sku', 'like', "%{$searchTerm}%")
                   ->orWhereHas('brand', function ($bq) use ($searchTerm) {
                       $bq->where('name', 'like', "%{$searchTerm}%");
                   });
            });
        }

        // Sorting
        if ($this->sort === 'low_to_high') {
            $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), base_price) ASC');
        } elseif ($this->sort === 'high_to_low') {
            $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), base_price) DESC');
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
        }

        return $query;
    }

    public function getProductsProperty()
    {
        return $this->getFilteredQuery()->take($this->perPage)->get();
    }

    public function getTotalProductsProperty()
    {
        return $this->getFilteredQuery()->count();
    }

    public function getHasMoreProperty()
    {
        return $this->totalProducts > $this->perPage;
    }

    #[Renderless]
    public function addToCart($productId)
    {
        $product = Product::sellable()->find($productId);
        if (!$product) return;

        CartService::add($product, 1);
        $this->dispatch('cart-updated');

        $msg = "Đã thêm \"{$product->name}\" vào giỏ hàng!";
        $this->dispatch('cat-cart-toast', message: $msg);
    }
};
