<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpStnk;
use App\Mail\ReiminderExpStnk;
use Illuminate\Support\Facades\Mail;

class ExpStnkController extends Controller
{
   /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $expStnk = new ExpStnk();
        return response([
            'data' => $expStnk->get(),
            // 'data' => $expStnk->reminderemailstnk(),
            'attributes' => [
                'totalRows' => $expStnk->totalRows,
                'totalPages' => $expStnk->totalPages
            ]
        ]);
    }

    public function sendEmailReminder()
    {

        // $data = [
        //     (object)[
        //         "toemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
        //         "ccemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
        //         "bccemail"=> "ryan_vixy1402@yahoo.com",
        //         "judul"=> "Reminder Ban Lebih dari 7 Hari di Gdg Sementara/Pihak Ke 3 (Makassar)",
        //     ],
        // ];
        $ExpStnk = (new ExpStnk())->reminderemailstnk()->get();
        $data = $ExpStnk->toArray();
        $toemail = explode(';',$data[0]->toemail);
        $ccemail = explode(';',$data[0]->ccemail);
        $bccemail = explode(';',$data[0]->bccemail);
        $ExpStnk = json_encode($ExpStnk);
        Mail::to($toemail)
        ->cc($ccemail)
        ->bcc($bccemail)
        ->send(new ReiminderExpStnk($ExpStnk));
        // return (new ReiminderExpStnk($ExpStnk))->render();

    }


}