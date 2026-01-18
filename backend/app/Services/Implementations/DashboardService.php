<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\DashboardServiceInterface;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class DashboardService implements DashboardServiceInterface
{
    // TTL (seconds)
    private const TTL_CARDS = 60;
    private const TTL_CHARTS = 300;
    private const TTL_TABLES = 180;

    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepo
    ) {
    }

    /**
     * Build cache key
     */
    private function cacheKey(string $type, int $year): string
    {
        return "dashboard:$type:$year";
    }

    /**
     * Dashboard cards
     */
    public function getCards(int $year = null): array
    {
        $year = $year ?? now()->year;
        $key = $this->cacheKey('cards', $year);

        return Cache::remember($key, self::TTL_CARDS, function () use ($year) {
            return [
                'revenue' => $this->dashboardRepo->revenueCardData($year),
                'orders' => $this->dashboardRepo->orderCardData($year),
                'users' => $this->dashboardRepo->userCardData($year),
                'reviews' => $this->dashboardRepo->reviewCardData($year),
            ];
        });
    }

    /**
     * Dashboard charts
     */
    public function getCharts(int $year = null): array
    {
        $year = $year ?? now()->year;
        $key = $this->cacheKey('charts', $year);

        return Cache::remember($key, self::TTL_CHARTS, function () use ($year) {
            return [
                'revenue_by_month' => $this->dashboardRepo->revenueByMonth($year),
                'sales_by_category' => $this->dashboardRepo->salesByCategory($year),
            ];
        });
    }

    /**
     * Dashboard tables
     */
    public function getTables(int $year = null): array
    {
        $year = $year ?? now()->year;
        $key = $this->cacheKey('tables', $year);

        return Cache::remember($key, self::TTL_TABLES, function () use ($year) {
            return [
                'top_selling_products' => $this->dashboardRepo->topSellingProducts(5, $year),
                'top_wishlist_products' => $this->dashboardRepo->topWishlistProducts(),
                'customers' => $this->dashboardRepo->customers(10, $year),
                'recent_activities' => $this->dashboardRepo->recentActivities(),
            ];
        });
    }

    /**
     * Clear dashboard cache (invalidate)
     */
    public function clearCache(int $year = null): void
    {
        $year = $year ?? now()->year;

        Cache::forget($this->cacheKey('cards', $year));
        Cache::forget($this->cacheKey('charts', $year));
        Cache::forget($this->cacheKey('tables', $year));
    }
}
