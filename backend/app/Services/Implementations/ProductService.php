<?php
namespace App\Services\Implementations;

use App\Services\Interfaces\ProductServiceInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\CloudinaryService;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductService implements ProductServiceInterface
{
    protected $productRepository;
    protected $cloudinaryService;

    public function __construct(
        ProductRepositoryInterface $productRepository, 
        CloudinaryService $cloudinaryService
    ) {
        $this->productRepository = $productRepository;
        $this->cloudinaryService = $cloudinaryService;
    }

    public function getAllProducts(int $page = 1, int $pageSize = 15): array
    {
        try {
            $products = $this->productRepository->getAll($page, $pageSize);

            return [
                'success' => true,
                'data' => ProductResource::collection($products->items())->resolve(),
                'total' => $products->total(),
                'current' => $products->currentPage(),
                'pageSize' => $products->perPage(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting all products', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách sản phẩm'
            ];
        }
    }

    public function getProductsByCategory(string $categorySlug, int $page = 1, int $pageSize = 15): array
    {
        try {
            $products = $this->productRepository->findByCategorySlug($categorySlug, $page, $pageSize);

            if ($products->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm trong danh mục này'
                ];
            }

            return [
                'success' => true,
                'data' => ProductResource::collection($products->items())->resolve(),
                'total' => $products->total(),
                'current' => $products->currentPage(),
                'pageSize' => $products->perPage(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting products by category', [
                'category_slug' => $categorySlug,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách sản phẩm'
            ];
        }
    }

    public function getProductDetail(string $slug): array
    {
        try {
            $product = $this->productRepository->findBySlug($slug);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm'
                ];
            }

            // Load relationships
            $product->load(['category', 'brand', 'images', 'variants', 'reviews']);

            $relatedProducts = $this->productRepository->getRelatedProducts(
                $product->id,
                $product->category_id,
                6
            );

            return [
                'success' => true,
                'data' => new ProductResource($product),
                'related_products' => ProductResource::collection($relatedProducts)->resolve()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting product detail', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy thông tin sản phẩm'
            ];
        }
    }

    public function filterProducts(array $filters, int $page = 1, int $pageSize = 15): array
    {
        try {
            $products = $this->productRepository->filter($filters, $page, $pageSize);

            return [
                'success' => true,
                'data' => ProductResource::collection($products->items())->resolve(),
                'total' => $products->total(),
                'current' => $products->currentPage(),
                'pageSize' => $products->perPage(),
                'filters_applied' => $filters
            ];
        } catch (\Exception $e) {
            Log::error('Error filtering products', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lọc sản phẩm'
            ];
        }
    }

    public function searchProducts(string $keyword, int $page = 1, int $pageSize = 15): array
    {
        try {
            $products = $this->productRepository->search($keyword, $page, $pageSize);

            return [
                'success' => true,
                'data' => ProductResource::collection($products->items())->resolve(),
                'total' => $products->total(),
                'current' => $products->currentPage(),
                'pageSize' => $products->perPage(),
                'keyword' => $keyword
            ];
        } catch (\Exception $e) {
            Log::error('Error searching products', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tìm kiếm sản phẩm'
            ];
        }
    }

    public function getFeaturedProducts(int $limit = 10): array
    {
        try {
            $products = $this->productRepository->getFeatured($limit);

            return [
                'success' => true,
                'data' => ProductResource::collection($products)->resolve()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting featured products', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy sản phẩm nổi bật'
            ];
        }
    }

    public function getNewProducts(int $limit = 10): array
    {
        try {
            $products = $this->productRepository->getNew($limit);

            return [
                'success' => true,
                'data' => ProductResource::collection($products)->resolve()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting new products', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy sản phẩm mới'
            ];
        }
    }

    public function getBestsellerProducts(int $limit = 10): array
    {
        try {
            $products = $this->productRepository->getBestseller($limit);

            return [
                'success' => true,
                'data' => ProductResource::collection($products)->resolve()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting bestseller products', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy sản phẩm bán chạy'
            ];
        }
    }

    public function getAllCategories(): array
    {
        try {
            $categories = $this->productRepository->getAllCategories();

            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (\Exception $e) {
            Log::error('Error getting categories', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách danh mục'
            ];
        }
    }

    public function getAllDressStyles(): array
    {
        try {
            $dressStyles = $this->productRepository->getAllDressStyles();

            return [
                'success' => true,
                'data' => $dressStyles
            ];
        } catch (\Exception $e) {
            Log::error('Error getting dress styles', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách phong cách'
            ];
        }
    }

    public function getAllBrands(): array
    {
        try {
            $brands = $this->productRepository->getAllBrands();

            return [
                'success' => true,
                'data' => $brands
            ];
        } catch (\Exception $e) {
            Log::error('Error getting brands', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy danh sách thương hiệu'
            ];
        }
    }

    public function getProductBySlug(string $slug)
    {
        return $this->productRepository->findBySlug($slug);
    }

    public function getProductById(int $id)
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Tạo sản phẩm mới với variants và images
     */
    public function createProduct(array $data): array
    {
        DB::beginTransaction();
        $uploadedImageUrls = [];
        
        try {
            $productData = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'price' => $data['price'],
                'compare_price' => $data['compare_price'] ?? null,
                'material' => $data['material'] ?? null,
                'care_instructions' => $data['care_instructions'] ?? null,
                'dress_style' => $data['dress_style'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
                'is_active' => $data['is_active'] ?? true,
            ];

            $product = Product::create($productData);

            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $variantData['size'],
                        'color' => $variantData['color'],
                        'stock' => $variantData['stock'],
                        'price' => $variantData['price'],
                    ]);
                }
            }

            if (isset($data['images']) && is_array($data['images'])) {
                $uploadedImageUrls = $this->uploadImages($data['images']);
                
                foreach ($uploadedImageUrls as $index => $imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageUrl,
                        'is_primary' => $index === 0, 
                    ]);
                }
            }

            DB::commit();

            $product->load(['category', 'brand', 'images', 'variants']);

            return [
                'success' => true,
                'message' => 'Tạo sản phẩm thành công',
                'data' => new ProductResource($product)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Rollback: xóa các ảnh đã upload
            if (!empty($uploadedImageUrls)) {
                $this->cleanupImages($uploadedImageUrls);
            }

            Log::error('Error creating product', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tạo sản phẩm: ' . $e->getMessage()
            ];
        }
    }

    public function updateProduct(int $id, array $data): array
    {
        DB::beginTransaction();
        $uploadedImageUrls = [];
        $imagesToDelete = [];
        
        try {
            $product = Product::findOrFail($id);

            $updateData = [];
            $fillableFields = [
                'name', 'slug', 'description', 'category_id', 'brand_id',
                'price', 'compare_price', 'material', 'care_instructions',
                'dress_style', 'is_featured', 'is_active'
            ];
            
            foreach ($fillableFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            if (!empty($updateData)) {
                $product->update($updateData);
            }
            
            // 2. Update variants nếu có
            if (array_key_exists('variants', $data) && $data['variants'] !== null) {
                
                $product->variants()->delete();
                
                foreach ($data['variants'] as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $variantData['size'],
                        'color' => $variantData['color'],
                        'stock' => $variantData['stock'],
                        'price' => $variantData['price'],
                    ]);
                }
            }

            // 3. Xử lý images
            if (array_key_exists('existing_images', $data) || array_key_exists('new_images', $data)) {
                
                $currentImages = $product->images()->pluck('image_url')->toArray();
                $existingImages = $data['existing_images'] ?? $currentImages;
                
                // Upload ảnh mới
                if (!empty($data['new_images'])) {
                    $uploadedImageUrls = $this->uploadImages($data['new_images']);
                }
                
                // Tìm ảnh cần xóa
                $imagesToDelete = array_diff($currentImages, $existingImages);
                
                if (!empty($imagesToDelete)) {
                    $product->images()->whereIn('image_url', $imagesToDelete)->delete();
                }
                
                // Tạo image records mới
                foreach ($uploadedImageUrls as $index => $imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageUrl,
                        'is_primary' => $product->images()->count() === 0 && $index === 0,
                    ]);
                }
            }

            DB::commit();

            if (!empty($imagesToDelete)) {
                $this->cleanupImages($imagesToDelete);
            }

            $product->load(['category', 'brand', 'images', 'variants']);

            return [
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công',
                'data' => new ProductResource($product)
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (!empty($uploadedImageUrls)) {
                $this->cleanupImages($uploadedImageUrls);
            }
            return [
                'success' => false,
                'message' => 'Không thể cập nhật sản phẩm: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload nhiều ảnh lên Cloudinary
     */
    private function uploadImages(array $images): array
    {
        $uploadedUrls = [];
        
        foreach ($images as $image) {
            // Nếu là URL (ảnh cũ), giữ nguyên
            if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
                $uploadedUrls[] = $image;
                continue;
            }

            // Upload ảnh mới (File object từ FormData)
            $uploadedUrl = $this->cloudinaryService->uploadImage($image, 'products');
            
            if ($uploadedUrl) {
                $uploadedUrls[] = $uploadedUrl;
            }
        }
        
        return $uploadedUrls;
    }

    private function cleanupImages(array $imageUrls): void
    {
        foreach ($imageUrls as $url) {
            try {
                $publicId = $this->cloudinaryService->extractPublicId($url);
                if ($publicId) {
                    $this->cloudinaryService->deleteImage($publicId);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete image from Cloudinary', [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Xóa sản phẩm (soft delete)
     */
    public function deleteProduct(int $id): array
    {
        try {
            $product = Product::findOrFail($id);
            
            $product->delete();

            $imageUrls = $product->images()->pluck('image_url')->toArray();
            $this->cleanupImages($imageUrls);

            return [
                'success' => true,
                'message' => 'Xóa sản phẩm thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error deleting product', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể xóa sản phẩm'
            ];
        }
    }

    public function checkStock(int $productId, int $variantId, int $quantity = 1): array
    {
        try {
            $variant = ProductVariant::where('product_id', $productId)
                ->where('id', $variantId)
                ->first();

            if (!$variant) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy biến thể'
                ];
            }

            $inStock = $variant->stock >= $quantity;

            return [
                'success' => true,
                'in_stock' => $inStock,
                'available_stock' => $variant->stock
            ];
        } catch (\Exception $e) {
            Log::error('Error checking stock', [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể kiểm tra tồn kho'
            ];
        }
    }
}