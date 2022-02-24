<?php

namespace Database\Seeders;
use App\Models\PengeluaranHeader;
use Illuminate\Database\Seeder;

class PengeluaranHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PengeluaranHeader::create([
            'nobukti' => '',
            'tgl' => '',
            'pelanggan_id' => '',
            'keterangan' => '',
            'type' => '',
            'postingdari' => '',
            'statusapproval' => '',
            'dibayarke' => '',
            'cabang' => '',
            'sumberdata' => '',
            'terima_nobukti' => '',
            'statuskasbank' => '',
            'statuspengembalian' => '',
            'userapproval' => '',
            'tglapproval' => '',
            'transferkeac' => '',
            'transferkean' => '',
            'transferkebank' => '',
            'noresi' => '',
            'statusberkas' => '',
            'userberkas' => '',
            'tglberkas' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
