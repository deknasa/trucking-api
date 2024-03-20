<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\DateAllowedAbsenMandor;
use Illuminate\Foundation\Http\FormRequest;
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

        $deleted_id = request()->deleted_id ?? 0;
        if ($deleted_id !=0) {
            return [];
        }
        $data = json_decode(request()->data, true);
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

        // Tentukan aturan validasi untuk setiap kunci data
        $validaasismass = collect($keys)->mapWithKeys(function ($key) use ($data) {
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
                    "$key.namasupir" => ['required', new MandorAbsensiSupirEditSupirValidasiTrado($data[$key]['trado_id'], $data[$key]['supir_id'])],
                    "$key.supir_id" => ['required', new MandorAbsensiSupirEditSupirValidasiTrado($data[$key]['trado_id'], $data[$key]['supir_id'])],
                ];
            }

            $rule = array_merge(
                $rules,
                $rulesBeda
            );
            return $rule;

            // return [
            //     "$key.tglbukti"=>[
            //         'required', 'date_format:d-m-Y',
            //         new DateAllowedAbsenMandor(),
            //         new DateTutupBuku(),
            //     ],

            //     "$key.supir_id" => [
            //         // Aturan validasi untuk supir_id
            //         // Misalnya, wajib diisi jika kondisi tertentu terpenuhi
            //         Rule::requiredIf(function () use ($key) {
            //             // Logika kondisi berdasarkan nilai dari data
            //             return !empty($this->input("$key.trado_id"));
            //         }),
            //     ],
            //     "$key.absen_id"=>['required'],
            //     "$key.kodetrado"=>['required'],
            //     "$key.jam"=>['required'],
            //     "$key.absentrado"=>['required'],
            //     "$key.keterangan"=>['required'],
            // ];
        })->all();

        // $dataValidator = validator($data, [
        //     '*.tglbukti'=>[
        //         'required', 'date_format:d-m-Y',
        //         new DateAllowedAbsenMandor(),
        //         new DateTutupBuku(),

        //     ],

        //     '*.id'=>['required'],
        //     '*.trado_id'=>['required'],
        //     '*.supir_id'=>[
        //         new MandorAbsensiSupirInputSupirValidasiTrado(),
        //         // new SupirRequiredConditonAbsen(),



        //     ],
        //     '*.namasupir'=>[
        //         new SupirRequiredConditonAbsen(),

        //         Rule::requiredIf(function () use($data, $supirAbsen){
        //         dd($data,$this->input('data'));
        //             // return true;
        //         }),
        //         // function ($attribute, $value, $fail) use($data, $supirAbsen) {
        //         //     $attr = explode('.',$attribute);
        //         //     $key = $attr[0];
        //         //     $absen_id = $key.'.absen_id';
        //         //     $data = json_decode(request()->data, true);
        //         //     $query = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
        //         //     ->select('text')
        //         //     ->join(DB::raw("absentrado as b with (readuncommitted)"), 'a.text', '=', 'b.id')
        //         //     ->where('a.grp', 'TIDAK ADA SUPIR')
        //         //     ->where('a.subgrp', 'TIDAK ADA SUPIR')
        //         //     ->where('b.id', $data[$key]['absen_id'])
        //         //     ->first();
        //         //     // dd();
        //         //     if ((!isset($query) && !($supirAbsen->id == $data[$key]['absen_id'])) && empty($value)) {
        //         //         // dd(!isset($query) );
        //         //         $fail($value);
        //         //     }

        //         // },
        //     ],
        //     '*.absen_id'=>['required'],
        //     '*.kodetrado'=>['required'],
        //     '*.jam'=>['required'],
        //     '*.absentrado'=>['required'],
        //     '*.keterangan'=>['required'],


        // ],);



        if ($data != []) {
            $validatedMainData = $mainValidator->validated();
        } 
        // dd($data);
        $validatedDetailData = validator($data, $validaasismass)->validated();
        // dd($validatedDetailData);

        // dd($rules);
        return $validatedDetailData;
    }
}
