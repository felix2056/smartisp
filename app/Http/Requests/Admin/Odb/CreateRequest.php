<?php

namespace App\Http\Requests\Admin\Odb;

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
        $plan_name = $this->get('name');

        return [
            'name' => 'required|unique:zone,name,'.$plan_name,
            'port' => 'required',
            'zone_id' => 'required|exists:zone,id',
            'location' => 'required',
        ];
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'name' => __('app.name'),
            'port' => 'port',
            'zone_id' => 'zone_id',
            'location' => 'location',
        ]);

        return $validator;
    }

}
