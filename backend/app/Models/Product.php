<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
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

    protected $casts = [
        'price' => 'integer',
        'compare_price' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')
            ->orderBy('is_primary', 'desc') // Ảnh primary lên đầu
            ->orderBy('id', 'asc');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', 1);
    }
    
}
