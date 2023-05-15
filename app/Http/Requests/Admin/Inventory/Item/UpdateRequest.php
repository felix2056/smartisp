<?php

namespace App\Http\Requests\Admin\Inventory\Item;

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
            'product_id' => 'required|exists:inv_products,id',
            'bar_code' => 'required',
            'serial_number' => 'required',
            'amount_with_tax' => 'required',
            'file' => 'mimes:gif,png,jpg,jpeg',
        ];
    }

    public function messages()
    {
        return [
            'amount_with_tax.required' => 'The amount field is required.'
        ];
    }
}
