<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'stock',
        'price',
    ];

    protected $casts = [
        'stock' => 'integer',
        'price' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
