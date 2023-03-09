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
            'tglbukti' => '2022/3/21',
            'pelanggan_id' => 0,
            // 'keterangan' => 'DEPOSITO SUPIR',
            'postingdari' => 'DEPOSITO SUPIR',
            'diterimadari' => 'DEPOSITO SUPIR',
            'tgllunas' => '2022/3/21',
            // 'cabang_id' => 3,
            // 'statuskas' => 54,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            // 'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);

        // pengembalian pinjaman
        PenerimaanHeader::create([
            'nobukti' => 'KMT 0002/III/2022',
            'tglbukti' => '2022/3/21',
            'pelanggan_id' => 0,
            // 'keterangan' => 'PENGEMBALIAN PINJAMAN',
            'postingdari' => 'PENGEMBALIAN PINJAMAN',
            'diterimadari' => 'PENGEMBALIAN PINJAMAN',
            'tgllunas' => '2022/3/21',
            // 'cabang_id' => 3,
            // 'statuskas' => 54,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            // 'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanHeader::create([
            'nobukti' => 'KMT 0001/IV/2022',
            'tglbukti' => '2022/4/8',
            'pelanggan_id' => 0,
            // 'keterangan' => 'DEPOSITO SUPIR',
            'postingdari' => 'DEPOSITO SUPIR',
            'diterimadari' => 'DEPOSITO SUPIR',
            'tgllunas' => '2022/4/8',
            // 'cabang_id' => 3,
            // 'statuskas' => 54,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            // 'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);

        PenerimaanHeader::create([
            'nobukti' => 'BMT-M BCA 0001/V/2022',
            'tglbukti' => '2022/5/31',
            'pelanggan_id' => 0,
            // 'keterangan' => 'PENERIMAAN GIRO',
            'postingdari' => 'PENERIMAAN GIRO',
            'diterimadari' => 'PENERIMAAN GIRO',
            'tgllunas' => '2022/5/31',
            // 'cabang_id' => 3,
            // 'statuskas' => 55,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            // 'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);


        PenerimaanHeader::create([
            'nobukti' => 'KMT 0001/V/2022',
            'tglbukti' => '2022/5/31',
            'pelanggan_id' => 0,
            // 'keterangan' => 'PENGEMBALIAN KAS GANTUNG',
            'postingdari' => 'PENGEMBALIAN KAS GANTUNG',
            'diterimadari' => 'PENGEMBALIAN KAS GANTUNG',
            'tgllunas' => '2022/5/31',
            // 'cabang_id' => 3,
            // 'statuskas' => 54,
            'statusapproval' => 4,
            'userapproval' => '',
            'tglapproval' => '1900/1/1',
            // 'noresi' => '',
            'statusberkas' => 82,
            'userberkas' => '',
            'tglberkas' => '1900/1/1',
            'modifiedby' => 'ADMIN',
        ]);


    }
}
