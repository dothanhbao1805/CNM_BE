<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class SearchRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'keyword' => 'required|string|min:2|max:255',
            'current' => 'nullable|integer|min:1',
            'pageSize' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'keyword.required' => 'Vui lòng nhập từ khóa tìm kiếm.',
            'keyword.min' => 'Từ khóa phải có ít nhất 2 ký tự.',
        ];
    }
}