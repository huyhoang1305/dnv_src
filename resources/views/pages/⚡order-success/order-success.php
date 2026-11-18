<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public $setting;
    public $orderCode;
    public $orderData = [];

    public function mount($orderCode = null)
    {
        $this->setting = Setting::getSystemSetting();
        $sessionOrder = session()->get('latest_order', null);

        $this->orderCode = $orderCode ?: ($sessionOrder['order_number'] ?? null);

        if ($sessionOrder && !empty($sessionOrder['order_number']) && $sessionOrder['order_number'] === $this->orderCode) {
            $this->orderData = $sessionOrder;
            return;
        }

        if ($this->orderCode) {
            $dbOrder = Order::with('items.product')->where('order_number', $this->orderCode)->first();
            if ($dbOrder) {
                $items = [];
                foreach ($dbOrder->items as $it) {
                    $img = $it->product_image
                        ?: ($it->product?->image_url ? (get_curator_media_url($it->product->image_url) ?: curator_url($it->product->image_url, 150, 150)) : null);
                    $items[] = [
                        'name' => $it->product_name ?? $it->product?->name ?? 'Sản phẩm',
                        'price' => $it->price ?? 0,
                        'qty' => $it->quantity ?? 1,
                        'image' => $img ?: asset('storage/logo/favicon-dnv.png'),
                    ];
                }

                $this->orderData = [
                    'order_number' => $dbOrder->order_number,
                    'customer_name' => $dbOrder->customer_name ?: 'Quý khách hàng',
                    'customer_phone' => $dbOrder->customer_phone,
                    'customer_email' => $dbOrder->customer_email,
                    'shipping_address' => $dbOrder->shipping_address,
                    'order_notes' => $dbOrder->order_notes,
                    'delivery_method' => $dbOrder->delivery_method ?? 'standard',
                    'payment_method' => $dbOrder->payment_method ?? 'cod',
                    'items' => $items,
                    'subtotal' => $dbOrder->sub_total,
                    'discount' => $dbOrder->discount_amount,
                    'shipping_fee' => $dbOrder->shipping_fee,
                    'total' => $dbOrder->total_amount,
                    'created_at' => $dbOrder->created_at ? $dbOrder->created_at->format('H:i d/m/Y') : now()->format('H:i d/m/Y'),
                    'is_restricted' => false,
                ];
            }
        }
    }
};
