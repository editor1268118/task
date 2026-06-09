<?php

namespace App\Services;

use App\Repositories\AnalyticsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportService
{
    protected $analytics;
    protected $kpi;

    public function __construct(AnalyticsRepository $analytics, KPIEngine $kpi)
    {
        $this->analytics = $analytics;
        $this->kpi = $kpi;
    }

    /**
     * Parse date range from request filter.
     */
    public function parseDateRange(Request $request): array
    {
        $startDate = null;
        $endDate = null;

        if ($request->has('date_range') && !empty($request->date_range)) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
            } else {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[0])->endOfDay();
            }
        }

        return [$startDate, $endDate];
    }

    /**
     * Generate dashboard summary chart data based on role context.
     */
    public function getDashboardChartsData(?int $departmentId = null, ?int $userId = null): array
    {
        $trends = $this->analytics->getMonthlyTrends(6, $departmentId, $userId);
        $priorities = $this->analytics->getPriorityDistribution($departmentId, $userId);

        return [
            'trends' => [
                'labels' => array_column($trends, 'month'),
                'created' => array_column($trends, 'created'),
                'completed' => array_column($trends, 'completed'),
            ],
            'priorities' => [
                'labels' => ['High', 'Medium', 'Low'],
                'data' => [$priorities['high'], $priorities['medium'], $priorities['low']],
            ]
        ];
    }
}
