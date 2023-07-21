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
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        $id = request()->id;
        $isInUsed = $pengeluaranStokHeader->isInUsed($id);
        $printValidation = $pengeluaranStokHeader->printValidation($id);
        if($isInUsed){
          $this->message = 'SATL';
          return false;
        }
        if($printValidation){
          $this->message = 'SDC';
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
