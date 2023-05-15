<?php

namespace App\Events;

use App\models\ClientService;
use App\models\Router;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeleteServiceClientEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $service;
    public $client;
    public $data;
    public $authUser;

    /**
     * AddClientsOnNewRouter constructor.
     * @param $service
     * @param $data
     * @param $authUser
     */
    public function __construct($service, $client, $data, $authUser)
    {
        $this->service = $service;
        $this->client = $client;
        $this->data = $data;
        $this->authUser = $authUser;
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
