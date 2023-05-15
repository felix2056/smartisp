<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PppoeSimpleQueueWithTree
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
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $API, $con, $ip, $client, $plan_id, $router_id, $rocket, $address_list, $log, $error, $debug)
    {
        $this->data = $data;
        $this->API = $API;
        $this->con = $con;
        $this->ip = $ip;
        $this->client = $client;
        $this->plan_id = $plan_id;
        $this->router_id = $router_id;
        $this->rocket = $rocket;
        $this->address_list = $address_list;
        $this->log = $log;
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
