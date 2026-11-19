<?php

namespace App\Services;

class GhnService
{
    protected static ?array $cachedDataset = null;

    /**
     * Tải dữ liệu chuẩn 34 Tỉnh / Thành phố và 3.321 Phường / Xã / Đặc khu sau sáp nhập (2025)
     */
    protected function loadDataset(): array
    {
        if (self::$cachedDataset !== null) {
            return self::$cachedDataset;
        }

        $file = database_path('data/vn_addresses_2025.json');
        if (file_exists($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded)) {
                self::$cachedDataset = $decoded;
                return self::$cachedDataset;
            }
        }

        self::$cachedDataset = [];
        return self::$cachedDataset;
    }

    /**
     * Danh sách 34 Tỉnh / Thành phố mới sau sáp nhập (Nghị quyết 202/2025/QH15)
     */
    public function getProvinces(): array
    {
        $provinces = [];
        foreach ($this->loadDataset() as $p) {
            $provinces[] = [
                'id' => (string) $p['id'],
                'name' => (string) $p['name'],
            ];
        }

        return $provinces;
    }

    /**
     * Lấy danh sách đầy đủ Phường / Xã / Đặc khu trực tiếp theo Tỉnh / Thành phố (Tổng 3.321 xã/phường/đặc khu)
     */
    public function getWardsByProvince(string $provinceId): array
    {
        $aliasMap = [
            'hcm' => '79',
            '202' => '79',
            'hn' => '01',
            '201' => '01',
            'dn' => '48',
            '203' => '48',
            'hp' => '31',
            'ct' => '92',
            'hue' => '46',
        ];

        $normalizedId = $aliasMap[strtolower(trim($provinceId))] ?? trim($provinceId);

        foreach ($this->loadDataset() as $p) {
            if ((string) $p['id'] === (string) $normalizedId) {
                return $p['wards'] ?? [];
            }
        }

        return [];
    }

    /**
     * Phí vận chuyển cố định 25.000đ (Đã bỏ tính phí qua GHN)
     */
    public function calculateShippingFee(int $toDistrictId = 0, string $toWardCode = '', int $weightInGrams = 500, int $insuranceValue = 0): int
    {
        return 25000;
    }
}
