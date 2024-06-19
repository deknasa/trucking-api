<?php

namespace App\Rules;

use App\Models\Error;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiTglTolakan implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $error;
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
        $query = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select('tglbukti')
            ->where('nobukti', $value)->first();
        if(isset($query)){
            if(date('Y-m-d', strtotime($query->tglbukti)) > date('Y-m-d', strtotime('2024-06-18'))){
                $isAdmin = auth()->user()->isAdmin();
                if(!$isAdmin){
                    $error = new Error();
                    $keteranganerror = $error->cekKeteranganError('MIN') ?? '';
                    $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
                    $this->error = 'trip ' . $keteranganerror . ' TANGGAL 18-06-2024. ' . $keterangantambahanerror;
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error;
    }
}
