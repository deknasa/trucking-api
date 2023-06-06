<?php

namespace App\Http\Requests;


use App\Http\Controllers\Api\ErrorController;
use App\Models\MandorAbsensiSupir;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MandorAbsensiSupirInputSupirValidasiTrado;
use App\Rules\MandorAbsensiSupirEditSupirValidasiTrado;
use Illuminate\Validation\Rule;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidasiDestroyMandorAbsensiSupir ;

class MandorAbsensiSupirRequest extends FormRequest
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
        $query = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select(
                'text',
            )
            ->where('grp', 'TIDAK ADA SUPIR')
            ->where('subgrp', 'TIDAK ADA SUPIR')
            ->first();

            $absen_id=$this->absen_id ?? 0;
        if ($absen_id == $query->text) {
            $rules = [
                'trado' => 'required',
                'trado_id' => 'required',
                'supir' => 'nullable',
                'supir_id' => 'nullable',
                'absen' => 'nullable',
                'jam' => [Rule::requiredIf(function () {
                    return empty($this->input('absen'));
                }), Rule::when(empty($this->input('absen')), 'date_format:H:i')]
            ];
            $rulesBeda = [];
        } else {
            $rules = [
                'trado_id' => 'required',
                'supir' => 'required',
                'absen' => 'nullable',
                'jam' => [Rule::requiredIf(function () {
                    return empty($this->input('absen'));
                }), Rule::when(empty($this->input('absen')), 'date_format:H:i')]
            ];
        
            if (request()->isMethod('POST')) {
                $rulesBeda = [
                    'trado' => 'required',
                    'supir_id' => ['required', new MandorAbsensiSupirInputSupirValidasiTrado()],
                ];
            } else if (request()->isMethod('PATCH')) {
                $rulesBeda = [
                    'trado' => 'required',
                    'supir_id' => ['required', new MandorAbsensiSupirEditSupirValidasiTrado()],
                ];
            } else if (request()->isMethod('DELETE')) {
                $mandorabsensisupir = new MandorAbsensiSupir();
                $cekdata = $mandorabsensisupir->cekvalidasihapus($this->trado_id,$this->supir_id,date('Y-m-d', strtotime($this->tglbukti))); 
                $rulesBeda= [
                    'trado' => [ new ValidasiDestroyMandorAbsensiSupir($cekdata['kondisi'])],
                ];               

            } else {
                $rulesBeda = [
                    'trado' => 'required',
                    'supir_id' => ['required', new MandorAbsensiSupirInputSupirValidasiTrado()],
                ];
            }
        }

        $rule = array_merge(
            $rules,
            $rulesBeda
        );

        return $rule;
    }

    public function attributes()
    {
        return [
            'supir_id' => 'supir',
        ];
    }

    public function messages()
    {
        return [
            'jam.date_format' => app(ErrorController::class)->geterror('HF')->keterangan,
        ];
    }
}