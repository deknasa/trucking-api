<?php

namespace Database\Seeders;

use App\Models\Gandengan;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GandenganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement("delete Gandengan");
        DB::statement("DBCC CHECKIDENT ('Gandengan', RESEED, 1);");

        gandengan::create(['kodegandengan' => '0', 'keterangan' => 'TRUCK', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '1', 'keterangan' => 'GANDENGAN T-01 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '10', 'keterangan' => 'GANDENGAN T-10 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '11', 'keterangan' => 'GANDENGAN T-11 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '12', 'keterangan' => 'GANDENGAN T-12 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '13', 'keterangan' => 'GANDENGAN T-13 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '14', 'keterangan' => 'GANDENGAN T-14 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '15', 'keterangan' => 'GANDENGAN T-15 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '16', 'keterangan' => 'GANDENGAN T-16 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '17', 'keterangan' => 'GANDENGAN T-17 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '18', 'keterangan' => 'GANDENGAN T-18 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '19', 'keterangan' => 'GANDENGAN T-19 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '2', 'keterangan' => 'GANDENGAN T-02 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '20', 'keterangan' => 'GANDENGAN T-20 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '21', 'keterangan' => 'GANDENGAN T-21 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '22', 'keterangan' => 'GANDENGAN T-22 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '23', 'keterangan' => 'GANDENGAN T-23 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '24', 'keterangan' => 'GANDENGAN T-24 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '25', 'keterangan' => 'GANDENGAN T-25 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '26', 'keterangan' => 'GANDENGAN T-26 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '27', 'keterangan' => 'GANDENGAN T-27 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '28', 'keterangan' => 'GANDENGAN T-28 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '29', 'keterangan' => 'GANDENGAN T-29 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '3', 'keterangan' => 'GANDENGAN T-03 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '30', 'keterangan' => 'GANDENGAN T-30 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '31', 'keterangan' => 'GANDENGAN T-31 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '32', 'keterangan' => 'GANDENGAN T-32 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '33', 'keterangan' => 'GANDENGAN T-33 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '34', 'keterangan' => 'GANDENGAN T-34 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '35', 'keterangan' => 'GANDENGAN T-35 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '36', 'keterangan' => 'GANDENGAN T-36 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '37', 'keterangan' => 'GANDENGAN T-37 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '38', 'keterangan' => 'GANDENGAN T-38 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '39', 'keterangan' => 'GANDENGAN T-39 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '4', 'keterangan' => 'GANDENGAN T-04 PANJANG', 'statusaktif' => '2', 'modifiedby' => '',]);
        gandengan::create(['kodegandengan' => '40', 'keterangan' => 'GANDENGAN T-40 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '41', 'keterangan' => 'GANDENGAN T-41 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '42', 'keterangan' => 'GANDENGAN T-42 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '43', 'keterangan' => 'GANDENGAN T-43 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '44', 'keterangan' => 'GANDENGAN T-44 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '45', 'keterangan' => 'GANDENGAN T-45 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '46', 'keterangan' => 'GANDENGAN T-46 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '47', 'keterangan' => 'GANDENGAN T-47 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '48', 'keterangan' => 'GANDENGAN T-48 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '49', 'keterangan' => 'GANDENGAN T-49 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '5', 'keterangan' => 'GANDENGAN T-05 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '50', 'keterangan' => 'GANDENGAN T-50 PANJANG', 'statusaktif' => '2', 'modifiedby' => '',]);
        gandengan::create(['kodegandengan' => '51', 'keterangan' => 'GANDENGAN T-51 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '52', 'keterangan' => 'GANDENGAN T-52 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '53', 'keterangan' => 'GANDENGAN T-53 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '54', 'keterangan' => 'GANDENGAN T-54 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '55', 'keterangan' => 'GANDENGAN T-55 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '56', 'keterangan' => 'GANDENGAN T-56 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '57', 'keterangan' => 'GANDENGAN T-57 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '58', 'keterangan' => 'GANDENGAN T-58 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '59', 'keterangan' => 'GANDENGAN T-59 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '6', 'keterangan' => 'GANDENGAN T-06 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '60', 'keterangan' => 'GANDENGAN T-60 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '61', 'keterangan' => 'GANDENGAN T-61 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'TRUCKING',]);
        gandengan::create(['kodegandengan' => '62', 'keterangan' => 'GANDENGAN T-62 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '63', 'keterangan' => 'GANDENGAN T-63 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'TRUCKING',]);
        gandengan::create(['kodegandengan' => '64', 'keterangan' => 'GANDENGAN T-64 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '65', 'keterangan' => 'GANDENGAN T-65 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '66', 'keterangan' => 'GANDENGAN T-66 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '67', 'keterangan' => 'GANDENGAN T-67 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '68', 'keterangan' => 'GANDENGAN T-68 PANJANG (MUTASI DARI PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '69', 'keterangan' => 'GANDENGAN T-69 PENDEK (MUTASI DARI PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '7', 'keterangan' => 'GANDENGAN T-07 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '70', 'keterangan' => 'GANDENGAN T-70 PENDEK (MUTASI DARI PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '71', 'keterangan' => 'GANDENGAN T-71 PANJANG (MUTASI DR PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '72', 'keterangan' => 'GANDENGAN T-72 PANJANG (MUTASI DR PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '73', 'keterangan' => 'GANDENGAN T-73 PENDEK (MUTASI DR PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '74', 'keterangan' => 'GANDENGAN T-74 PANJANG (MUTASI DR PKU)', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '75', 'keterangan' => 'GANDENGAN T 75 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '76', 'keterangan' => 'GANDENGAN T 76 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '77', 'keterangan' => 'GANDENGAN T 77 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '78', 'keterangan' => 'GANDENGAN T 78 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '79', 'keterangan' => 'GANDENGAN T 79 PENDEK', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '8', 'keterangan' => 'GANDENGAN T-08 PANJANG', 'statusaktif' => '1', 'modifiedby' => 'chairunnisa',]);
        gandengan::create(['kodegandengan' => '80', 'keterangan' => 'GANDENGAN T-80 PENDEK', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '81', 'keterangan' => 'GANDENGAN T-81 PENDEK', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
        gandengan::create(['kodegandengan' => '9', 'keterangan' => 'GANDENGAN T-09 PANJANG', 'statusaktif' => '2', 'modifiedby' => 'YESSICA',]);
    }
}
