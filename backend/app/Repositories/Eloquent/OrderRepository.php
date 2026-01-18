<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function getAll()
    {
        return Order::latest()->get();
    }

    public function findById(string $id)
    {
        return Order::find($id);
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function update(string $id, array $data)
    {
        $order = Order::find($id);
        if ($order) {
            $order->update($data);
            return $order->fresh();
        }
        return null;
    }

    public function delete(string $id)
    {
        $order = Order::find($id);
        return $order ? $order->delete() : false;
    }

    public function forceDelete(string $id)
    {
        $order = Order::withTrashed()->find($id);
        return $order ? $order->forceDelete() : false;
    }

    public function restore(string $id)
    {
        $order = Order::withTrashed()->find($id);
        return $order ? $order->restore() : false;
    }

    public function getOrdersByUserId(string $userId)
    {
        return Order::where('user_id', $userId)->latest()->get();
    }

    public function getOrderByOrderNumber(string $orderNumber)
    {
        return Order::where('order_code', $orderNumber)->first();
    }

    public function getOrdersByStatus(string $status)
    {
        return Order::where('order_status', $status)->latest()->get();
    }

    public function getOrdersByDateRange($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();
    }

    public function searchOrders(array $filters)
    {
        $query = Order::query();

        if (isset($filters['order_code'])) {
            $query->where('order_code', 'like', '%' . $filters['order_code'] . '%');
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Đã sửa: từ customer_info->email sang email
        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        // Đã sửa: từ customer_info->phone sang phone
        if (isset($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['min_total'])) {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (isset($filters['max_total'])) {
            $query->where('total', '<=', $filters['max_total']);
        }

        return $query->latest()->get();
    }

    public function paginate(int $perPage = 15)
    {
        return Order::latest()->paginate($perPage);
    }

    public function getOrdersByUserIdPaginated(string $userId, int $perPage = 15)
    {
        return Order::where('user_id', $userId)->latest()->paginate($perPage);
    }

    public function updateStatus(string $id, string $status)
    {
        $order = Order::find($id);
        if ($order) {
            $order->order_status = $status;
            $order->save();
            return $order;
        }
        return null;
    }

    public function updatePaymentStatus(string $id, string $paymentStatus)
    {
        $order = Order::find($id);
        if ($order) {
            $order->payment_status = $paymentStatus;
            $order->save();
            return $order;
        }
        return null;
    }

    public function getOrderWithItems(string $id)
    {
        return Order::with('items')->find($id);
    }

    public function getOrderWithUser(string $id)
    {
        return Order::with('user')->find($id);
    }

    public function getTotalRevenue(array $filters = [])
    {
        $query = Order::where('payment_status', 'paid');

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        return $query->sum('total');
    }

    public function countOrdersByStatus(string $status)
    {
        return Order::where('order_status', $status)->count();
    }

    public function getRecentOrders(int $limit = 10)
    {
        return Order::latest()->limit($limit)->get();
    }

    public function getTrashed()
    {
        return Order::onlyTrashed()->latest()->get();
    }

    public function getOrderByEmail(string $email)
    {
        return Order::where('email', $email)

            ->with(['items', 'payment']) // Eager load relationships
            ->orderBy('created_at', 'desc')
            ->get();
    }
}



