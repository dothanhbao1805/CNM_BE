<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\OrderServiceInterface;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\UpdatePaymentStatusRequest;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\SearchOrderRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $orders = $this->orderService->getPaginatedOrders($perPage);

            return response()->json([
                'success' => true,
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created order
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $data = $request->validated();
            $user = auth('api')->user(); 
            
            $data['user_id'] = $user->id;
            
            $order = $this->orderService->createOrder($data);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(string $id)
    {
        try {
            $order = $this->orderService->getOrderById($id);

            return response()->json([
                'success' => true,
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, string $id)
    {
        try {
            $order = $this->orderService->updateOrder($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(string $id)
    {
        try {
            $this->orderService->deleteOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search orders with filters
     */
    public function search(SearchOrderRequest $request)
    {
        try {
            $orders = $this->orderService->searchOrders($request->validated());

            return response()->json([
                'success' => true,
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders by user
     */
    public function getUserOrders(Request $request, string $userId)
    {
        try {
            $orders = $this->orderService->getUserOrders($userId);

            return response()->json([
                'success' => true,
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order by order code
     */
    public function getByOrderCode(string $orderCode)
    {
        try {
            $order = $this->orderService->getOrderByCode($orderCode);

            return response()->json([
                'success' => true,
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(UpdateOrderStatusRequest $request, string $id)
    {
        try {
            $order = $this->orderService->updateOrderStatus($id, [
                'order_status' => $request->order_status,
                'payment_status' => $request->payment_status,
                'note' => $request->note,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, string $id)
    {
        try {
            $order = $this->orderService->updatePaymentStatus($id, $request->payment_status);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(CancelOrderRequest $request, string $id)
    {
        try {
            $order = $this->orderService->cancelOrder($id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete order (mark as delivered)
     */
    public function complete(string $id)
    {
        try {
            $order = $this->orderService->completeOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request)
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'order_status']);
            $statistics = $this->orderService->getOrderStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getOrderByEmail(Request $request)
    {
        $email = $request->query('email'); // hoặc $request->email nếu gửi body

        $result = $this->orderService->getOrderByEmail($email);

        return response()->json($result);
    }
}