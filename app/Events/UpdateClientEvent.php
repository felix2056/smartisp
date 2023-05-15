<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UpdateClientEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $client_id;
    public $clientOldName;

    public function __construct($client_id, $clientOldName)
    {
        $this->client_id = $client_id;
        $this->clientOldName = $clientOldName;
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
