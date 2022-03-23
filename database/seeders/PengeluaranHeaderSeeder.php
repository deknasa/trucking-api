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
            'nobukti' => 'KBT 0001/II/2022',
            'tgl' => '2022-02-24',
            'pelanggan_id' => 0,
            'keterangan' => 'PROSES ABSENSI SUPIR',
            'statusjenistransaksi' => 54,
            'postingdari' => 'PROSES ABSENSI SUPIR',
            'statusapproval' => 4,
            'dibayarke' => '',
            'cabang_id' => 0,
            'bank_id' => 1,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'transferkeac' => '',
            'transferkean' => '',
            'transferkebank' => '',
            'modifiedby' => 'ADMIN',
        ]);

        //pinjaman
        PengeluaranHeader::create([
            'nobukti' => 'KBT 0001/III/2022',
            'tgl' => '2022-03-01',
            'pelanggan_id' => 0,
            'keterangan' => 'PINJAMAN SUPIR',
            'statusjenistransaksi' => 54,
            'postingdari' => 'PINJAMAN SUPIR',
            'statusapproval' => 4,
            'dibayarke' => '',
            'cabang_id' => 0,
            'bank_id' => 1,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'transferkeac' => '',
            'transferkean' => '',
            'transferkebank' => '',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
