<?php

namespace App\Http\Requests\Admin\Inventory\Product;

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
            'name' => 'required|string|max:50',
            'vendor_id' => 'required|exists:inv_vendors,id',
            'rent_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
            'photo' => 'mimes:gif,png,jpg,jpeg',
        ];
    }
}
