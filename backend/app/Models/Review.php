<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'order_id',
        'user_id',
        'rating',
        'content',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'rating' => 'integer',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
