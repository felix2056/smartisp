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

class AddServiceClientEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $router_id;
    public $client_id;
    public $requestData;
    public $data;
    public $authUser;

    /**
     * AddServiceClientEvent constructor.
     * @param $router_id
     * @param $client
     * @param $requestData
     * @param $data
     * @param $authUser
     */
    public function __construct($router_id, $client_id, $requestData, $data, $authUser)
    {
        $this->router_id = $router_id;
        $this->client_id = $client_id;
        $this->requestData = $requestData;
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
