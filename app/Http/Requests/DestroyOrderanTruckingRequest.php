<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OrderanTrucking;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyOrderanTrucking ;
use App\Http\Controllers\Api\OrderanTruckingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $nobukti = OrderanTrucking::from(DB::raw("orderantrucking"))->where('id', request()->id)->first();
        $cekdata = $orderantrucking->cekvalidasihapus($nobukti->nobukti, 'delete');
        $request = new Request();
        $request['nobukti'] = $nobukti->nobukti;
        $cekdatacetak = $controller->cekvalidasi($this->id, 'delete', $request);
        if ($cekdatacetak->original['errors']=='success') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }
        return [
            'id' => [ new ValidasiDestroyOrderanTrucking($cekdata['kondisi'],$cekdtcetak)],
        ];
    }
}
