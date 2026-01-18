<?php

namespace App\Http\Requests\ShippingFee;

use App\Http\Requests\BaseRequest;

class ShippingFeeRequest extends BaseRequest
{

    public function rules()
    {
        return [
            'province_code' => 'required|numeric',
            'province_name' => 'required|string',
            'ward_code' => 'required|numeric',
            'ward_name' => 'required|string',
            'fee' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ];
    }
}
