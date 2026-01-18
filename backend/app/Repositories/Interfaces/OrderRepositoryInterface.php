<?php
namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface
{
    // CRUD cơ bản
    public function getAll();
    public function findById(string $id);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function forceDelete(string $id);
    public function restore(string $id);
    
    // Tìm kiếm & lọc
    public function getOrdersByUserId(string $userId);
    public function getOrderByEmail(string $email);
    public function getOrderByOrderNumber(string $orderNumber);
    public function getOrdersByStatus(string $status);
    public function getOrdersByDateRange($startDate, $endDate);
    public function searchOrders(array $filters);
    
    // Phân trang
    public function paginate(int $perPage = 15);
    public function getOrdersByUserIdPaginated(string $userId, int $perPage = 15);
    
    // Cập nhật trạng thái
    public function updateStatus(string $id, string $status);
    public function updatePaymentStatus(string $id, string $paymentStatus);
    
    // Lấy với quan hệ
    public function getOrderWithItems(string $id);
    public function getOrderWithUser(string $id);
    
    // Thống kê
    public function getTotalRevenue(array $filters = []);
    public function countOrdersByStatus(string $status);
    public function getRecentOrders(int $limit = 10);
    public function getTrashed();
}