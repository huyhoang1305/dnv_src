<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StatsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

        if ($totalRevenue >= 1000000000) {
            $formattedRevenue = rtrim(rtrim(number_format($totalRevenue / 1000000000, 2, ',', '.'), '0'), ',') . ' Tỷ';
        } elseif ($totalRevenue >= 1000000) {
            $formattedRevenue = rtrim(rtrim(number_format($totalRevenue / 1000000, 2, ',', '.'), '0'), ',') . ' Triệu';
        } else {
            $formattedRevenue = number_format((float) $totalRevenue, 0, ',', '.');
        }

        return [
            Stat::make('Tổng Doanh Thu', $formattedRevenue . ' ₫')
                ->description('Doanh thu từ các đơn hàng')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Đơn Hàng Mới', Order::count())
                ->description('Tổng số đơn đặt trên hệ thống')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
                
            Stat::make('Sản Phẩm', Product::count())
                ->description('Sản phẩm đang được bày bán')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
                
            Stat::make('Khách Hàng', User::count())
                ->description('Tài khoản mạng lưới')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
