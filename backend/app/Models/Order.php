<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use SoftDeletes;
    use Notifiable;
    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'user_id',
        'full_name',
        'email',
        'phone',
        'house_number',
        'province',
        'ward',
        'note',
        'payment_method',
        'payment_status',
        'order_status',
        'subtotal',
        'discount',
        'delivery_fee',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount' => 'integer',
        'delivery_fee' => 'integer',
        'total' => 'integer',
    ];

    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
