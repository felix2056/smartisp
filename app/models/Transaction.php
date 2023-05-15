<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $guarded = [];

    public function client()
	{
		//uno o muchos planes tienen mucho clientes
		return $this->belongsTo(Client::class);
    }
}
