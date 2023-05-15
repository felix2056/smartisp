<?php

namespace App\Http\Resources;

use http\Client;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $plans = [];
        $routers = [];
        if($this->client) {
            foreach($this->client->service as $service) {
                $plans[] = $service->plan->name;
                $routers[] = $service->router->name;
            }
        }

      $data = [
        'id' => $this->id,
        'detail' => $this->client ? implode(',', $plans) : $this->detail,
        'typepay' => $this->way_to_pay,
        'date' => $this->date,
        'amount' => $this->amount,
        'client' => $this->client ? $this->client->name : $this->name,
        'user' => $this->user ? $this->user->name : ($this->received ? $this->received->name : ''),
    ];

    return $data;
}
}
