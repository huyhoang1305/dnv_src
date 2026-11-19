<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add standard security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Chống nhúng khung (Clickjacking protection)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Chống đoán loại tệp (MIME type sniffing protection)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Chống tấn công XSS trên trình duyệt cũ
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Chính sách Referrer bảo mật
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Quyền truy cập thiết bị (Permissions Policy)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Kích hoạt HSTS nếu kết nối an toàn HTTPS
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
