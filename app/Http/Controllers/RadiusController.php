<?php

namespace App\Http\Controllers;

use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\models\radius\Nas;
use App\models\radius\Radreply;


class RadiusController extends Controller {

    public function index(){

      /*  $radreply = Radreply::all();
        dd($radreply);

        dd(\DB::connection('radius'));
        $radreply = \DB::connection('radius')->select("SELECT * FROM radreply")->get();
        dd($radreply);*/

        /*$radreply_ip = Radreply::create([
            'username' => "pepe",
            'attribute' => 'Session-Timeout',
            'op' => '=',
            'value' => "60"
        ]);*/
        //Radreply::where('username','pepe')->delete();

        $router_con = new RouterConnect();
        $con = $router_con->get_connect(2);

        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);

        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];
        $secret = md5($con['name']);

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            /**agregar radius**/
            /*$API->write("/radius/add",false);
            $API->write("=service=login,ppp",false);
            $API->write("=secret=".$secret,false);
            $API->write('=address='.env('RADIUS_SERVER'),true); // IP
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            $API->write("/ppp/aaa/set",false);
            $API->write("=use-radius=yes",false);
            $API->write("=use-circuit-id-in-nas-port-id=yes",false);
            $API->write("accounting=yes",true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);*/

            /**eliminar radius**/
            /*$API->write("/radius/print",false);
            $API->write('?address='.env('RADIUS_SERVER'),true); // IP
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            foreach($ARRAY as $radius){
                $id = $radius['.id'];
                $API->write("/radius/remove",false);
                $API->write("=.id=".$id,true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
            }*/

            /** configurar de radius en nmkt**/
            /*$API->write("/ppp/aaa/set",false);
            $API->write("=use-radius=no",false);
            $API->write("=use-circuit-id-in-nas-port-id=no",false);
            $API->write("accounting=yes",true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);*/




            /*$API->write("/ppp/profile/print",false);
            $API->write('?name=default',true); // IP
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            foreach($ARRAY as $radius){
                $id = $radius['.id'];
                $API->write("/ppp/profile/set",false);
                $API->write("=.id=".$id,false);
                $API->write("=local-address=1.1.1.1",true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
            }*/


            die();


            /**update radius*/
            $API->write("/radius/print",false);
            $API->write('?address='.env('DB_HOST_RADIUS'),true); // IP
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            foreach($ARRAY as $radius){
                $id = $radius['.id'];
                $API->write("/radius/set",false);
                $API->write("=.id=".$id,false);
                $API->write("=secret=6",true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                dd($ARRAY);
            }
            return $ARRAY;
        }



dd(8);
/*        $nas_name = Nas::create([
            'nasname' => $con['name'],
            'shortname' => $con['name'],
            'secret' =>$secret
        ]);

        $conexion=ssh2_connect(env('RADIUS_SERVER'), 22);
        ssh2_auth_password($conexion, env('RADIUS_USER'), env('RADIUS_PASS'));

        $ejecutar = "echo SmartISP77ac | sudo -S sudo -u root sudo service freeradius restart";
        $comando= ssh2_exec($conexion,$ejecutar);
*/

        dd($comando);

        /**TODO

         * antes de hacer esto por cada cliente debemos:
         * crear el nas en la bd de radius // todo listo
         * reiniciar el servicio de freradius para que tome el nas // todo listo
         * habilitar pppoe use radius por api de mkt // todo listo
         * crear radius en mkt (acordarse del secret) // todo listo

         **/

    }

}
