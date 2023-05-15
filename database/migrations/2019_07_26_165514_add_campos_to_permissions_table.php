<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\Permission;
class AddCamposToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->integer('facturacion')->nullable();

            $table->integer('tran_facturacion_editar')->nullable();
            $table->integer('tran_facturacion_eliminar')->nullable();

            $table->integer('factura_pagar')->nullable();
            $table->integer('factura_editar')->nullable();
            $table->integer('factura_eliminar')->nullable();

            $table->integer('pagos_nuevo')->nullable();
            $table->integer('pagos_editar')->nullable();
            $table->integer('pagos_eliminar')->nullable();

            $table->integer('finanzas')->nullable();

            $table->integer('tran_finanzas_editar')->nullable();
            $table->integer('tran_finanzas_eliminar')->nullable();

            $table->integer('factura_finanzas_pagar')->nullable();
            $table->integer('factura_finanzas_editar')->nullable();
            $table->integer('factura_finanzas_eliminar')->nullable();

            $table->integer('pagos_finanzas_editar')->nullable();
            $table->integer('pagos_finanzas_eliminar')->nullable();

            $table->timestamps();
        });

        $superadmin = Permission::find(1);
        $superadmin->facturacion=1;
        $superadmin->tran_facturacion_editar=1;
        $superadmin->tran_facturacion_eliminar=1;
        $superadmin->factura_pagar=1;
        $superadmin->factura_editar=1;
        $superadmin->factura_eliminar=1;
        $superadmin->pagos_nuevo=1;
        $superadmin->pagos_editar=1;
        $superadmin->pagos_eliminar=1;
        $superadmin->finanzas=1;
        $superadmin->tran_finanzas_editar=1;
        $superadmin->tran_finanzas_eliminar=1;
        
        $superadmin->factura_finanzas_pagar=1;
        $superadmin->factura_finanzas_editar=1;
        $superadmin->factura_finanzas_eliminar=1;
        $superadmin->pagos_finanzas_editar=1;
        $superadmin->pagos_finanzas_eliminar=1;
        $superadmin->save();



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {

        });
    }
}
