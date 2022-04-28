<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbsensiSupirApprovalDetail;

class AbsensiSupirApprovalDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        AbsensiSupirApprovalDetail::create([
            'nobukti' => 'ASA 0001/II/2022',
            'absensisupirapproval_id' => 1,
            'trado_id' => 1,
            'supir_id' => 1,
            'supirserap_id' => 0,         
            'modifiedby' => 'ADMIN',
        ]);        

 
    }
}
