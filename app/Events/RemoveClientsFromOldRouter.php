<?php

namespace App\Events;

use App\models\Router;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Support\Facades\Log;

class RemoveClientsFromOldRouter
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $router;
    public $controlRouter;


    /**
     * RemoveClientsFromOldRouter constructor.
     * @param $router
     * @param $controlRouter
     */
    public function __construct($router, $controlRouter)
    {
        $this->router = $router;
        $this->controlRouter = $controlRouter;
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
