<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class OdbSplitter extends Model
{
    protected $table = 'odb_splitter';

    protected $casts = ['map_marker_icon' => 'array'];
}
