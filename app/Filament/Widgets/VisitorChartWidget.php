<?php

namespace App\Filament\Widgets;

use App\Models\DailyVisit;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class VisitorChartWidget extends ChartWidget
{
    use HasWidgetShield;
    protected ?string $heading = 'Lưu lượng Truy cập';
    protected static ?int $sort = 3;
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

        $labels = [];
        $pageViewsData = [];
        $uniqueVisitorsData = [];

        if ($activeFilter === 'day') {
            $visits = DailyVisit::where('visit_date', '>=', Carbon::now()->subDays(7))
                ->orderBy('visit_date', 'asc')
                ->get();
            
            // Generate full 7 days array to ensure no gaps
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d/m');
                
                $visit = $visits->firstWhere('visit_date', $date->toDateString());
                $pageViewsData[] = $visit ? $visit->page_views : 0;
                $uniqueVisitorsData[] = $visit ? $visit->unique_visitors : 0;
            }

        } elseif ($activeFilter === 'week') {
            for ($i = 7; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                $labels[] = 'T' . $startOfWeek->format('W');

                $visits = DailyVisit::whereBetween('visit_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])->get();
                $pageViewsData[] = $visits->sum('page_views');
                $uniqueVisitorsData[] = $visits->sum('unique_visitors');
            }
        } elseif ($activeFilter === 'month') {
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = 'Tháng ' . $month->format('m/y');

                $visits = DailyVisit::whereYear('visit_date', $month->year)
                    ->whereMonth('visit_date', $month->month)
                    ->get();

                $pageViewsData[] = $visits->sum('page_views');
                $uniqueVisitorsData[] = $visits->sum('unique_visitors');
            }
        } elseif ($activeFilter === 'year') {
            for ($i = 4; $i >= 0; $i--) {
                $year = Carbon::now()->subYears($i);
                $labels[] = $year->format('Y');

                $visits = DailyVisit::whereYear('visit_date', $year->year)->get();
                $pageViewsData[] = $visits->sum('page_views');
                $uniqueVisitorsData[] = $visits->sum('unique_visitors');
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Lượt Xem (Page Views)',
                    'data' => $pageViewsData,
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Khách Mới (Unique IP)',
                    'data' => $uniqueVisitorsData,
                    'borderColor' => '#10b981', // emerald-500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
