<?php

namespace App\Http\Requests\Service;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends CoreRequest
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
        $rules = [
            'router' => 'required|exists:routers,id',
            'ip' => 'required|ip|unique:client_services,ip',
            'plan' => 'required',
            'date_in' => 'required',
            'pass_hot' => '',
            'pass' => 'min:3',
        ];

        if($this->has('user_hot') && $this->user_hot != '') {
            $rules['user_hot'] = 'unique:client_services,user_hot';
        }

        return $rules;
    }
}
