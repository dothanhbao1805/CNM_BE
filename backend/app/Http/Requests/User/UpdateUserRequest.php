<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdateUserRequest extends BaseRequest
{
    public function prepareForValidation()  
    {
        if ($this->has('fullName')) {
            $this->merge([
                'full_name' => $this->get('fullName'),
            ]);
        }
        if ($this->has('phoneNumber')) {
            $value = $this->get('phoneNumber');
            $this->merge([
                'phone_number' => ($value === '' || $value === 'null' || $value === null) ? null : $value,
            ]);
        }
        if ($this->has('gender')) {
            $value = $this->gender;
            
            if ($value === '' || $value === 'null' || $value === null) {
                $gender = null;
            } elseif ($value === '1' || $value === 1 || $value === true || $value === 'true') {
                $gender = 1;
            } elseif ($value === '0' || $value === 0 || $value === false || $value === 'false') {
                $gender = 0;
            } else {
                $gender = null;
            }
            
            $this->merge(['gender' => $gender]);
        }
    }
    
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        return [
            'full_name' => 'sometimes|nullable|string|max:255',  
            'email' => "sometimes|nullable|email|max:255|unique:users,email,{$userId}",
            'gender' => 'sometimes|nullable|in:0,1',
            'phone_number' => 'sometimes|nullable|string|max:15',
            'status' => 'sometimes',
            'role' => 'sometimes|nullable|in:ADMIN,USER',
            'avatar' => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'The email has already been taken by another user.',
            'avatar.mimes' => 'The avatar must be a file of type: jpg, jpeg, png.',
            'avatar.max' => 'The avatar may not be greater than 2048 kilobytes.',
            'phone_number.max' => 'The phone number may not be greater than 15 characters.',
        ];
    }
}
