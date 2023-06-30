<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\PengeluaranStokHeaderController;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdatePengeluaranStokHeaderRequest extends FormRequest
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
            'id' => function ($attribute, $value, $fail) {
                $id = $this->route('pengeluaranstokheader');
                $statusEdit = app(PengeluaranStokHeaderController::class)->cekvalidasi($id);
                if($statusEdit->original['kodestatus']){
                    $fail(app(ErrorController::class)->geterror('SDC')->keterangan);
                }
            },
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
        $returRules =[];
        if($retur->text == request()->pengeluaranstok_id) {
            $returRules = [
                'statuspotongretur' => 'required',
                'bank_id' => 'required',
                'bank' => 'required'
            ];
            $potongHutang = DB::table('parameter')->where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();
            if (request()->statuspotongretur ==$potongHutang->id) {
                $returRules = array_merge($returRules,["penerimaanstok_nobukti"=>'required']);
            }
        }
        $rules = array_merge($rules, $gudangTradoGandengan,$returRules);
        
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
    }
}
