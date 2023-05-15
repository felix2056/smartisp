<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;
class BillCustomer extends Model {

    protected $table = 'bill_customers';

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentNew::class, 'num_bill', 'num_bill');
    }

    public function invoice_items()
    {
        return $this->hasMany(BillCustomerItem::class);
    }
}
