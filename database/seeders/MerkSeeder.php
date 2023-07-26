<?php

namespace Database\Seeders;

use App\Models\Merk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("delete Merk");
        DB::statement("DBCC CHECKIDENT ('Merk', RESEED, 1);");

        merk::create([ 'kodemerk' => 'JETWAY', 'keterangan' => 'JETWAY', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'PERTAMINA', 'keterangan' => 'PERTAMINA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NPR', 'keterangan' => 'NPR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SKF', 'keterangan' => 'SKF', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ROBTEC', 'keterangan' => 'ROBTEC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'KTO', 'keterangan' => 'KTO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'UNICAL', 'keterangan' => 'UNICAL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'JPNUK', 'keterangan' => 'JPNUK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NAKATA', 'keterangan' => 'NAKATA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'THREE BOND', 'keterangan' => 'THREE BOND', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'YOLGAR', 'keterangan' => 'YOLGAR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TONYCO', 'keterangan' => 'TONYCO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'FLAG', 'keterangan' => 'FLAG', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SBS', 'keterangan' => 'SBS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MRF', 'keterangan' => 'MRF', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'INCOE', 'keterangan' => 'INCOE', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'CKB', 'keterangan' => 'CKB', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HITACHI', 'keterangan' => 'HITACHI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HAMMER SPIR', 'keterangan' => 'HAMMER SPIR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'IYE', 'keterangan' => 'IYE', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'REZUKA', 'keterangan' => 'REZUKA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'DEXTON', 'keterangan' => 'DEXTON', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'LAISIN', 'keterangan' => 'LAISIN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'IZUMI', 'keterangan' => 'IZUMI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NILES', 'keterangan' => 'NILES', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NEW ERA', 'keterangan' => 'NEW ERA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'CPU', 'keterangan' => 'CPU', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ROTARY', 'keterangan' => 'ROTARY', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'LINGLONG', 'keterangan' => 'LINGLONG', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SELLERY', 'keterangan' => 'SELLERY', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'DENSO', 'keterangan' => 'DENSO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'FUWA', 'keterangan' => 'FUWA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'WAKASA', 'keterangan' => 'WAKASA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SWALLOW', 'keterangan' => 'SWALLOW', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GEN JAPAN', 'keterangan' => 'GEN JAPAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SHENGXIN', 'keterangan' => 'SHENGXIN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HELLA', 'keterangan' => 'HELLA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MIC', 'keterangan' => 'MIC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'YUQIU BRAIDED', 'keterangan' => 'YUQIU BRAIDED', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HASHER', 'keterangan' => 'HASHER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'IWOZI-OTO', 'keterangan' => 'IWOZI-OTO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MC', 'keterangan' => 'MC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MMC', 'keterangan' => 'MMC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'STECKER', 'keterangan' => 'STECKER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'WSP', 'keterangan' => 'WSP', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NAIGAI', 'keterangan' => 'NAIGAI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'IBK', 'keterangan' => 'IBK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GMB', 'keterangan' => 'GMB', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SYE', 'keterangan' => 'SYE', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ETG', 'keterangan' => 'ETG', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'INDOSPRING', 'keterangan' => 'INDOSPRING', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HITO', 'keterangan' => 'HITO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'AWT', 'keterangan' => 'AWT', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MAXXIS', 'keterangan' => 'MAXXIS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'THREEFIVE', 'keterangan' => 'THREEFIVE', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HNTC', 'keterangan' => 'HNTC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NSK', 'keterangan' => 'NSK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'UD', 'keterangan' => 'UD', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'YUASA', 'keterangan' => 'YUASA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GEN', 'keterangan' => 'GEN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HAO-GUO', 'keterangan' => 'HAO-GUO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SJ', 'keterangan' => 'SJ', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'OEM', 'keterangan' => 'OEM', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'EMGI', 'keterangan' => 'EMGI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NO MERK', 'keterangan' => 'NO MERK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'APOLLO', 'keterangan' => 'APOLLO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MRK', 'keterangan' => 'MRK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ITC', 'keterangan' => 'ITC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'UD TRUCKS', 'keterangan' => 'UD TRUCKS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'AMERICAN', 'keterangan' => 'AMERICAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MENBERS', 'keterangan' => 'MENBERS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'YORK', 'keterangan' => 'YORK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'BS', 'keterangan' => 'BS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'KOYO', 'keterangan' => 'KOYO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SAKURA', 'keterangan' => 'SAKURA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TOP COLOUR', 'keterangan' => 'TOP COLOUR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'AIR HORN', 'keterangan' => 'AIR HORN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'APM', 'keterangan' => 'APM', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NAGOYA', 'keterangan' => 'NAGOYA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GS', 'keterangan' => 'GS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'DAIWON', 'keterangan' => 'DAIWON', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'DS', 'keterangan' => 'DS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'AEOLUS', 'keterangan' => 'AEOLUS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ITALY', 'keterangan' => 'ITALY', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ND', 'keterangan' => 'ND', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'UMC', 'keterangan' => 'UMC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MITOYO', 'keterangan' => 'MITOYO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'STANLEE STAR', 'keterangan' => 'STANLEE STAR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MDR', 'keterangan' => 'MDR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'UTM', 'keterangan' => 'UTM', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GOODYEAR', 'keterangan' => 'GOODYEAR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SEIKEN', 'keterangan' => 'SEIKEN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'KOTSURU', 'keterangan' => 'KOTSURU', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MEC', 'keterangan' => 'MEC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GP', 'keterangan' => 'GP', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'HHT', 'keterangan' => 'HHT', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'DELTA', 'keterangan' => 'DELTA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'YAZAKI', 'keterangan' => 'YAZAKI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'BK', 'keterangan' => 'BK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SH', 'keterangan' => 'SH', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NATIONAL', 'keterangan' => 'NATIONAL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'UDPD', 'keterangan' => 'UDPD', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'CICADA', 'keterangan' => 'CICADA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SBD', 'keterangan' => 'SBD', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ISM', 'keterangan' => 'ISM', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'LYF', 'keterangan' => 'LYF', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MATSUSAKI', 'keterangan' => 'MATSUSAKI', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => '3 BERLIAN', 'keterangan' => '3 BERLIAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TUNIX', 'keterangan' => 'TUNIX', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'FKK', 'keterangan' => 'FKK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TOP ONE', 'keterangan' => 'TOP ONE', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'BAHCO', 'keterangan' => 'BAHCO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'RIK', 'keterangan' => 'RIK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'AVIAN', 'keterangan' => 'AVIAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MUTOMO', 'keterangan' => 'MUTOMO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'WORTH', 'keterangan' => 'WORTH', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'SUMO', 'keterangan' => 'SUMO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'DNY', 'keterangan' => 'DNY', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'PHILIPS', 'keterangan' => 'PHILIPS', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TSK', 'keterangan' => 'TSK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MRCC', 'keterangan' => 'MRCC', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GARRET', 'keterangan' => 'GARRET', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ETALIA', 'keterangan' => 'ETALIA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NILLER JAPAN', 'keterangan' => 'NILLER JAPAN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'FLOSSER', 'keterangan' => 'FLOSSER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'OHK', 'keterangan' => 'OHK', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'NTN', 'keterangan' => 'NTN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TEKIRO', 'keterangan' => 'TEKIRO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'MSW', 'keterangan' => 'MSW', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GAJAH TUNGGAL', 'keterangan' => 'GAJAH TUNGGAL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'WEILL', 'keterangan' => 'WEILL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'OKAYAMA', 'keterangan' => 'OKAYAMA', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'CORTECO', 'keterangan' => 'CORTECO', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'KNORR', 'keterangan' => 'KNORR', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'GSL', 'keterangan' => 'GSL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'KOBE STEEL', 'keterangan' => 'KOBE STEEL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'BA-JUN', 'keterangan' => 'BA-JUN', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'WAGNER', 'keterangan' => 'WAGNER', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'ZEXEL', 'keterangan' => 'ZEXEL', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
        merk::create([ 'kodemerk' => 'TUV', 'keterangan' => 'TUV', 'statusaktif' => '1', 'modifiedby' => 'ADMIN',]);
    }
}
