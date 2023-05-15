<?php

namespace App\Events;

use App\models\Router;
use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChangeControlRouterUpdate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $router;
    public $request;
    public $controlRouter;
    public function __construct($router, $request, $controlRouter)
    {
        $this->router = $router;
        $this->request = $request;
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
