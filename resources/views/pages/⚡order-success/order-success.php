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

        $dbOrder = $this->orderCode
            ? Order::with('items.product')->where('order_number', $this->orderCode)->first()
            : null;

        if ($sessionOrder && !empty($sessionOrder['order_number']) && $sessionOrder['order_number'] === $this->orderCode) {
            if ($dbOrder) {
                $sessionOrder['status'] = $dbOrder->status ?? 'pending';
            } else {
                $sessionOrder['status'] = $sessionOrder['status'] ?? 'pending';
            }
            $sessionOrder['is_restricted'] = false;
            $this->orderData = $sessionOrder;
            return;
        }

        if ($dbOrder) {
            $user = auth()->user();
            $isOwner = $user && (
                ($dbOrder->user_id && (int) $dbOrder->user_id === (int) $user->id)
                || (!empty($user->phone) && $dbOrder->customer_phone === $user->phone)
                || (!empty($user->email) && $dbOrder->customer_email === $user->email)
                || $user->hasRole('super_admin')
                || $user->roles()->exists()
            );

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

            $rawPhone = (string) $dbOrder->customer_phone;
            $maskedPhone = strlen($rawPhone) >= 7
                ? (substr($rawPhone, 0, 4) . '***' . substr($rawPhone, -3))
                : '******';

            $this->orderData = [
                'order_number' => $dbOrder->order_number,
                'status' => $dbOrder->status ?? 'pending',
                'customer_name' => $isOwner ? ($dbOrder->customer_name ?: 'Quý khách hàng') : 'Quý khách hàng',
                'customer_phone' => $isOwner ? $rawPhone : $maskedPhone,
                'customer_email' => $isOwner ? $dbOrder->customer_email : null,
                'shipping_address' => $isOwner ? $dbOrder->shipping_address : 'Thông tin địa chỉ đã được bảo mật',
                'order_notes' => $isOwner ? $dbOrder->order_notes : null,
                'delivery_method' => $dbOrder->delivery_method ?? 'standard',
                'payment_method' => $dbOrder->payment_method ?? 'cod',
                'items' => $items,
                'subtotal' => $dbOrder->sub_total,
                'discount' => $dbOrder->discount_amount,
                'shipping_fee' => $dbOrder->shipping_fee,
                'total' => $dbOrder->total_amount,
                'created_at' => $dbOrder->created_at ? $dbOrder->created_at->format('H:i d/m/Y') : now()->format('H:i d/m/Y'),
                'is_restricted' => !$isOwner,
            ];
        }
    }
};
