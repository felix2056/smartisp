<?php

namespace App\Handlers;

use App\libraries\AddClient;
use App\libraries\CountClient;
use App\libraries\PermitidosList;
use App\libraries\StatusIp;
use App\models\AddressRouter;
use App\models\Network;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PppoeSimpleQueueWithTree implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(\App\Events\PppoeSimpleQueueWithTree $event)
    {
        set_time_limit(0);
        if ($event->API->connect($event->con['ip'], $event->con['login'], $event->con['password'])) {


            //get gateway for addres
            $network = Network::where('ip', $event->ip)->get();
            $gat = AddressRouter::find($network[0]->address_id);


            $usedip = new StatusIp();
            $counter = new CountClient();
            //marcamos como ocupada la ip
            $usedip->is_used_ip($event->ip, $event->data['service_id'], true);
            // aumentamos el contador de numero de clientes del router
            $counter->step_up_router($event->router_id);
            $counter->step_up_plan($event->plan_id);

            $PPP = $event->rocket->add_ppp_simple_queue_with_tree($event->API, $event->data, $event->ip, $gat->gateway, 'add', $event->debug);
            if($event->address_list == 1) {
                $list = PermitidosList::add($event->API,$event->data, $event->debug, $event->error);
            }

            //Desconectamos la API MIKROTIK
            $event->API->disconnect();
            //save log
//            $event->log->save("Se ha registrado un cliente:", "success", $event->client->name);

            DB::commit();

        }
    }
}
