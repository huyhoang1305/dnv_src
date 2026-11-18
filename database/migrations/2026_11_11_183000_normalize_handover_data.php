<?php

use App\Models\Content;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Chuẩn hóa payment_status của đơn hàng từ 'pending' sang 'unpaid'
        if (Schema::hasTable('orders')) {
            Order::where('payment_status', 'pending')->update(['payment_status' => 'unpaid']);
        }

        // 2. Làm sạch tên thương hiệu cũ còn sót trong nội dung blog và seo_title trang chính sách
        if (Schema::hasTable('contents')) {
            foreach (Content::all() as $content) {
                $changed = false;

                if (!empty($content->body)) {
                    $newBody = str_ireplace(['thv.vn', 'www.dnv1.com'], 'dnv1.biz', $content->body);
                    $newBody = preg_replace('/\bTHV\b/u', 'DNV', $newBody);
                    if ($newBody !== $content->body) {
                        $content->body = $newBody;
                        $changed = true;
                    }
                }

                if (!empty($content->seo_title)) {
                    $trimmedSeoTitle = trim($content->seo_title);
                    if (in_array($trimmedSeoTitle, ['Chính Sách Thanh Toán', 'Quy Trình Mua Hàng'], true)) {
                        $content->seo_title = $trimmedSeoTitle . ' - DOANH NGHIỆP VIỆT';
                        $changed = true;
                    } elseif ($trimmedSeoTitle !== $content->seo_title) {
                        $content->seo_title = $trimmedSeoTitle;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $content->save();
                }
            }
        }
    }

    public function down(): void
    {
        // No-op data normalization migration
    }
};
