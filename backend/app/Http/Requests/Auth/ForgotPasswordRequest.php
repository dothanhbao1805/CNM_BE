<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class ForgotPasswordRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.exists' => 'Email does not exist',
        ];
    }
}
