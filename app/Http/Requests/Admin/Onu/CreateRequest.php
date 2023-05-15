<?php

namespace App\Http\Requests\Admin\Onu;

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
            'pontype' => 'required',
            'onutype' => 'required',
            'ethernet_ports' => 'required',
            'wifi_ssids' => 'required',
            // 'voip_ports' => 'required',
            // 'capability' => 'required',
        ];
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'pontype' => 'pontype',
            'onutype' => 'onutype',
            'ethernet_ports' => 'ethernet_ports',
            'wifi_ssids' => 'wifi_ssids',
            'detail' => 'detail',
            //  'voip_ports' => 'voip_ports',
            //  'catv' => 'catv',
            //  'allow_custom_profiles' => 'allow_custom_profiles',
            //  'capability' => 'capability',
        ]);

        return $validator;
    }

}
