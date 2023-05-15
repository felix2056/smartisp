<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionsToAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = \App\models\User::where('email', 'support@smartisp.us')->first();
        $user->permission->access_clients_editar = 1;
        $user->permission->access_clients_eliminar = 1;
        $user->permission->edit_client_balance = 1;
        $user->permission->save();
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
