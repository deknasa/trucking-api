<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\RekapPenerimaanHeaderController;
use Illuminate\Foundation\Http\FormRequest;

use App\Models\RekapPengeluaranheader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyRekapPengeluaranHeader ;
use App\Http\Controllers\Api\RekapPengeluaranHeaderController;
use App\Models\RekapPenerimaanHeader;
use App\Rules\ValidasiDestroyRekapPenerimaanHeader;

class DestroyRekapPenerimaanHeaderRequest extends FormRequest
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
        $controller = new RekapPenerimaanHeaderController;
        $rekappenerimaanheader = new RekapPenerimaanHeader();
        // dd('test');
        $cekdata = $rekappenerimaanheader->cekvalidasiaksi($this->nobukti);
        
        $cekdatacetak = $controller->cekvalidasi($this->id);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyRekapPenerimaanHeader($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
