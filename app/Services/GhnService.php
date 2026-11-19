<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhnService
{
    protected string $baseUrl = 'https://tinhthanhpho.com/api/v1';

    protected static ?array $cachedLocalDataset = null;

    /**
     * Lấy danh sách 34 Tỉnh / Thành phố mới sau sáp nhập từ API https://tinhthanhpho.com/api/v1/new-provinces
     */
    public function getProvinces(): array
    {
        $cacheKey = 'ttp_api_v1_new_provinces';
        $cached = Cache::get($cacheKey);
        if (!empty($cached) && is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['User-Agent' => 'DNV-Ecommerce/1.0'])
                ->timeout(8)
                ->get($this->baseUrl . '/new-provinces', [
                    'limit' => 100,
                ]);

            if ($response->successful() && $response->json('success') === true) {
                $data = $response->json('data') ?? [];
                if (!empty($data) && is_array($data)) {
                    $provinces = [];
                    foreach ($data as $item) {
                        $code = (string) ($item['code'] ?? '');
                        $name = trim((string) ($item['name'] ?? ''));
                        $type = trim((string) ($item['type'] ?? ''));

                        if ($code === '' || $name === '') {
                            continue;
                        }

                        $fullName = ($type !== '' && !str_starts_with(mb_strtolower($name), mb_strtolower($type)))
                            ? ($type . ' ' . $name)
                            : $name;

                        $provinces[] = [
                            'id' => $code,
                            'name' => $fullName,
                        ];
                    }

                    if (!empty($provinces)) {
                        // Ưu tiên 6 Thành phố trực thuộc Trung ương lên đầu
                        $priorityCodes = ['79' => 1, '01' => 2, '48' => 3, '31' => 4, '92' => 5, '46' => 6];
                        usort($provinces, function ($a, $b) use ($priorityCodes) {
                            $pA = $priorityCodes[$a['id']] ?? 99;
                            $pB = $priorityCodes[$b['id']] ?? 99;
                            if ($pA !== $pB) {
                                return $pA <=> $pB;
                            }
                            return strcmp($a['name'], $b['name']);
                        });

                        Cache::put($cacheKey, $provinces, now()->addHours(24));
                        return $provinces;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('tinhthanhpho.com getProvinces error: ' . $e->getMessage());
        }

        // Fallback dự phòng khi mất kết nối mạng
        $fallback = [];
        foreach ($this->loadLocalDataset() as $p) {
            $fallback[] = [
                'id' => (string) $p['id'],
                'name' => (string) $p['name'],
            ];
        }

        return $fallback;
    }

    /**
     * Lấy danh sách Phường / Xã / Đặc khu theo mã Tỉnh / Thành phố mới từ API https://tinhthanhpho.com/api/v1/new-provinces/{code}/wards
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

        $code = $aliasMap[strtolower(trim($provinceId))] ?? trim($provinceId);
        if ($code === '') {
            return [];
        }

        $cacheKey = "ttp_api_v1_new_wards_{$code}";
        $cached = Cache::get($cacheKey);
        if (!empty($cached) && is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['User-Agent' => 'DNV-Ecommerce/1.0'])
                ->timeout(8)
                ->get("{$this->baseUrl}/new-provinces/{$code}/wards", [
                    'limit' => 500,
                ]);

            if ($response->successful() && $response->json('success') === true) {
                $data = $response->json('data') ?? [];
                if (!empty($data) && is_array($data)) {
                    $wards = [];
                    foreach ($data as $item) {
                        $wCode = (string) ($item['code'] ?? '');
                        $wName = trim((string) ($item['name'] ?? ''));
                        $wType = trim((string) ($item['type'] ?? ''));

                        if ($wCode === '' || $wName === '') {
                            continue;
                        }

                        $fullName = ($wType !== '' && !str_starts_with(mb_strtolower($wName), mb_strtolower($wType)))
                            ? ($wType . ' ' . $wName)
                            : $wName;

                        $wards[] = [
                            'code' => $wCode,
                            'name' => $fullName,
                        ];
                    }

                    if (!empty($wards)) {
                        Cache::put($cacheKey, $wards, now()->addHours(24));
                        return $wards;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("tinhthanhpho.com getWardsByProvince({$code}) error: " . $e->getMessage());
        }

        // Fallback dự phòng khi mất kết nối mạng
        foreach ($this->loadLocalDataset() as $p) {
            if ((string) $p['id'] === (string) $code) {
                return $p['wards'] ?? [];
            }
        }

        return [];
    }

    /**
     * Phí vận chuyển cố định 25.000đ
     */
    public function calculateShippingFee(int $toDistrictId = 0, string $toWardCode = '', int $weightInGrams = 500, int $insuranceValue = 0): int
    {
        return 25000;
    }

    protected function loadLocalDataset(): array
    {
        if (self::$cachedLocalDataset !== null) {
            return self::$cachedLocalDataset;
        }

        $file = database_path('data/vn_addresses_2025.json');
        if (file_exists($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded)) {
                self::$cachedLocalDataset = $decoded;
                return self::$cachedLocalDataset;
            }
        }

        self::$cachedLocalDataset = [];
        return self::$cachedLocalDataset;
    }
}
