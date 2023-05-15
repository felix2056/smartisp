<?php

namespace App\Http\Controllers;
use App\libraries\Ipbin;
use App\libraries\Ipv4Subnet;
use App\models\AddressRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetNetworkInfoController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {

        $info = AddressRouter::find($request->get('id', 0));
        if (is_null($info))
            return Response::json(array('success' => false));

        $sn = Ipv4Subnet::fromString($info->network);

        $data = array(
            'success' => true,
            'address' => $sn->getNetwork(),
            'gateway' => $info->gateway,
            'network' => $info->network,
            'maskbit' => $info->mask,
            'maskadd' => $sn->getNetmask(),
            'classip' => Ipbin::getclassip($info->mask),
            'hostrange' => $sn->getFirstHostAddr() . ' - ' . $sn->getLastHostAddr(),
            'broadcast' => $sn->getBroadcastAddr(),
            'totalips' => $sn->getTotalHosts(),
            'binary' => Ipbin::ip2bin($sn->getNetwork())

        );

        return Response::json($data);
    }
}
