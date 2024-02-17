<?php

namespace App\Http\Requests;


use App\Http\Controllers\Api\ErrorController;
use App\Models\MandorAbsensiSupir;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MandorAbsensiSupirInputSupirValidasiTrado;
use App\Rules\MandorAbsensiSupirEditSupirValidasiTrado;
use App\Rules\DateAllowedAbsenMandor;
use App\Rules\DateTutupBuku;

use Illuminate\Validation\Rule;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidasiDestroyMandorAbsensiSupir;

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

        $absen_id = $this->absen_id ?? 0;

        $query = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select(
                'text',
            )
            ->join(DB::raw("absentrado as b with (readuncommitted)"), 'a.text', '=', 'b.id')
            ->where('a.grp', 'TIDAK ADA SUPIR')
            ->where('a.subgrp', 'TIDAK ADA SUPIR')
            ->where('b.id', $this->absen_id)
            ->first();
        $supirAbsen = DB::table("absentrado")->from(DB::raw("absentrado with (readuncommitted)"))
            ->where('kodeabsen', 'S')
            ->first();

        if (isset($query)) {
            $rules = [
                'trado' => 'required',
                'trado_id' => 'required',
                'supir' => 'nullable',
                'supir_id' => 'nullable',
                'absen' => 'nullable',
                // 'jam' => [Rule::requiredIf(function () {
                //     return empty($this->input('absen'));
                // }), Rule::when(empty($this->input('absen')), 'date_format:H:i')]
            ];
            $rulesBeda = [];
        } else if ($supirAbsen->id == request()->absen_id) {
            $rules = [
                'trado' => 'required',
                'trado_id' => 'required',
                'supir' => 'nullable',
                'supir_id' => 'nullable',
                'absen' => 'nullable',
                // 'jam' => [Rule::requiredIf(function () {
                //     return empty($this->input('absen'));
                // }), Rule::when(empty($this->input('absen')), 'date_format:H:i')]
            ];
            $rulesBeda = [];
        } else {
            $requiredSupir = Rule::requiredIf(function () {
            
                $cekSupir = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp',"ABSENSI SUPIR")
                    ->where('subgrp',"TRADO MILIK SUPIR")
                    ->first();
                    
                if ($cekSupir->text != 'YA') {
                    return false;
                }
                return true;
            });
            $rules = [
                'trado_id' => 'required',
                'supir' => $requiredSupir,
                'absen' => 'nullable',
                // 'jam' => [Rule::requiredIf(function () {
                //     return empty($this->input('absen'));
                // }), Rule::when(empty($this->input('absen')), 'date_format:H:i')]
            ];

            if (request()->isMethod('POST')) {
                $rulesBeda = [
                    'tglbukti' => [
                        'required', 'date_format:d-m-Y',
                        new DateAllowedAbsenMandor(),
                        new DateTutupBuku(),

                    ],
                    'trado' => 'required',
                    'supir_id' => ['required', new MandorAbsensiSupirInputSupirValidasiTrado()],
                ];
            } else if (request()->isMethod('PATCH')) {
                $rulesBeda = [
                    'tglbukti' => [
                        'required', 'date_format:d-m-Y',
                        function ($attribute, $value, $fail) {

                            // Ubah format tanggal dari input menjadi format yang ada di database
                            $statusTidakbolehEditAbsensi = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT ABSENSI')->where('text', 'TIDAK BOLEH EDIT ABSENSI')->first();
                            $formattedDate = date('Y-m-d', strtotime($value));

                            // Cek apakah ada data dengan tanggal yang sama dalam database
                            $existingRecord = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
                                ->select(
                                    'absensisupirdetail.id',
                                    'header.statusapprovaleditabsensi as statusedit',
                                    'header.tglbataseditabsensi as tglbatas',
                                )
                                ->where('absensisupirdetail.id', $this->id)
                                ->leftJoin(DB::raw("absensisupirheader as header with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'header.id')
                                ->first();
                            if (strtotime($existingRecord->tglbatas) == strtotime($formattedDate)) {
                                $fail(app(ErrorController::class)->geterror('TSTB')->keterangan);
                            }
                        },
                        new DateTutupBuku(),
                    ],
                    'trado' => 'required',
                    'supir_id' => ['required', new MandorAbsensiSupirEditSupirValidasiTrado()],
                ];
            } else if (request()->isMethod('DELETE')) {
                $mandorabsensisupir = new MandorAbsensiSupir();
                $cekdata = $mandorabsensisupir->cekvalidasihapus($this->trado_id, $this->supir_id, date('Y-m-d', strtotime($this->tglbukti)));
                $rulesBeda = [
                    'supir' => 'nullable',
                    'trado' => [new ValidasiDestroyMandorAbsensiSupir($cekdata['kondisi'], $cekdata['keterangan'])],
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
