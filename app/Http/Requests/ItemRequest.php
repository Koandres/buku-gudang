<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:32', Rule::unique('items', 'kode')->ignore($this->route('item'))],
            'nama' => ['required', 'string', 'max:191'],
            'satuan' => ['required', 'string', Rule::in(config('erp.units'))],
            'harga_acuan' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'aktif' => ['boolean'],
        ];
    }
}
