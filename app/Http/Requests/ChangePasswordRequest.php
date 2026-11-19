<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak benar.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.string' => 'Kata sandi baru harus berupa teks.',
            'password.min' => 'Kata sandi baru minimal :min karakter.',
        ];
    }
}
