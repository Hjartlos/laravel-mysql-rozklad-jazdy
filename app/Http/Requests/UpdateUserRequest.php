<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('user') ? $this->route('user')->user_id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'sometimes|boolean',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
