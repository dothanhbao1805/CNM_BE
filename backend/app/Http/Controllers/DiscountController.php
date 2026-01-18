<?php

namespace App\Http\Controllers;

use App\Http\Requests\Discount\CreateDiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Services\Interfaces\DiscountServiceInterface;

class DiscountController extends Controller
{
    protected $discountService;
    public function __construct(DiscountServiceInterface $discountService)
    {
        $this->discountService = $discountService;
    }
    public function store(CreateDiscountRequest $request)
    {
        $result = $this->discountService->createDiscount($request->validated());
        
        $status = $result['success'] ? 201 : 400;
        return response()->json($result, $status);
    }

    public function findByCode($code)
    {
        $discount = $this->discountService->getDiscountByCode($code);
        return new DiscountResource($discount);
    }

}