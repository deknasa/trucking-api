<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OrderanTrucking;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyOrderanTrucking ;
use App\Http\Controllers\Api\OrderanTruckingController;

class DestroyOrderanTruckingRequest extends FormRequest
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
        $controller = new OrderanTruckingController;
        $orderantrucking = new OrderanTrucking();
        $cekdata = $orderantrucking->cekvalidasihapus($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        

         
    
        return [
            'id' => [ new ValidasiDestroyOrderanTrucking($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
