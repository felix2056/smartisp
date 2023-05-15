<?php

namespace App\Http\Requests\Admin\Client;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|max:100|string',
            'phone' => 'required|unique:clients',
            'email' => 'required|email|unique:clients',
            'dni' => 'required|unique:clients',
            'billing_due_date' => 'required|integer|between:1,31',
            'pass' => 'min:3',
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'name' => __('app.name'),
            'phone' => __('app.telephone'),
            'dni' => 'DNI/CI',
            'billing_due_date' => __('app.billingDue'),
            'pass' => __('app.password') . ' portal',
        ]);

        return $validator;
    }

}
