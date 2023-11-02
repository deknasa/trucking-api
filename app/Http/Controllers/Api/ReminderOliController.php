<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderOli;
use App\Mail\EmailReminderOli;
use Illuminate\Support\Facades\Mail;

class ReminderOliController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {

        $reminderOli = new ReminderOli();
        // dd(system('getmac'));
        return response([
            'data' => $reminderOli->get(request()->status),
            'attributes' => [
                'totalRows' => $reminderOli->totalRows,
                'totalPages' => $reminderOli->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function export()
    {
    }


    public function sendEmailReminder_olimesin()
    {

        // $data = [
        //     (object)[
        //         "tgl"=> "2023-11-15",
        //         "kodetrado"=> "BK SKSK HY",
        //         "tanggal"=> "11-Oktober-2023",
        //         "batasganti"=> "10000",
        //         "kberjalan"=> "3000",
        //         "Keterangan"=> "04817106",
        //         "warna"=> "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail"=> "ryan_vixy1402@yahoo.com",
        //         "judul"=> "Reminder Penggantian Oli Mesin (Mdn)",
        //     ],
        // ];

        $ReminderOliMesin = (new ReminderOli())->reminderemailolimesin()->get();
        $data = $ReminderOliMesin->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliMesin = json_encode($ReminderOliMesin);

        // Mail::to($toemail)
        //     ->cc($ccemail)
        //     ->bcc($bccemail)
        //     ->send(new EmailReminderOli($ReminderOliMesin, 'mesin'));
            
        return (new EmailReminderOli($ReminderOliMesin,'mesin'))->render();
    }
    public function sendEmailReminder_saringanhawa()
    {

        // $data = [
        //     (object)[
        //         "tgl" => "2023-11-15",
        //         "kodetrado" => "BK SKSK HY",
        //         "tanggal" => "11-Oktober-2023",
        //         "batasganti" => "10000",
        //         "kberjalan" => "3000",
        //         "Keterangan" => "04817106",
        //         "warna" => "RED",
        //         "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail" => "ryan_vixy1402@yahoo.com",
        //         "judul" => "RReminder Penggantian Saringan Hawa (Mks)",
        //     ],
        // ];

        $ReminderSaringanHawa = (new ReminderOli())->reminderemailsaringanhawa()->get();
        $data = $ReminderSaringanHawa->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderSaringanHawa = json_encode($ReminderSaringanHawa);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderSaringanHawa, 'saringanhawa'));
            
        // return (new EmailReminderOli($ReminderSaringanHawa,'saringanhawa'))->render();
    }
    public function sendEmailReminder_perseneling()
    {

        // $data = [
        //     (object)[
        //         "tgl" => "2023-11-15",
        //         "kodetrado" => "BK SKSK HY",
        //         "tanggal" => "11-Oktober-2023",
        //         "batasganti" => "10000",
        //         "kberjalan" => "3000",
        //         "Keterangan" => "04817106",
        //         "warna" => "RED",
        //         "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail" => "ryan_vixy1402@yahoo.com",
        //         "judul" => "Reminder Penggantian Oli Perseneling (Sby)",
        //     ],
        // ];



        $ReminderOliPersneling = (new ReminderOli())->reminderemailolipersneling()->get();
        $data = $ReminderOliPersneling->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliPersneling = json_encode($ReminderOliPersneling);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliPersneling, 'perseneling'));
            
