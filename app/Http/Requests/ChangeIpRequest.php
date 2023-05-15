<?php

namespace App\Http\Requests;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class ChangeIpRequest extends CoreRequest
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
            'name' => 'required|unique:routers,name,'.$this->route('id'),
            'location' => 'required',
            'login_edit' => 'required',
            'port_edit' => 'required',
        ];
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'name' => __('app.name'),
            'location' => __('app.address'),
            'login_edit' => 'login',
            'port_edit' => __('app.port')
        ]);

        return $validator;
    }
}
