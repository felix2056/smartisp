<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PppoeSimpleQueueWithTreeUpdate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $API;
    public $con;
    public $changeIP;
    public $usedip;
    public $oldtarget;
    public $address_list;
    public $debug;
    public $error;
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
        $this->debug = $debug;
        $this->error = $error;
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
