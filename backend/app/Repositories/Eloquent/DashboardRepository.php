<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use App\Models\Category;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    /* ===== CARD ===== */

    public function revenueCardData(int $year): array
    {
        $monthly = Order::selectRaw('
            MONTH(created_at) as month,
            SUM(total) as revenue,
            COUNT(*) as orders
        ')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $revenues = [];
        $orders = [];

        foreach ($monthly as $row) {
            $labels[] = str_pad($row->month, 2, '0', STR_PAD_LEFT) . "/$year";
            $revenues[] = (int) $row->revenue;
            $orders[] = (int) $row->orders;
        }

        $currentMonthRevenue = $monthly->last()->revenue ?? 0;
        $previousMonthRevenue = $monthly->count() > 1
            ? $monthly[$monthly->count() - 2]->revenue
            : 0;

        $growth = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100)
            : 0;

        return [
            'total' => (int) $monthly->sum('revenue'),
            'growth' => $growth,
            'chart' => [
                'labels' => $labels,
                'revenue' => $revenues,
                'orders' => $orders,
            ]
        ];
    }

    public function orderCardData(int $year): array
    {
        $monthly = Order::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as total
        ')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        foreach ($monthly as $row) {
            $labels[] = str_pad($row->month, 2, '0', STR_PAD_LEFT) . "/$year";
            $data[] = (int) $row->total;
        }

        $current = $monthly->last()->total ?? 0;
        $previous = $monthly->count() > 1
            ? $monthly[$monthly->count() - 2]->total
            : 0;

        $growth = $previous > 0
            ? round((($current - $previous) / $previous) * 100)
            : 0;

        return [
            'total' => (int) $monthly->sum('total'),
            'growth' => $growth,
            'chart' => [
                'labels' => $labels,
                'data' => $data,
            ]
        ];
    }

    public function userCardData(int $year): array
    {
        $monthly = User::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as total
        ')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        foreach ($monthly as $row) {
            $labels[] = str_pad($row->month, 2, '0', STR_PAD_LEFT) . "/$year";
            $data[] = (int) $row->total;
        }

        $current = $monthly->last()->total ?? 0;
        $previous = $monthly->count() > 1
            ? $monthly[$monthly->count() - 2]->total
            : 0;

        $growth = $previous > 0
            ? round((($current - $previous) / $previous) * 100)
            : 0;

        return [
            'total' => (int) $monthly->sum('total'),
            'growth' => $growth,
            'chart' => [
                'labels' => $labels,
                'data' => $data,
            ]
        ];
    }

    public function reviewCardData(int $year): array
    {
        $monthly = Review::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as total
        ')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        foreach ($monthly as $row) {
            $labels[] = str_pad($row->month, 2, '0', STR_PAD_LEFT) . "/$year";
            $data[] = (int) $row->total;
        }

        $current = $monthly->last()->total ?? 0;
        $previous = $monthly->count() > 1
            ? $monthly[$monthly->count() - 2]->total
            : 0;

        $growth = $previous > 0
            ? round((($current - $previous) / $previous) * 100)
            : 0;

        return [
            'total' => (int) $monthly->sum('total'),
            'growth' => $growth,
            'chart' => [
                'labels' => $labels,
                'data' => $data,
            ]
        ];
    }

    public function totalRevenue(): int
    {
        return Order::sum('total');
    }

    public function totalOrders(): int
    {
        return Order::count();
    }

    public function totalUsers(): int
    {
        return User::count();
    }

    public function totalReviews(): int
    {
        return Review::count();
    }

    /* ===== CHART ===== */

    public function revenueByMonth(int $year): array
    {
        return Order::selectRaw('MONTH(created_at) as month, SUM(total) as revenue')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'month' => $row->month,
                'revenue' => $row->revenue,
            ])
            ->toArray();
    }

    public function salesByCategory(int $year): array
    {
        return Category::join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereYear('orders.created_at', $year) // lọc theo năm
            ->selectRaw('categories.name as category, SUM(order_items.quantity) as total')
            ->groupBy('categories.name')
            ->get()
            ->toArray();
    }


    /* ===== TABLE ===== */

    public function topSellingProducts(int $limit = 5, int $year): array
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereYear('orders.created_at', $year) // lọc theo năm
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }


    public function topWishlistProducts(int $limit = 5): array
    {
        return Product::join('wishlist_items', 'products.id', '=', 'wishlist_items.product_id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('COUNT(wishlist_items.id) as wishlist_count')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('wishlist_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function customers(int $limit = 10, int $year): array
    {
        return DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->select(
                'users.id',
                'users.full_name',
                'users.email',
                'users.avatar',
                'users.created_at',
                DB::raw('COALESCE(SUM(orders.total), 0) as spent')
            )
            ->whereNull('users.deleted_at')
            ->whereYear('orders.created_at', $year)
            ->groupBy(
                'users.id',
                'users.full_name',
                'users.email',
                'users.avatar',
                'users.created_at'
            )
            ->orderByDesc('spent')
            ->limit($limit)
            ->get()
            ->toArray();
    }



    public function recentActivities(int $limit = 10): array
    {
        // --- Order activities ---
        $orderActivities = Order::with('user')
            ->select('id', 'order_code', 'user_id', 'total', 'created_at')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($o) => [
                'type' => 'ORDER',
                'description' => "Order {$o->order_code}",
                'user' => $o->user?->full_name,
                'amount' => $o->total,
                'created_at' => $o->created_at,
            ])
            ->toArray();

        // --- Wishlist activities ---
        $wishlistActivities = WishlistItem::with(['wishlist.user', 'product'])
            ->select('id', 'wishlist_id', 'product_id', 'added_at')
            ->orderByDesc('added_at') // 🔥 dùng added_at thay vì created_at
            ->limit($limit)
            ->get()
            ->map(fn($w) => [
                'type' => 'WISHLIST',
                'description' => "Added {$w->product?->name} to wishlist",
                'user' => $w->wishlist?->user?->full_name,
                'amount' => null,
                'created_at' => $w->added_at, // dùng added_at
            ])
            ->toArray();


        // --- Review activities ---
        $reviewActivities = Review::with('user', 'product')
            ->select('id', 'user_id', 'product_id', 'rating', 'created_at')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($r) => [
                'type' => 'REVIEW',
                'description' => "Reviewed {$r->product?->name} - {$r->rating}★",
                'user' => $r->user?->full_name,
                'amount' => null,
                'created_at' => $r->created_at,
            ])
            ->toArray();

        // --- Payment activities ---
        $paymentActivities = Payment::with('order.user')
            ->select('id', 'order_id', 'transaction_no', 'pay_date', 'created_at')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'type' => 'PAYMENT',
                'description' => "Payment {$p->transaction_no} for order {$p->order?->order_code}",
                'user' => $p->order?->user?->full_name,
                'amount' => $p->order?->total,
                'created_at' => $p->pay_date ?? $p->created_at,
            ])
            ->toArray();

        // --- Merge all activities ---
        $allActivities = array_merge($orderActivities, $wishlistActivities, $reviewActivities, $paymentActivities);

        // --- Sắp xếp giảm dần theo created_at ---
        usort($allActivities, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

        return array_slice($allActivities, 0, $limit);
    }


}
