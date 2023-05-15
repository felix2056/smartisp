<?php

namespace App\Http\Controllers;
use App\DataTables\NetworkDataTable;
use App\libraries\Ipv4Subnet;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\AddressRouter;
use App\models\GlobalSetting;
use App\models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class NetworkController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex(NetworkDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_pays;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
                
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('networks.index', $permissions);

        } else
            return Redirect::to('admin');
    }

    //metodo para listar planes simple queues
    public function postList()
    {
        $networks = AddressRouter::all();
        return Response::json($networks);
    }

    //metodo para registrar IP/redes
    public function postCreate(Request $request)
    {

        $friendly_names = array(
            'name' => __('app.name'),
            'network' => __('app.net'),
            'mask' => __('app.mask'),
            'type' => __('app.type')
        );

        $rules = array(
            'name' => 'required',
            'network' => 'required|unique:address_routers',
            'mask' => 'required',
            'type' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        //control de red
        $net = $request->get('network') . '/' . $request->get('mask');
        $sn = Ipv4Subnet::fromString($net);
        $net = $sn->getNetwork() . '/' . $request->get('mask');

        if (DB::table('address_routers')->where('network', $net)->count() > 0)
            return Response::json(['msg' => 'duplicate']);

        $log = new Slog();

        // registramos y generamos la ip
        $id = DB::table('address_routers')->insertGetId(
            array('name' => $request->get('name'), 'router_id' => '0', 'gateway' => $sn->getFirstHostAddr(),
                'type' => $request->get('type'), 'mask' => $request->get('mask'), 'network' => $net,
                'hosts' => $sn->getTotalHosts()
            ));

        $ips = $sn->getIterator();

        //iteramos y registramos todas las redes ip
        foreach ($ips as $addr) {
            DB::table('networks')->insert(['address_id' => $id, 'ip' => $addr, 'is_used' => '0', 'client_id' => '0']);
        }
        //registramos en logs
        $log->save("Se ha agredo una nueva IP/red:", "info", $net);

        return json_encode(array('msg' => 'success'));

    }

    public function postUpdate(Request $request)
    {

        $net_id = $request->get('netid');

        $friendly_names = array(
            'name' => __('app.name'),
            'edit_type' => __('app.type'),
            'netid' => 'ID'
        );

        $rules = array(
            'name' => 'required|unique:address_routers,name,' . $net_id,
            'edit_type' => 'required',
            'netid' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        $network = AddressRouter::find($net_id);
        $network->name = $request->get('name');
        $network->type = $request->get('edit_type');
        $network->save();

        return Response::json(array('msg' => 'success'));

    }

    public function postDelete(Request $request)
    {

        //eliminamos
        $net = AddressRouter::find($request->get('id'));
        //coprobamos si no existe debuelve null
        if (is_null($net))
            return Response::json(array('msg' => 'notfound'));

        $cnet = Network::where('address_id', $request->get('id'))->where('is_used', '1')->count();
        if ($cnet > 0) {
            # Existen ips ocupados por clientes no se puede eliminar
            return Response::json(array('msg' => 'inused'));
        } else {
            //verificamos que no tenga ips utilizadas
            $net->delete();
            //eliminamos todas sus redes ip
            Network::where('address_id', $request->get('id'))->delete();
            return json_encode(array('msg' => 'success'));
        }

    }


}
