<?php

namespace Database\Seeders;

use App\Models\SaldoReminderpergantian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class SaldoReminderPergantianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldoreminderpergantian");
        DB::statement("DBCC CHECKIDENT ('saldoreminderpergantian', RESEED, 0);");

    }
}
