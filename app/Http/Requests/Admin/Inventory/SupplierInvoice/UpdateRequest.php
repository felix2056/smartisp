<?php

namespace App\Http\Requests\Admin\Inventory\SupplierInvoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'supplier_id' => 'required|exists:inv_supplier,id',
            'invoice_number' => 'required|numeric',
            'invoice_date' => 'required',
            'file' => 'mimes:gif,png,jpg,jpeg',
            'products.*' => 'required|exists:inv_products,id',
        ];
    }
}
