<?php
namespace App\models;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model {

	protected $table = 'global_settings';
	public $timestamps = false;

	public function factura_template()
    {
	    return $this->belongsTo(Template::class, 'invoice_template_id');
    }

}
