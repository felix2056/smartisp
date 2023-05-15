<?php

namespace App\Events;

use App\models\Router;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UpdatePlanEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $routers;
    public $plan_id;
    public $requestData;
    public $plan;

    /**
     * AddClientsOnNewRouter constructor.
     * @param Router $router
     */
    public function __construct($routers, $plan_id, $requestData, $plan)
    {
        $this->routers = $routers;
        $this->plan_id = $plan_id;
        $this->requestData = $requestData;
        $this->plan = $plan;
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
