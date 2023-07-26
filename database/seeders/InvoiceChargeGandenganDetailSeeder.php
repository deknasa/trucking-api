<?php

namespace Database\Seeders;

use App\Models\InvoiceChargeGandenganDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceChargeGandenganDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete invoicechargegandengandetail");
        DB::statement("DBCC CHECKIDENT ('invoicechargegandengandetail', RESEED, 1);");
    }
}
