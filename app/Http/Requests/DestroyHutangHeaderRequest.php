<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Hutangheader;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyHutangHeader ;
use App\Http\Controllers\Api\HutangHeaderController;

class DestroyHutangHeaderRequest extends FormRequest
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

        $controller = new HutangHeaderController;
        $hutangheader = new HutangHeader();
        $cekdata = $hutangheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        dd($cekdatacetak);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyHutangHeader($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
