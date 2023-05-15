<?php

namespace App\Http\Requests\Admin\Inventory\Item;

use Illuminate\Foundation\Http\FormRequest;

class ReturnItemRequest extends FormRequest
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
            'status' => 'required|in:In Stock,Returned',
            'mark' => 'required|in:Used,New,Broken',
            'notes' => 'required',
        ];
    }
}
