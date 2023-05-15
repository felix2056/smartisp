<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class GlobalApi extends Model {

	protected $table = 'global_apis';
	public $timestamps = false;

	protected $fillable = [
		'name', 'options', 'status'
	];
}
