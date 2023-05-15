<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
