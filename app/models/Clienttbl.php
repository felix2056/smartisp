<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Clienttbl extends Model {

	protected $table = 'clients';
	public $timestamps = false;

	public function plan()
	{
		//uno o muchos planes tienen mucho clientes
		return $this->belongsTo(Plan::class);
    }

    public function billing_settings()
    {
        return $this->hasOne(BillingSettings::class, 'client_id');
    }

    public function suspend_client()
    {
        return $this->hasOne(SuspendClient::class, 'client_id');
    }

    public function getDateInAttribute()
    {
        return Carbon::parse($this->attributes['date_in']);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function invoices()
    {
        return $this->hasMany(BillCustomer::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function currentMonthInvoice()
    {
        return $this->hasMany(BillCustomer::class)->whereMonth('release_date', Carbon::now()->month);
    }

}
