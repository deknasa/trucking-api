<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerkiraanLabaRugi;
use Illuminate\Support\Facades\DB;

class PerkiraanLabaRugiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete perkiraanlabarugi");
        DB::statement("DBCC CHECKIDENT ('perkiraanlabarugi', RESEED, 1);");

        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2012', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2012', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2012', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2012', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2012', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2012', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2013', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2014', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2015', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2016', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2017', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2018', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2019', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2020', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2021', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2022', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '1', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '2', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '3', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '4', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '5', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '6', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '7', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '8', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '9', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '10', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '11', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
        perkiraanlabarugi::create([ 'coa' => '05.02.02.01', 'bulan' => '12', 'tahun' => '2023', 'keterangancoa' => 'Laba/Rugi Bulan Berjalan - Medan', 'type' => 'Laba/Rugi', 'modifiedby' => 'admin',]);
    }
    
}
