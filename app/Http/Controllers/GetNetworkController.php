<?php

namespace App\Http\Controllers;
use App\models\AddressRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class GetNetworkController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {

        $net = AddressRouter::find($request->get('id', 0));
        if (is_null($net))
            return Response::json(array('success' => false));

        $data = array(
            'success' => true,
            'name' => $net->name,
            'network' => $net->network,
            'type' => $net->type
        );

        return Response::json($data);
    }

    public function postNetworks()
    {
        return Response::json(DB::table('address_routers')->select('id', 'name', 'network')->get());
    }

    public function postIp(Request $request)
    {
        $query = $request->get('search');

        $addresses = DB::table('address_routers')
            ->join('networks', function ($join) use($request) {
                $join->on('address_routers.id', '=', 'networks.address_id')
                    ->where('address_routers.router_id', '=', $request->get('rid'));
            })
            ->select('networks.id', 'networks.ip As name')->where('networks.is_used', '!=', '1')->where('networks.ip', 'LIKE', '%' . $query . '%')->take(6)->get();


        return Response::json($addresses);
    }
}
