<?php

namespace App\Http\Requests\Admin\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class ChangeAssigneeRequest extends FormRequest
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
        	'user_id' => 'required|exists:users,id'
        ];
    }
    
    public function messages()
    {
	    return [
	    	'user_id.required' => 'Please choose a assignee.',
	    	'user_id.exists' => 'This assignee is not exists.'
	    ];
    }
	
}
