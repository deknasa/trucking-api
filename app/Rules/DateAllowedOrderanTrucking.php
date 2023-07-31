<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BukaAbsensi;
use App\Models\OrderanTrucking;
use DB;

class DateAllowedOrderanTrucking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public $pesan;
    
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $date = date('Y-m-d', strtotime($value));
        $today = date('Y-m-d', strtotime("today"));
        $allowed = false ;
        // $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();
        $todayValidation = OrderanTrucking::todayValidation($value);
        $isEditAble = OrderanTrucking::isEditAble($value);
        $nobukti = OrderanTrucking::from(DB::raw("orderantrucking"))->where('id', $value)->first();
        $cekdata = OrderanTrucking::cekvalidasihapus($nobukti, 'edit');
        
        if(!$todayValidation){
            $allowed = true;
            $this->pesan = "BAED";
        }
        else if (!$isEditAble){
            $allowed = true;
            $this->pesan = "BAED";
        }
        else if  (!$cekdata['kondisi']) {
            $allowed = true;
            $this->pesan = "SATL";
        }
        else {
            $allowed = false ;   
        }
        
        return $allowed ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return DB::table('error')->select('keterangan')->where('kodeerror', '=', $this->pesan)->first()->keterangan;

        return $this->pesan.'asdasd';
    }
}