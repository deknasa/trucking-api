<?php

namespace Database\Seeders;

use App\Models\Parameter;
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
        Parameter::create([
            'grp' => 'COA',
            'subgrp' => 'PENGELUARAN KAS KREDIT',
            'text' => '',
            'memo' => 'NOMOR PENGELUARAN KAS',
            'modifiedby' => 'ADMIN',
        ]);

        // Parameter::create([
        //     'grp' => 'ABSENSI',
        //     'subgrp' => 'ABSENSI',
        //     'text' => '#ABS# 9999#/#R#/#Y',
        //     'memo' => '',
        //     'modifiedby' => 'ADMIN',
        // ]);
        
        // Parameter::create([
        //     'grp' => 'KAS GANTUNG',
        //     'subgrp' => 'NOMOR KAS GANTUNG',
        //     'text' => '#KGT# 9999#/#R#/#Y',
        //     'memo' => '',
        //     'modifiedby' => 'ADMIN',
        // ]);
        
        Parameter::factory()
            ->count(100)
            ->create();
    }
}
