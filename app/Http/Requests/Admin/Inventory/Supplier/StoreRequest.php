<?php

namespace App\Http\Requests\Admin\Inventory\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'contact_name' => 'required|string|max:50',
            'email' => 'required|email|max:60',
            'phone' => 'required|max:15',
            'address' => 'required|max:200',

        ];
    }
}
