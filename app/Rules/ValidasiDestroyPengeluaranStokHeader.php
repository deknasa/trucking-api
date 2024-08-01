<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\PengeluaranStokHeader;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Error;
use App\Models\Locking;
use App\Models\Parameter;
use DateTime;

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
    if ($isInUsed) {
      $keteranganerror = (new Error())->cekKeteranganError('SATL') ?? '';
      $this->message = $keteranganerror;
      return false;
    }
    if ($printValidation) {
      $keteranganerror = (new Error())->cekKeteranganError('SDC') ?? '';
      $this->message = $keteranganerror;
      return false;
    }
    $nameUser = auth('api')->user()->name;

    $getEditing = (new Locking())->getEditing('pengeluaranstokheader', $id);
    $useredit = $getEditing->editing_by ?? '';
    if ($useredit != '' && $useredit != $nameUser) {
      $waktu = (new Parameter())->cekBatasWaktuEdit('pengeluaran stok header BUKTI');

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
    return true;
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
