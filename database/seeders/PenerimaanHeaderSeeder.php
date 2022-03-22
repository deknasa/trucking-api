<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenerimaanHeader;

class PenerimaanHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PenerimaanHeader::create([
            'nobukti' => 'KMT 0001/III/2022',
            'tgl' => '2022/3/21',
            'pelanggan_id' => 0,
            'keterangan' => 'DEPOSITO SUPIR',
            'postingdari' => 'DEPOSITO SUPIR',
            'diterimadari' => 'DEPOSITO SUPIR',
            'tgllunas' => '2022/3/21',
            'cabang_id' => 3,
            'statuskas' => 54,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);

        // pengembalian pinjaman
        PenerimaanHeader::create([
            'nobukti' => 'KMT 0002/III/2022',
            'tgl' => '2022/3/21',
            'pelanggan_id' => 0,
            'keterangan' => 'PENGEMBALIAN PINJAMAN',
            'postingdari' => 'PENGEMBALIAN PINJAMAN',
            'diterimadari' => 'PENGEMBALIAN PINJAMAN',
            'tgllunas' => '2022/3/21',
            'cabang_id' => 3,
            'statuskas' => 54,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);

    }
}
