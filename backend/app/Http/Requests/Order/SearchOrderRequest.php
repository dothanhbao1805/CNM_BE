<?php
namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class SearchOrderRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'order_code' => 'nullable|string',
            'user_id' => 'nullable|string',
            'order_status' => 'nullable|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,paid,refunded,failed',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_total' => 'nullable|numeric|min:0',
            'max_total' => 'nullable|numeric|min:0|gte:min_total'
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'Email không đúng định dạng',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'max_total.gte' => 'Tổng tiền tối đa phải lớn hơn hoặc bằng tổng tiền tối thiểu',
        ];
    }
}