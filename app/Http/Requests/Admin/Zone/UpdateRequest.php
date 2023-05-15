<?php

namespace App\Http\Requests\Admin\Zone;

use App\models\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $plan_id = $this->get('plan_id');

        $Zone = Zone::find($plan_id);
        return [
            'edit_name' => [
                'required',
                Rule::unique('zone', 'name')->ignore($Zone)
            ],
        ];
    }


    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->setAttributeNames([
            'edit_name' => __('app.name'),
        ]);

        return $validator;
    }

}
