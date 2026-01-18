<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseRequest;

class StoreCategoryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục không được để trống.',
            'name.string' => 'Tên danh mục phải là chuỗi ký tự.',
            'name.max' => 'Tên danh mục tối đa 255 ký tự.',
        ];
    }
}
