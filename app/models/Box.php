<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;
class Box extends Model {

	public $timestamps = false;
	protected $table = 'boxes';

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
