<?php

namespace App\Http\Requests\Review;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Str;

class CreateReviewRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
           'product_id' => 'required|string',
            'order_id' => 'required|string',
            'user_id'    => 'required|string',
            'rating'     => 'required|integer|min:1|max:5',
            'content'    => 'required|string|min:20',
            'images'     => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
 public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'order_id.required' => 'orderId is required',
            'user_id.required'    => 'User is required.',
            'rating.required'     => 'Rating is required.',
            'content.required'    => 'Review content is required.',
            'content.min'         => 'Review content must be at least 20 characters.',
        ];
    }
}