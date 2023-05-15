<?php

namespace App\Http\Requests;

use App\Http\Requests\CoreRequest;
use Illuminate\Foundation\Http\FormRequest;

class CsvRequest extends CoreRequest
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
            'date' => 'required',
            'refint_num' => 'required',
            'fisc_num' => 'required',
            'type_doc' => 'required',
            'status' => 'required',
            'printer' => 'required',
            'doc_refer' => 'required',
            'numz' => 'required',
            'file_name' => 'required',
            'inv_content' => 'required',
        ];
    }

//    protected function getValidatorInstance()
//    {
//        $validator = parent::getValidatorInstance();
//
//        $validator->setAttributeNames([
//            'name' => __('app.name'),
//            'location' => __('app.address'),
//            'login_edit' => 'login',
//            'port_edit' => __('app.port')
//        ]);
//
//        return $validator;
//    }
}
