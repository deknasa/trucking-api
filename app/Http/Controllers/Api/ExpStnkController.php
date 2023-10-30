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

        $data = [
            (object)[
                "toemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com",
                "ccemail"=> "iqbal13rafli@gmail.com;ryan_vixy1402@yahoo.com;denicetas15@gmail.com",
                "bccemail"=> "ryan_vixy1402@yahoo.com",
                "judul"=> "Reminder Ban Lebih dari 7 Hari di Gdg Sementara/Pihak Ke 3 (Makassar)",
            ],
        ];
        $ExpStnk =  $data;
        $toemail = explode(';',$ExpStnk[0]->toemail);
        $ccemail = explode(';',$ExpStnk[0]->ccemail);
        $bccemail = explode(';',$ExpStnk[0]->bccemail);
        
        $ExpStnk = (new ExpStnk())->reminderemailstnk()->get();
        $ExpStnk = json_encode($ExpStnk);
        Mail::to($toemail)
        ->cc($ccemail)
        ->bcc($bccemail)
        ->send(new ReiminderExpStnk($ExpStnk));
        // return (new ReiminderExpStnk($ExpStnk))->render();

    }


}