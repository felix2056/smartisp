<?php

namespace App\Http\Requests\RecurringInvoice;

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
        return [
            'start_date' => 'required',
            'frequency' => 'required|in:week,month,year',
            'pos.*' => 'required|integer',
            'quantity.*' => 'required|min:1',
            'unit.*' => 'required|min:1',
            'price.*' => 'required',
            'description.*' => 'required',
            'invoice_note' => 'required',
        ];
    }
}
