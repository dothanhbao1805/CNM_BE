<?php
namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class CancelOrderRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'reason' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'reason.max' => 'Lý do hủy không được vượt quá 500 ký tự',
        ];
    }
}