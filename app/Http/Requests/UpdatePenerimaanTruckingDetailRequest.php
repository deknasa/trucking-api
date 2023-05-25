<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdatePenerimaanTruckingDetailRequest extends FormRequest
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
        $requiredKeterangan = Rule::requiredIf(function () {
            $idpenerimaan = request()->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();
            if ($fetchFormat->kodepenerimaan == 'PJP') {
                return false;
            } else {
                return true;
            }
        });
        $requiredPJP = Rule::requiredIf(function () {
            $idpenerimaan = request()->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();
            if ($fetchFormat->kodepenerimaan == 'PJP') {
                return true;
            } else {
                return false;
            }
        });
        $idpenerimaan = request()->penerimaantrucking_id;
        $fetchFormat =  DB::table('penerimaantrucking')
            ->where('id', $idpenerimaan)
            ->first();

        return [
            'pjp_id' => [$requiredPJP,'array'],
            'pjp_id.*' => $requiredPJP,
            'sisa' => [$requiredPJP,Rule::when(($fetchFormat->kodepenerimaan == 'PJP'),'array')],
            'sisa.*' => [$requiredPJP,Rule::when(($fetchFormat->kodepenerimaan == 'PJP'),'numeric|gt:0')],
            'nominal' => 'required|array',
            'nominal.*' => ['required','numeric','gt:0'],
            'keterangan' => [$requiredKeterangan,'array'],
            'keterangan.*' => $requiredKeterangan
        ];
    }
    public function attributes()
    {
        return[
            'pjp_id' => 'pjt',
            'nominal.*' => 'nominal',
            'keterangan.*' => 'keterangan'
        ];
    }

    public function messages(){
       return [
        'pjp_id.required' => 'PJT '.app(ErrorController::class)->geterror('WP')->keterangan,
        'nominal.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
        'sisa.*.gt' => 'SISA '.app(ErrorController::class)->geterror('NTM')->keterangan,
       ];
    }
}
