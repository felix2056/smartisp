<?php

namespace App\Handlers;

use App\libraries\AddClient;
use App\libraries\CountClient;
use App\libraries\PermitidosList;
use App\libraries\RocketCore;
use App\libraries\StatusIp;
use App\models\AddressRouter;
use App\models\Network;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SimpleQueueWithTree implements ShouldQueue
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
    public function handle(\App\Events\SimpleQueueWithTree $event)
    {
        set_time_limit(0);
        if ($event->API->connect($event->con['ip'], $event->con['login'], $event->con['password'])) {


            //marcamos como ocupada la ip
            $event->usedip->is_used_ip($event->ip, $event->data['service_id'], true);
            // aumentamos el contador de numero de clientes del router
            $event->counter->step_up_router($event->router_id);
            // aumentamos el contador del plan
            $event->counter->step_up_plan($event->plan_id);
            //					$expclient->exp($id,$event->router_id,$pay_date);

            $SQUEUES = $event->rocket->add_simple_queue_with_tree($event->API, $event->data, $event->ip, 'add', $event->debug);
            if($event->address_list == 1) {
                $list = PermitidosList::add($event->API,$event->data, $event->debug, $event->error);
            }

            //Desconectamos la API MIKROTIK
            $event->API->disconnect();

            //save log
            $event->log->save("Se ha registrado un cliente:", "success", $event->client->name);

        }

    }
}
