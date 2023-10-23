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

        $ExpStnk = (new ExpStnk())->reminderemailstnk()->get();
        $toemail = explode(';',$ExpStnk[0]->toemail);
        $ccemail = explode(';',$ExpStnk[0]->ccemail);
        $bccemail = explode(';',$ExpStnk[0]->bccemail);

        $expSTNK = json_encode($ExpStnk);
        Mail::to($toemail)
        ->cc($ccemail)
        ->bcc($bccemail)
        ->send(new ReiminderExpStnk($expSTNK));
        return (new ReiminderExpStnk($expSTNK))->render();

    }


}