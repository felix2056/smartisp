<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SimpleQueueWithTreeUpdate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $API;
    public $con;
    public $ip;
    public $client;
    public $router_id;
    public $rocket;
    public $address_list;
    public $log;
    public $error;
    public $debug;
    public $oldtarget;
    public $changeIP;
    public $usedip;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $API, $con,$changeIP,$usedip,$oldtarget,$address_list,$debug,$error)
    {
        $this->data = $data;
        $this->API = $API;
        $this->con = $con;
        $this->changeIP = $changeIP;
        $this->usedip = $usedip;
        $this->oldtarget = $oldtarget;
        $this->address_list = $address_list;
        $this->error = $error;
        $this->debug = $debug;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [];
    }
}
