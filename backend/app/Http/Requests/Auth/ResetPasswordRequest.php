<?php

namespace App\Http\Requests\Auth;
use App\Http\Requests\BaseRequest;

class ResetPasswordRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|string|min:8',
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.exists' => 'Email does not exist',
            'new_password.required' => 'New password is required',
            'new_password.string' => 'New password must be a string',
            'new_password.min' => 'New password must be at least 8 characters',
        ];
    }
}
