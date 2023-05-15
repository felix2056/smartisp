<?php

namespace App\Http\Requests\Admin\User;

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
        $user_id = $this->get('user_id');
        return [
            'edit_name' => 'required',
            'edit_phone' => 'required|min:2|numeric|unique:users,phone,' . $user_id,
            'edit_email' => 'required|email|unique:users,email,' . $user_id,
            'password' => 'nullable|min:3|confirmed',
            'password_confirmation' => 'nullable|min:3',
            'user_acc' => 'required_without:edit_cashdesk'
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'edit_name' => __('app.fullName'),
            'edit_phone' => __('app.telephone'),
            'edit_email' => 'Email',
            'password' => __('app.new').' '.__('app.password'),
            'password_confirmation' => __('app.confirm').' '.__('app.password'),
            'user_acc' => __('app.Userpermits')
        ]);

        return $validator;
    }

}
