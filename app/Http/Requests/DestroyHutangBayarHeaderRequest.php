<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Hutangbayarheader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyHutangBayarHeader ;
use App\Http\Controllers\Api\HutangBayarHeaderController;

class DestroyHutangBayarHeaderRequest extends FormRequest
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
        $controller = new HutangBayarHeaderController;
        $hutangbayarheader = new HutangBayarHeader();
        $cekdata = $hutangbayarheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyHutangBayarHeader($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
