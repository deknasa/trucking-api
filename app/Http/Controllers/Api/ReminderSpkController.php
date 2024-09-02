<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReminderSpk;
use App\Models\ExpStnk;
use App\Mail\EmailReiminderSpk;
use App\Models\LaporanBanGudangSementara;
use Illuminate\Support\Facades\Mail;

class ReminderSpkController extends Controller
{
    /**
     * @ClassName 
     * ReminderSpkController
     * @Detail ReminderSpkDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $reminderSpk = new ReminderSpk();
        $getdetail=0;
        return response([
            'data' => $reminderSpk->get($getdetail),
            'attributes' => [
                'totalRows' => $reminderSpk->totalRows,
                'totalPages' => $reminderSpk->totalPages
            ]
        ]);
    }

    public function sendEmailReminder()
    {

        // $data = [
        //     (object)[
        //         "tgl"=> "2023-11-15",
        //         "gudang"=> "GUDANG PIHAK KE-3",
        //         "tanggal"=> "11-Oktober-2023",
        //         "nopg"=> "PG 0020/X/2023",
        //         "kodeban"=> "04817106",
        //         "warna"=> "RED",
        //         "toemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail"=> "ryan_vixy1402@yahoo.com",
        //         "judul"=> "Reminder Ban Lebih dari 7 Hari di Gdg Sementara/Pihak Ke 3 (Makassar)",
        //     ],
        // ];
        // // $ExpStnk =  $data;
        // // $ExpStnk = (new ReminderSpk())->reminderemailbanpihakke3()->get();
        // // $data = $ExpStnk->toArray();
        // $toemail = explode(';',$ExpStnk[0]->toemail);
        // $ccemail = explode(';',$ExpStnk[0]->ccemail);
        // $bccemail = explode(';',$ExpStnk[0]->bccemail);
        // $expSTNK = json_encode($ExpStnk);
        // Mail::to($toemail)
        // ->cc($ccemail)
        // ->bcc($bccemail)
        // ->send(new EmailReiminderSpk($expSTNK));
        // // return (new EmailReiminderSpk($expSTNK))->render();

        $ReminderBan = (new LaporanBanGudangSementara())->reminderemailbanpihakke3()->get();
        $data = $ReminderBan->toArray();
        $toemail = explode(';', $data[0]->toemail);
        $ccemail = explode(';', $data[0]->ccemail);
        $bccemail = explode(';', $data[0]->bccemail);
        $ReminderBan = json_encode($ReminderBan);

        Mail::to($toemail)
            ->cc($ccemail)
            ->bcc($bccemail)
            ->send(new EmailReiminderSpk($ReminderBan));

                //    return (new EmailReiminderSpk($ReminderBan))->render();
    }
}
