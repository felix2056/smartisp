<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {

	protected $table = 'payments';

	public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }
}
