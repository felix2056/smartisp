<?php

namespace App\Http\Requests\LanguageSettings;

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
            'language_code' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'language_code.required' => 'Please select at-least one language'
        ];
    }

    protected function formatErrors(\Illuminate\Contracts\Validation\Validator  $validator)
    {
         return [
            'msg' => 'error',
            'errors' => $validator->getMessageBag()->toArray()
        ];
    }
}
