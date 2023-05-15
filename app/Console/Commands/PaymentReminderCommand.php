<?php

namespace App\Console\Commands;

use App\Http\Controllers\CrtokenController;
use App\models\GlobalSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\libraries\Helpers;
use SMSGatewayMe\Client\Configuration;

use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\ApiClient;
use App\models\ClientService;
use App\Service\CommonService;
use Twilio\Rest\Client;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\GetPlan;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Psms;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;

use App\models\Clienttbl;
use App\models\ControlRouter;
use App\models\Sms;
use App\models\SuspendClient;
use App\models\Template;
use App\models\TempSms;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class PaymentReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'day (defined in config)  before, send payment reminder over email or twilio sms or twilio whatsapp sms ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0); //unlimited execution time php
        $global = GlobalSetting::all()->first();
        $prebill = $global->send_prebill;
        $presms = $global->send_presms;
        $prewhatsapp = $global->send_prewhatsapp;
        $prewhatsappcloudapi = $global->send_prewhatsappcloudapi;
        $prewaboxapp = $global->send_prewaboxapp;
        $hrs = $global->send_hrs;
        $cronApi = new CrtokenController();

        //send email or sms
        if ($prebill == 1 || $presms == 1 || $prewhatsapp == 1 || $prewaboxapp == 1 || $prewhatsappcloudapi == 1) {
            if ($hrs == date('H:i') . ':00') { //verificamos la hora para el envio
                //obtenemos todos los clientes planes routers y control
                
                $clients = DB::table('client_services')
                    ->join('clients', 'clients.id', '=', 'client_services.client_id')
                    ->join('plans', 'plans.id', '=', 'client_services.plan_id')
                    ->join('routers', 'routers.id', '=', 'client_services.router_id')
                    ->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
                    ->join('control_routers', 'control_routers.router_id', '=', 'client_services.router_id')
                    ->select('clients.name', 'clients.balance', 'client_services.status As stclient', 'client_services.ip As ipclient', 'clients.email As clientEmail', 'client_services.mac',
                        'plans.name As plan_name', 'plans.cost', 'plans.iva', 'routers.ip As iprouter', 'routers.name As routername',
                        'routers.login', 'routers.password', 'routers.lan', 'control_routers.type_control', 'control_routers.arpmac', 'clients.dni',
                        'control_routers.adv', 'client_services.router_id As routerid', 'clients.id As client_id', 'clients.address', 'clients.phone', 'billing_settings.billing_grace_period')->where('client_services.status', 'ac')
                    ->where('clients.balance', '<', 0)
                    ->get();

                $days = $global->before_days;
                $prebill = $global->send_prebill;
                $company = $global->company;
                $tol = $global->tolerance;
                $money = $global->nmoney;

                $subject = "Esto es un pre Aviso de corte de servicio";
                //Iniciamos las clases principales
                //for sms gateway.me
                if ($presms == 1) {
                    $smsg = Helpers::get_api_options('smsgateway');

                    if (count($smsg) > 0) {
                        if ($smsg['e'] == '1') {
                            $config = Configuration::getDefaultConfiguration();
                            $config->setApiKey('Authorization', $smsg['t']);
                            $apiClient = new ApiClient($config);
                            $messageClient = new MessageApi($apiClient);
                        }
                    }
                }
                //verificamos si hay clientes para cortar o notificar
                foreach ($clients as $client) {


                    //inicializamos variables principales
                    $cutdate = CommonService::getCortadoDateWithTolerence($client->client_id, $client->billing_grace_period, $global->tolerance);

                    $name = $client->name;

                    $newAdvice = strtotime('-' . $days . ' day', strtotime($cutdate));
                    $dc = date('Y-m-d', $newAdvice);

                    if ($dc <= date('Y-m-d') && $cutdate >= date('Y-m-d')) {

                        //data general para las plantillas email sms
                        $timestamp = strtotime($cutdate);
                        $cutday = strtotime('+' . $tol . ' day', strtotime($cutdate));

                        $Totalcost = $client->balance + ($client->iva * ($client->balance / 100));
                        $Totalcost = round($Totalcost, 2);

                        $data = array(
                            "empresa" => $company,
                            "cliente" => $name,
                            "vencimiento" => date("d/m/Y", $timestamp),
                            "corte" => date('d/m/Y', $cutday),
                            "plan" => $client->plan_name,
                            "costo" => $client->balance,
                            "total" => $Totalcost,
                            "moneda" => $money,
                            "Smoneda" => $global->smoney,
                            "emailCliente" => $client->clientEmail,
                            "direccionCliente" => $client->address,
                            "telefonoCliente" => $client->phone,
                            "dniCliente" => $client->dni
                        );
                        //enviamos el email si este esta activo
                        if ($prebill == 1) {
                            if (!empty($client->clientEmail)) {
                                $emails = $client->clientEmail;
                                try {
                                    Mail::send('templates.Recordatorio_de_pago_email', $data, function ($mesage) use ($emails, $subject, $company, $global) {
                                        $mesage->to([$emails])
                                            ->from($global->email, $company)
                                            ->subject($subject)
                                            ->getSwiftMessage()
                                            ->getHeaders()
                                            ->addTextHeader('X-Special-Header', 'Just for skip duplicates');
                                    });
                                } catch (\Exception $exception) {
                                    throw $exception;
                                }

                            }
                        }

                        if ($presms == 1) {

                            //configuramos el envio de mensajes
                            $sms = Helpers::get_api_options('twiliosms');
                            if (count($sms) > 0) {
                                if ($sms['e'] == '1') {
                                    if (!empty($client->phone)) {
                                        //enviamos el mensaje
                                        $messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
                                        $global = GlobalSetting::all()->first();
                                        $phone = '+' . $global->phone_code . $client->phone;
                                        $message_dtl = $cronApi->send_twilio_sms($phone, $messagetem);
                                    }
                                }
                            }
                        }

                        if ($prewhatsapp == 1) {

                            //configuramos el envio de mensajes
                            $smsw = Helpers::get_api_options('twiliowhatsappsms');
                            if (count($smsw) > 0) {
                                if ($smsw['e'] == '1') {
                                    if (!empty($client->phone)) {
                                        //enviamos el mensaje
                                        $messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
                                        $global = GlobalSetting::all()->first();
                                        $phone = '+' . $global->phone_code . $client->phone;
                                        $message_dtl = $cronApi->send_twilio_whatsapp($phone, $messagetem);
                                    }
                                }
                            }
                        }

                        if ($prewhatsappcloudapi == 1) {

                            if (!empty($client->phone)) {
                                //enviamos el mensaje
                                $global = GlobalSetting::all()->first();
                                $phone = $global->phone_code . $client->phone;
                                $template = Template::where('name', 'payment_reminder_sms')->first();
                                $message_dtl = $cronApi->send_whatsappcloudapi_sms($phone, $template->provider_template_name, $global->locale, $data['vencimiento'], $data['costo'], $client->name);
                            }
                        }

                        if ($prewaboxapp == 1) {

                            //configuramos el envio de mensajes
                            $smsw = Helpers::get_api_options('weboxapp');

                            if (count($smsw) > 0) {
                                if ($smsw['e'] == '1') {
                                    if (!empty($client->phone)) {
                                        //enviamos el mensaje
                                        $messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
                                        $global = GlobalSetting::all()->first();
                                        $phone = '+' . $global->phone_code . $client->phone;

                                        $message_dtl = $cronApi->send_waboxapp_message($phone, $messagetem);
                                    }
                                }
                            }
                        }


                        //enviamos el sms si este esta activo
                        if ($presms == 1) {
                            //verificamos los gateways
                            $sms = Helpers::get_api_options('modem');

                            if (count($sms) > 0) {

                                if ($sms['e'] == '1') {
                                    //solo enviamos sms a los clientes que tengan un telefono registrado
                                    if (!empty($client->phone)) {
                                        //recuperamos la plantilla
                                        $messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
                                        $process = new Chkerr();
                                        //get connection data for login ruter
                                        $router = new RouterConnec();
                                        $con = $router->get_connect($sms['r']);
                                        $conf = Helpers::get_api_options('mikrotik');
                                        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                                        $API->debug = $conf['d'];
                                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                                            $phone = '+' . $global->phone_code . $client->phone;
                                            Psms::send_sms($API, $sms['p'], $sms['c'], $phone, $messagetem);

                                        }

                                        $API->disconnect();
                                    }//end if
                                }//end if
                            }//end if

                            // if ($smsg['e']=='1') {
                            // //solo enviamos sms a los clientes que tengan un telefono registrado
                            // if (!empty($client->phone)) {
                            // //recuperamos la plantilla
                            // $messagetem = View::make('templates.Recordatorio_de_pago_sms',$data)->render();
                            // //recuperamos información del gateway
                            // $number = '+'.$global->phone_code.$client->phone;
                            // // Sending a SMS Message
                            // $sendMessageRequests[] = new SendMessageRequest([
                            // 'phoneNumber' => $number,
                            // 'message' => "$messagetem",
                            // 'deviceId' => $smsg['d']
                            // ]);

                            // }//end if
                            // }//end if

                        }//end if presms

                    }//end if
                } //end foreach

                //enviamos loes emails y sms

                //send sms for smsgateway.me
                if ($presms == 1) {
                    if (count($smsg) > 0) {
                        if ($smsg['e'] == '1') {
                            if (isset($sendMessageRequests)) {
                                if (count($sendMessageRequests) > 0) {
                                    $sendMessages = $messageClient->sendMessages($sendMessageRequests);
                                }
                            }
                        }
                    }
                }

            }//end if hrs
        }//end if send or not send prebill
    }
}
