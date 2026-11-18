<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;
    public $slug;
    public $product;
    public $relatedProducts;
    public $selectedVariantId = null;
    public $selectedImageUrl = null;
    public $quantity = 1;
    public $toastMessage = '';
    public $showToast = false;

    public $reviewName = '';
    public $reviewRating = 5;
    public $reviewContent = '';
    public $reviewMessage = '';
    public $reviewError = '';

    public function mount($slug = null)
    {
        $this->setting = Setting::getSystemSetting();
        $this->slug = $slug;

        if (Auth::check()) {
            $this->reviewName = Auth::user()->name ?? '';
        }

        $query = Product::with([
            'brand',
            'category',
            'categories',
            'variants' => fn ($q) => $q->orderBy('sort_order')->with('attributeValues.attributeType'),
            'reviews' => fn ($q) => $q->where('is_approved', true)->orderBy('sort_order')->latest(),
        ]);

        $this->product = $this->slug
            ? (clone $query)->sellable()->where('slug', $this->slug)->first()
            : $query->sellable()->first();

        if (!$this->product) {
            abort(404);
        }

        $this->product->increment('view_count');

        if ($this->product->variants->isNotEmpty()) {
            $firstVar = $this->product->variants->first();
            $this->selectedVariantId = $firstVar->id;
            if (!empty($firstVar->image_url)) {
                $this->selectedImageUrl = curator_url($firstVar->image_url, 600, 600);
            }
        }

        $pivotCatIds = $this->product->categories->pluck('id')->filter()->values()->all();

        $this->relatedProducts = Product::with('brand')
            ->where('id', '!=', $this->product->id)
            ->where(function ($q) use ($pivotCatIds) {
                if ($this->product->category_id) {
                    $q->orWhere('category_id', $this->product->category_id);
                }
                if (!empty($pivotCatIds)) {
                    $q->orWhereHas('categories', fn ($cq) => $cq->whereIn('categories.id', $pivotCatIds));
                }
                if ($this->product->brand_id) {
                    $q->orWhere('brand_id', $this->product->brand_id);
                }
            })
            ->sellable()
            ->take(5)
            ->get();

        if ($this->relatedProducts->isEmpty()) {
            $this->relatedProducts = Product::with('brand')
                ->where('id', '!=', $this->product->id)
                ->sellable()
                ->take(5)
                ->get();
        }
    }

    public function selectVariant($variantId)
    {
        if (!$this->product) {
            return;
        }

        $variant = $this->product->variants->firstWhere('id', (int) $variantId);
        if ($variant) {
            $this->selectedVariantId = $variant->id;
            $this->selectedImageUrl = !empty($variant->image_url)
                ? curator_url($variant->image_url, 600, 600)
                : null;
        }
    }

    public function getSelectedVariantProperty()
    {
        if (!$this->product || !$this->selectedVariantId) {
            return null;
        }

        return $this->product->variants->firstWhere('id', (int) $this->selectedVariantId);
    }

    public function getActivePriceProperty(): float
    {
        if ($variant = $this->selectedVariant) {
            return $variant->effective_price;
        }

        if (!$this->product) {
            return 0;
        }

        return (float) (($this->product->sale_price && $this->product->sale_price > 0)
            ? $this->product->sale_price
            : $this->product->base_price);
    }

    public function submitReview()
    {
        $this->reviewMessage = '';
        $this->reviewError = '';

        if (!$this->product) {
            return;
        }

        $name = trim($this->reviewName);
        $content = trim($this->reviewContent);

        if ($name === '') {
            $this->reviewError = 'Vui lòng nhập tên của bạn.';
            return;
        }

        if ($content === '') {
            $this->reviewError = 'Vui lòng nhập nội dung đánh giá.';
            return;
        }

        // Chống spam: Giới hạn tối đa 3 lần gửi đánh giá trong 10 phút từ 1 IP (Issue #12)
        $throttleKey = 'review_attempt:' . request()->ip() . ':' . $this->product->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->reviewError = 'Bạn đã gửi đánh giá quá nhiều lần. Vui lòng thử lại sau ' . ceil($seconds / 60) . ' phút.';
            return;
        }
        RateLimiter::hit($throttleKey, 600);

        Review::create([
            'product_id' => $this->product->id,
            'user_id' => Auth::id(),
            'customer_name' => $name,
            'rating' => max(1, min(5, (int) $this->reviewRating)),
            'content' => $content,
            'is_approved' => false,
        ]);

        $this->reviewContent = '';
        $this->reviewRating = 5;
        $this->reviewMessage = 'Cảm ơn bạn đã gửi đánh giá! Nhận xét của bạn sẽ hiển thị sau khi được ban quản trị kiểm duyệt.';

        $this->product->load([
            'reviews' => fn ($q) => $q->where('is_approved', true)->orderBy('sort_order')->latest(),
        ]);
    }

    public function incrementQty()
    {
        $this->quantity++;
    }

    public function decrementQty()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        if (!$this->product || $this->activePrice <= 0) return;

        CartService::add($this->product, $this->quantity, $this->selectedVariantId ? (int) $this->selectedVariantId : null);
        $this->dispatch('cart-updated');

        $variantSuffix = $this->selectedVariant ? (' (' . $this->selectedVariant->label . ')') : '';
        $msg = "Đã thêm {$this->quantity} sản phẩm{$variantSuffix} vào giỏ hàng!";
        $this->toastMessage = $msg;
        $this->showToast = true;
        $this->dispatch('prod-cart-toast', message: $msg);
    }

    public function buyNow()
    {
        if (!$this->product || $this->activePrice <= 0) return;

        CartService::add($this->product, $this->quantity, $this->selectedVariantId ? (int) $this->selectedVariantId : null);
        $this->dispatch('cart-updated');

        return $this->redirect(route('checkout'), navigate: true);
    }
};
