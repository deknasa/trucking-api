<?php

namespace App\Rules;

use App\Models\Parameter;
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
      $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
      $pgdo = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
      // if ($penerimaanStokHeader->isOutUsed($id) && ($data->penerimaanstok_id != $spb->text)) {
      //   $this->message = 'SATL';
      //   return false;
      // }
      // if ($penerimaanStokHeader->isEhtUsed($id)) {
      //   $this->message = 'SATL';
      //   return false;
      // }
      // if ($penerimaanStokHeader->isPOUsed($id)) {
      //   $this->message = 'SATL';
      //   return false;
      // } else if ($penerimaanStokHeader->printValidation($id)) {
      //   $this->message = 'SDC';
      //   return false;
      // }
      //   return true;
      $passes = true;
      $isEhtUsed = $penerimaanStokHeader->isEhtUsed($id);
      if ($isEhtUsed) {
        
        $this->message = 'SATL';
        $passes = false;
        // return response($data);
      }
      
      $isEHTApprovedJurnal = $penerimaanStokHeader->isEHTApprovedJurnal($id);
      if ($isEHTApprovedJurnal) {
        
        $this->message = 'SAP';
        $passes = false;
        // return response($data);
      }
      
      $isPOUsed = $penerimaanStokHeader->isPOUsed($id);
      if ($isPOUsed) {
        
        $this->message = 'SATL';
        $passes = false;
        // return response($data);
      }
      if ($pg->text == $data->penerimaanstok_id || $pgdo->text == $data->penerimaanstok_id) {
        $todayValidation = true;
      }
      $todayValidation = $penerimaanStokHeader->todayValidation($data->tglbukti);
      if (!$todayValidation) {
        
        $this->message = 'TEPT';
        
        $passes = false;
        // return response($data);
      }
      $isEditAble = $penerimaanStokHeader->isEditAble($id);
      $isKeteranganEditAble = $penerimaanStokHeader->isKeteranganEditAble($id);
      if ((!$isEditAble) || (!$isKeteranganEditAble)) {
        $this->message = 'TED';
        $passes = false;
        // return response($data);
      }
      $printValidation = $penerimaanStokHeader->printValidation($id);
      if ($printValidation) {
        $this->message = 'SDC';
        $passes = false;
        // return response($data);
      }
      $isOutUsed = $penerimaanStokHeader->isOutUsed($id);
      if ($isOutUsed) {
        $this->message = 'SATL';
        $passes = false;
        // return response($data);
      }
      
      if (($todayValidation || (($isEditAble || $isKeteranganEditAble) && !$printValidation))) {
        $passes =true;
        if ($spb->text == $data->penerimaanstok_id) {
          //ika sudah digunakan di eth, jurnal, dan po
          if ($isEhtUsed || $isEHTApprovedJurnal || $isPOUsed) {
            return $passes;
          }
        }
        if (!$isOutUsed) {
          return $passes;
        }
        
      }
      return $passes;
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
