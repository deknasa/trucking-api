<?php

namespace Database\Seeders;

use App\Models\PengeluaranHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete pengeluaranheader");
        DB::statement("DBCC CHECKIDENT ('pengeluaranheader', RESEED, 1);");

        pengeluaranheader::create(['nobukti' => 'KBT 0001/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0002/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0003/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0004/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'pelanggan_id' => '0', 'alatbayar_id' => '0', 'postingdari' => 'ENTRY PENGELUARAN TRUCKING', 'statusapproval' => '4', 'dibayarke' => '', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0006/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => '', 'pelanggan_id' => '0', 'alatbayar_id' => '1', 'postingdari' => 'ENTRY PENGELUARAN KAS/BANK', 'statusapproval' => '4', 'dibayarke' => 'SUPIR', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'ADMIN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0007/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => 'Setor Fee Harry Ananda atas ritasi unit trado yang jalan Bulan Januari 2023 = 302 Unit @ Rp. 2.500', 'pelanggan_id' => '0', 'alatbayar_id' => '1', 'postingdari' => '', 'statusapproval' => '4', 'dibayarke' => 'MEKANIK PARDEDE', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'AYEN',]);
        pengeluaranheader::create(['nobukti' => 'KBT 0008/II/2023', 'tglbukti' => '2023/2/1', 'keterangan' => 'Biaya Jasa Pengamanan Trado di Belawan MAR 84 Bulan Februari 2023', 'pelanggan_id' => '0', 'alatbayar_id' => '1', 'postingdari' => '', 'statusapproval' => '4', 'dibayarke' => 'IBU SUTOMO', 'bank_id' => '1', 'userapproval' => '', 'tglapproval' => '1900/1/1', 'statuscetak' => '175', 'userbukacetak' => '', 'tglbukacetak' => '1900/1/1', 'jumlahcetak' => '0', 'transferkeac' => '', 'transferkean' => '', 'transferkebank' => '', 'statusformat' => '33', 'modifiedby' => 'AYEN',]);
    }
}
