<?php
namespace App\models;
use App\libraries\Ipv4Subnet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AddressRouter extends Model {

	protected $table = 'address_routers';
	public $timestamps = false;


	public function getIdAttribute($value)
    {

     	    $ips = DB::table('networks')->where('networks.is_used','1')->where('address_id',$value)->count();
            //convertimos a porcentaje

            $net = $this::find($value);
            $sn = Ipv4Subnet::fromString($net->network);
            $total = $sn->getTotalHosts();
            $percent = ($ips * 100)/$total;
            $net->used = $percent;
            $net->save();

		return $value;

    }

}
