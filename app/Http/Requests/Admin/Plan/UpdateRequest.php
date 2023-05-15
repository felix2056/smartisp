<?php

namespace App\Http\Requests\Admin\Plan;

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
            'edit_name' => 'required|unique:plans,name,'.$this->get('plan_id'),
            'edit_download' => 'required|min:2',
            'edit_upload' => 'required|min:2',
            'edit_cost' => 'required|numeric',
            'edit_iva' => 'numeric',
            //advanced
            'edit_limitat' => 'required|numeric|min:10|max:100|integer',
            'edit_priority' => 'required',
            'edit_aggregation' => 'required|numeric|integer|min:1',
            'edit_bl' => 'required|min:0|max:100|numeric|integer',
            'edit_bth' => 'required|min:0|max:100|numeric|integer',
            'edit_bt' => 'required|numeric|integer',
//            'address_list_name' => 'required_if:no_rules,1',
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'edit_name' => __('app.name'),
            'edit_title' => __('app.title'),
            'edit_download' => __('app.discharge'),
            'edit_upload' => __('app.rise'),
            'edit_cost' => __('app.cost'),
            'edit_limitat' => __('app.Guaranteedspeed').' Limit At',
            'edit_priority' => __('app.priority'),
            'edit_bl' => 'Burst Limit',
            'edit_bth' => 'Burst Threshold',
            'edit_bt' => 'Burst Time'
        ]);

        return $validator;
    }

}
