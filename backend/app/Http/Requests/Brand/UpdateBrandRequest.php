<?php

namespace App\Http\Requests\Brand;

use App\Http\Requests\BaseRequest;

class UpdateBrandRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:brands,name,' . $this->route('brand'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.string' => 'Tên thương hiệu phải là chuỗi ký tự.',
            'name.max' => 'Tên thương hiệu tối đa 255 ký tự.',
        ];
    }
}
