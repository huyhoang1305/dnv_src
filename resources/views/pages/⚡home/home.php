<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Content;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;
    public $categories;
    public $flashSaleProducts;
    public $newArrivalProducts;
    public $latestArticles;
    public $coupons;
    public $savedVoucherCode = '';
    public $toastMessage = '';
    public $showToast = false;

    public function mount()
    {
        $this->setting = Setting::getSystemSetting();
        $this->coupons = Coupon::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $this->savedVoucherCode = (string) (CartService::getVoucher() ?? '');
        $this->categories = Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order', 'asc')->get();

        // 1. Flash sale products from database
        $this->flashSaleProducts = Product::with('brand')
            ->sellable()
            ->where('is_flash_sale', true)
            ->orderBy('sort_order', 'asc')
            ->take(12)
            ->get();

        // 2. New arrivals & featured products from database
        $this->newArrivalProducts = Product::with('brand')
            ->sellable()
            ->where('is_flash_sale', false)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->take(12)
            ->get();

        // 3. Latest blog articles from database
        $this->latestArticles = Content::with('contentCategory')
            ->where('is_active', true)
            ->whereHas('contentType', fn ($q) => $q->where('slug', '!=', 'chinh-sach'))
            ->latest()
            ->take(3)
            ->get();
    }

    public function quickAdminLogin()
    {
        if (!app()->isLocal()) {
            return;
        }

        $admin = User::where('is_active', true)->first() ?? User::first();
        if ($admin) {
            auth()->login($admin, remember: true);
            return redirect()->to('/hg-admin');
        }
    }

    #[Renderless]
    public function addToCart($productId)
    {
        $product = Product::sellable()->find($productId);
        if (!$product) return;

        CartService::add($product, 1);
        $this->dispatch('cart-updated');

        $msg = "Đã thêm \"{$product->name}\" vào giỏ hàng!";
        $this->dispatch('home-cart-toast', message: $msg);
    }

    #[Renderless]
    public function saveVoucher($code)
    {
        $cleanCode = strtoupper(trim((string) $code));
        if ($cleanCode === '') {
            return;
        }

        CartService::setVoucher($cleanCode);
    }
};
