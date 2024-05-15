<?php

namespace App\Http\Requests;

use App\Rules\ExistSupir;
use App\Rules\ExistTrado;
use App\Rules\DateTutupBuku;
use App\Rules\ValidasiTglTradoTambahan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTradoTambahanAbsensiRequest extends FormRequest
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
        $rules = [
            'tglabsensi' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                new ValidasiTglTradoTambahan()
            ],
            'trado' => 'required',
            'keterangan' => 'required',
            'supir' => ['required'],
            'statusjeniskendaraan' => ['required'],
            'statusjeniskendaraannama' => ['required'],
        ];

        $trado_id = $this->trado_id;
        $rulesTrado_id = [];
        if ($trado_id != null) {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', new ExistTrado()]
            ];
        } else if ($trado_id == null && $this->trado != '') {
            $rulesTrado_id = [
                'trado_id' => ['required', 'numeric', 'min:1', new ExistTrado()]
            ];
        }

        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            $rulesSupir_id = [
                'supir_id' => [ 'numeric', 'min:1', new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => [ 'numeric', 'min:1', new ExistSupir()]
            ];
        }

        $supirserap_id = $this->supirserap_id;
        

        $rules = array_merge(
            $rules,
            $rulesTrado_id,
            $rulesSupir_id,
        );

        return $rules;
    }
    
    public function attributes()
    {
        return [
            'statusjeniskendaraan' => 'status jenis kendaraan',
            'statusjeniskendaraannama' => 'status jenis kendaraan',
        ];
    }
}
