<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;
class TicketViewColumn extends Model {

	protected $table = 'ticket_column_view';
	
	protected $guarded = [
		"id"
	];
}
