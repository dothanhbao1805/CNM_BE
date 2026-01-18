<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function getAll(int $page = 1, int $perPage = 9);
    public function findById(int $id);
    public function findBySlug(string $slug);
    public function findByCategorySlug(string $categorySlug, int $page, int $perPage = 15);
    public function findByCategoryName(string $categoryName, int $perPage = 15);
    public function findByParentCategory(string $parentName, int $perPage = 15);
    public function filter(array $filters,int $current, int $perPage = 15);
    public function search(string $keyword, int $page = 1, int $perPage = 12);
    public function getFeatured(int $limit = 10);
    public function getNew(int $limit = 10);
    public function getBestseller(int $limit = 10);
    public function getAllCategories();
    public function getAllDressStyles();
    public function getAllBrands();
    public function getRelatedProducts(int $productId, int $categoryId, int $limit = 6);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function forceDelete(int $id);
    public function updateStock(int $id, int $quantity);
    public function decreaseStock(int $id, int $quantity);
    public function increaseStock(int $id, int $quantity);

    /**
     * Kiểm tra sản phẩm có còn hàng không
     */
    public function isInStock(int $id, int $quantity = 1);

    /**
     * Lấy sản phẩm theo khoảng giá
     */
    public function getByPriceRange(int $minPrice, int $maxPrice, int $perPage = 15);
}