<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {

	protected $table = 'tickets';
	
	protected $guarded = ["id"];

}
