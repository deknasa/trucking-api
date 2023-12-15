<?php

namespace App\Rules;

use Illuminate\Support\Facades\DB;
use App\Models\PenerimaanStokHeader;
use Illuminate\Contracts\Validation\Rule;
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
      $id =  $value;
      $penerimaanStokHeader = new PenerimaanStokHeader();
      $data = $penerimaanStokHeader->find($id);
      $spb = DB::table('parameter')->where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
      if ($penerimaanStokHeader->isOutUsed($id) && ($data->penerimaanstok_id != $spb->text)) {
        $this->message = 'SATL';
        return false;
      }
      if ($penerimaanStokHeader->isEhtUsed($id)) {
        $this->message = 'SATL';
        return false;
      }
      if ($penerimaanStokHeader->isPOUsed($id)) {
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
