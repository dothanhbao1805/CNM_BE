<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateProductRequest extends BaseRequest
{
    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId, 'id')
            ],
            'description' => 'sometimes|nullable|string',
            
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'brand_id' => 'sometimes|nullable|exists:brands,id',
            
            'price' => 'sometimes|integer|min:0',
            'compare_price' => 'sometimes|nullable|integer|min:0',
            
            'material' => 'sometimes|nullable|string|max:255',
            'care_instructions' => 'sometimes|nullable|string',
            'dress_style' => 'sometimes|nullable|string|max:100',
            
            'variants' => 'sometimes|nullable|array',
            'variants.*.size' => 'required|string|max:50',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.price' => 'required|integer|min:0',
            
            'existing_images' => 'sometimes|nullable|array',
            'existing_images.*' => 'url',
            
            'new_images' => 'sometimes|nullable|array',
            'new_images.*' => 'file|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            'slug.unique' => 'Slug đã tồn tại, vui lòng chọn slug khác',
            
            'category_id.exists' => 'Danh mục không tồn tại',
            'brand_id.exists' => 'Thương hiệu không tồn tại',
            
            'price.integer' => 'Giá phải là số nguyên',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0',
            'compare_price.integer' => 'Giá so sánh phải là số nguyên',
            'compare_price.min' => 'Giá so sánh phải lớn hơn hoặc bằng 0',
            
            'variants.array' => 'Biến thể phải là một mảng',
            'variants.*.size.required' => 'Kích thước biến thể là bắt buộc',
            'variants.*.color.required' => 'Màu sắc biến thể là bắt buộc',
            'variants.*.stock.required' => 'Số lượng tồn kho là bắt buộc',
            'variants.*.stock.integer' => 'Số lượng tồn kho phải là số nguyên',
            'variants.*.stock.min' => 'Số lượng tồn kho phải lớn hơn hoặc bằng 0',
            'variants.*.price.required' => 'Giá biến thể là bắt buộc',
            'variants.*.price.integer' => 'Giá biến thể phải là số nguyên',
            'variants.*.price.min' => 'Giá biến thể phải lớn hơn hoặc bằng 0',
            
            'existing_images.array' => 'Danh sách ảnh hiện tại phải là một mảng',
            'existing_images.*.url' => 'URL ảnh không hợp lệ',
            'new_images.array' => 'Danh sách ảnh mới phải là một mảng',
            'new_images.*.file' => 'File phải là một file hợp lệ',
            'new_images.*.image' => 'File phải là ảnh',
            'new_images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, webp, gif',
            'new_images.*.max' => 'Kích thước ảnh tối đa 5MB',
        ];
    }

    protected function prepareForValidation()
    {
        if (is_string($this->variants)) {
            $this->merge(['variants' => json_decode($this->variants, true)]);
        }
        
        if (is_string($this->existing_images)) {
            $this->merge(['existing_images' => json_decode($this->existing_images, true)]);
        }
        
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
        if ($this->has('price')) {
            $this->merge(['price' => (int) $this->price]);
        }
        
        if ($this->has('compare_price')) {
            $this->merge(['compare_price' => $this->compare_price ? (int) $this->compare_price : null]);
        }
        
        // Cast IDs về integer
        if ($this->has('category_id') && $this->category_id) {
            $this->merge(['category_id' => (int) $this->category_id]);
        }
        
        if ($this->has('brand_id') && $this->brand_id) {
            $this->merge(['brand_id' => (int) $this->brand_id]);
        }
        
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => $this->generateUniqueSlug($this->name)
            ]);
        }
        
        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->convertToBoolean($this->is_featured)]);
        }
        
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->convertToBoolean($this->is_active)]);
        }
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        
        $productId = $this->route('id');
        
        while (\App\Models\Product::where('slug', $slug)
            ->where('id', '!=', $productId)
            ->exists()
        ) {
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
     * Get validated data để update product
     */
    public function getProductData(): array
    {
        $data = [];
        
        // Chỉ lấy các field có trong request
        $fillableFields = [
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
        ];
        
        foreach ($fillableFields as $field) {
            if ($this->has($field)) {
                $data[$field] = $this->input($field);
            }
        }
        
        return $data;
    }

    /**
     * Get variants data
     */
    public function getVariantsData(): ?array
    {
        return $this->has('variants') ? $this->validated()['variants'] : null;
    }

    /**
     * Get existing images URLs
     */
    public function getExistingImages(): ?array
    {
        return $this->has('existing_images') ? $this->input('existing_images') : null;
    }

    /**
     * Get new images files
     */
    public function getNewImages(): array
    {
        return $this->file('new_images', []);
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên sản phẩm',
            'slug' => 'đường dẫn',
            'description' => 'mô tả',
            'price' => 'giá bán',
            'compare_price' => 'giá so sánh',
            'material' => 'chất liệu',
            'care_instructions' => 'hướng dẫn bảo quản',
            'dress_style' => 'phong cách',
            'category_id' => 'danh mục',
            'brand_id' => 'thương hiệu',
            'is_featured' => 'sản phẩm nổi bật',
            'is_active' => 'trạng thái kích hoạt',
        ];
    }
}