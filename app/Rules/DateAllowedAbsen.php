<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\BukaAbsensi;

class DateAllowedAbsen implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->kondisi = $param;
    }
    public $kondisi;
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
        
        $bukaAbsensi = BukaAbsensi::where('tglabsensi', '=', $date)->first();

        if($date == $today){
            $allowed = true;
        }
        else if ($bukaAbsensi){
            $allowed = true;
        }

        else if  ($this->kondisi==true) {
            $allowed = true;
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
        return 'Tidak Bisa memilih tanggal tersebut';
    }
}
