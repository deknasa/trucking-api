<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranTruckingHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class validasiNominalTarikDeposito implements Rule
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
        $aksi = strtoupper(request()->aksi);
        if ($aksi == 'ADD') {
            $getdeposito = (new PenerimaanTruckingHeader())->createTempDeposito(request()->supirheader_id, date('Y-m-d', strtotime(request()->tglbukti)));
        } else {
            $getdeposito = (new PengeluaranTruckingHeader())->cekValidasiTarikDeposito(request()->supirheader_id, request()->nobukti, date('Y-m-d', strtotime(request()->tglbukti)));
        }
        $query = DB::table($getdeposito)->select(db::raw("sum(sisa) as sisa"))->first();
        if (isset($query)) {
            if ($value > $query->sisa) {
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
        return app(ErrorController::class)->geterror('STC')->keterangan;
    }
}
