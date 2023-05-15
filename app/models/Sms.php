<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model {

	public $timestamps = false;
	protected $table = 'sms';

	protected $fillable = [
		'client',
		'phone',
		'gateway',
		'message',
		'type',
		'received_at'
	];

}
