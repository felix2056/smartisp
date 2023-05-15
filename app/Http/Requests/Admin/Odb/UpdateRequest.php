<?php

namespace App\Http\Requests\Admin\Odb;

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
        $plan_name = $this->get('edit_name');

        return [
            'edit_name' => 'required|unique:zone,name,'.$plan_name,
            'edit_port' => 'required',
            'edit_zone_id' => 'required|exists:zone,id',
            'location_edit' => 'required',
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'edit_name' => __('app.name'),
            'edit_port' => 'port',
            'edit_zone_id' => 'zone_id',
            'location_edit' => 'location_edit',
        ]);

        return $validator;
    }

}
