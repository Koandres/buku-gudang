<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('aktif', true)],
            'purchase_request_id' => ['required', 'integer', 'exists:purchase_requests,id'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_request_item_id' => ['required', 'integer', 'distinct', 'exists:purchase_request_items,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.harga' => ['required', 'integer', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.required' => 'Minimal satu barang harus dipesan.',
            'items.*.jumlah.min' => 'Jumlah minimal satu.',
            'items.*.harga.min' => 'Harga tidak boleh negatif.',
        ];
    }
}
