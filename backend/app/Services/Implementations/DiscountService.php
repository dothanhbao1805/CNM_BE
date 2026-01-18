<?php
namespace App\Services\Implementations;

use App\Models\Discount;
use App\Repositories\Eloquent\DiscountRepository;
use App\Services\Interfaces\DiscountServiceInterface;
use Illuminate\Support\Facades\Log;

class DiscountService implements DiscountServiceInterface
{
    protected $discountRepository;

    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    public function getDiscountByCode(string $code): ?Discount
    {
         return $this->discountRepository->findByCode($code);
    }
    
    public function createDiscount(array $data): array
    {
        try {
            $discount = $this->discountRepository->create($data);

            return [
                'success' => true,
                'message' => 'Tạo mã giảm giá thành công',
                'data' => $discount
            ];
        } catch (\Exception $e) {
            Log::error('Error creating product', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tạo mã giảm giá'
            ];
        }
    }


}