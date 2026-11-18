<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class CheckSiteStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Luôn cho phép các API nội bộ phục vụ Admin/Livewire để không bị gián đoạn
        if ($request->is('hg-admin*') || $request->is('admin*') || $request->is('livewire/*')) {
            return $next($request);
        }

        $isActive = true; // Mặc định là bật

        try {
            $setting = Setting::getSystemSetting();
            if ($setting && isset($setting->general_settings['system']['is_active'])) {
                $isActive = (bool) $setting->general_settings['system']['is_active'];
            }
        } catch (\Exception $e) {
            return $next($request);
        }

        // Nếu Web bị tắt: Chỉ tài khoản Ban quản trị có quyền truy cập Admin mới được vượt rào
        if (!$isActive) {
            $user = auth()->user();
            $isAdmin = $user && $user->is_active && ($user->hasRole('super_admin') || $user->roles()->exists() || $user->permissions()->exists());
            if (!$isAdmin) {
                abort(503, 'Website đang bảo trì hoặc tạm ngưng hoạt động. Vui lòng quay lại sau.');
            }
        }

        return $next($request);
    }
}
