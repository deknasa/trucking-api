<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class JobTruckingRequired implements Rule
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
        $dari_id = request()->dari_id;
        
        $statuspelabuhan = (new Parameter())->cekId('STATUS PELABUHAN', 'STATUS PELABUHAN','PELABUHAN') ?? 0;
        $isPelabuhan=DB::table("kota")->from(db::raw("kota a with (readuncommitted)"))
        ->where('a.id', $dari_id)
        ->where('a.statuspelabuhan',$statuspelabuhan)
        ->first();  
        if ($isPelabuhan == '') {
            return !empty($value); // Fail if value is empty
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
        return 'jobtrucking '.app(ErrorController::class)->geterror('WI')->keterangan;
    }
}
