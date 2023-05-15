<?php

namespace App\models\radius;

use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{

    protected $connection = 'radius';
    protected $table = 'nas';
    protected $fillable = ['nasname','shortname','type','ports','secret','server','community','description'];
    public $timestamps = false;

}
