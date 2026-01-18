<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Str;

class CreateProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            
            'price' => 'required|integer|min:0',
            'compare_price' => 'nullable|integer|min:0|gte:price',
            
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,webp,gif|max:5120', 
            
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string|max:50',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.price' => 'required|integer|min:0',
            
            'material' => 'nullable|string|max:255',
            'care_instructions' => 'nullable|string',
            
            'dress_style' => 'nullable|string|max:100',
            
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',   
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'slug.unique' => 'Slug đã tồn tại',
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0',
            'price.integer' => 'Giá phải là số nguyên',
            'compare_price.gte' => 'Giá so sánh phải lớn hơn hoặc bằng giá bán',
            'compare_price.integer' => 'Giá so sánh phải là số nguyên',
            'variants.required' => 'Phải có ít nhất 1 biến thể',
            'variants.min' => 'Phải có ít nhất 1 biến thể',
            'variants.*.stock.integer' => 'Số lượng phải là số nguyên',
            'variants.*.price.integer' => 'Giá biến thể phải là số nguyên',
            'images.*.image' => 'File phải là ảnh',
            'images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, webp, gif',
            'images.*.max' => 'Kích thước ảnh tối đa 5MB',
            'category_id.exists' => 'Danh mục không tồn tại',
            'brand_id.exists' => 'Thương hiệu không tồn tại',
        ];
    }

    protected function prepareForValidation()
    {
        // Parse variants từ FormData (nếu gửi dạng JSON string)
        if (is_string($this->variants)) {
            $this->merge(['variants' => json_decode($this->variants, true)]);
        }
        
        // Xử lý variants - cast về integer
        if ($this->variants && is_array($this->variants)) {
            $variants = array_map(function($variant) {
                return [
                    'size' => $variant['size'] ?? '',
                    'color' => $variant['color'] ?? '',
                    'stock' => isset($variant['stock']) ? (int) $variant['stock'] : 0,
                    'price' => isset($variant['price']) ? (int) $variant['price'] : 0,
                ];
            }, $this->variants);
            
            $this->merge(['variants' => $variants]);
        }
        
        // Cast price về integer
        $this->merge([
            'price' => $this->price ? (int) $this->price : 0,
            'compare_price' => $this->compare_price ? (int) $this->compare_price : null,
        ]);
        
        // Cast IDs về integer (nếu gửi dạng string)
        if ($this->category_id) {
            $this->merge(['category_id' => (int) $this->category_id]);
        }
        
        if ($this->brand_id) {
            $this->merge(['brand_id' => (int) $this->brand_id]);
        }
        
        // Auto generate slug nếu chưa có
        if (!$this->slug && $this->name) {
            $this->merge(['slug' => $this->generateUniqueSlug($this->name)]);
        }

        // Set default values
        $this->merge([
            'is_active' => $this->convertToBoolean($this->is_active ?? true),
            'is_featured' => $this->convertToBoolean($this->is_featured ?? false),
        ]);
    }

    /**
     * Generate unique slug cho product
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        
        while (\App\Models\Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    /**
     * Convert giá trị sang boolean
     */
    private function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }
        
        return (bool) $value;
    }

    /**
     * Get validated data để tạo product
     * Loại bỏ các field không cần thiết cho bảng products
     */
    public function getProductData(): array
    {
        return $this->only([
            'name',
            'slug',
            'description',
            'category_id',
            'brand_id',
            'price',
            'compare_price',
            'material',
            'care_instructions',
            'dress_style',
            'is_featured',
            'is_active',
        ]);
    }

    /**
     * Get variants data để tạo product variants
     */
    public function getVariantsData(): array
    {
        return $this->validated()['variants'] ?? [];
    }

    /**
     * Get images files để upload
     */
    public function getImagesFiles(): array
    {
        return $this->file('images', []);
    }
}