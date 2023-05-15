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

class SimpleQueueWithTreeUpdate implements ShouldQueue
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
    public function handle(\App\Events\SimpleQueueWithTreeUpdate $event)
    {
        set_time_limit(0);
        if($event->API->connect($event->con['ip'], $event->con['login'], $event->con['password'])){

            //actualizamos el estado del ip en IP/Redes
            if ($event->changeIP) { //esta cambiando de IP
                //desactivamos la IP anterior
                $event->usedip->is_used_ip($event->oldtarget,$event->data['client_id'],false);
                //activamos la nueva IP
                $event->usedip->is_used_ip($event->data['newtarget'],$event->data['client_id'],true);
            }
            else{ //no esta cambiando la ip pero actualizamos el estado.
                $event->usedip->refresh_ip($event->oldtarget,$event->data['client_id']);
            }

        }

        $rocket = new RocketCore();

        $UPDATE = $rocket->update_simple_queue_with_tree($event->API,$event->data,$event->oldtarget,$event->data['newtarget'],$event->debug);

        if($event->address_list == 1) {
            $list = PermitidosList::add($event->API,$event->data, $event->debug, $event->error);
        }
    }
}
