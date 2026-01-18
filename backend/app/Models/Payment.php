<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'transaction_no',
        'bank_code',
        'card_type',
        'pay_date',
        'response_code',
    ];

    protected $casts = [
        'pay_date' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
