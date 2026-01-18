<?php
namespace App\Services\Interfaces;

use App\Models\Product;

interface ProductServiceInterface
{
    public function getAllProducts(int $page = 1, int $pageSize = 15): array;
    public function getProductsByCategory(string $categorySlug, int $page = 1, int $pageSize = 15): array;
    public function getProductDetail(string $slug): array;
    public function filterProducts(array $filters, int $page = 1, int $pageSize = 15): array;
    public function searchProducts(string $keyword, int $page = 1, int $pageSize = 15): array;
    public function getFeaturedProducts(int $limit = 10): array;
    public function getNewProducts(int $limit = 10): array;
    public function getBestsellerProducts(int $limit = 10): array;
    public function getAllCategories(): array;
    public function getAllDressStyles(): array;
    public function getAllBrands(): array;
    public function createProduct(array $data): array;
    public function updateProduct(int $id, array $data): array;
    public function deleteProduct(int $id): array;
    public function checkStock(int $productId, int $variantId, int $quantity = 1): array;
    public function getProductBySlug (string $slug);
    public function getProductById (int $id);
}