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


        kota::create(['kodekota' => 'BELAWAN', 'keterangan' => 'BELAWAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BRAYAN', 'keterangan' => 'BRAYAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'CANANG', 'keterangan' => 'CANANG', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'HELVETIA', 'keterangan' => 'HELVETIA', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KAYU PUTIH', 'keterangan' => 'KAYU PUTIH', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KIM', 'keterangan' => 'KIM', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KIM II', 'keterangan' => 'KIM II', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KIM III', 'keterangan' => 'KIM III', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KOTA BANGUN', 'keterangan' => 'KOTA BANGUN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KRAKATAU', 'keterangan' => 'KRAKATAU', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MABAR', 'keterangan' => 'MABAR', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MARELAN', 'keterangan' => 'MARELAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MARTUBUNG', 'keterangan' => 'MARTUBUNG', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PAYA PASIR', 'keterangan' => 'PAYA PASIR', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PERTEMPURAN', 'keterangan' => 'PERTEMPURAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIDEMPUAN', 'keterangan' => 'SIDEMPUAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIMPANG KANTOR', 'keterangan' => 'SIMPANG KANTOR', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SMART', 'keterangan' => 'SMART', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TANJUNG MULIA', 'keterangan' => 'TANJUNG MULIA', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TITI PAPAN', 'keterangan' => 'TITI PAPAN', 'zona_id' => '1', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TEMBUNG', 'keterangan' => 'TEMBUNG', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SUNGGAL', 'keterangan' => 'SUNGGAL', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SISINGAMANGARAJA', 'keterangan' => 'SISINGAMANGARAJA', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SAMPALI', 'keterangan' => 'SAMPALI', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PASAR MERAH', 'keterangan' => 'PASAR MERAH', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MEDAN KOTA', 'keterangan' => 'MEDAN KOTA', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MARENDAL', 'keterangan' => 'MARENDAL', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KELAMBIR JAYA', 'keterangan' => 'KELAMBIR JAYA', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'AMPLAS', 'keterangan' => 'AMPLAS', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'AMPLAS S/D POLDASU', 'keterangan' => 'AMPLAS S/D POLDASU', 'zona_id' => '2', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'JL. BINJAI', 'keterangan' => 'JL. BINJAI', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'JL. KARTINI', 'keterangan' => 'JL. KARTINI', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'DELI TUA', 'keterangan' => 'DELI TUA', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BINJAI', 'keterangan' => 'BINJAI', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BRAHRANG', 'keterangan' => 'BRAHRANG', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'NAMORAMBE', 'keterangan' => 'NAMORAMBE', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LUBUK PAKAM', 'keterangan' => 'LUBUK PAKAM', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PATUMBAK', 'keterangan' => 'PATUMBAK', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TALON KENAS', 'keterangan' => 'TALON KENAS', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TANDEM', 'keterangan' => 'TANDEM', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TANJUNG MORAWA', 'keterangan' => 'TANJUNG MORAWA', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TITI KUNING', 'keterangan' => 'TITI KUNING', 'zona_id' => '3', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ACEH SUBULUSSALAM', 'keterangan' => 'ACEH SUBULUSSALAM', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ACEH UTARA', 'keterangan' => 'ACEH UTARA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'AEK KANOPAN', 'keterangan' => 'AEK KANOPAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BALIGE', 'keterangan' => 'BALIGE', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BANDA ACEH', 'keterangan' => 'BANDA ACEH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BANGUN PURBA', 'keterangan' => 'BANGUN PURBA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BRASTAGI', 'keterangan' => 'BRASTAGI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'CEMARA', 'keterangan' => 'CEMARA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'CIKAMPAK', 'keterangan' => 'CIKAMPAK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'DUMAI', 'keterangan' => 'DUMAI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'DURI', 'keterangan' => 'DURI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'GALANG', 'keterangan' => 'GALANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'HAMPARAN PERAK', 'keterangan' => 'HAMPARAN PERAK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KABANJAHE', 'keterangan' => 'KABANJAHE', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KAMPUNG LALANG', 'keterangan' => 'KAMPUNG LALANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KISARAN', 'keterangan' => 'KISARAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KOTA', 'keterangan' => 'KOTA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KUALA GUMIT', 'keterangan' => 'KUALA GUMIT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KUALA TANJUNG', 'keterangan' => 'KUALA TANJUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LABUHAN', 'keterangan' => 'LABUHAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LANGKAT', 'keterangan' => 'LANGKAT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LANGSA', 'keterangan' => 'LANGSA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LHOKSEUMAWE', 'keterangan' => 'LHOKSEUMAWE', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LIMA PULUH', 'keterangan' => 'LIMA PULUH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MANDAILING NATAL', 'keterangan' => 'MANDAILING NATAL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MANDALA', 'keterangan' => 'MANDALA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MEULABOH', 'keterangan' => 'MEULABOH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PALEMBANG', 'keterangan' => 'PALEMBANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PANCING', 'keterangan' => 'PANCING', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PANCUR BATU', 'keterangan' => 'PANCUR BATU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PANGKALAN SUSU', 'keterangan' => 'PANGKALAN SUSU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PEKAN BARU', 'keterangan' => 'PEKAN BARU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PERBAUNGAN', 'keterangan' => 'PERBAUNGAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PERCUT', 'keterangan' => 'PERCUT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PERDAGANGAN', 'keterangan' => 'PERDAGANGAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PERKEBUNAN PULAU MANDI', 'keterangan' => 'PERKEBUNAN PULAU MANDI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PORSEA', 'keterangan' => 'PORSEA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PULO BRAYAN', 'keterangan' => 'PULO BRAYAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'RANTAU PRAPAT', 'keterangan' => 'RANTAU PRAPAT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SEI BULUH', 'keterangan' => 'SEI BULUH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SEI RAMPAH', 'keterangan' => 'SEI RAMPAH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SEI SUKA BANDAR TINGGI', 'keterangan' => 'SEI SUKA BANDAR TINGGI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SELAYANG', 'keterangan' => 'SELAYANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIBOLGA', 'keterangan' => 'SIBOLGA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIDIKALANG', 'keterangan' => 'SIDIKALANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIMALUNGUN', 'keterangan' => 'SIMALUNGUN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'STABAT', 'keterangan' => 'STABAT', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TANJUNG BALAI', 'keterangan' => 'TANJUNG BALAI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TANJUNG PURA', 'keterangan' => 'TANJUNG PURA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TARUTUNG', 'keterangan' => 'TARUTUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TITIPAPAN', 'keterangan' => 'TITIPAPAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ACEH', 'keterangan' => 'ACEH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BGR PAYA PASIR', 'keterangan' => 'BGR PAYA PASIR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BROHOL T.TINGGI', 'keterangan' => 'BROHOL T.TINGGI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'DAIRI', 'keterangan' => 'DAIRI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'DAMULI', 'keterangan' => 'DAMULI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'DEPO PIL', 'keterangan' => 'DEPO PIL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'GAHAPI', 'keterangan' => 'GAHAPI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'JL. AR HAKIM', 'keterangan' => 'JL. AR HAKIM', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'K. TANJUNG', 'keterangan' => 'K. TANJUNG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KANDANG', 'keterangan' => 'KANDANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KIM (KANDANG)', 'keterangan' => 'KIM (KANDANG)', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KP.LALANG', 'keterangan' => 'KP.LALANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PULAU BRAYAN', 'keterangan' => 'PULAU BRAYAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PADANG BULAN', 'keterangan' => 'PADANG BULAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PEMATANG SIANTAR', 'keterangan' => 'PEMATANG SIANTAR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PADANG SIDEMPUAN', 'keterangan' => 'PADANG SIDEMPUAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PANGKALAN BRANDAN', 'keterangan' => 'PANGKALAN BRANDAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PANTAI LABU', 'keterangan' => 'PANTAI LABU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PAYA ROBA', 'keterangan' => 'PAYA ROBA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SBU', 'keterangan' => 'SBU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SEIRAMPAH', 'keterangan' => 'SEIRAMPAH', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SETIA JADI', 'keterangan' => 'SETIA JADI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SINDOGLOBAL', 'keterangan' => 'SINDOGLOBAL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIMPANG KANTOR', 'keterangan' => 'SIMPANG KANTOR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'T.KENAS', 'keterangan' => 'T.KENAS', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TEBING TINGGI', 'keterangan' => 'TEBING TINGGI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TAKENGON', 'keterangan' => 'TAKENGON', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'THAMRIN PLAZA', 'keterangan' => 'THAMRIN PLAZA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ZONA I', 'keterangan' => 'ZONA I', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ZONA II', 'keterangan' => 'ZONA II', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ZONA III', 'keterangan' => 'ZONA III', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TOL TAMORA', 'keterangan' => 'TOL TAMORA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PULANG KOSONG', 'keterangan' => 'PULANG KOSONG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TURUN RANGKA', 'keterangan' => 'TURUN RANGKA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BELAWAN RANGKA', 'keterangan' => 'BELAWAN RANGKA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KIM RANGKA', 'keterangan' => 'KIM RANGKA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SEI KAMBING', 'keterangan' => 'SEI KAMBING', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PERBATASAN PAKAM', 'keterangan' => 'PERBATASAN PAKAM', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TITIKUNING', 'keterangan' => 'TITIKUNING', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PONDOK KELAPA', 'keterangan' => 'PONDOK KELAPA', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIMPANG MARENDAL', 'keterangan' => 'SIMPANG MARENDAL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'PEKANBARU', 'keterangan' => 'PEKANBARU', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'ACEH BESAR', 'keterangan' => 'ACEH BESAR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'KIM SERUWAI', 'keterangan' => 'KIM SERUWAI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'NAD', 'keterangan' => 'NAD', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'MKT BELAWAN', 'keterangan' => 'MKT BELAWAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SINGKIL', 'keterangan' => 'SINGKIL', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'LANGSIR PELABUHAN', 'keterangan' => 'LANGSIR PELABUHAN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'BGR', 'keterangan' => 'BGR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SIGODANG', 'keterangan' => 'SIGODANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TERJUN', 'keterangan' => 'TERJUN', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'TPI GABION', 'keterangan' => 'TPI GABION', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'SENTOSA PLASTIK', 'keterangan' => 'SENTOSA PLASTIK', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'JL.SELAYANG', 'keterangan' => 'JL.SELAYANG', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'JL.KARTINI', 'keterangan' => 'JL.KARTINI', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'WARUNA LANGSIR DEPO', 'keterangan' => 'WARUNA LANGSIR DEPO', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
        kota::create(['kodekota' => 'EXPORT LANGSIR', 'keterangan' => 'EXPORT LANGSIR', 'zona_id' => '0', 'statusaktif' => '1', 'modifiedby' => 'admin',]);
    }
}
