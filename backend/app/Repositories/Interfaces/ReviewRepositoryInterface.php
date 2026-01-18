<?php

namespace App\Repositories\Interfaces;

interface ReviewRepositoryInterface
{
    public function create(array $data);
    public function findReviewsByIdProduct ($idProduct);
    public function findReviewByOrderId ($orderId);
}