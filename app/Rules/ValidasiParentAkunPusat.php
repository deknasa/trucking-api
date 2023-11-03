<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidasiParentAkunPusat implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $statusParent = request()->statusparent;
        $parent = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PARENT')->where('text', 'PARENT')->first();
        if($value != '' && $statusParent == $parent->id){
            $this->kode = 'TBI';
            return false;
        } else if ($value == '' && $statusParent != $parent->id) {
            $this->kode = 'WI';
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
        return app(ErrorController::class)->geterror($this->kode)->keterangan;
    }
}
