<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingFee extends Model
{
    protected $table = 'shipping_fees';

    protected $fillable = [
        'province_code',
        'province_name',
        'ward_code',
        'ward_name',
        'fee',
        'note',
    ];

    protected $casts = [
        'fee' => 'integer',
    ];
}
