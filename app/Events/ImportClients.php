<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ImportClients
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $router;

    /**
     * ImportClients constructor.
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
