<?php

namespace App\Repositories\Interfaces;

interface DashboardRepositoryInterface
{
    /* ===== CARD ===== */
    public function revenueCardData(int $year): array;
    public function orderCardData(int $year): array;
    public function userCardData(int $year): array;
    public function reviewCardData(int $year): array;
    public function totalRevenue(): int;
    public function totalOrders(): int;
    public function totalUsers(): int;
    public function totalReviews(): int;

    /* ===== CHART ===== */
    public function revenueByMonth(int $year): array;
    public function salesByCategory(int $year): array;

    /* ===== TABLE ===== */
    public function topSellingProducts(int $limit = 5, int $year): array;
    public function topWishlistProducts(int $limit = 5): array;
    public function customers(int $limit = 10, int $year): array;
    public function recentActivities(int $limit = 10): array;
}
