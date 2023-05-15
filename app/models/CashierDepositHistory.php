<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierDepositHistory extends Model
{
    use HasFactory;
    
    protected $table = 'cashier_deposit_history';
    
    public function client()
    {
    	return $this->belongsTo(Client::class, 'client_id');
    }
}
