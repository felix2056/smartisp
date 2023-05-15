<?php

use Database\Seeders\templatesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $global_settings = DB::table('global_settings')       
        ->select('global_settings.status_seed_dian')->get()->first();
        if ($global_settings->status_seed_dian==0) {
            $this->call(dian_settingsSeeder::class);
            $this->call(typeoperationSeeder::class);
            $this->call(typedocSeeder::class);
            $this->call(typetaxpayerSeeder::class);
            $this->call(accountingregimeSeeder::class);
            $this->call(typeresponsibilitySeeder::class);
            $this->call(economicactivitySeeder::class);
            $this->call(paisSeeder::class);
            $this->call(departamentoSeeder::class);
            $this->call(municipioSeeder::class);
            $this->call(templatesSeeder::class);
            //indica que el seed de la factura electronica de colombia se ha ejecudato
            DB::table('global_settings')->update(['status_seed_dian'=>'1']);
        }
    }
}
