<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model {

    protected $table = 'payment_records';

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

}
