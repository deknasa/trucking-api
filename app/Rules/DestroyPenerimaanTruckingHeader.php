<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Bank;
use App\Models\Penerima;
use App\Models\PenerimaanTruckingHeader;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DestroyPenerimaanTruckingHeader implements Rule
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

    private $message;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $penerimaan = new PenerimaanTruckingHeader();
        $PenerimaanTruckingHeader = $penerimaan->where('id',request()->id)->first();
        
        $printValidation = $penerimaan->printValidation($PenerimaanTruckingHeader->id);
        if ($printValidation) {
            $this->message = 'SDC';
            return false;
        }
        $isUangJalanProcessed = $penerimaan->isUangJalanProcessed($PenerimaanTruckingHeader->nobukti);
        if ($isUangJalanProcessed['kondisi']) {
            $this->message = 'TDT';
            return false;
        }
        $isUangOut = $penerimaan->isUangOut($PenerimaanTruckingHeader->nobukti);
        if ($isUangOut) {
            $this->message = 'SATL';
            return false;
        }
        $isUangOut = $penerimaan->isUangOut($PenerimaanTruckingHeader->nobukti);
        if ($isUangOut) {
            $this->message = 'SATL';
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
        return app(ErrorController::class)->geterror($this->message)->keterangan;
    }
}
