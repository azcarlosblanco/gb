<?php

use Illuminate\Database\Seeder;

class CommissionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::insert("
            INSERT INTO 
                sls_commission_types (name, display_name, created_at, updated_at) 
            VALUES 
                ('Fixed', 'Comisión fija', '" . Carbon\Carbon::now() . "', '" . Carbon\Carbon::now() . "'),
                ('SaleType', 'Comisión por tipo de venta', '" . Carbon\Carbon::now() . "', '" . Carbon\Carbon::now() . "'),
                ('Plan', 'Comisión por plan vendido', '" . Carbon\Carbon::now() . "', '" . Carbon\Carbon::now() . "'),
                ('PlanAndSaleType', 'Comisión por plan vendido y tipo de venta', '" . Carbon\Carbon::now() . "', '" . Carbon\Carbon::now() . "'),
                ('ServiceType', 'Comisión por el tipo de servicio vendido', '" . Carbon\Carbon::now() . "', '" . Carbon\Carbon::now() . "');
        ");
    }
}
