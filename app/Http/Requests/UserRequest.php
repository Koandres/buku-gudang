<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role' => ['required', Rule::in(UserRole::values())],
            'active' => ['boolean'],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8'],
        ];
    }
}
