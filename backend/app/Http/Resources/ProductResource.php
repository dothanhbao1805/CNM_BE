<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Helper to get value from array or object (MongoDB vs MySQL compatibility)
        $getValue = function($data, $key, $default = null) {
            if (is_array($data)) {
                return $data[$key] ?? $default;
            }
            if (is_object($data)) {
                return $data->$key ?? $default;
            }
            return $default;
        };

        // Get category data (handle both array and object)
        $category = $this->category ?? null;
        $categoryData = [
            'name' => $category ? $getValue($category, 'name') : null,
            'slug' => $category ? $getValue($category, 'slug') : null,
        ];

        // Get brand data (handle both array and object)
        $brand = $this->brand ?? null;
        $brandData = [
            'name' => $brand ? $getValue($brand, 'name') : null,
            'slug' => $brand ? $getValue($brand, 'slug') : null,
        ];

        // Calculate review stats
        $reviewStats = $this->getReviewStats();

        return [
            'id' => (string) ($this->_id ?? $this->id ?? ''),
            'name' => $this->name ?? '',
            'slug' => $this->slug ?? '',
            'description' => $this->description ?? '',
            "category_id" => $this->category_id ?? null,
            "brand_id" => $this->brand_id ?? null,
            // Category (handle both MongoDB array and MySQL object)
            'category' => $categoryData,
            
            // Brand (handle both MongoDB array and MySQL object)
            'brand' => $brandData,
            
            // Pricing
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'formatted_price' => number_format($this->price, 0, ',', '.') . ' ₫',
            'formatted_compare_price' => $this->compare_price 
                ? number_format($this->compare_price, 0, ',', '.') . ' ₫' 
                : null,
            'discount_percentage' => $this->getDiscountPercentage(),
            'on_sale' => $this->compare_price && $this->compare_price > $this->price,
            
 
            'stock' => $this->getTotalStock(),
            'in_stock' => $this->getTotalStock() > 0,
            
            // Images (handle both collection and array)
            'images' => $this->getImagesArray(),
            'main_image' => $this->getMainImage(),
            'thumbnail' => $this->getThumbnail(),
            
            // Variants (handle both collection and array)
            'variants' => $this->getVariantsArray(),
            'dressStyle' => $this->dress_style ?? $this->dressStyle ?? null,
            'available_sizes' => $this->getAvailableSizes(),
            'available_colors' => $this->getAvailableColors(),
            

            'material' => $this->material,
            'care_instructions' => $this->care_instructions,
            
            // Status
            'is_featured' => (bool) ($this->is_featured ?? false),
            'is_active' => (bool) ($this->is_active ?? true),
            
            // Statistics
            'stats' => [
                'rating_average' => $reviewStats['rating_average'],
                'rating_count' => $reviewStats['rating_count'],
                'review_count' => $reviewStats['review_count'],
                'sold_count' => (int) ($this->sold_count ?? 0), 
                'view_count' => (int) ($this->view_count ?? 0), 
                'wishlist_count' => (int) ($this->wishlist_count ?? 0), 
            ],
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    // Helper methods
    private function getDiscountPercentage(): int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return 0;
        }
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    private function getReviewStats(): array
    {
        try {
            // Check if reviews relationship exists
            if (method_exists($this->resource, 'reviews')) {
                $avgRating = $this->reviews()->avg('rating');
                $reviewCount = $this->reviews()->count();
                
                return [
                    'rating_average' => $avgRating ? round((float) $avgRating, 2) : 0,
                    'rating_count' => (int) $reviewCount,
                    'review_count' => (int) $reviewCount,
                ];
            }
            
            // Fallback: Try to get from loaded relationship
            if (isset($this->reviews) && $this->reviews !== null) {
                $reviews = is_array($this->reviews) ? $this->reviews : $this->reviews->toArray();
                $count = count($reviews);
                
                if ($count > 0) {
                    $sum = 0;
                    foreach ($reviews as $review) {
                        $rating = is_array($review) ? ($review['rating'] ?? 0) : ($review->rating ?? 0);
                        $sum += (float) $rating;
                    }
                    $average = $sum / $count;
                    
                    return [
                        'rating_average' => round($average, 2),
                        'rating_count' => $count,
                        'review_count' => $count,
                    ];
                }
            }
        } catch (\Exception $e) {
            // If anything fails, return default values
        }

        return [
            'rating_average' => 0,
            'rating_count' => 0,
            'review_count' => 0,
        ];
    }

    private function getMainImage(): ?string
    {
        $images = $this->getImagesArray();
        if (empty($images)) {
            return null;
        }

        // Find primary image
        foreach ($images as $image) {
            $isPrimary = is_array($image) ? ($image['is_primary'] ?? 0) : ($image->is_primary ?? 0);
            if ($isPrimary == 1) {
                return is_array($image) ? ($image['url'] ?? $image['image_url'] ?? null) : ($image->url ?? $image->image_url ?? null);
            }
        }

        // Return first image if no primary
        $firstImage = $images[0] ?? null;
        if (!$firstImage) {
            return null;
        }
        
        return is_array($firstImage) 
            ? ($firstImage['url'] ?? $firstImage['image_url'] ?? null)
            : ($firstImage->url ?? $firstImage->image_url ?? null);
    }

    private function getThumbnail(): ?string
    {
        // Same as main image for now, can add thumbnail logic later
        return $this->getMainImage();
    }

    // Helper to get images as array (handle both collection and array)
    private function getImagesArray(): array
    {
        $images = $this->images ?? [];
        
        // Convert Eloquent collection to array
        if (is_object($images) && method_exists($images, 'toArray')) {
            $images = $images->toArray();
        }
        
        if (!is_array($images)) {
            return [];
        }

        // Convert to consistent format
        return array_map(function($img) {
            // Handle Eloquent model object
            if (is_object($img) && !is_array($img)) {
                return [
                    'id' => $img->id ?? null,
                    'product_id' => $img->product_id ?? null,
                    'image_url' => $img->image_url ?? $img->url ?? null,
                    'url' => $img->url ?? $img->image_url ?? null,
                    'is_primary' => $img->is_primary ?? 0,
                ];
            }
            
            // Handle array
            if (is_array($img)) {
                return [
                    'id' => $img['id'] ?? null,
                    'product_id' => $img['product_id'] ?? null,
                    'image_url' => $img['image_url'] ?? $img['url'] ?? null,
                    'url' => $img['url'] ?? $img['image_url'] ?? null,
                    'is_primary' => $img['is_primary'] ?? 0,
                ];
            }
            
            return $img;
        }, $images);
    }

    // Helper to get variants as array (handle both collection and array)
    private function getVariantsArray(): array
    {
        $variants = $this->variants ?? [];
        
        if (is_object($variants) && method_exists($variants, 'toArray')) {
            $variants = $variants->toArray();
        }
        
        if (!is_array($variants)) {
            return [];
        }

        return $variants;
    }

    private function getTotalStock(): int
    {
        $variants = $this->getVariantsArray();
        
        if (empty($variants)) {
            return 0;
        }
        
        $totalStock = 0;
        foreach ($variants as $variant) {
            $stock = is_array($variant) ? ($variant['stock'] ?? 0) : ($variant->stock ?? 0);
            $totalStock += (int) $stock;
        }
        
        return $totalStock;
    }

    private function getAvailableSizes(): array
    {
        $variants = $this->getVariantsArray();
        if (empty($variants)) {
            return [];
        }

        $sizes = [];
        foreach ($variants as $variant) {
            $size = is_array($variant) ? ($variant['size'] ?? null) : ($variant->size ?? null);
            if ($size && !in_array($size, $sizes)) {
                $sizes[] = $size;
            }
        }

        return $sizes;
    }

    private function getAvailableColors(): array
    {
        $variants = $this->getVariantsArray();
        if (empty($variants)) {
            return [];
        }

        $colors = [];
        foreach ($variants as $variant) {
            $color = is_array($variant) ? ($variant['color'] ?? null) : ($variant->color ?? null);
            $colorCode = is_array($variant) ? ($variant['color_code'] ?? null) : ($variant->color_code ?? null);
            
            if ($color && !isset($colors[$color])) {
                $colors[$color] = [
                    'name' => $color,
                    'code' => $colorCode,
                    'hex' => $colorCode, // Add 'hex' as alias for frontend compatibility
                ];
            }
        }

        return array_values($colors);
    }
}