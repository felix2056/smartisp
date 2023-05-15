<?php

namespace App\Http\Requests\Admin\Onu;

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

            'edit_pontype' => 'required',
            'edit_onutype' => 'required',
            'edit_ethernet_ports' => 'required',
            'edit_wifi_ssids' => 'required',
            // 'edit_voip_ports' => 'required',
            // 'edit_capability' => 'required',
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'edit_pontype' => 'pontype',
            'edit_onutype' => 'onutype',
            'edit_ethernet_ports' => 'ethernet_ports',
            'edit_wifi_ssids' => 'wifi_ssids',
            'edit_detail' => 'detail',
            // 'edit_catv' => 'catv',
            // 'edit_allow_custom_profiles' => 'allow_custom_profiles',
            // 'edit_capability' => 'capability',
        ]);

        return $validator;
    }

}
