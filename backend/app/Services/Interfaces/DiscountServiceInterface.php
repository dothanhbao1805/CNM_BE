<?php
namespace App\Services\Interfaces;

use App\Models\Discount;

interface DiscountServiceInterface
{
    public function createDiscount(array $data): array;
    public function getDiscountByCode(string $code): ?Discount;

}