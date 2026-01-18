<?php

namespace App\Repositories\Eloquent;

use App\Models\Discount;
use App\Repositories\Interfaces\DiscountRepositoryInterface;

class DiscountRepository implements DiscountRepositoryInterface
{
    public function create(array $data)
    {
        return Discount::create($data);
    }
    public function findByCode($code)
    {
        return Discount::where('code', strtoupper($code))->first();
    }
}