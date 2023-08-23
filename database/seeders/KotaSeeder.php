<?php

namespace Database\Seeders;

use App\Models\Kota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Kota");
        DB::statement("DBCC CHECKIDENT ('Kota', RESEED, 1);");

        kota::create(['kodekota' => 'TANJUNG PRIOK', 'keterangan' => 'TANJUNG PRIOK', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'ANCOL', 'keterangan' => 'ANCOL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'ANYER', 'keterangan' => 'ANYER', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BALARAJA', 'keterangan' => 'BALARAJA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BANDENGAN', 'keterangan' => 'BANDENGAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BANDUNG', 'keterangan' => 'BANDUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BANDUNG / PADALARANG', 'keterangan' => 'BANDUNG / PADALARANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BEKASI', 'keterangan' => 'BEKASI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BEKASI BARAT', 'keterangan' => 'BEKASI BARAT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BEKASI TIMUR', 'keterangan' => 'BEKASI TIMUR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BOGOR', 'keterangan' => 'BOGOR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BOGOR / GN SINDUR', 'keterangan' => 'BOGOR / GN SINDUR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'BOJONEGORO', 'keterangan' => 'BOJONEGORO', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CAHAYA MULIA', 'keterangan' => 'CAHAYA MULIA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CAKUNG', 'keterangan' => 'CAKUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CENGKARENG', 'keterangan' => 'CENGKARENG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIANJUR', 'keterangan' => 'CIANJUR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIAWI', 'keterangan' => 'CIAWI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIBINONG', 'keterangan' => 'CIBINONG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIBITUNG', 'keterangan' => 'CIBITUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIBUBUR', 'keterangan' => 'CIBUBUR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIKAMPEK', 'keterangan' => 'CIKAMPEK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIKANDE', 'keterangan' => 'CIKANDE', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIKARANG', 'keterangan' => 'CIKARANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CILACAP', 'keterangan' => 'CILACAP', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CILANDAK', 'keterangan' => 'CILANDAK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CILEDUG', 'keterangan' => 'CILEDUG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CILEGON', 'keterangan' => 'CILEGON', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CILEUNGSI', 'keterangan' => 'CILEUNGSI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIMANGGIS', 'keterangan' => 'CIMANGGIS', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIPINANG', 'keterangan' => 'CIPINANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIRACAS', 'keterangan' => 'CIRACAS', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIRACAS/PS.PEBO', 'keterangan' => 'CIRACAS/PS.PEBO', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CIREBON', 'keterangan' => 'CIREBON', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'CITEREUP', 'keterangan' => 'CITEREUP', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'DAAN MOGOT', 'keterangan' => 'DAAN MOGOT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'DADAP', 'keterangan' => 'DADAP', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'DEPO INGGOM', 'keterangan' => 'DEPO INGGOM', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'DEPOK', 'keterangan' => 'DEPOK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'GARUT', 'keterangan' => 'GARUT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'GD.PANJANG', 'keterangan' => 'GD.PANJANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'GUNUNG PUTRI', 'keterangan' => 'GUNUNG PUTRI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'INDRAMAYU', 'keterangan' => 'INDRAMAYU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'JELAMBAR', 'keterangan' => 'JELAMBAR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'JEMBATAN 2', 'keterangan' => 'JEMBATAN 2', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'JEMBATAN 2,3,5', 'keterangan' => 'JEMBATAN 2,3,5', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'JEMBATAN 3', 'keterangan' => 'JEMBATAN 3', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'JEMBATAN 5', 'keterangan' => 'JEMBATAN 5', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KALIMALANG', 'keterangan' => 'KALIMALANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KAPUK', 'keterangan' => 'KAPUK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KEDOYA', 'keterangan' => 'KEDOYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KELAPA GADING', 'keterangan' => 'KELAPA GADING', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KEMAYORAN', 'keterangan' => 'KEMAYORAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KERAWANG', 'keterangan' => 'KERAWANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KOTA', 'keterangan' => 'KOTA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KRAGILAN', 'keterangan' => 'KRAGILAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'KROYA', 'keterangan' => 'KROYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MAJALENGKA', 'keterangan' => 'MAJALENGKA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MANGGA DUA', 'keterangan' => 'MANGGA DUA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MARUNDA', 'keterangan' => 'MARUNDA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MERAK', 'keterangan' => 'MERAK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MERUYA', 'keterangan' => 'MERUYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MUARA BARU', 'keterangan' => 'MUARA BARU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'MUARA KARANG', 'keterangan' => 'MUARA KARANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'OB MARUNDA KE 005', 'keterangan' => 'OB MARUNDA KE 005', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PANDEGLANG', 'keterangan' => 'PANDEGLANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PASAR IKAN', 'keterangan' => 'PASAR IKAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PEKALONGAN', 'keterangan' => 'PEKALONGAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PEMALANG', 'keterangan' => 'PEMALANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PETRO JAYA', 'keterangan' => 'PETRO JAYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PLUIT', 'keterangan' => 'PLUIT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PLUIT/MUARA KARANG', 'keterangan' => 'PLUIT/MUARA KARANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PLUMPANG', 'keterangan' => 'PLUMPANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PONDOK INDAH', 'keterangan' => 'PONDOK INDAH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PONDOK UNGU', 'keterangan' => 'PONDOK UNGU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PONDOK UNGU / BEKASI', 'keterangan' => 'PONDOK UNGU / BEKASI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PULO GADUNG', 'keterangan' => 'PULO GADUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PURBALINGGA', 'keterangan' => 'PURBALINGGA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PURWAKARTA', 'keterangan' => 'PURWAKARTA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'PURWOKERTO', 'keterangan' => 'PURWOKERTO', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'RANCAEKEK', 'keterangan' => 'RANCAEKEK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'RANGKAS BITUNG', 'keterangan' => 'RANGKAS BITUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SEMARANG', 'keterangan' => 'SEMARANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SEMPER', 'keterangan' => 'SEMPER', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SENTUL', 'keterangan' => 'SENTUL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SERANG', 'keterangan' => 'SERANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SERPONG', 'keterangan' => 'SERPONG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SOLO', 'keterangan' => 'SOLO', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SRENGSENG/KEBON JERUK', 'keterangan' => 'SRENGSENG/KEBON JERUK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SUBANG', 'keterangan' => 'SUBANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SUKABUMI', 'keterangan' => 'SUKABUMI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SUMEDANG', 'keterangan' => 'SUMEDANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SUNDA KELAPA', 'keterangan' => 'SUNDA KELAPA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SUNTER', 'keterangan' => 'SUNTER', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SURABAYA', 'keterangan' => 'SURABAYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'SURALAYA', 'keterangan' => 'SURALAYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TAMBUN', 'keterangan' => 'TAMBUN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TANGERANG', 'keterangan' => 'TANGERANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TANGERANG/BELIMBING', 'keterangan' => 'TANGERANG/BELIMBING', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TANGERANG/SERPONG', 'keterangan' => 'TANGERANG/SERPONG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TASIKMALAYA', 'keterangan' => 'TASIKMALAYA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TEGAL', 'keterangan' => 'TEGAL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TELUK GONG', 'keterangan' => 'TELUK GONG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TG. PRIOK', 'keterangan' => 'TG. PRIOK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
        kota::create(['kodekota' => 'TUBAGUS ANGKE', 'keterangan' => 'TUBAGUS ANGKE', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'RYAN',]);
    }
}
