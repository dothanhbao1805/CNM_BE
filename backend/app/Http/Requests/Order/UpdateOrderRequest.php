<?php
namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class UpdateOrderRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'customer_info' => 'sometimes|array',
            'customer_info.name' => 'sometimes|string|max:255',
            'customer_info.email' => 'sometimes|email|max:255',
            'customer_info.phone' => 'sometimes|string|max:20',
            'shipping_address' => 'sometimes|array',
            'shipping_address.address' => 'sometimes|string',
            'shipping_address.city' => 'sometimes|string',
            'shipping_address.district' => 'sometimes|string',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'sometimes|string|in:cod,vnpay,momo',
            'discount' => 'sometimes|numeric|min:0',
            'delivery_fee' => 'sometimes|numeric|min:0',
            'note' => 'sometimes|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'customer_info.email.email' => 'Email không đúng định dạng',
            'items.min' => 'Đơn hàng phải có ít nhất 1 sản phẩm',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
        ];
    }
}