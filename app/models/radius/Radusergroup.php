<?php

namespace App\models\radius;

use Illuminate\Database\Eloquent\Model;

class Radusergroup extends Model
{
    protected $connection = 'radius';
    protected $table = 'radusergroup';
    protected $fillable = ['username','groupname','priority'];
    public $timestamps = false;
}
