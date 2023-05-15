<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCredit extends Model
{
    use HasFactory;
    
    public function user()
    {
    	return $this->belongsTo(User::class, 'user_id');
    }
}
