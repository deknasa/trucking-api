<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\PengeluaranStokHeader;
use App\Http\Controllers\Api\ErrorController;

class ValidasiDestroyPengeluaranStokHeader implements Rule
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
        $penerimaanStokHeader = new PengeluaranStokHeader();
        // $cekdata = $penerimaanStokHeader->cekvalidasihapus(request()->id);
        $isJurnalUsed = $penerimaanStokHeader->isJurnalUsed(request()->id);
        $isHutangUsed = $penerimaanStokHeader->isHutangBayarUsed(request()->id);
        // $isPOUsed = $penerimaanStokHeader->isPOUsed(request()->id);
        // dd([$isOutUsed
        // $isEhtUsed
        // $isPOUsed])
        if($isOutUsed){
          return false;
        }
        if($isEhtUsed){
          return false;
        }
        if($isPOUsed){
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
        return app(ErrorController::class)->geterror('SATL')->keterangan;
    }
}
