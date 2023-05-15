<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Client extends Model {

	protected $table = 'clients';
	public $timestamps = false;

    protected $casts = [
        'map_marker_icon' => 'array',
        'odb_geo_json' => 'array',
        'odb_geo_json_styles' => 'array'
    ];

	protected $guarded = [
	    "id"
    ];

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

    public function recurring_invoices()
    {
        return $this->hasMany(RecurringInvoice::class, 'client_id');
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function service()
    {
        return $this->hasMany(ClientService::class, 'client_id');
    }

    public function currentMonthInvoice()
    {
        return $this->hasMany(BillCustomer::class)->whereMonth('release_date', Carbon::now()->month);
    }

}
