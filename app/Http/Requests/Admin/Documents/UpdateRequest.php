<?php

namespace App\Http\Requests\Admin\Documents;

use App\Http\Requests\CoreRequest;

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
	        'title' => 'required|max:255',
	        'file' => 'nullable|mimes:gif,png,jpg,jpeg,pdf,txt',
        ];
    }
}
