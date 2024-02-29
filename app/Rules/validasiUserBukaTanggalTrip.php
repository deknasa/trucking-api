<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiUserBukaTanggalTrip implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $user_id;
    public $validasi;
    public function __construct($user_id)
    {
        //
        $this->user_id = $user_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $id = request()->id;
        $user = request()->user_id;
        $tgl = request()->tglbukti;

        $cekQuery = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->where('approvalbukatanggal_id', $id)
            ->first();
        if ($cekQuery != '') {            
            if($user != $this->user_id){
                $this->validasi = 'DTSA';
                return false;
            }   
        }

        if ($user != $this->user_id) {
            $cekApproval = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
                ->where('tglbukti', date('Y-m-d', strtotime($tgl)))
                ->where('user_id', $user)
                ->orderBy('id', 'desc')
                ->first();

            if ($cekApproval != '') {
                $getBatasInput = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JAMBATASINPUTTRIP')->where('subgrp', 'JAMBATASINPUTTRIP')->first()->text;
                $getBatasHari = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'BATASHARIINPUTTRIP')->where('subgrp', 'BATASHARIINPUTTRIP')->first()->text;
                $tanggal = date('Y-m-d', strtotime("+$getBatasHari days"));
                if ($cekApproval->tglbatas > date('Y-m-d', strtotime($tanggal)) . ' ' . $getBatasInput) {
                    $queryTrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
                        ->where('a.approvalbukatanggal_id', $cekApproval->id)
                        // ->groupBy('a.approvalbukatanggal_id')
                        ->first();

                    if ($queryTrip != '') {
                        $this->validasi = 'SPI';
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return app(ErrorController::class)->geterror($this->validasi)->keterangan;
    }
}
