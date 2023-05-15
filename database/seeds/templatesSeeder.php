<?php

namespace Database\Seeders;

use App\models\Template;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class templatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Template::updateOrCreate(
            [
                'name' => 'payment_reminder_sms'
            ],
            [
                'registered' => Carbon::today(),
                'type' => 'whatsapp',
                'system' => 1,
                'status' => 1,
                'content' => 'Señor(a) {{1}}, recordamos que su servicio de internet vence el {{2}}, Total a pagar {{3}}.'
            ]
        );
    }
}
