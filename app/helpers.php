<?php

use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('resize_image_gd')) {
    /**
     * Tự động thay đổi kích thước & nén ảnh theo độ phân giải khung hình bằng PHP GD.
     * HIỂN THỊ FULL 100% ẢNH, KHÔNG BỊ CẮT XÉN (FIT / CONTAIN MODE).
     *
     * @param  string  $relativePath  Path tương đối của ảnh trong storage
     * @param  int  $width  Độ rộng tối đa của khung
     * @param  int|null  $height  Độ cao tối đa của khung
     * @param  string  $disk  Storage disk ('public')
     * @return string|null URL ảnh đã nén
     */
    function resize_image_gd(string $relativePath, int $width, ?int $height = null, string $disk = 'public'): ?string
    {
        if (empty($relativePath) || ! Storage::disk($disk)->exists($relativePath)) {
            return null;
        }

        // Tránh nén lặp lại các ảnh đã có đuôi _width-height
        if (preg_match('/_\d+-\d+\.[a-zA-Z0-9]+$/', $relativePath)) {
            return Storage::disk($disk)->url($relativePath);
        }

        $fullPath = storage_path('app/public/'.$relativePath);
        if (! file_exists($fullPath)) {
            return Storage::disk($disk)->url($relativePath);
        }

        $pathInfo = pathinfo($relativePath);
        $dirName = $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] : '';
        $fileName = $pathInfo['filename'];
        $ext = strtolower($pathInfo['extension'] ?? 'jpg');

        // Bỏ qua các file không phải là hình ảnh (vd: svg, pdf, ico)
        if (in_array($ext, ['svg', 'pdf', 'ico'])) {
            return Storage::disk($disk)->url($relativePath);
        }

        $srcInfo = @getimagesize($fullPath);
        if (! $srcInfo) {
            return Storage::disk($disk)->url($relativePath);
        }

        $srcW = $srcInfo[0];
        $srcH = $srcInfo[1];

        if ($srcW <= 0 || $srcH <= 0) {
            return Storage::disk($disk)->url($relativePath);
        }

        // Tính tỷ lệ thu nhỏ để giữ FULL 100% nội dung ảnh (Fit/Contain Mode - KHÔNG CẮT CÁC GÓC)
        if (empty($height) || $height <= 0) {
            $scale = $width / $srcW;
        } else {
            $scale = min($width / $srcW, $height / $srcH);
        }

        // Nếu ảnh gốc nhỏ hơn khung -> giữ nguyên kích thước gốc để không bị rỗ/vỡ ảnh
        if ($scale > 1.0) {
            $scale = 1.0;
        }

        $dstW = (int) max(1, round($srcW * $scale));
        $dstH = (int) max(1, round($srcH * $scale));

        $outputExt = function_exists('imagewebp') ? 'webp' : $ext;
        $resizedFileName = "{$fileName}_{$dstW}-{$dstH}.{$outputExt}";
        $resizedRelativePath = $dirName ? "{$dirName}/{$resizedFileName}" : $resizedFileName;
        $resizedFullPath = storage_path("app/public/{$resizedRelativePath}");

        // Trả về từ cache nếu tệp ảnh nén vừa khung đã tồn tại
        if (file_exists($resizedFullPath)) {
            return Storage::disk($disk)->url($resizedRelativePath);
        }

        // Tải ảnh gốc bằng GD
        $src = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
            'png' => @imagecreatefrompng($fullPath),
            'webp' => @imagecreatefromwebp($fullPath),
            'gif' => @imagecreatefromgif($fullPath),
            default => null,
        };

        if (! $src) {
            return Storage::disk($disk)->url($relativePath);
        }

        $dst = imagecreatetruecolor($dstW, $dstH);

        // Giữ độ trong suốt đối với định dạng PNG và WebP
        if (in_array($outputExt, ['png', 'webp']) || in_array($ext, ['png', 'webp'])) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
        }

        // Resample FULL ảnh từ (0, 0) đến (srcW, srcH) -> Không cắt xén bất kỳ vị trí nào
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        // Tạo thư mục lưu nếu chưa có
        $resizedDir = dirname($resizedFullPath);
        if (! file_exists($resizedDir)) {
            @mkdir($resizedDir, 0755, true);
        }

        // Xuất tệp ảnh GD đã nén tối ưu WebP / PNG / JPEG
        if ($outputExt === 'webp' && function_exists('imagewebp')) {
            imagewebp($dst, $resizedFullPath, 82);
        } elseif ($outputExt === 'png') {
            imagepng($dst, $resizedFullPath, 8);
        } else {
            imagejpeg($dst, $resizedFullPath, 82);
        }

        imagedestroy($src);
        imagedestroy($dst);

        return Storage::disk($disk)->url($resizedRelativePath);
    }
}

