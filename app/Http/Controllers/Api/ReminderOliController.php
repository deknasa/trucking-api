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
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan EXPORT KE EXCEL
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
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul"=> "Reminder Penggantian Oli Mesin (Mdn)",
        //     ],
        // ];

        $ReminderOliMesin = (new ReminderOli())->reminderemailolimesin()->get();
        $data = $ReminderOliMesin->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliMesin = json_encode($ReminderOliMesin);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliMesin, 'mesin'));
            
        // return (new EmailReminderOli($ReminderOliMesin,'mesin'))->render();
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
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
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
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul" => "Reminder Penggantian Oli Perseneling (Sby)",
        //     ],
        // ];



        $ReminderOliPersneling = (new ReminderOli())->reminderemailolipersneling()->get();
        $data = $ReminderOliPersneling->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliPersneling = json_encode($data);

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
        //         "toemail"=> "iqbal13rafli@gmail.com",
        //         "ccemail"=> "iqbal_rafli13@yahoo.com",
        //         "bccemail"=> "raflimaestro@gmail.com",
        //         "judul" => "Reminder Penggantian Oli Gardan (Bitung)",
        //     ],
        // ];

        $ReminderOliGardan = (new ReminderOli())->reminderemailoligardan()->get();
        $data = $ReminderOliGardan->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderOliGardan = json_encode($data);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderOliGardan, 'oligardan'));
            
        // return (new EmailReminderOli($ReminderOliGardan,'oligardan'))->render();
    }
    public function sendEmailReminder_ServiceRutin()
    {

        $ReminderServiceRutin = (new ReminderOli())->reminderemailservicerutin()->get();
        $data = $ReminderServiceRutin->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderServiceRutin = json_encode($ReminderServiceRutin);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReminderOli($ReminderServiceRutin, 'ServiceRutin'));
            
        // return (new EmailReminderOli($ReminderServiceRutin,'ServiceRutin'))->render();
    }
}
