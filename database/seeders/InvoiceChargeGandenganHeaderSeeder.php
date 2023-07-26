<?php

namespace Database\Seeders;

use App\Models\InvoiceChargeGandenganHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceChargeGandenganHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete invoicechargegandenganheader");
        DB::statement("DBCC CHECKIDENT ('invoicechargegandenganheader', RESEED, 1);");

    }
}
