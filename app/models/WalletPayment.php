<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletPayment extends Model
{
    use HasFactory;
    
    protected $table = 'wallet_payments';
    
    protected $guarded = ['id'];
	
	public function received()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
	
	public function client()
	{
		return $this->belongsTo(Client::class);
	}
}
