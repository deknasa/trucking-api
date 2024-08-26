<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;

use App\Models\SuratPengantar;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;

class ApprovalGabungJobTrucking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trip;
    public $bjumlah;
    public function __construct()
    {
        //
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
        $parameter = new Parameter();

        // $pelabuhancabang = $parameter->cekText('PELABUHAN CABANG', 'PELABUHAN CABANG') ?? '0';
        $statuspelabuhan = $parameter->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN','PELABUHAN') ?? 0;
        $pelabuhancabang=db::table("kota")->from(db::raw("kota a with (readuncommitted)"))
        ->select(
            db::raw("STRING_AGG(id,',') as id"),
        )
        ->where('a.statuspelabuhan',$statuspelabuhan)
        ->first()->id ?? 1;  

        $bjumlah=0;
        for ($i = 0; $i < count(request()->Id); $i++) {
            $nobukti = request()->Id[$i];
            // dd($nobukti);
            $querypelabuhan=db::table("suratpengantar")->from(db::raw("suratpengantar a with (readuncommitted)"))
            ->select(
                'a.jobtrucking'
            )
            ->where('a.nobukti', $nobukti)
            ->whereraw("(a.dari_id in(" . $pelabuhancabang . ") or isnull(a.statuslongtrip,0)=65)")
            ->first();

            if (isset($querypelabuhan)) {
                $bjumlah=$bjumlah+1; 
            }

        }
        $this->bjumlah=$bjumlah;

        if ($bjumlah==0 ) {
            return false;
        }

        if ($bjumlah>1) {
            
            return false;
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
        // dd($this->bjumlah);
        if ($this->bjumlah==0)  {
            return app(ErrorController::class)->geterror('TTAP')->keterangan ;
        }
        if ($this->bjumlah>1)  {
            return app(ErrorController::class)->geterror('TTPL')->keterangan ;
        }
        
    }
}
