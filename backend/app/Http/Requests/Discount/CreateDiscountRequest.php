<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CreateDiscountRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('discounts', 'code')->ignore($this->id) // ignore when updating
            ],

            'description' => ['nullable', 'string', 'max:255'],

            'type' => ['required', Rule::in(['percent', 'fixed'])],

            'value' => ['required', 'numeric', 'min:1'],

            'min_order_value' => ['nullable', 'numeric', 'min:0'],

            'max_discount' => ['nullable', 'numeric', 'min:0'],

            'usage_limit' => ['nullable', 'integer', 'min:0'],

            'used' => ['nullable', 'integer', 'min:0'],

            'start_date' => ['required', 'date'],

            'end_date' => ['required', 'date', 'after_or_equal:start_date'],

            'is_active' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'The discount code is required.',
            'code.unique' => 'This discount code already exists.',
            'type.required' => 'Please select a discount type.',
            'value.required' => 'Please enter a discount value.',
            'value.min' => 'The discount value must be greater than 0.',
            'start_date.required' => 'Please select a start date.',
            'end_date.required' => 'Please select an end date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}
