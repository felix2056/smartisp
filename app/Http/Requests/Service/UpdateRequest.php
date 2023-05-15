<?php

namespace App\Http\Requests\Service;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends CoreRequest
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
            'router' => 'required|exists:routers,id',
            'ip' => 'required|ip|unique:client_services,ip,'.$this->route('id'),
            'plan' => 'required',
            'date_in' => 'required',
            'user_hot' => 'unique:client_services,user_hot,'.$this->route('id'),
            'pass_hot' => '',
            'pass' => 'min:3',
        ];
    }
}
