<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:32', Rule::unique('warehouses', 'kode')->ignore($this->route('warehouse'))],
            'nama' => ['required', 'string', 'max:191'],
        ];
    }
}
