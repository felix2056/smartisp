<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use App\models\GlobalSetting;

trait StripeSettings{
    public function setStripeConfigs(){
        $settings = GlobalSetting::first();
        $stripe_key       = ($settings->stripe_key)? $settings->stripe_key : env('STRIPE_KEY');
        $stripe_secret = ($settings->stripe_secret)? $settings->stripe_secret : env('STRIPE_SECRET');

        Config::set('services.stripe.key', $stripe_key);
        Config::set('services.stripe.secret', $stripe_secret);
    }
}

