<?php
namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class UpdatePaymentStatusRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'payment_status' => 'required|string|in:unpaid,paid,refunded,failed'
        ];
    }

    public function messages()
    {
        return [
            'payment_status.required' => 'Trạng thái thanh toán là bắt buộc',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ',
        ];
    }
}