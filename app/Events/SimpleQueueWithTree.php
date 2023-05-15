<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SimpleQueueWithTree
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $API;
    public $con;
    public $ip;
    public $client;
    public $plan_id;
    public $router_id;
    public $rocket;
    public $address_list;
    public $log;
    public $error;
    public $debug;
    public $usedip;
    public $counter;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $API, $con, $ip,$usedip, $client, $plan_id, $router_id, $rocket, $address_list, $log, $error, $debug,$counter)
    {
        $this->data = $data;
        $this->API = $API;
        $this->con = $con;
        $this->ip = $ip;
        $this->usedip = $usedip;
        $this->client = $client;
        $this->plan_id = $plan_id;
        $this->router_id = $router_id;
        $this->rocket = $rocket;
        $this->address_list = $address_list;
        $this->log = $log;
        $this->error = $error;
        $this->debug = $debug;
        $this->counter = $counter;
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
