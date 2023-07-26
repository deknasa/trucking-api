<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceHeader;
use Illuminate\Support\Facades\DB;

class InvoiceHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete invoiceheader");
        DB::statement("DBCC CHECKIDENT ('invoiceheader', RESEED, 1);");
    }
}
