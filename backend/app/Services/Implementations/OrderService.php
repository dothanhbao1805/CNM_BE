<?php

namespace App\Services\Implementations;

use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService implements OrderServiceInterface
{
    protected $orderRepository;
    protected $userRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, UserRepository $userRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllOrders()
    {
        return $this->orderRepository->getAll();
    }

    public function getOrderById(string $id)
    {
        $order = $this->orderRepository->findById($id);
        
        if (!$order) {
            throw new \Exception('Order not found');
        }
        
        return $order;
    }

    public function createOrder(array $data)
    {
        DB::beginTransaction();
        try {
            if (!isset($data['order_code'])) {
                $data['order_code'] = $this->generateOrderCode();
            }

            if (!isset($data['subtotal']) && isset($data['items'])) {
                $subtotal = 0;
                foreach ($data['items'] as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
                $data['subtotal'] = $subtotal;
            }

            if (!isset($data['total'])) {
                $discount = $data['discount'] ?? 0;
                $deliveryFee = $data['delivery_fee'] ?? 0;
                $data['total'] = $data['subtotal'] - $discount + $deliveryFee;
            }

            $data['order_status'] = $data['order_status'] ?? 'pending';
            $data['payment_status'] = $data['payment_status'] ?? 'unpaid';

            $items = $data['items'];
            unset($data['items']);

            // 5. Tạo Order
            $order = $this->orderRepository->create($data);

            // 6. Tạo OrderItems
            foreach ($items as $item) {
                $orderItemData = [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'image' => $item['image'] ?? null,
                    'total' => $item['price'] * $item['quantity'],
                ];

                $order->items()->create($orderItemData);
            }

            $order->load('items', 'user');

            DB::commit();
            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrder(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $order = $this->getOrderById($id);

            // Recalculate total if items changed
            if (isset($data['items'])) {
                $orderTotal = $this->calculateOrderTotal(
                    $data['items'],
                    [
                        'discount' => $data['discount'] ?? $order->discount ?? 0,
                        'delivery_fee' => $data['delivery_fee'] ?? $order->delivery_fee ?? 0
                    ]
                );
                $data['subtotal'] = $orderTotal['subtotal'];
                $data['total'] = $orderTotal['total'];
            }

            $updatedOrder = $this->orderRepository->update($id, $data);

            DB::commit();
            return $updatedOrder;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteOrder(string $id)
    {
        $order = $this->getOrderById($id);
        
        if (!in_array($order->order_status, ['pending', 'cancelled'])) {
            throw new \Exception('Only pending or cancelled orders can be deleted');
        }

        return $this->orderRepository->delete($id);
    }

    public function restoreOrder(string $id)
    {
        return $this->orderRepository->restore($id);
    }

    public function getUserOrders(string $userId)
    {
        return $this->orderRepository->getOrdersByUserId($userId);
    }

    public function getOrderByCode(string $orderCode)
    {
        $order = $this->orderRepository->getOrderByOrderNumber($orderCode);
        
        if (!$order) {
            throw new \Exception('Order not found');
        }
        
        return $order;
    }

    public function searchOrders(array $filters)
    {
        return $this->orderRepository->searchOrders($filters);
    }

    public function getPaginatedOrders(int $perPage = 15)
    {
        return $this->orderRepository->paginate($perPage);
    }

    public function updateOrderStatus(string $id, array $data)
    {
        $order = $this->getOrderById($id);

        if (!$order) {
            throw new \Exception('Order not found');
        }

        $newStatus = $data['order_status'];
        $currentStatus = $order->order_status;

        // 1. Danh sách trạng thái hợp lệ
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        if (!in_array($newStatus, $validStatuses)) {
            throw new \Exception('Invalid order status');
        }

        // 2. Xác định các transition cho phép
        $allowedTransitions = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [], // không thể thay đổi
            'cancelled' => []  // không thể thay đổi
        ];

        // 3. Kiểm tra transition hợp lệ
        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new \Exception("Cannot change order status from '$currentStatus' to '$newStatus'");
        }

        // 4. Update
        return $this->orderRepository->update($id, $data);
    }

    public function updatePaymentStatus(string $id, string $paymentStatus)
    {
        $order = $this->getOrderById($id);

        $validStatuses = ['unpaid', 'paid', 'refunded', 'failed'];
        if (!in_array($paymentStatus, $validStatuses)) {
            throw new \Exception('Invalid payment status');
        }

        return $this->orderRepository->updatePaymentStatus($id, $paymentStatus);
    }

    public function getOrderStatistics(array $filters = [])
    {
        return [
            'total_revenue' => $this->orderRepository->getTotalRevenue($filters),
            'pending_orders' => $this->orderRepository->countOrdersByStatus('pending'),
            'processing_orders' => $this->orderRepository->countOrdersByStatus('processing'),
            'shipped_orders' => $this->orderRepository->countOrdersByStatus('shipped'),
            'delivered_orders' => $this->orderRepository->countOrdersByStatus('delivered'),
            'cancelled_orders' => $this->orderRepository->countOrdersByStatus('cancelled'),
            'recent_orders' => $this->orderRepository->getRecentOrders(5)
        ];
    }

    public function generateOrderCode()
    {
        do {
            $code = 'ORD' . date('Ymd') . strtoupper(Str::random(6));
            $exists = $this->orderRepository->getOrderByOrderNumber($code);
        } while ($exists);

        return $code;
    }

    public function calculateOrderTotal(array $items, array $options = [])
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $subtotal += $price * $quantity;
        }

        $discount = $options['discount'] ?? 0;
        $deliveryFee = $options['delivery_fee'] ?? 0;

        $total = $subtotal - $discount + $deliveryFee;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total' => max(0, $total) // Ensure total is not negative
        ];
    }

    public function cancelOrder(string $id, string $reason = null)
    {
        DB::beginTransaction();
        try {
            $order = $this->getOrderById($id);

            // Only allow cancellation of pending or processing orders
            if (!in_array($order->order_status, ['pending', 'processing'])) {
                throw new \Exception('This order cannot be cancelled');
            }

            $updateData = [
                'order_status' => 'cancelled'
            ];

            if ($reason) {
                $updateData['cancellation_reason'] = $reason;
            }

            $cancelledOrder = $this->orderRepository->update($id, $updateData);

            // If payment was made, update payment status to refunded
            if ($order->payment_status === 'paid') {
                $this->updatePaymentStatus($id, 'refunded');
            }

            DB::commit();
            return $cancelledOrder;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function completeOrder(string $id)
    {
        $order = $this->getOrderById($id);

        if ($order->order_status !== 'shipped') {
            throw new \Exception('Only shipped orders can be marked as delivered');
        }

        return $this->orderRepository->updateStatus($id, 'delivered');
    }

    public function getOrderByEmail(string $email)
    {
        // Kiểm tra user có tồn tại không
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email này không tồn tại trong hệ thống.',
                'orders'  => []
            ];
        }

        // Lấy danh sách đơn hàng theo email
        $orders = $this->orderRepository->getOrderByEmail($email);

        return [
            'success' => true,
            'message' => 'Lấy đơn hàng thành công.',
            'orders'  => $orders
        ];
    }
}