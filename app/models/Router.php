<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Router extends Model {

    protected $table = 'routers';

    protected $casts = ['map_marker_icon' => 'array'];

    public function client()
    {
        return $this->hasMany(Client::class);
    }

    public function payment_records()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function boxes()
    {
        return $this->hasMany(Box::class);
    }

    public function control_router()
    {
        return $this->hasOne(ControlRouter::class, 'router_id');
    }

    public function radius()
    {
        return $this->hasOne('App\models\radius\Radius', 'router_id','id');
    }
}
