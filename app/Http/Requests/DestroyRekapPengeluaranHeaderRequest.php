<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\RekapPengeluaranheader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyRekapPengeluaranHeader ;
use App\Http\Controllers\Api\RekapPengeluaranHeaderController;

class DestroyRekapPengeluaranHeaderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $controller = new RekapPengeluaranHeaderController;
        $rekappengeluaranheader = new RekapPengeluaranHeader();
        $cekdata = $rekappengeluaranheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyRekapPengeluaranHeader($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
