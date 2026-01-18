<?php

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function create(array $data)
    {
        return Review::create($data);
    }

    public function findReviewsByIdProduct($idProduct)
    {
        return Review::with('user')
            ->where("product_id", $idProduct)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function findReviewByOrderId($orderId)
    {
        return Review::with('user')
            ->where("order_id", $orderId)
            ->first();
    }
}