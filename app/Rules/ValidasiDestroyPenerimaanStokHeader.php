<?php

namespace App\Rules;

use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Models\PenerimaanStokHeader;
use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use App\Models\Locking;
use DateTime;

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
    
    $nameUser = auth('api')->user()->name;

    $getEditing = (new Locking())->getEditing('penerimaanstokheader', $value);
    $useredit = $getEditing->editing_by ?? '';
    if ($useredit != '' && $useredit != $nameUser) {
      $waktu = (new Parameter())->cekBatasWaktuEdit('penerimaan stok header BUKTI');

      $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
      $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
      $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
      if ($totalminutes < $waktu) {

        $keteranganerror = (new Error())->cekKeteranganError('SDE') ?? '';
        $keterangantambahanerror = (new Error())->cekKeteranganError('PTBL') ?? '';
        $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
        $this->message = $keterror;


        return false;
      }
    }

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
      $keteranganerror = (new Error())->cekKeteranganError('SATL') ?? '';

      $this->message = $keteranganerror;
      $passes = false;
      // return response($data);
    }

    $isEHTApprovedJurnal = $penerimaanStokHeader->isEHTApprovedJurnal($id);
    if ($isEHTApprovedJurnal) {
      $keteranganerror = (new Error())->cekKeteranganError('SAP') ?? '';

      $this->message = $keteranganerror;
      $passes = false;
      // return response($data);
    }

    $isPOUsed = $penerimaanStokHeader->isPOUsed($id);
    if ($isPOUsed) {
      $keteranganerror = (new Error())->cekKeteranganError('SATL') ?? '';

      $this->message = $keteranganerror;
      $passes = false;
      // return response($data);
    }
    if ($pg->text == $data->penerimaanstok_id || $pgdo->text == $data->penerimaanstok_id) {
      $todayValidation = true;
    }
    $todayValidation = $penerimaanStokHeader->todayValidation($data->tglbukti);
    if (!$todayValidation) {
      $keteranganerror = (new Error())->cekKeteranganError('TEPT') ?? '';

      $this->message = $keteranganerror;

      $passes = false;
      // return response($data);
    }
    $isEditAble = $penerimaanStokHeader->isEditAble($id);
    $isKeteranganEditAble = $penerimaanStokHeader->isKeteranganEditAble($id);
    if ((!$isEditAble) || (!$isKeteranganEditAble)) {
      $keteranganerror = (new Error())->cekKeteranganError('TED') ?? '';
      $this->message = $keteranganerror;
      $passes = false;
      // return response($data);
    }
    $printValidation = $penerimaanStokHeader->printValidation($id);
    if ($printValidation) {
      $keteranganerror = (new Error())->cekKeteranganError('SDC') ?? '';
      $this->message = $keteranganerror;
      $passes = false;
      // return response($data);
    }
    $isOutUsed = $penerimaanStokHeader->isOutUsed($id);
    if ($isOutUsed) {
      
      $keteranganerror = (new Error())->cekKeteranganError('SATL') ?? '';
      $this->message = $keteranganerror;
      $passes = false;
      // return response($data);
    }

    if (($todayValidation || (($isEditAble || $isKeteranganEditAble) && !$printValidation))) {
      $passes = true;
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
    return $this->message;
  }
}
