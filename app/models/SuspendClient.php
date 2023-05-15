<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class SuspendClient extends Model {

	protected $table = 'suspend_clients';
	public $timestamps = false;

	protected $dates = ['expiration'];

}