if (! function_exists('curator_img')) {
    /**
     * Lấy URL ảnh từ Curator Media bằng ID hoặc path kèm hỗ trợ resize GD nén theo khung ảnh.
     *
     * @param  int|string|null  $value  Media ID hoặc path string
     * @param  mixed  $width  Độ rộng (int) hoặc chuỗi resolution ('400x400', '400-400')
     * @param  mixed  $height  Độ cao (int)
     * @param  string  $return  'url' (mặc định) | 'path' | 'media'
     * @param  string|null  $default  URL fallback khi không tìm được
     * @return string|Media|null
     */
    function curator_img(
        int|string|null $value,
        mixed $width = null,
        mixed $height = null,
        string $return = 'url',
        ?string $default = null
    ): mixed {
        if (empty($value)) {
            return $default;
        }

        static $staticMediaCache = [];

        $targetWidth = null;
        $targetHeight = null;

        if (is_numeric($width)) {
            $targetWidth = (int) $width;
            $targetHeight = is_numeric($height) ? (int) $height : null;
        } elseif (is_string($width) && (str_contains($width, 'x') || str_contains($width, '-'))) {
            $parts = preg_split('/[x-]/i', $width);
            if (count($parts) >= 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $targetWidth = (int) $parts[0];
                $targetHeight = (int) $parts[1];
            }
        } elseif (in_array($width, ['url', 'path', 'media'])) {
            $return = $width;
        }

        // Mặc định nén theo khung chuẩn (max width 1200) nếu không truyền kích thước
        if ($targetWidth === null) {
            $targetWidth = 1200;
        }

        $isUuid = is_string($value) && (Str::isUuid($value) || Str::isUlid($value));
        $isNumeric = is_numeric($value);
        $relativePath = null;
        $disk = 'public';

        if ($isNumeric || $isUuid) {
            if (! array_key_exists($value, $staticMediaCache)) {
                $staticMediaCache[$value] = Cache::remember("curator_media_data_{$value}", 86400, function () use ($value) {
                    $media = App\Models\Media::find($value);
                    if (! $media) {
                        return null;
                    }

                    return [
                        'path' => $media->path,
                        'disk' => $media->disk ?? 'public',
                    ];
                });
            }
            $mediaData = $staticMediaCache[$value];

            if (! $mediaData) {
                return $default ?: asset('storage/logo/logo-dnv-asxh-chinh-thuc.png');
            }

            if ($return === 'media') {
                return App\Models\Media::find($value);
            }

            $relativePath = $mediaData['path'];
            $disk = $mediaData['disk'] ?? 'public';
        } else {
            $rawStr = (string) $value;
            if (empty($rawStr)) {
                return $default ?: asset('storage/logo/logo-dnv-asxh-chinh-thuc.png');
            }
            if (str_starts_with($rawStr, 'http://') || str_starts_with($rawStr, 'https://')) {
                return $rawStr;
            }
            if (str_starts_with($rawStr, '/storage/')) {
                $relativePath = substr($rawStr, 9);
            } elseif (str_starts_with($rawStr, 'storage/')) {
                $relativePath = substr($rawStr, 8);
            } else {
                $relativePath = $rawStr;
            }

            // If file doesn't exist at relativePath, check alternative folders
            if (! Storage::disk($disk)->exists($relativePath)) {
                $bName = basename($relativePath);
                if (Storage::disk($disk)->exists("san-pham/{$bName}")) {
                    $relativePath = "san-pham/{$bName}";
                } elseif (Storage::disk($disk)->exists("roots/products/{$bName}")) {
                    $relativePath = "roots/products/{$bName}";
                }
            }
        }

        if ($return === 'path') {
            return $relativePath;
        }

        // Nén ảnh tự động bằng PHP GD theo khung hình đã xác định
        $resizedUrl = resize_image_gd($relativePath, $targetWidth, $targetHeight, $disk);
        if ($resizedUrl) {
            return $resizedUrl;
        }

        if (! Storage::disk($disk)->exists($relativePath)) {
            return $default ?: asset('storage/logo/favicon-dnv.png');
        }

        return Storage::disk($disk)->url($relativePath);
    }
}

if (! function_exists('curator_url')) {
    /**
     * Shorthand lấy URL ảnh — tự động nén PHP GD theo khung hình kèm đuôi _width-height.
     */
    function curator_url(
        int|string|null $value,
        mixed $width = null,
        mixed $height = null,
        ?string $default = null
    ): ?string {
        if (is_string($width) && ! is_numeric($width) && ! str_contains($width, 'x') && ! str_contains($width, '-')) {
            $default = $width;
            $width = null;
            $height = null;
        }

        return curator_img($value, $width, $height, 'url', $default);
    }
}

if (! function_exists('get_curator_media_url')) {
    /**
     * Lấy URL tuyệt đối từ Curator Media ID hoặc đường dẫn lưu trữ, không fallback nếu không có ảnh.
     */
    function get_curator_media_url($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (is_array($value)) {
            $first = reset($value);
            if (is_array($first) && ! empty($first['id'])) {
                $value = $first['id'];
            } elseif (is_string($first)) {
                $value = $first;
            }
        }
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }
        $media = App\Models\Media::find($value);
        if ($media) {
            return $media->url ?: Storage::disk($media->disk ?? 'public')->url($media->path);
        }
        $rawStr = (string) $value;
        if (str_starts_with($rawStr, 'http://') || str_starts_with($rawStr, 'https://')) {
            return $rawStr;
        }
        $path = ltrim($rawStr, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return null;
    }
}
