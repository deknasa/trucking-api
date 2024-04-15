<?php

namespace App\Rules;

use App\Models\Error;
use App\Models\Parameter;
use Illuminate\Contracts\Validation\Rule;

class validasiBukaCetakKirimBerkas implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $nobukti;
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
        $backSlash = " \ ";
        $table = Parameter::where('text', request()->table)->first();
        $model = 'App\Models' . trim($backSlash) . $table->text;
        $statusKirimBerkas = Parameter::where('grp', '=', 'STATUSKIRIMBERKAS')->where('text', '=', 'KIRIM BERKAS')->first();
        $exist = 0;
        $nobukti = '';
        for ($i = 0; $i < count(request()->tableId); $i++) {
            $id = request()->tableId[$i];
            if ($id != '') {

                $data = app($model)->findOrFail($id);
                if ($data->statuskirimberkas == $statusKirimBerkas->id) {
                    $exist++;
                    if ($nobukti == '') {
                        $nobukti = $data->nobukti;
                    } else {
                        $nobukti = $nobukti . ', ' . $data->nobukti;
                    }
                }
            }
        }
        $this->nobukti = $nobukti;
        if ($exist > 0) {
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
        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SKB') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        
       return 'No Bukti <b>' . $this->nobukti . '</b> <br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
    }
}
