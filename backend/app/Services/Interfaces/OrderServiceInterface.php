<?php
namespace App\Services\Interfaces;

interface OrderServiceInterface
{
    public function getAllOrders();
    public function getOrderById(string $id);
    public function createOrder(array $data);
    public function updateOrder(string $id, array $data);
    public function deleteOrder(string $id);
    public function restoreOrder(string $id);
    public function getUserOrders(string $userId);
    public function getOrderByCode(string $orderCode);
    public function searchOrders(array $filters);
    public function getPaginatedOrders(int $perPage = 15);
    public function updateOrderStatus(string $id, array $data);
    public function updatePaymentStatus(string $id, string $paymentStatus);
    public function getOrderStatistics(array $filters = []);
    public function generateOrderCode();
    public function calculateOrderTotal(array $items, array $options = []);
    public function cancelOrder(string $id, string $reason = null);
    public function completeOrder(string $id);
    public function getOrderByEmail(string $email);
}