<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\PenerimaanStokHeader;
use App\Http\Controllers\Api\ErrorController;

class ValidasiDestroyPenerimaanStokHeader implements Rule
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
      $penerimaanStokHeader = new PenerimaanStokHeader();
      if ($penerimaanStokHeader->isOutUsed($id)) {
        $this->message = 'SATL';
        return false;
      }
      if ($penerimaanStokHeader->isEhtUsed($id)) {
        $this->message = 'SATL';
        return false;
      }
      if ($penerimaanStokHeader->isApproved($id)) {
        $this->message = 'SAP';
        return false;
      } else if ($penerimaanStokHeader->isPOUsed($id)) {
        $this->message = 'SATL';
        return false;
      } else if ($penerimaanStokHeader->printValidation($id)) {
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
