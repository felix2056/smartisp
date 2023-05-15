<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class BillCustomerItem extends Model
{
    protected $table = 'bill_customer_item';

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(BillCustomer::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

}
