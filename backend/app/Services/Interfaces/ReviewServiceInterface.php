<?php
namespace App\Services\Interfaces;

interface ReviewServiceInterface
{
    public function createReview(array $data): array;
    public function getReviewByIdProduct($idProduct): array;
    public function getReviewByOrderId ($orderId): array;

}