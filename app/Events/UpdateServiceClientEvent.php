<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UpdateServiceClientEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $oldServiceDetails;

    public function __construct($data, $oldServiceDetails)
    {
        $this->data = $data;
        $this->oldServiceDetails = $oldServiceDetails;
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
