<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CortadoReason extends Model
{
    use HasFactory;
    
    protected $table = 'cortado_reasons';
    
    public function service()
    {
    	return $this->belongsTo(ClientService::class, 'service_id');
    }
}
