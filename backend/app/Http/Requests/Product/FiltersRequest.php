<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

class FiltersRequest extends BaseRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];
        
        // Cast numeric fields to integer/float
        if ($this->has('minPrice')) {
            $data['minPrice'] = (int)$this->input('minPrice');
        }
        
        if ($this->has('maxPrice')) {
            $data['maxPrice'] = (int)$this->input('maxPrice');
        }
        
        if ($this->has('current')) {
            $data['current'] = (int)$this->input('current');
        }
        
        if ($this->has('pageSize')) {
            $data['pageSize'] = (int)$this->input('pageSize');
        }
        
        $this->merge($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current' => 'sometimes|integer|min:1',
            'pageSize' => 'sometimes|integer|min:1|max:100',
            'categories' => 'sometimes|array', 
            'maxPrice' => 'sometimes|integer|min:0',  // Changed to integer
            'minPrice' => 'sometimes|integer|min:0',  // Changed to integer
            'colors' => 'sometimes|array',
            'sizes' => 'sometimes|array',
            'dressStyles' => 'sometimes|array',
        ];
    }
}