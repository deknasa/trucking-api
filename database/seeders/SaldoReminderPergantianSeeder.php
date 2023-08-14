<?php

namespace Database\Seeders;

use App\Models\SaldoReminderpergantian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class SaldoReminderPergantianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement("delete saldoreminderpergantian");
        DB::statement("DBCC CHECKIDENT ('saldoreminderpergantian', RESEED, 0);");

        saldoreminderpergantian::create(['trado_id' => '1', 'nopol' => 'B 9120 QZ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2018/8/10', 'tglsampai' => '2023/4/30', 'jarak' => '107425.80',]);
        saldoreminderpergantian::create(['trado_id' => '1', 'nopol' => 'B 9120 QZ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/4/26', 'tglsampai' => '2023/4/30', 'jarak' => '17555.80',]);
        saldoreminderpergantian::create(['trado_id' => '1', 'nopol' => 'B 9120 QZ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/9/21', 'tglsampai' => '2023/4/30', 'jarak' => '7616.00',]);
        saldoreminderpergantian::create(['trado_id' => '1', 'nopol' => 'B 9120 QZ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/4/26', 'tglsampai' => '2023/4/30', 'jarak' => '17555.80',]);
        saldoreminderpergantian::create(['trado_id' => '1', 'nopol' => 'B 9120 QZ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/3/7', 'tglsampai' => '2023/4/30', 'jarak' => '22111.80',]);
        saldoreminderpergantian::create(['trado_id' => '2', 'nopol' => 'B 9270 WX', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2019/12/18', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '2', 'nopol' => 'B 9270 WX', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2019/12/18', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '2', 'nopol' => 'B 9270 WX', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2019/12/18', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '2', 'nopol' => 'B 9270 WX', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2019/12/20', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '3', 'nopol' => 'B 9451 AA', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2020/4/13', 'tglsampai' => '2023/4/30', 'jarak' => '316.00',]);
        saldoreminderpergantian::create(['trado_id' => '3', 'nopol' => 'B 9451 AA', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2020/4/3', 'tglsampai' => '2023/4/30', 'jarak' => '316.00',]);
        saldoreminderpergantian::create(['trado_id' => '3', 'nopol' => 'B 9451 AA', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2020/4/14', 'tglsampai' => '2023/4/30', 'jarak' => '316.00',]);
        saldoreminderpergantian::create(['trado_id' => '3', 'nopol' => 'B 9451 AA', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2020/3/27', 'tglsampai' => '2023/4/30', 'jarak' => '316.00',]);
        saldoreminderpergantian::create(['trado_id' => '3', 'nopol' => 'B 9451 AA', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2020/4/14', 'tglsampai' => '2023/4/30', 'jarak' => '316.00',]);
        saldoreminderpergantian::create(['trado_id' => '4', 'nopol' => 'B 9465 EZ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2019/11/14', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '4', 'nopol' => 'B 9465 EZ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2019/11/14', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '4', 'nopol' => 'B 9465 EZ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2019/11/14', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '5', 'nopol' => 'B 9492 SU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2020/2/14', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '5', 'nopol' => 'B 9492 SU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2020/2/14', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '5', 'nopol' => 'B 9492 SU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2020/2/14', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '6', 'nopol' => 'B 9508 PH', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/7/15', 'tglsampai' => '2023/4/30', 'jarak' => '57017.40',]);
        saldoreminderpergantian::create(['trado_id' => '6', 'nopol' => 'B 9508 PH', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/8/30', 'tglsampai' => '2023/4/30', 'jarak' => '17839.40',]);
        saldoreminderpergantian::create(['trado_id' => '6', 'nopol' => 'B 9508 PH', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/12/17', 'tglsampai' => '2023/4/30', 'jarak' => '8342.40',]);
        saldoreminderpergantian::create(['trado_id' => '6', 'nopol' => 'B 9508 PH', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/2/21', 'tglsampai' => '2023/4/30', 'jarak' => '37906.40',]);
        saldoreminderpergantian::create(['trado_id' => '6', 'nopol' => 'B 9508 PH', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/7/8', 'tglsampai' => '2023/4/30', 'jarak' => '22115.40',]);
        saldoreminderpergantian::create(['trado_id' => '7', 'nopol' => 'B 9614 QZ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2022/6/16', 'tglsampai' => '2023/4/30', 'jarak' => '12800.10',]);
        saldoreminderpergantian::create(['trado_id' => '7', 'nopol' => 'B 9614 QZ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/9/10', 'tglsampai' => '2023/4/30', 'jarak' => '32444.50',]);
        saldoreminderpergantian::create(['trado_id' => '7', 'nopol' => 'B 9614 QZ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/2/15', 'tglsampai' => '2023/4/30', 'jarak' => '4071.10',]);
        saldoreminderpergantian::create(['trado_id' => '7', 'nopol' => 'B 9614 QZ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/9/10', 'tglsampai' => '2023/4/30', 'jarak' => '32444.50',]);
        saldoreminderpergantian::create(['trado_id' => '7', 'nopol' => 'B 9614 QZ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2023/2/24', 'tglsampai' => '2023/4/30', 'jarak' => '3644.10',]);
        saldoreminderpergantian::create(['trado_id' => '8', 'nopol' => 'B 9668 QZ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/7/28', 'tglsampai' => '2023/4/30', 'jarak' => '49479.20',]);
        saldoreminderpergantian::create(['trado_id' => '8', 'nopol' => 'B 9668 QZ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/4/8', 'tglsampai' => '2023/4/30', 'jarak' => '30525.20',]);
        saldoreminderpergantian::create(['trado_id' => '8', 'nopol' => 'B 9668 QZ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/3/28', 'tglsampai' => '2023/4/30', 'jarak' => '2068.00',]);
        saldoreminderpergantian::create(['trado_id' => '8', 'nopol' => 'B 9668 QZ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/7/19', 'tglsampai' => '2023/4/30', 'jarak' => '21156.00',]);
        saldoreminderpergantian::create(['trado_id' => '8', 'nopol' => 'B 9668 QZ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/10/13', 'tglsampai' => '2023/4/30', 'jarak' => '14626.00',]);
        saldoreminderpergantian::create(['trado_id' => '10', 'nopol' => 'B 9776 WV', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2020/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '10', 'nopol' => 'B 9776 WV', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2020/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '10', 'nopol' => 'B 9776 WV', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2020/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '10', 'nopol' => 'B 9776 WV', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2020/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '11', 'nopol' => 'B 9807 ZI', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2023/2/9', 'tglsampai' => '2023/4/30', 'jarak' => '3004.90',]);
        saldoreminderpergantian::create(['trado_id' => '11', 'nopol' => 'B 9807 ZI', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/3/31', 'tglsampai' => '2023/4/30', 'jarak' => '29442.40',]);
        saldoreminderpergantian::create(['trado_id' => '11', 'nopol' => 'B 9807 ZI', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/4/6', 'tglsampai' => '2023/4/30', 'jarak' => '415.40',]);
        saldoreminderpergantian::create(['trado_id' => '11', 'nopol' => 'B 9807 ZI', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/2/16', 'tglsampai' => '2023/4/30', 'jarak' => '32584.40',]);
        saldoreminderpergantian::create(['trado_id' => '11', 'nopol' => 'B 9807 ZI', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2023/1/26', 'tglsampai' => '2023/4/30', 'jarak' => '3843.90',]);
        saldoreminderpergantian::create(['trado_id' => '12', 'nopol' => 'B 9949 JH', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/12/7', 'tglsampai' => '2023/4/30', 'jarak' => '31424.40',]);
        saldoreminderpergantian::create(['trado_id' => '12', 'nopol' => 'B 9949 JH', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/3/3', 'tglsampai' => '2023/4/30', 'jarak' => '48246.40',]);
        saldoreminderpergantian::create(['trado_id' => '12', 'nopol' => 'B 9949 JH', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/12/9', 'tglsampai' => '2023/4/30', 'jarak' => '8471.60',]);
        saldoreminderpergantian::create(['trado_id' => '12', 'nopol' => 'B 9949 JH', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/5/9', 'tglsampai' => '2023/4/30', 'jarak' => '24111.40',]);
        saldoreminderpergantian::create(['trado_id' => '12', 'nopol' => 'B 9949 JH', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2023/1/5', 'tglsampai' => '2023/4/30', 'jarak' => '6620.60',]);
        saldoreminderpergantian::create(['trado_id' => '14', 'nopol' => 'BK 8007 XA', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2022/2/15', 'tglsampai' => '2023/4/30', 'jarak' => '12285.50',]);
        saldoreminderpergantian::create(['trado_id' => '14', 'nopol' => 'BK 8007 XA', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/9/19', 'tglsampai' => '2023/4/30', 'jarak' => '5903.50',]);
        saldoreminderpergantian::create(['trado_id' => '14', 'nopol' => 'BK 8007 XA', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/7/8', 'tglsampai' => '2023/4/30', 'jarak' => '8068.50',]);
        saldoreminderpergantian::create(['trado_id' => '14', 'nopol' => 'BK 8007 XA', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/9/15', 'tglsampai' => '2023/4/30', 'jarak' => '19975.50',]);
        saldoreminderpergantian::create(['trado_id' => '14', 'nopol' => 'BK 8007 XA', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/1/31', 'tglsampai' => '2023/4/30', 'jarak' => '13168.50',]);
        saldoreminderpergantian::create(['trado_id' => '15', 'nopol' => 'BK 8037 BQ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2020/2/14', 'tglsampai' => '2023/4/30', 'jarak' => '65224.20',]);
        saldoreminderpergantian::create(['trado_id' => '15', 'nopol' => 'BK 8037 BQ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/1/21', 'tglsampai' => '2023/4/30', 'jarak' => '21592.20',]);
        saldoreminderpergantian::create(['trado_id' => '15', 'nopol' => 'BK 8037 BQ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/2/22', 'tglsampai' => '2023/4/30', 'jarak' => '2612.50',]);
        saldoreminderpergantian::create(['trado_id' => '15', 'nopol' => 'BK 8037 BQ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/10/19', 'tglsampai' => '2023/4/30', 'jarak' => '8673.50',]);
        saldoreminderpergantian::create(['trado_id' => '15', 'nopol' => 'BK 8037 BQ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2023/4/17', 'tglsampai' => '2023/4/30', 'jarak' => '256.10',]);
        saldoreminderpergantian::create(['trado_id' => '16', 'nopol' => 'BK 8050 CJ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2023/1/19', 'tglsampai' => '2023/4/30', 'jarak' => '3682.20',]);
        saldoreminderpergantian::create(['trado_id' => '16', 'nopol' => 'BK 8050 CJ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2023/4/18', 'tglsampai' => '2023/4/30', 'jarak' => '118.00',]);
        saldoreminderpergantian::create(['trado_id' => '16', 'nopol' => 'BK 8050 CJ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/4/10', 'tglsampai' => '2023/4/30', 'jarak' => '258.00',]);
        saldoreminderpergantian::create(['trado_id' => '16', 'nopol' => 'BK 8050 CJ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2023/4/18', 'tglsampai' => '2023/4/30', 'jarak' => '118.00',]);
        saldoreminderpergantian::create(['trado_id' => '16', 'nopol' => 'BK 8050 CJ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/1/14', 'tglsampai' => '2023/4/30', 'jarak' => '24015.40',]);
        saldoreminderpergantian::create(['trado_id' => '17', 'nopol' => 'BK 8145 CE', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2020/11/19', 'tglsampai' => '2023/4/30', 'jarak' => '37399.10',]);
        saldoreminderpergantian::create(['trado_id' => '17', 'nopol' => 'BK 8145 CE', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/4/19', 'tglsampai' => '2023/4/30', 'jarak' => '7562.10',]);
        saldoreminderpergantian::create(['trado_id' => '17', 'nopol' => 'BK 8145 CE', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/4/29', 'tglsampai' => '2023/4/30', 'jarak' => '6492.10',]);
        saldoreminderpergantian::create(['trado_id' => '17', 'nopol' => 'BK 8145 CE', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/5/18', 'tglsampai' => '2023/4/30', 'jarak' => '5209.10',]);
        saldoreminderpergantian::create(['trado_id' => '17', 'nopol' => 'BK 8145 CE', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/4/20', 'tglsampai' => '2023/4/30', 'jarak' => '7495.10',]);
        saldoreminderpergantian::create(['trado_id' => '18', 'nopol' => 'BK 8208 BQ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2023/4/11', 'tglsampai' => '2023/4/30', 'jarak' => '613.10',]);
        saldoreminderpergantian::create(['trado_id' => '18', 'nopol' => 'BK 8208 BQ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/4/19', 'tglsampai' => '2023/4/30', 'jarak' => '26536.80',]);
        saldoreminderpergantian::create(['trado_id' => '18', 'nopol' => 'BK 8208 BQ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/4/7', 'tglsampai' => '2023/4/30', 'jarak' => '4515.80',]);
        saldoreminderpergantian::create(['trado_id' => '18', 'nopol' => 'BK 8208 BQ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/2/16', 'tglsampai' => '2023/4/30', 'jarak' => '31501.80',]);
        saldoreminderpergantian::create(['trado_id' => '18', 'nopol' => 'BK 8208 BQ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2023/2/6', 'tglsampai' => '2023/4/30', 'jarak' => '3797.80',]);
        saldoreminderpergantian::create(['trado_id' => '19', 'nopol' => 'BK 8342 CJ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2020/9/8', 'tglsampai' => '2023/4/30', 'jarak' => '55942.20',]);
        saldoreminderpergantian::create(['trado_id' => '19', 'nopol' => 'BK 8342 CJ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/4/18', 'tglsampai' => '2023/4/30', 'jarak' => '17624.20',]);
        saldoreminderpergantian::create(['trado_id' => '19', 'nopol' => 'BK 8342 CJ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/10/5', 'tglsampai' => '2023/4/30', 'jarak' => '8686.40',]);
        saldoreminderpergantian::create(['trado_id' => '19', 'nopol' => 'BK 8342 CJ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/4/18', 'tglsampai' => '2023/4/30', 'jarak' => '17624.20',]);
        saldoreminderpergantian::create(['trado_id' => '19', 'nopol' => 'BK 8342 CJ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/10/19', 'tglsampai' => '2023/4/30', 'jarak' => '8530.40',]);
        saldoreminderpergantian::create(['trado_id' => '20', 'nopol' => 'BK 8405 BM', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2020/12/22', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '21', 'nopol' => 'BK 8431 LU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2014/11/7', 'tglsampai' => '2023/4/30', 'jarak' => '2351.00',]);
        saldoreminderpergantian::create(['trado_id' => '21', 'nopol' => 'BK 8431 LU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2014/6/25', 'tglsampai' => '2023/4/30', 'jarak' => '12450.00',]);
        saldoreminderpergantian::create(['trado_id' => '22', 'nopol' => 'BK 8596 LU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/1/11', 'tglsampai' => '2023/4/30', 'jarak' => '46537.20',]);
        saldoreminderpergantian::create(['trado_id' => '22', 'nopol' => 'BK 8596 LU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/6/25', 'tglsampai' => '2023/4/30', 'jarak' => '10607.00',]);
        saldoreminderpergantian::create(['trado_id' => '22', 'nopol' => 'BK 8596 LU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/11/7', 'tglsampai' => '2023/4/30', 'jarak' => '6693.00',]);
        saldoreminderpergantian::create(['trado_id' => '22', 'nopol' => 'BK 8596 LU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2023/1/25', 'tglsampai' => '2023/4/30', 'jarak' => '2989.00',]);
        saldoreminderpergantian::create(['trado_id' => '22', 'nopol' => 'BK 8596 LU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/10/13', 'tglsampai' => '2023/4/30', 'jarak' => '7494.00',]);
        saldoreminderpergantian::create(['trado_id' => '23', 'nopol' => 'BK 8669 BG', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/1/20', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '23', 'nopol' => 'BK 8669 BG', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '23', 'nopol' => 'BK 8669 BG', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2021/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '23', 'nopol' => 'BK 8669 BG', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/1/13', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '23', 'nopol' => 'BK 8669 BG', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2021/2/1', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '24', 'nopol' => 'BK 8739 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2020/10/9', 'tglsampai' => '2023/4/30', 'jarak' => '47259.80',]);
        saldoreminderpergantian::create(['trado_id' => '24', 'nopol' => 'BK 8739 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/1/28', 'tglsampai' => '2023/4/30', 'jarak' => '19172.80',]);
        saldoreminderpergantian::create(['trado_id' => '24', 'nopol' => 'BK 8739 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/1/28', 'tglsampai' => '2023/4/30', 'jarak' => '4687.40',]);
        saldoreminderpergantian::create(['trado_id' => '24', 'nopol' => 'BK 8739 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/7/28', 'tglsampai' => '2023/4/30', 'jarak' => '25511.80',]);
        saldoreminderpergantian::create(['trado_id' => '24', 'nopol' => 'BK 8739 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/7/5', 'tglsampai' => '2023/4/30', 'jarak' => '14391.40',]);
        saldoreminderpergantian::create(['trado_id' => '25', 'nopol' => 'BK 8741 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2023/3/20', 'tglsampai' => '2023/4/30', 'jarak' => '964.90',]);
        saldoreminderpergantian::create(['trado_id' => '25', 'nopol' => 'BK 8741 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/7/28', 'tglsampai' => '2023/4/30', 'jarak' => '8402.80',]);
        saldoreminderpergantian::create(['trado_id' => '25', 'nopol' => 'BK 8741 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/3/27', 'tglsampai' => '2023/4/30', 'jarak' => '946.90',]);
        saldoreminderpergantian::create(['trado_id' => '25', 'nopol' => 'BK 8741 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/7/28', 'tglsampai' => '2023/4/30', 'jarak' => '8402.80',]);
        saldoreminderpergantian::create(['trado_id' => '25', 'nopol' => 'BK 8741 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/5/12', 'tglsampai' => '2023/4/30', 'jarak' => '12458.10',]);
        saldoreminderpergantian::create(['trado_id' => '26', 'nopol' => 'BK 8742 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2014/5/14', 'tglsampai' => '2023/4/30', 'jarak' => '4925.00',]);
        saldoreminderpergantian::create(['trado_id' => '26', 'nopol' => 'BK 8742 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2014/5/8', 'tglsampai' => '2023/4/30', 'jarak' => '5324.00',]);
        saldoreminderpergantian::create(['trado_id' => '26', 'nopol' => 'BK 8742 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2014/3/28', 'tglsampai' => '2023/4/30', 'jarak' => '9978.00',]);
        saldoreminderpergantian::create(['trado_id' => '26', 'nopol' => 'BK 8742 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2014/6/25', 'tglsampai' => '2023/4/30', 'jarak' => '1395.00',]);
        saldoreminderpergantian::create(['trado_id' => '27', 'nopol' => 'BK 8743 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/8/24', 'tglsampai' => '2023/4/30', 'jarak' => '30554.00',]);
        saldoreminderpergantian::create(['trado_id' => '27', 'nopol' => 'BK 8743 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/5/20', 'tglsampai' => '2023/4/30', 'jarak' => '36808.00',]);
        saldoreminderpergantian::create(['trado_id' => '27', 'nopol' => 'BK 8743 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/10/31', 'tglsampai' => '2023/4/30', 'jarak' => '6770.20',]);
        saldoreminderpergantian::create(['trado_id' => '27', 'nopol' => 'BK 8743 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/5/20', 'tglsampai' => '2023/4/30', 'jarak' => '36808.00',]);
        saldoreminderpergantian::create(['trado_id' => '27', 'nopol' => 'BK 8743 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/9/1', 'tglsampai' => '2023/4/30', 'jarak' => '7832.20',]);
        saldoreminderpergantian::create(['trado_id' => '28', 'nopol' => 'BK 8745 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/7/26', 'tglsampai' => '2023/4/30', 'jarak' => '24232.20',]);
        saldoreminderpergantian::create(['trado_id' => '28', 'nopol' => 'BK 8745 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/11/25', 'tglsampai' => '2023/4/30', 'jarak' => '15692.20',]);
        saldoreminderpergantian::create(['trado_id' => '28', 'nopol' => 'BK 8745 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/8/1', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '28', 'nopol' => 'BK 8745 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/11/25', 'tglsampai' => '2023/4/30', 'jarak' => '15692.20',]);
        saldoreminderpergantian::create(['trado_id' => '28', 'nopol' => 'BK 8745 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2021/9/10', 'tglsampai' => '2023/4/30', 'jarak' => '21323.20',]);
        saldoreminderpergantian::create(['trado_id' => '29', 'nopol' => 'BK 8746 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2019/5/3', 'tglsampai' => '2023/4/30', 'jarak' => '17185.00',]);
        saldoreminderpergantian::create(['trado_id' => '29', 'nopol' => 'BK 8746 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2019/2/20', 'tglsampai' => '2023/4/30', 'jarak' => '21696.00',]);
        saldoreminderpergantian::create(['trado_id' => '29', 'nopol' => 'BK 8746 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2019/11/25', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '29', 'nopol' => 'BK 8746 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2019/2/20', 'tglsampai' => '2023/4/30', 'jarak' => '21696.00',]);
        saldoreminderpergantian::create(['trado_id' => '29', 'nopol' => 'BK 8746 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2019/9/2', 'tglsampai' => '2023/4/30', 'jarak' => '6383.00',]);
        saldoreminderpergantian::create(['trado_id' => '30', 'nopol' => 'BK 8747 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2022/4/14', 'tglsampai' => '2023/4/30', 'jarak' => '17585.00',]);
        saldoreminderpergantian::create(['trado_id' => '30', 'nopol' => 'BK 8747 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/9/7', 'tglsampai' => '2023/4/30', 'jarak' => '27505.00',]);
        saldoreminderpergantian::create(['trado_id' => '30', 'nopol' => 'BK 8747 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/10/26', 'tglsampai' => '2023/4/30', 'jarak' => '7538.60',]);
        saldoreminderpergantian::create(['trado_id' => '30', 'nopol' => 'BK 8747 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2020/8/28', 'tglsampai' => '2023/4/30', 'jarak' => '48695.00',]);
        saldoreminderpergantian::create(['trado_id' => '30', 'nopol' => 'BK 8747 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2023/4/4', 'tglsampai' => '2023/4/30', 'jarak' => '754.00',]);
        saldoreminderpergantian::create(['trado_id' => '31', 'nopol' => 'BK 8834 BK', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2019/12/3', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '31', 'nopol' => 'BK 8834 BK', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2019/12/3', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '31', 'nopol' => 'BK 8834 BK', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2019/12/3', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '34', 'nopol' => 'BK 9415 LO', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/10/1', 'tglsampai' => '2023/4/30', 'jarak' => '21255.50',]);
        saldoreminderpergantian::create(['trado_id' => '34', 'nopol' => 'BK 9415 LO', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2023/1/9', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '34', 'nopol' => 'BK 9415 LO', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/11/24', 'tglsampai' => '2023/4/30', 'jarak' => '1091.00',]);
        saldoreminderpergantian::create(['trado_id' => '34', 'nopol' => 'BK 9415 LO', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/10/5', 'tglsampai' => '2023/4/30', 'jarak' => '21195.50',]);
        saldoreminderpergantian::create(['trado_id' => '34', 'nopol' => 'BK 9415 LO', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2021/10/5', 'tglsampai' => '2023/4/30', 'jarak' => '21195.50',]);
        saldoreminderpergantian::create(['trado_id' => '35', 'nopol' => 'BK 9418 LO', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/7/6', 'tglsampai' => '2023/4/30', 'jarak' => '27337.40',]);
        saldoreminderpergantian::create(['trado_id' => '35', 'nopol' => 'BK 9418 LO', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/11/4', 'tglsampai' => '2023/4/30', 'jarak' => '18461.40',]);
        saldoreminderpergantian::create(['trado_id' => '35', 'nopol' => 'BK 9418 LO', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/5/25', 'tglsampai' => '2023/4/30', 'jarak' => '3585.40',]);
        saldoreminderpergantian::create(['trado_id' => '35', 'nopol' => 'BK 9418 LO', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/11/26', 'tglsampai' => '2023/4/30', 'jarak' => '16495.40',]);
        saldoreminderpergantian::create(['trado_id' => '35', 'nopol' => 'BK 9418 LO', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2021/11/15', 'tglsampai' => '2023/4/30', 'jarak' => '17195.40',]);
        saldoreminderpergantian::create(['trado_id' => '36', 'nopol' => 'BK 9451 LO', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2022/3/21', 'tglsampai' => '2023/4/30', 'jarak' => '7033.20',]);
        saldoreminderpergantian::create(['trado_id' => '36', 'nopol' => 'BK 9451 LO', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/2/9', 'tglsampai' => '2023/4/30', 'jarak' => '35654.20',]);
        saldoreminderpergantian::create(['trado_id' => '36', 'nopol' => 'BK 9451 LO', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/10/24', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '36', 'nopol' => 'BK 9451 LO', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2022/10/20', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '36', 'nopol' => 'BK 9451 LO', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/11/2', 'tglsampai' => '2023/4/30', 'jarak' => '0.00',]);
        saldoreminderpergantian::create(['trado_id' => '38', 'nopol' => 'BK 9541 BO', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/10/12', 'tglsampai' => '2023/4/30', 'jarak' => '33348.50',]);
        saldoreminderpergantian::create(['trado_id' => '38', 'nopol' => 'BK 9541 BO', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/9/24', 'tglsampai' => '2023/4/30', 'jarak' => '34448.50',]);
        saldoreminderpergantian::create(['trado_id' => '38', 'nopol' => 'BK 9541 BO', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/11/21', 'tglsampai' => '2023/4/30', 'jarak' => '8657.10',]);
        saldoreminderpergantian::create(['trado_id' => '38', 'nopol' => 'BK 9541 BO', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/8/4', 'tglsampai' => '2023/4/30', 'jarak' => '38847.50',]);
        saldoreminderpergantian::create(['trado_id' => '38', 'nopol' => 'BK 9541 BO', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/4/26', 'tglsampai' => '2023/4/30', 'jarak' => '19689.50',]);
        saldoreminderpergantian::create(['trado_id' => '39', 'nopol' => 'BK 9665 BU', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2021/11/19', 'tglsampai' => '2023/4/30', 'jarak' => '29494.80',]);
        saldoreminderpergantian::create(['trado_id' => '39', 'nopol' => 'BK 9665 BU', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2021/3/31', 'tglsampai' => '2023/4/30', 'jarak' => '46986.80',]);
        saldoreminderpergantian::create(['trado_id' => '39', 'nopol' => 'BK 9665 BU', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/4/6', 'tglsampai' => '2023/4/30', 'jarak' => '531.00',]);
        saldoreminderpergantian::create(['trado_id' => '39', 'nopol' => 'BK 9665 BU', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/3/31', 'tglsampai' => '2023/4/30', 'jarak' => '46986.80',]);
        saldoreminderpergantian::create(['trado_id' => '39', 'nopol' => 'BK 9665 BU', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2022/12/16', 'tglsampai' => '2023/4/30', 'jarak' => '6327.80',]);
        saldoreminderpergantian::create(['trado_id' => '40', 'nopol' => 'BK 9690 XA / L 8198 UZ ', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2019/9/2', 'tglsampai' => '2023/4/30', 'jarak' => '59492.60',]);
        saldoreminderpergantian::create(['trado_id' => '40', 'nopol' => 'BK 9690 XA / L 8198 UZ ', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/3/7', 'tglsampai' => '2023/4/30', 'jarak' => '5286.60',]);
        saldoreminderpergantian::create(['trado_id' => '40', 'nopol' => 'BK 9690 XA / L 8198 UZ ', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2022/2/2', 'tglsampai' => '2023/4/30', 'jarak' => '7063.60',]);
        saldoreminderpergantian::create(['trado_id' => '40', 'nopol' => 'BK 9690 XA / L 8198 UZ ', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/4/20', 'tglsampai' => '2023/4/30', 'jarak' => '27500.60',]);
        saldoreminderpergantian::create(['trado_id' => '40', 'nopol' => 'BK 9690 XA / L 8198 UZ ', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2021/8/25', 'tglsampai' => '2023/4/30', 'jarak' => '17758.60',]);
        saldoreminderpergantian::create(['trado_id' => '42', 'nopol' => 'BK 9975 BO', 'statusreminder' => 'Penggantian Aki', 'tglawal' => '2022/5/24', 'tglsampai' => '2023/4/30', 'jarak' => '7685.40',]);
        saldoreminderpergantian::create(['trado_id' => '42', 'nopol' => 'BK 9975 BO', 'statusreminder' => 'Penggantian Oli Gardan', 'tglawal' => '2022/5/9', 'tglsampai' => '2023/4/30', 'jarak' => '8555.40',]);
        saldoreminderpergantian::create(['trado_id' => '42', 'nopol' => 'BK 9975 BO', 'statusreminder' => 'Penggantian Oli Mesin', 'tglawal' => '2023/2/20', 'tglsampai' => '2023/4/30', 'jarak' => '2118.70',]);
        saldoreminderpergantian::create(['trado_id' => '42', 'nopol' => 'BK 9975 BO', 'statusreminder' => 'Penggantian Oli Persneling', 'tglawal' => '2021/3/9', 'tglsampai' => '2023/4/30', 'jarak' => '39826.40',]);
        saldoreminderpergantian::create(['trado_id' => '42', 'nopol' => 'BK 9975 BO', 'statusreminder' => 'Penggantian Saringan Hawa', 'tglawal' => '2021/11/12', 'tglsampai' => '2023/4/30', 'jarak' => '20318.40',]);
    }
}
