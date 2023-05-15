<?php

namespace App\models;

use App\Observers\ClientServiceObserver;
use Illuminate\Database\Eloquent\Model;

class ClientService extends Model
{
    protected $table = 'client_services';
    protected $dates = ['date_in'];
    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'geo_json' => 'array',
        'geo_json_styles' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::observe(ClientServiceObserver::class);
    }

    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function plan() {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function router() {
        return $this->belongsTo(Router::class, 'router_id', 'id');
    }

    public function suspend_client() {
        return $this->hasOne(SuspendClient::class, 'service_id', 'id');
    }
}
