<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:32', Rule::unique('suppliers', 'kode')->ignore($this->route('supplier'))],
            'nama' => ['required', 'string', 'max:191'],
            'kontak' => ['nullable', 'string', 'max:191'],
            'telepon' => ['nullable', 'string', 'max:32'],
            'alamat' => ['nullable', 'string'],
            'aktif' => ['boolean'],
        ];
    }
}
