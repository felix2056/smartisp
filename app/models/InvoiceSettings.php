<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSettings extends Model
{
    protected $table = 'invoice_setting';
    protected $fillable = [
        'is_live',
        'is_active',
        'apikey',
        'apikey_sandbox',
        'rfc',
        'product_code',
        'unit_code',
        'provider_name',
        'serie',
        'folio'
    ];
}
