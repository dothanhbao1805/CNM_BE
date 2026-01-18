<?php

namespace App\Repositories\Eloquent;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $page = 1, int $perPage = 15)
    {
       return Product::with(['category', 'brand', 'images', 'variants'])
        ->where('is_active', true)
        ->paginate($perPage, ['*'], 'page', $page);

    }

    public function findById(int $id)
    {
        return Product::find($id);
    }

    public function findBySlug(string $slug)
    {
        $product = Product::with(['images', 'variants'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }


    public function findByCategorySlug(string $categorySlug, int $page = 1, int $perPage = 15)
    {
        return Product::with(['category', 'brand', 'images', 'variants'])
            ->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            })
            ->where('is_active', true)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByCategoryName(string $categoryName, int $perPage = 15)
    {
        return Product::whereHas('category', function($q) use ($categoryName) {
                $q->where('name', $categoryName);
            })
            ->where('is_active', true)
            ->paginate($perPage);
    }

    public function findByParentCategory(string $parentName, int $perPage = 15)
    {
        return Product::whereHas('category', function($q) use ($parentName) {
                $q->where('parent', $parentName);
            })
            ->where('is_active', true)
            ->paginate($perPage);
    }

    public function search(string $keyword, int $page = 1, int $perPage = 12)
    {
        if (empty(trim($keyword))) {
            return Product::with(['category', 'brand', 'images', 'variants'])
                ->where('is_active', true)
                ->paginate($perPage, ['*'], 'page', $page);
        }

        $query = Product::with(['category', 'brand', 'images', 'variants'])
            ->where('products.is_active', true)
            ->where(function($q) use ($keyword) {
                $q->where('products.name', 'like', "%{$keyword}%")
                ->orWhere('products.description', 'like', "%{$keyword}%")
                ->orWhere('products.slug', 'like', "%{$keyword}%")
                ->orWhereHas('category', function($catQuery) use ($keyword) {
                    $catQuery->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('brand', function($brandQuery) use ($keyword) {
                    $brandQuery->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('products.created_at', 'desc');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function filter(array $filters,int $current, int $perPage = 9)
    {
        // Eager load relationships needed for ProductResource
        $query = Product::with(['category', 'brand', 'images', 'variants'])
            ->where('is_active', true);

        // Filter by categories - use whereHas to check category relationship
        if (isset($filters['categories']) && !empty($filters['categories'])) {
            $query->whereHas('category', function($q) use ($filters) {
                $q->whereIn('slug', $filters['categories']);
            });
        }

        // Filter by price range
        if (isset($filters['minPrice']) || isset($filters['maxPrice'])) {
            $minPrice = $filters['minPrice'] ?? 0;
            $maxPrice = $filters['maxPrice'] ?? PHP_INT_MAX;
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        // Filter by colors - use whereHas to check if product has variant with matching color
        if (isset($filters['colors']) && !empty($filters['colors'])) {
            $query->whereHas('variants', function($q) use ($filters) {
                $q->whereIn('color', $filters['colors']);
            });
        }

        // Filter by sizes - use whereHas to check if product has variant with matching size
        if (isset($filters['sizes']) && !empty($filters['sizes'])) {
            $query->whereHas('variants', function($q) use ($filters) {
                $q->whereIn('size', $filters['sizes']);
            });
        }

        // Filter by dress styles - direct column filter (dress_style is string column)
        // Note: Assuming dress_style stores the slug value, if it stores name, need to map slug to name
        if (isset($filters['dressStyles']) && !empty($filters['dressStyles'])) {
            $query->whereIn('dress_style', $filters['dressStyles']);
        }

        return $query->paginate($perPage, ['*'], 'page', $current);
    }

    public function getFeatured(int $limit = 4)
    {
        return Product::where('is_featured', true)
            ->where('is_active', true)
            ->limit($limit)
            ->get();
    }

    public function getNew(int $limit = 4)
    {
        return Product::with([
                'primaryImage:id,product_id,image_url'
            ])
            ->select(
                'id',
                'name',
                'slug',
                'price',
                'compare_price',
                'is_featured',
                'created_at'
            )
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }


    public function getBestseller(int $limit = 4)
    {
        return Product::with([
                'primaryImage:id,product_id,image_url'
            ])
            ->select(
                'id',
                'name',
                'slug',
                'price',
                'compare_price',
                'is_featured',
                'created_at'
            )
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('is_featured', true)
                    ->orWhere(function ($q) {
                        $q->whereNotNull('compare_price')
                            ->whereColumn('compare_price', '>', 'price');
                    });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }


    public function getAllCategories()
    {
        return Category::select('id', 'name', 'slug')->get();
    }

    public function getAllDressStyles()
    {
        return Product::whereNotNull('dress_style')
            ->where('is_active', true)
            ->distinct()
            ->pluck('dress_style')
            ->map(function($style) {
                // Return as object with name and slug for frontend compatibility
                // Assuming dress_style stores the name, create slug from it
                return [
                    'name' => $style,
                    'slug' => \Illuminate\Support\Str::slug($style)
                ];
            })
            ->values();
    }


    public function getAllBrands()
    {
        return Product::join('brands', 'products.brand_id', '=', 'brands.id')
            ->where('products.is_active', true)
            ->select('brands.id', 'brands.name', 'brands.slug')
            ->distinct()
            ->get();
    }

    public function getRelatedProducts(int $productId, int $categoryId, int $limit = 6)
    {
        return Product::whereHas('category', function($q) use ($categoryId) {
                $q->where('id', $categoryId);
            })
            ->where('id', '!=', $productId)
            ->where('is_active', true)
            ->limit($limit)
            ->get();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product|bool
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        // Update the product
        $product->update($data);
        
        // Refresh to get the latest data from database
        $product->refresh();
        
        // Return the updated product instance
        return $product;
    }

    public function delete(int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        return $product->update(['is_active' => false]);
    }

    public function forceDelete(int $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        return $product->delete();
    }

    public function updateStock(int $id, int $quantity)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        return $product->update(['stock' => $quantity]);
    }

    public function decreaseStock(int $id, int $quantity)
    {
        $product = Product::find($id);
        
        if (!$product || $product->stock < $quantity) {
            return false;
        }

        return $product->decrement('stock', $quantity);
    }

    public function increaseStock(int $id, int $quantity)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        return $product->increment('stock', $quantity);
    }

    public function isInStock(int $id, int $quantity = 1)
    {
        $product = Product::find($id);
        
        return $product && $product->stock >= $quantity;
    }

    public function getByPriceRange(int $minPrice, int $maxPrice, int $perPage = 15)
    {
        return Product::where('price', '>=', $minPrice)
            ->where('price', '<=', $maxPrice)
            ->where('is_active', true)
            ->paginate($perPage);
    }
}
