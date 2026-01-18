<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseRequest;

class UpdateCategoryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->route('category'),
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