        // return (new EmailReminderOli($ReminderOliPersneling,'perseneling'))->render();

    }
    public function sendEmailReminder_oligardan()
    {

        // $data = [
        //     (object)[
        //         "tgl" => "2023-11-15",
        //         "kodetrado" => "BK SKSK HY",
        //         "tanggal" => "11-Oktober-2023",
        //         "batasganti" => "10000",
        //         "kberjalan" => "3000",
        //         "Keterangan" => "04817106",
        //         "warna" => "RED",
        //         "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail" => "ryan_vixy1402@yahoo.com",
        //         "judul" => "Reminder Penggantian Oli Gardan (Bitung)",
        //     ],
        // ];

        $ReminderOliGardan = (new ReminderOli())->reminderemailoligardan()->get();
        $data = $ReminderOliGardan->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliGardan = json_encode($ReminderOliGardan);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliGardan, 'oligardan'));
            
        // return (new EmailReminderOli($ReminderOliGardan,'oligardan'))->render();
    }
    public function sendEmailReminder_ServiceRutin()
    {

        $data = [
            (object)[
                'kodetrado' => 'L 9051 UN',
                'tanggaldari' => '28-Agustus-2023',
                'tanggalsampai' => '13-September-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Jadwal Service Rutin (Surabaya)",
            ],
            (object)[
                'kodetrado' => 'L 9975 UL',
                'tanggaldari' => '8-September-2023',
                'tanggalsampai' => '25-September-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 8571 UY',
                'tanggaldari' => '12-September-2023',
                'tanggalsampai' => '29-September-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9202 UL',
                'tanggaldari' => '19-September-2023',
                'tanggalsampai' => '6-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9776 UJ',
                'tanggaldari' => '7-Oktober-2023',
                'tanggalsampai' => '24-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9810 EJ',
                'tanggaldari' => '7-Oktober-2023',
                'tanggalsampai' => '24-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9879 UQA',
                'tanggaldari' => '9-Oktober-2023',
                'tanggalsampai' => '25-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 8721 UK',
                'tanggaldari' => '9-Oktober-2023',
                'tanggalsampai' => '25-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9670 UEH',
                'tanggaldari' => '9-Oktober-2023',
                'tanggalsampai' => '25-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 8825 UL',
                'tanggaldari' => '10-Oktober-2023',
                'tanggalsampai' => '26-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9523 UZ',
                'tanggaldari' => '10-Oktober-2023',
                'tanggalsampai' => '26-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9776 WV',
                'tanggaldari' => '10-Oktober-2023',
                'tanggalsampai' => '26-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9051 UR',
                'tanggaldari' => '11-Oktober-2023',
                'tanggalsampai' => '27-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9210 UL',
                'tanggaldari' => '11-Oktober-2023',
                'tanggalsampai' => '27-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9691 VM',
                'tanggaldari' => '11-Oktober-2023',
                'tanggalsampai' => '27-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9050 UT',
                'tanggaldari' => '12-Oktober-2023',
                'tanggalsampai' => '28-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 8690 UP',
                'tanggaldari' => '12-Oktober-2023',
                'tanggalsampai' => '28-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9193 UQ',
                'tanggaldari' => '12-Oktober-2023',
                'tanggalsampai' => '28-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9665 AL',
                'tanggaldari' => '13-Oktober-2023',
                'tanggalsampai' => '30-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9136 S',
                'tanggaldari' => '13-Oktober-2023',
                'tanggalsampai' => '30-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9372 UT',
                'tanggaldari' => '14-Oktober-2023',
                'tanggalsampai' => '31-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'B 9411 VS',
                'tanggaldari' => '14-Oktober-2023',
                'tanggalsampai' => '31-Oktober-2023',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],
            (object)[
                'kodetrado' => 'L 9584 UE',
                'tanggaldari' => '16-Oktober-2023',
                'tanggalsampai' => '1-November-2022',
                'keterangan' => '',
                'warna' => 'yellow',
                "toemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail" => "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail" => "ryan_vixy1402@yahoo.com",
                "judul" => "Reminder Service Rutin (Bitung)",
            ],

        ];



        $ExpStnk =  $data;
        // $ExpStnk = (new ExpStnk())->reminderemailstnk()->get();
        // $data = $ExpStnk->toArray();
        $toemail = explode(';', $ExpStnk[0]->toemail);
        $ccemail = explode(';', $ExpStnk[0]->ccemail);
        $bccemail = explode(';', $ExpStnk[0]->bccemail);
        $expSTNK = json_encode($ExpStnk);
        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($expSTNK, 'ServiceRutin'));
        // return (new EmailReminderOli($expSTNK,'ServiceRutin'))->render();
    }
}
