<?php

namespace App\Observers;

use App\models\ClientService;

class ClientServiceObserver
{
    public function created(ClientService $clientService)
    {
        $total = ClientService::where('plan_id', $clientService->plan_id)->where('router_id', $clientService->router_id)->get();

        if($total->count() > 0) {
            $priority = $total->count() / 128;

            if($priority < 0) {
                $clientService->tree_priority = 0;
            } else {
                $clientService->tree_priority = floor($priority);
            }
        } else {
             $clientService->tree_priority = 0;
        }

        $clientService->save();
    }
}
