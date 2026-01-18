<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $table = 'wishlist_items';

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'added_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
