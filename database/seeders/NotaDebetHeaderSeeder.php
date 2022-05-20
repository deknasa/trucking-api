<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotaDebetHeader;

class NotaDebetHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotaDebetHeader::create([
            'nobukti' => 'NDT 0001/V/2022',
            'pelunasanpiutang_nobukti' => 'PPT 0003/V/2022',
            'tglbukti' => '2022/5/20',
            'keterangan' => 'PENDAPATAN LAIN',
            'postingdari' => '',
            'statusapproval' => 4,
            'tgllunas' => '',
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
