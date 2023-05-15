<?php

namespace App\Http\Requests\Admin\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Input;

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
            'edit_name' => 'required|max:100|string',
            'edit_phone' => 'string',
            'edit_email' => 'email|unique:clients,email,' . $this->get('client_id'),
            'edit_dni' => 'unique:clients,dni,' . $this->get('client_id'),
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'edit_name' => __('app.name'),
            'edit_phone' => __('app.telephone'),
            'edit_dni' => 'DNI/CI',
            'edit_pass' => __('app.new') . ' Password',
        ]);

        return $validator;
    }

}
