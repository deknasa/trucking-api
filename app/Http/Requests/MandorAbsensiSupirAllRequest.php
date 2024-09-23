<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\DateAllowedAbsenMandor;
use App\Rules\AbsensiSupirRICUangJalanRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MandorAbsensiSupirDuplicateSupir;
use App\Rules\MandorAbsensiSupirEditSupirValidasiTrado;
use App\Rules\MandorAbsensiSupirInputSupirValidasiTrado;

class MandorAbsensiSupirAllRequest extends FormRequest
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
        // dd(request()->all());
        // if ($data==[]) {
        //     return true;
        // }

        $mainValidator = validator(request()->all(), [
            'data' => [function ($attribute, $value, $fail) {
                if (empty(json_decode($value, true))) {
                    $fail($attribute . ' wajib diisi');
                }
            }],
        ]);


        $supirAbsen = DB::table("absentrado")->from(DB::raw("absentrado with (readuncommitted)"))
            ->where('kodeabsen', 'S')
            ->first();

        // Dapatkan kunci data yang dikirim
        $keys = array_keys($data);

        $supirIds = [];
        $duplicates = [];
        
        foreach ($data as $key=>$item) {
            if ($item['supir_id']!="") {
                if (in_array($item['supir_id'], $supirIds)) {
                    $duplicates[] = $item['supir_id']; // Menyimpan supir_id yang duplikat
                } else {
                    $supirIds[] = $item['supir_id']; // Menambahkan supir_id ke daftar
                }
            }
        }
        // Tentukan aturan validasi untuk setiap kunci data
        $validaasismass = collect($keys)->mapWithKeys(function ($key) use ($data,$duplicates) {
            // dd($data[$key]['absen_id']);

            $query = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
                ->select('text')
                ->join(DB::raw("absentrado as b with (readuncommitted)"), 'a.text', '=', 'b.id')
                ->where('a.grp', 'TIDAK ADA SUPIR')
                ->where('a.subgrp', 'TIDAK ADA SUPIR')
                ->where('b.id', $data[$key]['absen_id'])
                ->first();
            $supirAbsen = DB::table("absentrado")->from(DB::raw("absentrado with (readuncommitted)"))
                ->where('kodeabsen', 'S')
                ->first();
            $supirLibur = DB::table("absentrado")->from(DB::raw("absentrado with (readuncommitted)"))
                ->where('kodeabsen', 'L')
                ->first();


            if (isset($query)) {
                $rules = [
                    "$key.kodetrado" => 'required',
                    "$key.trado_id" => 'required',
                    "$key.namasupir" => 'nullable',
                    "$key.supir_id" => 'nullable',
                    "$key.absen" => 'nullable',
                    // "$key.jam" => [Rule::requiredIf(function () use($key,$data){
                    //     return empty($data[$key]['absen_id']);
                    // }), Rule::when(empty($data[$key]['absen_id']), 'date_format:H:i')]
                ];
                $rulesBeda = [];
            } else if ($supirAbsen->id == $data[$key]['absen_id']) {
                $rules = [
                    "$key.kodetrado" => 'required',
                    "$key.trado_id" => 'required',
                    "$key.namasupir" => 'nullable',
                    "$key.supir_id" => 'nullable',
                    "$key.absen" => 'nullable',
                    // "$key.jam" => [Rule::requiredIf(function () use($key,$data){
                    //     return empty($data[$key]['absen_id']);
                    // }), Rule::when(empty($data[$key]['absen_id']), 'date_format:H:i')]
                ];
                $rulesBeda = [];
            } else if ($supirLibur->id == $data[$key]['absen_id']) {
                $rules = [
                    "$key.kodetrado" => 'required',
                    "$key.trado_id" => 'required',
                    "$key.namasupir" => 'nullable',
                    "$key.supir_id" => 'nullable',
                    "$key.absen" => 'nullable',
                    // "$key.jam" => [Rule::requiredIf(function () use($key,$data){
                    //     return empty($data[$key]['absen_id']);
                    // }), Rule::when(empty($data[$key]['absen_id']), 'date_format:H:i')]
                ];
                $rulesBeda = [];
            } else {
                $requiredSupir = Rule::requiredIf(function () {
                    $cekSupir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', "ABSENSI SUPIR")
                        ->where('subgrp', "TRADO MILIK SUPIR")
                        ->first();
                    if ($cekSupir->text != 'YA') {
                        return false;
                    }
                    return true;
                });
                $rules = [
                    "$key.trado_id" => 'required',
                    "$key.namasupir" => $requiredSupir,
                    "$key.absen" => 'nullable',
                    // "$key.jam" => [Rule::requiredIf(function () use($key,$data){
                    //     return empty($data[$key]['absen_id']);
                    // }), Rule::when(empty($data[$key]['absen_id']), 'date_format:H:i')]
                ];
                // dd($this->input("$key.kodetrado"));
                $rulesBeda = [
                    "$key.namasupir" => ['required', new MandorAbsensiSupirDuplicateSupir($data[$key]['supir_id'],$duplicates), new MandorAbsensiSupirEditSupirValidasiTrado($data[$key]['trado_id'], $data[$key]['supir_id'], $data[$key]['tglbukti']),new AbsensiSupirRICUangJalanRule($data[$key])],
                    "$key.supir_id" => ['required', new MandorAbsensiSupirEditSupirValidasiTrado($data[$key]['trado_id'], $data[$key]['supir_id'], $data[$key]['tglbukti'])],
                    
                ];
            }

            $rule = array_merge(
                $rules,
                $rulesBeda
            );
            return $rule;
        })->all();
        $attribute = collect($keys)->mapWithKeys(function ($key) use ($data) {
            return [
                "$key.namasupir"=>$data[$key]['kodetrado'],
                "$key.supir_id"=>"supir",
            ];
        })->all();
        $message = [
            "*.namasupir.required"=>'Trado<b> :attribute </b>Status Absensi Wajib isi'
        ];      
        if ($data != []) {
            $validatedMainData = $mainValidator->validated();
        } 
        // dd($data);
        $validatedDetailData = validator(
            $data, $validaasismass,
            $message,
            $attribute,
        )->validated();
        // dd($validatedDetailData);

        // dd($rules);
        return $validatedDetailData;
    }
    
}
