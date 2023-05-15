<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSat extends Model
{
    protected $table = 'invoice_sat';
    protected $fillable = [
        'bill_customers_id',
        'uuid',
        'status',
        'verification_url',
        'json_response',
    ];
}
