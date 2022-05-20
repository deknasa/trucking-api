<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotaKreditHeader;

class NotaKreditHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotaKreditHeader::create([
            'nobukti' => 'NKT 0001/V/2022',
            'pelunasanpiutang_nobukti' => 'PPT 0002/V/2022',
            'tglbukti' => '2022/5/20',
            'keterangan' => 'POTONGAN',
            'postingdari' => '',
            'statusapproval' => 4,
            'tgllunas' => '2022/5/20',
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
