<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model {

	protected $table = 'plans';
	public $timestamps = false;
	protected $guarded = ['id'];

	public function clients()
	{
		//un plan tiene uno o muchos clientes
		return $this->hasMany('Client');
	}

	public function smart_bandwidth()
    {
        return $this->hasOne(SmartBandwidth::class, 'plan_id');
    }
}
