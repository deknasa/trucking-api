<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiTglApprovalBukaTanggalTrip implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $tgl;
    public $validasi;
    public function __construct($tgl)
    {
        $this->tgl = $tgl;
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



        $cekQuery = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
        ->where('approvalbukatanggal_id', $id)
        ->first();

        if($cekQuery != ''){
            if(date('Y-m-d',strtotime($value)) != $this->tgl){
                $this->validasi=1;
                return false;
            }   
        }

        $cekStatus =  DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip as a with (readuncommitted)"))
        ->where('a.tglbukti', date('Y-m-d', strtotime($value)))
        ->orderBy('a.id', 'desc')
        ->first();

      
        if (isset($cekStatus)) {
            $queryTrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
            ->select('a.approvalbukatanggal_id', DB::raw("isnull(count(a.nobukti), 0) as jumlahtrip"))
            ->where('a.approvalbukatanggal_id',$cekStatus->id )
            ->groupBy('a.approvalbukatanggal_id')
            ->first();

            $jumlahtripsp=$queryTrip->jumlahtrip ?? 0;

                
                if ($cekStatus->jumlahtrip>$jumlahtripsp) {
                    $this->validasi=2;
                    return false;  
                }
    
        }
        // dd('test');




        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->validasi==1) {
            return app(ErrorController::class)->geterror('DTSA')->keterangan;
        } else {
            return app(ErrorController::class)->geterror('SPI')->keterangan;
        }
        
    }
}
