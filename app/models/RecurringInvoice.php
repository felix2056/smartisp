<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    protected $table = 'recurring_invoices';

    protected $guarded = ['id'];

    protected $dates = [
        'start_date',
        'end_date',
        'next_pay_date',
        'expiration_date',
    ];

    public function items()
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
