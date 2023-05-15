<?php

namespace App\Http\Requests\Admin\Plan;

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
            'name' => 'required|unique:plans',
            'download' => 'required|min:2',
            'upload' => 'required|min:2',
            'cost' => 'required|numeric',
            'limitat' => 'required|numeric|min:10|max:100|integer',
            'priority' => 'required',
            'aggregation' => 'required|numeric|integer|min:1',
            'bl' => 'required|min:0|max:100|numeric|integer',
            'bth' => 'required|min:0|max:100|numeric|integer',
            'bt' => 'required|numeric|integer',
//	        'address_list_name' => 'required_if:no_rules,1',
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'name' => __('app.name'),
            'download' => __('app.discharge'),
            'upload' => __('app.rise'),
            'cost' => __('app.cost'),
            'limitat' => __('app.Guaranteedspeed').' Limit At',
            'priority' => __('app.priority'),
            'bl' => 'Burst Limit',
            'bth' => 'Burst Threshold',
            'bt' => 'Burst Time'
        ]);

        return $validator;
    }

}
