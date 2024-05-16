<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\SuratPengantar;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ApprovalBatalMuat implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $trip;
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
        $false = 0;
        $trip = '';
        for ($i = 0; $i < count(request()->Id); $i++) {
            $nobukti = request()->Id[$i];
            $getjob = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))->select('jobtrucking','statusjeniskendaraan')->where('nobukti', $nobukti)->first();
            if ($getjob != '') {

                $cek = (new SuratPengantar())->cekvalidasihapus($nobukti, $getjob->jobtrucking, $getjob);
                if ($cek['kondisi'] == true) {
                    $false++;
                    if ($trip == '') {
                        $trip = $nobukti . ' di ' . $cek['keterangan'];
                    } else {
                        $trip = $trip . ', ' . $nobukti . ' di ' . $cek['keterangan'];
                    }
                }
            }
        }

        $this->trip = $trip;
        if ($false > 0) {
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
        return app(ErrorController::class)->geterror('SATL')->keterangan . ' (' . $this->trip . ')';
    }
}
