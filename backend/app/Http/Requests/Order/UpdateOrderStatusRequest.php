<?php
namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class UpdateOrderStatusRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'order_status' => 'required|string|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,paid,refunded,failed',
            'note' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'order_status.required' => 'Trạng thái đơn hàng là bắt buộc',
            'order_status.in' => 'Trạng thái đơn hàng không hợp lệ',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ',
        ];
    }
}