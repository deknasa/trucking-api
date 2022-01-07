<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=0; $i < 50; $i++) { 
            DB::table('tparameter')->insert([
                'modifiedby' => 'admin',
                'grp' => "grp",
                'subgrp' => "subgrp",
                'text' => "text",
                'memo' => "memo",
            ]);
        }
    }
}
