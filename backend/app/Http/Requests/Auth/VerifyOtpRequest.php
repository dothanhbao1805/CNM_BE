<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class VerifyOtpRequest extends BaseRequest
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
            'otp'   => 'required|string|size:' . config('otp.length'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.exists' => 'Email does not exist',
            'otp.required' => 'OTP is required',
            'otp.string' => 'OTP must be a string',
            'otp.size' => 'OTP must be exactly ' . config('otp.length') . ' characters',
        ];
    }
}
