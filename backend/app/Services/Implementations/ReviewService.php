<?php
namespace App\Services\Implementations;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\Interfaces\ReviewServiceInterface;
use Illuminate\Support\Facades\Log;

class ReviewService implements ReviewServiceInterface
{

    protected $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function getReviewByOrderId ($orderId): array
    {
        $review = $this->reviewRepository->findReviewByOrderId($orderId);

        if (!$review) {
            return [
                "success" => false,
                "message" => "No review found for this order",
                "data" => null
            ];
        }

        return [
            "success" => true,
            "message" => "Review retrieved successfully",
            "data" => $review
        ];
    }
    
    public function getReviewByIdProduct($idProduct): array    
    {   
        $reviews = $this->reviewRepository->findReviewsByIdProduct($idProduct);

        if (!$reviews || $reviews->isEmpty()) {
            return [
                "success" => false,
                "message" => "There are no reviews for this product yet!",
                "data" => []
            ];
        }

        return [
            "success" => true,
            "message" => "Get the list of successful reviews!",
            "data" => $reviews
        ];
    }

    public function createReview(array $data): array
    {
        
        try {
            $review = $this->reviewRepository->create($data);

            return [
                'success' => true,
                'message' => 'Rated successful',
                'data' => $review
            ];
        } catch (\Exception $e) {
            Log::error('Error creating review', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Could not create review. Please try again later.'
            ];
        }
    }
}