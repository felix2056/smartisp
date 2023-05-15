<?php
namespace App\libraries;
use App\models\GlobalSetting;
use App\models\Logg;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

/**
 * Guardar Logs
 */
class SmartOLT
{
    protected $api_key;
    protected $link_smartolt;
    protected $global;

    public function __construct()
    {
        $smartolt =  Helpers::get_api_options('smartolt');
        /**vemos si termina o no con /**/
        $this->link_smartolt = "";
        if(substr($smartolt['l'], -1) == '/')
            $this->link_smartolt = $smartolt['l'];
        else
            $this->link_smartolt = $smartolt['l'].'/';

        $this->api_key = $smartolt['a'];
        $this->global = GlobalSetting::all()->first();

    }

    public function getLink(){
        return $this->link_smartolt;
    }

    public function consumir_api_smartolt($metodo,$url,$parametros = null){
        $client = new Client();
        try {
            $res = $client->request($metodo, $this->link_smartolt.$url, [
                'headers' => [
                    'X-Token' => $this->api_key
                ],
                'form_params' => $parametros
            ]);
            $status = $res->getStatusCode();

            if($status != 200)
                return redirect('/smartolt')->with('smart_olt_error','Unexpected HTTP status: ' . $res->getStatus() . ' ' .$res->getReasonPhrase());

            return $res;

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $res = json_decode(explode("\n",$e->getMessage())[1]);
            return $res;
        }
        catch (\GuzzleHttp\Exception\RequestException $e) {
            $res = json_decode(explode("\n",$e->getMessage())[1]);
            return $res;

        }

       // exit(header("Location: /smartolt_check_status"));

    }

}
