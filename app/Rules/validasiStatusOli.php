<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PengeluaranStok;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiStatusOli implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($spk)
    {
        $this->spk = $spk;
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
        $attribute = substr($attribute, 17);
        $stok = request()->detail_stok_id[$attribute];
        $get = DB::table('stok')->select('statusservicerutin')->where('id', $stok)->first();
        $status = $get->statusservicerutin;
        
        // $fetchFormat =  PengeluaranStok::where('id', request()->pengeluaranstok_id)->first();
        if ($this->spk == request()->pengeluaranstok_id) {
            if ($status == '345' || $status == '346' || $status == '347') {

                if ($value == null) {
                    return false;
                } else {
                    return true;
                }
            } else if ($status == '344' || $status == '348') {

                $getTambah = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS OLI')->where('text', 'TAMBAH')->first();
                if ($value == $getTambah->id) {
                    return false;
                }else{
                    return true;
                }
            } else {
                return true;
            }
        } else {
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
        $controller = new ErrorController;
        return 'STATUS OLI ' . $controller->geterror('WI')->keterangan;
    }
}
