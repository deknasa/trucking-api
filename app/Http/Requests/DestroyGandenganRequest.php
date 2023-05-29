<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Gandengan;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyGandengan ;

class DestroyGandenganRequest extends FormRequest
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

        $gandengan = new Gandengan();
        $cekdata = $gandengan->cekValidasihapus($this->id);
    
        return [
            'id' => [ new ValidasiDestroyGandengan($cekdata['kondisi'])],
        ];
    }

    public function attributes()
    {
        return [
            'kodegandengan' => 'kode gandengan',
            'keterangan' => 'keterangan',
            'statusaktif' => 'status',
        ];
    }
}
