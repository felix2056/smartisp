<?php

namespace App\models\radius;

use Illuminate\Database\Eloquent\Model;

class Radius extends Model
{

    //protected $connection = 'radius';
    protected $table = 'radius';
    protected $fillable = ['secret','router_id'];
    public $timestamps = false;

}
