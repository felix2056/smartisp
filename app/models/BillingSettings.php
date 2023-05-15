<?php

namespace App\models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BillingSettings extends Model
{
    protected $table = 'billing_settings';

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function getBillingDueDatesAttribute()
    {
        return Carbon::parse(Carbon::now()->year. '-'. Carbon::now()->month. '-'. $this->attributes['billing_due_date']);
    }

    public function getBillingDueDatesAttribute_2()
    {
        return Carbon::parse(Carbon::now()->year. '-'. Carbon::now()->month. '-'. $this->attributes['billing_date']);
    }
}
