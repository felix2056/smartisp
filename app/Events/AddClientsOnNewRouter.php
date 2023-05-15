<?php

namespace App\Events;

use App\models\Router;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AddClientsOnNewRouter
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $router;

    /**
     * AddClientsOnNewRouter constructor.
     * @param $router
     */
    public function __construct($router)
    {
        $this->router = $router;
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
