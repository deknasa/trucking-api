<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceDetail;
use Illuminate\Support\Facades\DB;

class InvoiceDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete invoicedetail");
        DB::statement("DBCC CHECKIDENT ('invoicedetail', RESEED, 1);");


    }
}
