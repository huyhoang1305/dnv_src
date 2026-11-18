<?php

namespace App\Filament\Widgets;

use App\Models\Media;
use Filament\Notifications\Notification;
use Filament\Widgets\AccountWidget as BaseAccountWidget;
use Illuminate\Support\Facades\Storage;

class AccountWidget extends BaseAccountWidget
{
    protected string $view = 'filament.widgets.account-widget';

    public ?array $storageStats = null;

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->storageStats = $this->getStorageStats();
    }

    public function getStorageStats(): array
    {
        try {
            $files = Storage::disk('public')->allFiles();
            $totalBytes = 0;
            $oversizedCount = 0;

            foreach ($files as $file) {
                $size = Storage::disk('public')->size($file);
                $totalBytes += $size;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if ($size > 1048576 && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $oversizedCount++;
                }
            }

            $formattedTotal = $totalBytes >= 1073741824
                ? round($totalBytes / 1073741824, 2) . ' GB'
                : round($totalBytes / 1048576, 2) . ' MB';

            return [
                'total_bytes' => $totalBytes,
                'total_size' => $formattedTotal,
                'total_files' => count($files),
                'oversized_count' => $oversizedCount,
            ];
        } catch (\Throwable $e) {
            return [
                'total_bytes' => 0,
                'total_size' => '0 MB',
                'total_files' => 0,
                'oversized_count' => 0,
            ];
        }
    }

    public function getOversizedFiles(): array
    {
        try {
            $files = Storage::disk('public')->allFiles();
            $oversizedFiles = [];

            foreach ($files as $file) {
                $size = Storage::disk('public')->size($file);
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if ($size > 1048576 && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $oversizedFiles[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => $size,
                        'formatted_size' => round($size / 1048576, 2) . ' MB',
                        'url' => Storage::disk('public')->url($file),
                        'ext' => $ext,
                    ];
                }
            }

            return $oversizedFiles;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function saveCompressedImage(string $path, string $base64Data): array
    {
        try {
            $path = trim($path, "/\\");
            if (empty($path) || empty($base64Data)) {
                return ['success' => false, 'error' => 'Dữ liệu không hợp lệ'];
            }

            // Chống tấn công Path Traversal
            if (str_contains($path, '..') || str_contains($path, "\0")) {
                return ['success' => false, 'error' => 'Đường dẫn tệp không hợp lệ'];
            }

            // Kiểm tra đuôi file nằm trong whitelist ảnh
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return ['success' => false, 'error' => 'Định dạng file không được phép'];
            }

            // Chỉ cho phép ghi đè lên file ảnh đã tồn tại trong public storage
            if (!Storage::disk('public')->exists($path)) {
                return ['success' => false, 'error' => 'Không tìm thấy tệp ảnh gốc trên hệ thống'];
            }

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }

            $decoded = base64_decode($base64Data);
            if ($decoded === false || empty($decoded)) {
                return ['success' => false, 'error' => 'Giải mã base64 thất bại'];
            }

            // Xác thực nội dung tệp thực tế là hình ảnh hợp lệ
            $imageInfo = @getimagesizefromstring($decoded);
            if (!$imageInfo || !in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'])) {
                return ['success' => false, 'error' => 'Nội dung tệp không phải là hình ảnh hợp lệ'];
            }

            $fullPath = Storage::disk('public')->path($path);
            $originalSize = file_exists($fullPath) ? filesize($fullPath) : 0;

            Storage::disk('public')->put($path, $decoded);
            clearstatcache(true, $fullPath);
            $newSize = file_exists($fullPath) ? filesize($fullPath) : strlen($decoded);

            // Cập nhật CSDL Media nếu có
            $media = Media::where('path', $path)->orWhere('path', '/' . $path)->first();
            if ($media) {
                $media->size = $newSize;
                $dims = @getimagesize($fullPath);
                if ($dims) {
                    $media->width = $dims[0];
                    $media->height = $dims[1];
                }
                $media->save();
            }

            return [
                'success' => true,
                'path' => $path,
                'original_size' => $originalSize,
                'new_size' => $newSize,
                'saved' => max(0, $originalSize - $newSize),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function notifyCompressionComplete(int $count, int $savedBytes): void
    {
        $savedMb = round($savedBytes / 1048576, 2);
        Notification::make()
            ->title('Nén Storage thành công!')
            ->body("Đã tối ưu {$count} ảnh lớn hơn 1MB xuống dưới 1MB. Tiết kiệm được {$savedMb} MB.")
            ->success()
            ->send();

        $this->refreshStats();
    }

    public function notifyAlreadyOptimized(): void
    {
        Notification::make()
            ->title('Storage đã tối ưu!')
            ->body('Tất cả các hình ảnh trong Storage đều đã dưới 1MB, không cần nén thêm.')
            ->info()
            ->send();
    }
}
