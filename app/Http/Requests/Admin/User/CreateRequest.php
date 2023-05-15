<?php

namespace App\Http\Requests\Admin\User;

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
            'name' => 'required',
            'phone' => 'required|min:2|numeric',
            'email' => 'required|email|unique:users',
            'username' => 'required|min:3|unique:users',
            'password' => 'required|min:3|confirmed',
            'password_confirmation' => 'required|min:3',
	        'user_acc' => 'required_without:cashdesk'
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'name' => __('app.fullName'),
            'phone' => __('app.telephone'),
            'email' => 'Email',
            'username' => __('app.username'),
            'password' => __('app.password'),
            'password_confirmation' => __('app.confirm').' '. __('app.password'),
            'user_acc' => __('app.Userpermits')
        ]);

        return $validator;
    }

}
