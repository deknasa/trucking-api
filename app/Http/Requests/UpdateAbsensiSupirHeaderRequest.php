<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateAllowedAbsen;
use App\Rules\DateTutupBuku;
use Illuminate\Support\Facades\DB;

class UpdateAbsensiSupirHeaderRequest extends FormRequest
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

        $querydata = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'tglbukti',
            )
            ->where('id', $this->id)
            ->first();


        $awal  = date_create($querydata->tglbukti);
        $akhir = date_create(); 
        $diff  = date_diff($awal, $akhir);

        // dd($diff->days);

        $detiknow = (substr($jamnow, 0, 2) * 3600) + (substr($jamnow, 4, 2) * 60);
        $detikval = (substr($query->text, 0, 2) * 3600) + (substr($query->text, 4, 2) * 60);


        if ($diff->days==1) {
           
            if ($detiknow<=$detikval) {
                $kondisi=true;
            } else {
                $kondisi=false;                
            }

        }

        $rules = [
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateAllowedAbsen($kondisi),
                new DateTutupBuku()
            ],
        ];

        $relatedRequests = [
            StoreAbsensiSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'Tanggal Bukti',
            'trado.*' => 'Trado',
            'uangjalan.*' => 'Uang Jalan',
            'supir.*' => 'Supir',
            // 'absen_id.*' => 'Absen',
            // 'absen' => 'Absen',
            'jam.*' => 'Jam',
            'keterangan_detail.*' => 'Keterangan Detail'
        ];
    }

    public function messages()
    {
        return [
            'uangjalan.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
