<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\EnumCast;           
use App\Enums\DiscountType;       

class Discount extends Model
{
    protected $table = 'discounts';

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_value',
        'max_discount',
        'usage_limit',
        'used',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'type' => EnumCast::class . ':' . DiscountType::class,
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'value' => 'integer',
        'max_discount' => 'integer',
        'min_order_value' => 'integer',
        'usage_limit' => 'integer',
        'used' => 'integer',
    ];
}
