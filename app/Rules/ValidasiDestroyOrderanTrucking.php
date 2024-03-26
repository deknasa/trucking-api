<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\OrderanTruckingController;
use App\Models\OrderanTrucking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidasiDestroyOrderanTrucking implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
    public $kodeerror;
    public $keterangan;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (request()->aksi != 'updatenocont') {

            $controller = new OrderanTruckingController;
            $nobukti = OrderanTrucking::from(DB::raw("orderantrucking"))->where('id', request()->id)->first();
            $request = new Request();
            $request['nobukti'] = $nobukti->nobukti;
            $cekCetak = app(OrderanTruckingController::class)->cekvalidasi(request()->id, request()->aksi, $request);
            $getOriginal = $cekCetak->original;
            if ($getOriginal['error'] == true) {
                $this->kodeerror = $getOriginal['kodeerror'];
                $this->keterangan = $getOriginal['message'];
                return false;
            }
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
        return $this->keterangan;
    }
}
