<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class CreateOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
             'user_id' => 'nullable|integer|exists:users,id',
            'customer_info.fullName' => 'required|string',
            'customer_info.email' => 'required|email',
            'customer_info.phone' => 'required|string',

            'shipping_address.houseNumber' => 'required|string',
            'shipping_address.province' => 'required|string',
            'shipping_address.ward' => 'required|string',
            'shipping_address.note' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
            'items.*.image' => 'nullable|string',

            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'delivery_fee' => 'required|numeric',
            'total_vnpay' => 'required|numeric',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            // Bạn có thể custom message theo ý bạn
            'items.required' => 'Order must have at least 1 product.',
        ];
    }
}