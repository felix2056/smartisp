<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoiceItem extends Model
{
    protected $table = 'recurring_invoice_items';

    protected $guarded = [
        'id'
    ];

    public function items()
    {
        return $this->belongsTo(RecurringInvoice::class, 'recurring_invoice_id', 'id');
    }
}
