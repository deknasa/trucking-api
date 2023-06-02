<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StorePengeluaranStokHeaderRequest extends FormRequest
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
        $spk = DB::table('parameter')->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $retur = DB::table('parameter')->where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $kor = DB::table('parameter')->where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        
        
        $rules = [
            "tglbukti" => [
                "required",
                new DateTutupBuku()
            ],
            
            "pengeluaranstok" => "required",
            "pengeluaranstok_id" => "required",
            "modifiedby"=> "string", 
        ];
        $gudangTradoGandengan= [];
        if ($kor->text == request()->pengeluaranstok_id) {
            
            $salahSatuDari = Rule::requiredIf(function () use ($kor) {
                if ((empty($this->input('trado')) && empty($this->input('gandengan')) && empty($this->input('gudang'))) && $this->input('pengeluaranstok_id') ==$kor->text) {
                    return true;
                }
                return false;
            });
            $gudangTradoGandengan = [
                'trado' => $salahSatuDari,
                'gandengan' => $salahSatuDari,
                'gudang' => $salahSatuDari,
            ];
        }
        if ($spk->text == request()->pengeluaranstok_id) {
            $salahSatuDari = Rule::requiredIf(function () use ($spk) {
                if ((empty($this->input('trado')) && empty($this->input('gandengan')) && $this->input('pengeluaranstok_id')) == $spk->text) {
                    return true;
                }
                return false;
            });
            $gudangTradoGandengan = [
                'trado' => $salahSatuDari,
                'gandengan' => $salahSatuDari,
                'gudang' => "",
            ];
        }
        $rules = array_merge($rules, $gudangTradoGandengan);
        
        $relatedRequests = [
            StorePengeluaranStokDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }
        return $rules;
        // return $rules;
       
    }
    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tgl Bukti',
            // 'pengeluaranstok' => 'nama pengeluaran',
            'pengeluaranstok' => 'Kode pengeluaran',
            'trado' => 'trado',
            'gandengan' => 'gandengan',
            'gudang' => 'gudang',
        ];

        $relatedRequests = [
            StorePengeluaranStokDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }

        return $attributes;
    }

    public function messages()
    {
        $messages = [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'penerimaanstok.required' => app(ErrorController::class)->geterror('WI')->keterangan,
            'trado.required' => app(ErrorController::class)->geterror('STGG')->keterangan,
            'gandengan.required' => app(ErrorController::class)->geterror('STGG')->keterangan,
            'gudang.required' => app(ErrorController::class)->geterror('STGG')->keterangan,
        ];

        $relatedRequests = [
            StorePengeluaranStokDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $messages = array_merge(
                $messages,
                (new $relatedRequest)->messages()
            );
        }

        return $messages;
    }
}
