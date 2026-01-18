<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Services\CloudinaryService;
use App\Services\Interfaces\ReviewServiceInterface;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    protected $service;
    protected $cloudinary;

    public function __construct(ReviewServiceInterface $service, CloudinaryService $cloudinary)
    {
        $this->service = $service;
        $this->cloudinary = $cloudinary;
    }

    public function getProductByIdProduct($productId)
    {
        $result = $this->service->getReviewByIdProduct($productId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews yet',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($result["data"]),
        ], 200);
    }

    public function getReviewByOrderId (string $orderId)
    {
        $result = $this->service->getReviewByOrderId(orderId: $orderId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews found for this order',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($result["data"]),
        ], 200);
    }

    public function createReview(CreateReviewRequest $request)
    {
        try {
            Log::info("▶ Create Review Request Received", [
                'input' => $request->except('images'),
                'files' => $request->allFiles()
            ]);

            $data = $request->validated();
            $uploadedImages = [];

            // ✅ Lấy file images
            $files = $request->file('images');

            if ($files) {
                // ✅ Đảm bảo luôn là array
                $files = is_array($files) ? $files : [$files];

                foreach ($files as $index => $image) {
                    if ($image && $image->isValid()) {
                        try {
                            $uploadedUrl = $this->cloudinary->uploadImage($image, 'reviews');
                            $uploadedImages[] = $uploadedUrl;

                            Log::info("📤 Image uploaded to Cloudinary", [
                                'index' => $index,
                                'original_name' => $image->getClientOriginalName(),
                                'cloudinary_url' => $uploadedUrl
                            ]);
                        } catch (\Exception $cloudErr) {
                            Log::error("❌ Cloudinary upload failed", [
                                'error' => $cloudErr->getMessage(),
                                'file' => $image->getClientOriginalName(),
                                'stack' => $cloudErr->getTraceAsString()
                            ]);
                        }
                    }
                }
            }

            // ✅ Gán images vào data (là array, KHÔNG json_encode)
            $data['images'] = $uploadedImages;

            // ✅ Log để check data trước khi lưu
            Log::info("📦 Data before saving to DB", [
                'images' => $data['images'],
                'images_type' => gettype($data['images']),
                'images_count' => count($data['images'])
            ]);

            // ⚠️ KIỂM TRA SERVICE - đây là nơi có thể bị json_encode
            $result = $this->service->createReview($data);

            Log::info("✅ Review created successfully", [
                'review_id' => $result['data']->_id ?? $result['data']->id ?? null,
                'images_saved' => $result['data']->images ?? null
            ]);

            return response()->json([
                'success' => true,
                'data' => new ReviewResource($result['data']),
            ], 201); // ← Thêm status code 201

        } catch (\Exception $e) {
            Log::error("❌ Create review error: " . $e->getMessage(), [
                'input' => $request->except('images'),
                'files' => $request->allFiles(),
                'stack' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }


}