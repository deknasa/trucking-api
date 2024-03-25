<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MandorAbsensiSupirEditSupirValidasiTradoSupirSerap;

class MandorAbsensiSupirAllSupirSerapRequest extends FormRequest
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
        // return [];
        $deleted_id = request()->deleted_id ?? 0;
        // dd($deleted_id);
        if ($deleted_id !=0) {
            return [];
        }
        $data = json_decode(request()->data, true);
 

        // Dapatkan kunci data yang dikirim
        $keys = array_keys($data);
        // dd($keys);
        // Tentukan aturan validasi untuk setiap kunci data
        $validaasismass = collect($keys)->mapWithKeys(function ($key) use ($data) {
                 $rule = [
                    "$key.kodetrado" => [ new MandorAbsensiSupirEditSupirValidasiTradoSupirSerap($data[$key]['trado_id'], $data[$key]['absen_id'], $data[$key]['tglbukti'], $data[$key]['supirold_id'])],
                ];
 
            return $rule;
        })->all();

        // dd($validaasismass);


        $validatedDetailData = validator($data, $validaasismass)->validated();
        return $validatedDetailData;
    }


}
