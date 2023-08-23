<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\OpnameHeaderController;
use App\Models\OpnameHeader;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyOpname implements Rule
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
        $cekCetak = app(OpnameHeaderController::class)->cekvalidasi(request()->id);
        $getOriginal = $cekCetak->original;
        if ($getOriginal['error'] == true) {
            $this->kodeerror = $getOriginal['kodeerror'];
            $this->keterangan = '';
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
        return app(ErrorController::class)->geterror($this->kodeerror)->keterangan.$this->keterangan;
    }
}
