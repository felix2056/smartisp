<?php

namespace App\Http\Requests\Admin\Inventory\Item;

use Illuminate\Foundation\Http\FormRequest;

class RentItemCustomerRequest extends FormRequest
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
            'client_id' => 'required|exists:clients,id',
            'router_id' => 'required|exists:routers,id',
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'The customer filed is required.',
            'client_id.exists' => 'The customer does not exists.',

        ];
    }
}
