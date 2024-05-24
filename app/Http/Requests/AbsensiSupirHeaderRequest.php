<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;

use App\Rules\DateAllowedAbsen;
use Illuminate\Validation\Rule;
use App\Models\AbsensiSupirHeader;
use Illuminate\Support\Facades\DB;

use App\Rules\ValidasiHapusAbsensiAdmin;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiDestroyAbsensiSupirHeader;
use App\Http\Controllers\Api\AbsensiSupirHeaderController;

class AbsensiSupirHeaderRequest extends FormRequest
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
            ->where('grp', 'BATAS JAM EDIT ABSENSI')
            ->where('subgrp', 'BATAS JAM EDIT ABSENSI')
            ->first();

        $jamnow = date("H:i");




        $rules = [];
        if (request()->isMethod('POST')) {

            $rulesBeda = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    //fungsi check unique
                    function ($attribute, $value, $fail) {
                        
                        $formattedDate = date('Y-m-d', strtotime($value));

                        // Cek apakah ada data dengan tanggal yang sama dalam database
                        $existingRecord = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))->where('tglbukti', $formattedDate)->first();

                        if ($existingRecord) {
                            $fail(app(ErrorController::class)->geterror('TSTB')->keterangan);
                        }
                    },
                    new DateAllowedAbsen(),
                    new DateTutupBuku(),
                ],
            ];
        } else if (request()->isMethod('PATCH')) {

            $queryexist = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
                ->select(
                    'tglbukti',
                    'nobukti',
                    'kasgantung_nobukti',
                    'statusapprovaleditabsensi'
                )
                ->where('id', $this->id)
                ->first();
            $approvaledit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                            ->where('grp','STATUS EDIT ABSENSI')
                            ->where('subgrp','STATUS EDIT ABSENSI')
                            ->first();

            $tglbukti = date('d-m-Y', strtotime($queryexist->tglbukti));
            $rulesBeda = [
                'id' => [new ValidasiDestroyAbsensiSupirHeader()],
                'tglbukti' => [
                    'required', 'date_format:d-m-Y',
                    new DateAllowedAbsen(),
                    new DateTutupBuku(),
                    Rule::in([$tglbukti]),
                ],
                'nobukti' => [
                    Rule::in([$queryexist->nobukti]),
                ],
                'kasgantung_nobukti' => [
                    Rule::in([$queryexist->kasgantung_nobukti]),
                ],
            ];
        } else if (request()->isMethod('DELETE')) {
            $absensisupirheader = new AbsensiSupirHeader();
            $cekdata = $absensisupirheader->cekvalidasiaksi($this->nobukti);



            return [
                'id' => [new ValidasiDestroyAbsensiSupirHeader(),new ValidasiHapusAbsensiAdmin()],
            ];
        } else {
            $awal  = date_create(date('Y-m-d', strtotime($this->tglbukti)));
            $akhir = date_create();
            $diff  = date_diff($awal, $akhir);

            // dd($diff->days);

            $detiknow = (substr($jamnow, 0, 2) * 3600) + (substr($jamnow, 4, 2) * 60);
            $detikval = (substr($query->text, 0, 2) * 3600) + (substr($query->text, 4, 2) * 60);

            if ($diff->days == 1) {

                if ($detiknow <= $detikval) {
                    $kondisi = true;
                } else {
                    $kondisi = false;
                }
            }


            $rulesBeda = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    function ($attribute, $value, $fail) {
                        // Ubah format tanggal dari input menjadi format yang ada di database
                        // $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                        $formattedDate = date('Y-m-d', strtotime($value));

                        // Cek apakah ada data dengan tanggal yang sama dalam database
                        $existingRecord = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))->where('tglbukti', $formattedDate)->first();

                        if ($existingRecord) {
                            $fail(app(ErrorController::class)->geterror('TSTB')->keterangan);
                        }
                    },
                    new DateAllowedAbsen($kondisi),
                    new DateTutupBuku(),
                ],
            ];
        }


        $relatedRequests = [
            StoreAbsensiSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rule = array_merge(
                $rules,
                $rulesBeda,
                (new $relatedRequest)->rules()
            );
        }


        return $rule;
    }
}
