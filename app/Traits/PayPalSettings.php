<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use App\models\GlobalSetting;

trait PayPalSettings{
    public function setPayPalConfigs(){
        $settings = GlobalSetting::first();

        $paypal_client_id       = ($settings->paypal_client_id)? $settings->paypal_client_id : env('PAYPAL_CLIENT_ID');

        $paypal_secret = ($settings->paypal_secret)? $settings->paypal_secret : env('PAYPAL_SECRET');

        $paypal_mode = ($settings->paypal_mode)? $settings->paypal_mode : env('PAYPAL_MODE');

        Config::set('paypal.client_id', $paypal_client_id);
        Config::set('paypal.secret', $paypal_secret);
        Config::set('paypal.settings.mode', $paypal_mode);
    }
}

