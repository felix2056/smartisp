<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentNew extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function received()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'num_bill', 'num_bill');
    }
}
