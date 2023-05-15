<?php

namespace App\models\radius;

use Illuminate\Database\Eloquent\Model;

class Radgroupcheck extends Model
{

    protected $connection = 'radius';
    protected $table = 'radgroupcheck';
    protected $fillable = ['groupname','attribute','op','value'];
    public $timestamps = false;

}
