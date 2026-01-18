<?php

namespace App\Http\Requests\User;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;

class CreateUserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation() //tránh không lưu được giới tính nữ
    {
        if ($this->has('gender')) {
            $this->merge([
                'gender' => $this->gender === null ? null : (int)$this->gender,
            ]);
        }
    }
    public function rules(): array
    {
        return [
            'full_name'   => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users,email',
            'role'        => 'required|in:ADMIN,USER',
            'status'      => 'sometimes|in:ACTIVE,INACTIVE',
            'gender'      => 'nullable|in:"0","1"',
            'phone_number'=> 'nullable|string|max:15', 
        ];
    }
    public function messages()
    {
        return [
            'full_name.required' => 'Tên người dùng là bắt buộc.',
            'full_name.string' => 'Tên người dùng phải là một chuỗi ký tự.',
            'full_name.max' => 'Tên người dùng không được vượt quá 255 ký tự.',
            'email.required' => 'Email là bắt buộc.',
            'email.string' => 'Email phải là một chuỗi ký tự.',
            'email.email' => 'Email phải có định dạng hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã được sử dụng.',
            'role.required' => 'Vai trò là bắt buộc.',
            'role.in' => 'Vai trò không hợp lệ. Chỉ chấp nhận ADMIN hoặc USER.',
            'status.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận ACTIVE hoặc INACTIVE.',
        ];
    }
}
