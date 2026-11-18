<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DailyVisit;
use Illuminate\Support\Facades\Cache;

class TrackDailyVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ track nếu là luồng khách (loại trừ Admin Panel nếu muốn, hiện tại cứ track hết luồng Web)
        if ($request->is('hg-admin*') || $request->is('livewire*') || $request->ajax()) {
            return $next($request);
        }

        try {
            $today = now()->toDateString();
            $ipAddress = $request->ip();

            // Lưu lại phiên của IP này trong ngày (sống 24 giờ kể từ khi truy cập)
            $ipCacheKey = 'visit_' . $today . '_' . $ipAddress;

            // Nếu IP chưa tồn tại trong cache ngày hôm nay -> Được tính là Khách độc nhất (Unique Visitor)
            $isNewVisitor = !Cache::has($ipCacheKey);

            if ($isNewVisitor) {
                Cache::put($ipCacheKey, true, now()->endOfDay());
            }

            // Tăng biến đếm thẳng vào DB cho ngày hôm nay
            DailyVisit::firstOrCreate(['visit_date' => $today])->increment('page_views');
            
            if ($isNewVisitor) {
                DailyVisit::where('visit_date', $today)->increment('unique_visitors');
            }
        } catch (\Exception $e) {
            // Không block trang web nếu DB gặp lỗi tracking
            \Log::error('Visit Tracking Error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
