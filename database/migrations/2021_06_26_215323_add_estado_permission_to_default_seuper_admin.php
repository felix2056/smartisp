<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoPermissionToDefaultSeuperAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = \App\models\User::with('permission')->where('email', 'support@smartisp.us')->first();

        if($user) {
            $user->permission()->update([
                'estado_financier' => 1
            ]);
        }

        $user = \App\models\User::with('permission')->where('email', 'default@example.com')->first();
        if($user) {
            $user->permission()->update([
                'estado_financier' => 1
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
