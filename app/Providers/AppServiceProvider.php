<?php

namespace App\Providers;

use App\Events\AddClientsOnNewRouter;
use App\Events\AddServiceClientEvent;
use App\Events\ChangeControlRouterUpdate;
use App\Events\DeleteServiceClientEvent;
use App\Events\ImportClients;
use App\Events\PppoeSimpleQueueWithTree;
use App\Events\PppoeSimpleQueueWithTreeUpdate;
use App\Events\RemoveClientsFromOldRouter;
use App\Events\SimpleQueueWithTree;
use App\Events\SimpleQueueWithTreeUpdate;
use App\Events\UpdateClientEvent;
use App\Events\UpdatePlanEvent;
use App\Events\UpdateServiceClientEvent;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
        $settings = GlobalSetting::first();
        if($settings) {
            App::setLocale($settings->locale);
        }
        \Event::listen(SimpleQueueWithTree::class, \App\Handlers\SimpleQueueWithTree::class);
        \Event::listen(SimpleQueueWithTreeUpdate::class, \App\Handlers\SimpleQueueWithTreeUpdate::class);
        \Event::listen(PppoeSimpleQueueWithTree::class, \App\Handlers\PppoeSimpleQueueWithTree::class);
        \Event::listen(PppoeSimpleQueueWithTreeUpdate::class, \App\Handlers\PppoeSimpleQueueWithTreeUpdate::class);
        \Event::listen(AddClientsOnNewRouter::class, \App\Handlers\AddClientsOnNewRouters::class);
        \Event::listen(RemoveClientsFromOldRouter::class, \App\Handlers\RemoveClientsFromOldRouters::class);
        \Event::listen(ChangeControlRouterUpdate::class, \App\Handlers\ChangeControlRouterUpdate::class);
        \Event::listen(UpdatePlanEvent::class, \App\Handlers\UpdatePlanHandler::class);
        \Event::listen(AddServiceClientEvent::class, \App\Handlers\AddServiceClientHandler::class);
        \Event::listen(DeleteServiceClientEvent::class, \App\Handlers\DeleteServiceClientHandler::class);
        \Event::listen(UpdateServiceClientEvent::class, \App\Handlers\UpdateServiceClientHandler::class);
        \Event::listen(UpdateClientEvent::class, \App\Handlers\UpdateClientHandler::class);
        \Event::listen(ImportClients::class, \App\Handlers\ImportClients::class);
    }
}
