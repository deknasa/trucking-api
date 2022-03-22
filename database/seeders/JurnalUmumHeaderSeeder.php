<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalUmumHeader;

class JurnalUmumHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JurnalUmumHeader::create([
            'nobukti' => 'KGT 0001/II/2022',
            'tgl' => '2022/2/23',
            'keterangan' => 'ABSENSI SUPIR',
            'postingdari' => 'ABSENSI SUPIR',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumHeader::create([
            'nobukti' => 'KBT 0001/II/2022',
            'tgl' => '2022/2/24',
            'keterangan' => 'PROSES ABSENSI SUPIR',
            'postingdari' => 'PENGELUARAN',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'modifiedby' => 'ADMIN',
        ]);

        // tgl 22-03-2022
        JurnalUmumHeader::create([
            'nobukti' => 'KMT 0001/IIi/2022',
            'tgl' => '2022/3/21',
            'keterangan' => 'DEPOSITO SUPIR',
            'postingdari' => 'DEPOSITO SUPIR',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'modifiedby' => 'ADMIN',
        ]);

        JurnalUmumHeader::create([
            'nobukti' => 'KBT 0001/III/2022',
            'tgl' => '2022/3/21',
            'keterangan' => 'PINJAMAN SUPIR',
            'postingdari' => 'PINJAMAN SUPIR',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'modifiedby' => 'ADMIN',
        ]);

        // pengembalian pinjaman
        JurnalUmumHeader::create([
            'nobukti' => 'KMT 0002/III/2022',
            'tgl' => '2022/3/21',
            'keterangan' => 'PENGEMBALIAN PINJAMAN',
            'postingdari' => 'PENGEMBALIAN PINJAMAN',
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900-01-01',
            'modifiedby' => 'ADMIN',
        ]);
    }
}
