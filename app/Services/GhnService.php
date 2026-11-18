<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhnService
{
    protected string $token;
    protected int $shopId;
    protected string $apiUrl;
    protected int $fromDistrictId;
    protected string $fromWardCode;
    protected int $fromProvinceId;

    public function __construct()
    {
        $setting = Setting::getSystemSetting();
        $ghnSettings = $setting?->general_settings['ghn'] ?? [];

        $this->token = !empty($ghnSettings['api_token'])
            ? (string) $ghnSettings['api_token']
            : (string) config('services.ghn.token', '');

        $this->shopId = !empty($ghnSettings['shop_id'])
            ? (int) $ghnSettings['shop_id']
            : (int) config('services.ghn.shop_id', 0);

        $apiUrl = !empty($ghnSettings['api_url'])
            ? (string) $ghnSettings['api_url']
            : (string) config('services.ghn.api_url', 'https://online-gateway.ghn.vn/shiip/public-api/');
        $this->apiUrl = rtrim($apiUrl, '/') . '/';

        $this->fromDistrictId = !empty($ghnSettings['from_district_id'])
            ? (int) $ghnSettings['from_district_id']
            : (int) config('services.ghn.from_district_id', 1454);

        $this->fromWardCode = !empty($ghnSettings['from_ward_code'])
            ? (string) $ghnSettings['from_ward_code']
            : (string) config('services.ghn.from_ward_code', '21201');

        $this->fromProvinceId = !empty($ghnSettings['from_province_id'])
            ? (int) $ghnSettings['from_province_id']
            : (int) config('services.ghn.from_province_id', 202);
    }

    /**
     * Default headers for GHN API requests
     */
    protected function getHeaders(): array
    {
        return [
            'token' => $this->token,
            'shop_id' => (string) $this->shopId,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get list of provinces/cities in Vietnam from GHN
     */
    public function getProvinces(): array
    {
        $cacheKey = 'ghn_master_provinces';
        $cached = Cache::get($cacheKey);
        if (!empty($cached) && is_array($cached)) {
            return $cached;
        }

        if (!empty($this->token)) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(8)
                    ->get($this->apiUrl . 'master-data/province');

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    if (!empty($data)) {
                        $provinces = [];
                        foreach ($data as $item) {
                            $provinces[] = [
                                'id' => (int) $item['ProvinceID'],
                                'name' => (string) $item['ProvinceName'],
                            ];
                        }

                        // Sort: Put HCM (202), Hanoi (201), Da Nang (203) on top, rest alphabetical
                        usort($provinces, function ($a, $b) {
                            $priorityIds = [202 => 1, 201 => 2, 203 => 3];
                            $pA = $priorityIds[$a['id']] ?? 999;
                            $pB = $priorityIds[$b['id']] ?? 999;
                            if ($pA !== $pB) {
                                return $pA <=> $pB;
                            }
                            return strcmp($a['name'], $b['name']);
                        });

                        Cache::put($cacheKey, $provinces, now()->addDays(7));
                        return $provinces;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('GHN API getProvinces failed: ' . $e->getMessage());
            }
        }

        // Dự phòng danh sách các tỉnh/thành phố phổ biến khi API lỗi hoặc chưa cấu hình token
        return $this->getFallbackProvinces();
    }

    /**
     * Get list of districts for a given province ID
     */
    public function getDistricts(int $provinceId): array
    {
        if ($provinceId <= 0) {
            return [];
        }

        $cacheKey = "ghn_master_districts_{$provinceId}";
        $cached = Cache::get($cacheKey);
        if (!empty($cached) && is_array($cached)) {
            return $cached;
        }

        if (!empty($this->token)) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(8)
                    ->post($this->apiUrl . 'master-data/district', [
                        'province_id' => $provinceId,
                    ]);

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    if (!empty($data)) {
                        $districts = [];
                        foreach ($data as $item) {
                            $districts[] = [
                                'id' => (int) $item['DistrictID'],
                                'name' => (string) $item['DistrictName'],
                            ];
                        }

                        usort($districts, fn($a, $b) => strcmp($a['name'], $b['name']));
                        Cache::put($cacheKey, $districts, now()->addDays(7));
                        return $districts;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("GHN API getDistricts failed for province {$provinceId}: " . $e->getMessage());
            }
        }

        return $this->getFallbackDistricts($provinceId);
    }

    /**
     * Get list of wards for a given district ID
     */
    public function getWards(int $districtId): array
    {
        if ($districtId <= 0) {
            return [];
        }

        $cacheKey = "ghn_master_wards_{$districtId}";
        $cached = Cache::get($cacheKey);
        if (!empty($cached) && is_array($cached)) {
            return $cached;
        }

        if (!empty($this->token)) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(8)
                    ->post($this->apiUrl . 'master-data/ward', [
                        'district_id' => $districtId,
                    ]);

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    if (!empty($data)) {
                        $wards = [];
                        foreach ($data as $item) {
                            $wards[] = [
                                'code' => (string) $item['WardCode'],
                                'name' => (string) $item['WardName'],
                            ];
                        }

                        usort($wards, fn($a, $b) => strcmp($a['name'], $b['name']));
                        Cache::put($cacheKey, $wards, now()->addDays(7));
                        return $wards;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("GHN API getWards failed for district {$districtId}: " . $e->getMessage());
            }
        }

        return $this->getFallbackWards($districtId);
    }

    /**
     * Fallback danh sách tỉnh/thành phố phổ biến tại Việt Nam
     */
    protected function getFallbackProvinces(): array
    {
        return [
            ['id' => 202, 'name' => 'TP. Hồ Chí Minh'],
            ['id' => 201, 'name' => 'Hà Nội'],
            ['id' => 203, 'name' => 'Đà Nẵng'],
            ['id' => 204, 'name' => 'Hải Phòng'],
            ['id' => 205, 'name' => 'Cần Thơ'],
            ['id' => 206, 'name' => 'Bình Dương'],
            ['id' => 207, 'name' => 'Đồng Nai'],
            ['id' => 208, 'name' => 'Bà Rịa - Vũng Tàu'],
            ['id' => 209, 'name' => 'Long An'],
            ['id' => 210, 'name' => 'Tiền Giang'],
            ['id' => 211, 'name' => 'Lâm Đồng'],
            ['id' => 212, 'name' => 'Khánh Hòa'],
            ['id' => 213, 'name' => 'Bình Thuận'],
            ['id' => 214, 'name' => 'Quảng Nam'],
            ['id' => 215, 'name' => 'Thừa Thiên Huế'],
            ['id' => 216, 'name' => 'Quảng Ninh'],
            ['id' => 217, 'name' => 'Bắc Ninh'],
            ['id' => 218, 'name' => 'Thanh Hóa'],
            ['id' => 219, 'name' => 'Nghệ An'],
            ['id' => 220, 'name' => 'Kiên Giang'],
        ];
    }

    /**
     * Fallback danh sách quận/huyện phổ biến khi GHN API offline
     */
    protected function getFallbackDistricts(int $provinceId): array
    {
        // 202: TP. Hồ Chí Minh
        if ($provinceId === 202) {
            return [
                ['id' => 1442, 'name' => 'Quận 1'],
                ['id' => 1443, 'name' => 'Quận 3'],
                ['id' => 1444, 'name' => 'Quận 4'],
                ['id' => 1445, 'name' => 'Quận 5'],
                ['id' => 1446, 'name' => 'Quận 6'],
                ['id' => 1447, 'name' => 'Quận 7'],
                ['id' => 1448, 'name' => 'Quận 8'],
                ['id' => 1449, 'name' => 'Quận 10'],
                ['id' => 1450, 'name' => 'Quận 11'],
                ['id' => 1451, 'name' => 'Quận 12'],
                ['id' => 1452, 'name' => 'Quận Bình Thạnh'],
                ['id' => 1453, 'name' => 'Quận Gò Vấp'],
                ['id' => 1454, 'name' => 'Quận Phú Nhuận'],
                ['id' => 1455, 'name' => 'Quận Tân Bình'],
                ['id' => 1456, 'name' => 'Quận Tân Phú'],
                ['id' => 1457, 'name' => 'Quận Bình Tân'],
                ['id' => 3695, 'name' => 'Thành Phố Thủ Đức'],
                ['id' => 1533, 'name' => 'Huyện Bình Chánh'],
                ['id' => 1534, 'name' => 'Huyện Nhà Bè'],
                ['id' => 1535, 'name' => 'Huyện Hóc Môn'],
                ['id' => 1536, 'name' => 'Huyện Củ Chi'],
                ['id' => 2090, 'name' => 'Huyện Cần Giờ'],
            ];
        }

        // 201: Hà Nội
        if ($provinceId === 201) {
            return [
                ['id' => 1482, 'name' => 'Quận Ba Đình'],
                ['id' => 1483, 'name' => 'Quận Hoàn Kiếm'],
                ['id' => 1484, 'name' => 'Quận Tây Hồ'],
                ['id' => 1485, 'name' => 'Quận Long Biên'],
                ['id' => 1486, 'name' => 'Quận Cầu Giấy'],
                ['id' => 1487, 'name' => 'Quận Đống Đa'],
                ['id' => 1488, 'name' => 'Quận Hai Bà Trưng'],
                ['id' => 1489, 'name' => 'Quận Hoàng Mai'],
                ['id' => 1490, 'name' => 'Quận Thanh Xuân'],
                ['id' => 1491, 'name' => 'Quận Nam Từ Liêm'],
                ['id' => 1492, 'name' => 'Quận Bắc Từ Liêm'],
                ['id' => 1493, 'name' => 'Quận Hà Đông'],
                ['id' => 1538, 'name' => 'Thị Xã Sơn Tây'],
                ['id' => 1539, 'name' => 'Huyện Ba Vì'],
                ['id' => 1540, 'name' => 'Huyện Đông Anh'],
                ['id' => 1541, 'name' => 'Huyện Gia Lâm'],
                ['id' => 1542, 'name' => 'Huyện Sóc Sơn'],
                ['id' => 1543, 'name' => 'Huyện Thanh Trì'],
            ];
        }

        // 203: Đà Nẵng
        if ($provinceId === 203) {
            return [
                ['id' => 1526, 'name' => 'Quận Hải Châu'],
                ['id' => 1527, 'name' => 'Quận Thanh Khê'],
                ['id' => 1528, 'name' => 'Quận Sơn Trà'],
                ['id' => 1529, 'name' => 'Quận Ngũ Hành Sơn'],
                ['id' => 1530, 'name' => 'Quận Liên Chiểu'],
                ['id' => 1531, 'name' => 'Quận Cẩm Lệ'],
                ['id' => 1532, 'name' => 'Huyện Hòa Vang'],
            ];
        }

        return [];
    }

    /**
     * Fallback danh sách phường/xã khi GHN API offline
     */
    protected function getFallbackWards(int $districtId): array
    {
        return [
            ['code' => '90001', 'name' => 'Phường 1'],
            ['code' => '90002', 'name' => 'Phường 2'],
            ['code' => '90003', 'name' => 'Phường 3'],
            ['code' => '90004', 'name' => 'Phường 4'],
            ['code' => '90005', 'name' => 'Phường 5'],
            ['code' => '90006', 'name' => 'Phường Bến Nghé'],
            ['code' => '90007', 'name' => 'Phường Bến Thành'],
            ['code' => '90008', 'name' => 'Phường Tân Định'],
            ['code' => '90009', 'name' => 'Phường Đa Kao'],
            ['code' => '90010', 'name' => 'Xã Trung Tâm'],
            ['code' => '90011', 'name' => 'Thị Trấn'],
        ];
    }
}
