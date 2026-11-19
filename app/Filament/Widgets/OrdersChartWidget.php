<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class OrdersChartWidget extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'Thống kê Bán hàng toàn diện';
    protected static ?int $sort = 2;
    public ?string $filter = 'day';

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Theo ngày (7 Ngày)',
            'week' => 'Theo tuần (2 Tháng)',
            'month' => 'Theo tháng (6 Tháng)',
            'year' => 'Theo năm',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        $revenueData = [];
        $orderCountData = [];
        $productsSoldCountData = [];
        $labels = [];

        if ($activeFilter === 'day') {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d/m');
                
                $q = Order::whereDate('created_at', $date->toDateString())
                    ->where(function ($sub) {
                        $sub->where('status', 'completed')->orWhere('payment_status', 'paid');
                    });
                
                $revenueData[] = (float) $q->sum('total_amount');
                $orderCountData[] = $q->count();
                $productsSoldCountData[] = (int) DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereDate('orders.created_at', $date->toDateString())
                    ->where('orders.status', '!=', 'cancelled')
                    ->sum('order_items.quantity');
            }
        } elseif ($activeFilter === 'week') {
            for ($i = 7; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                $labels[] = 'T' . $startOfWeek->format('W');

                $q = Order::whereBetween('created_at', [$startOfWeek->toDateString() . ' 00:00:00', $endOfWeek->toDateString() . ' 23:59:59'])
                    ->where(function ($sub) {
                        $sub->where('status', 'completed')->orWhere('payment_status', 'paid');
                    });

                $revenueData[] = (float) $q->sum('total_amount');
                $orderCountData[] = $q->count();
                $productsSoldCountData[] = (int) DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereBetween('orders.created_at', [$startOfWeek->toDateString() . ' 00:00:00', $endOfWeek->toDateString() . ' 23:59:59'])
                    ->where('orders.status', '!=', 'cancelled')
                    ->sum('order_items.quantity');
            }
        } elseif ($activeFilter === 'month') {
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = 'Tháng ' . $month->format('m/y');
                
                $q = Order::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where(function ($sub) {
                        $sub->where('status', 'completed')->orWhere('payment_status', 'paid');
                    });

                $revenueData[] = (float) $q->sum('total_amount'); 
                $orderCountData[] = $q->count();
                $productsSoldCountData[] = (int) DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereYear('orders.created_at', $month->year)
                    ->whereMonth('orders.created_at', $month->month)
                    ->where('orders.status', '!=', 'cancelled')
                    ->sum('order_items.quantity');
            }
        } elseif ($activeFilter === 'year') {
            for ($i = 4; $i >= 0; $i--) {
                $year = Carbon::now()->subYears($i);
                $labels[] = $year->format('Y');

                $q = Order::whereYear('created_at', $year->year)
                    ->where(function ($sub) {
                        $sub->where('status', 'completed')->orWhere('payment_status', 'paid');
                    });

                $revenueData[] = (float) $q->sum('total_amount');
                $orderCountData[] = $q->count();
                $productsSoldCountData[] = (int) DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereYear('orders.created_at', $year->year)
                    ->where('orders.status', '!=', 'cancelled')
                    ->sum('order_items.quantity');
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Doanh Thu (VNĐ)',
                    'data' => $revenueData,
                    'borderColor' => '#ef4444', 
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Số Đơn Hàng',
                    'data' => $orderCountData,
                    'borderColor' => '#eab308', 
                    'backgroundColor' => 'rgba(234, 179, 8, 0.2)',
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Sản Phẩm Bán',
                    'data' => $productsSoldCountData,
                    'borderColor' => '#8b5cf6', 
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
