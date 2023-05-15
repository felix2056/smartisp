<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
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
            'way_to_pay' => 'required',
            'date' => 'required|date',
            'id_pago' => [
                'required_if:way_to_pay,=,Bank Transfer'
            ]
        ];
    }
}
