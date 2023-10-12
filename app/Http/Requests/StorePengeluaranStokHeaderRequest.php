<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DatePengeluaranStokAllowed;
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
        $gst = DB::table('parameter')->where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();
        $reuse = DB::table('parameter')->where('grp', 'STATUS REUSE')->where('text', 'REUSE')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();
        
        
        $rules = [
            "tglbukti" => [
                "required",
                new DatePengeluaranStokAllowed(),
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

        $spkRules =[];
        if ($spk->text == request()->pengeluaranstok_id) {
            $spkRules = [
                'penerimaanstok_nobukti' => Rule::requiredIf(function () use ($reuse) {
                    $required = false; //kalau true required
                    foreach ($this->input('detail_stok_id') as $detail_stok_id) {
                        $stok = DB::table('stok')->where('id', $detail_stok_id)->first();
                        if ($stok) {
                            //check statusreuse pada stok ,jika = reuse maka wajib
                            if ($reuse->id == $stok->statusreuse) {
                                return true;
                            }
                        }                        
                    }
                    return $required;
                }),
            ];
            $salahSatuDari = Rule::requiredIf(function () use ($spk) {
                if ((empty($this->input('trado')) && empty($this->input('gandengan')) && empty($this->input('gudang')) && $this->input('pengeluaranstok_id')) == $spk->text) {
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
        if ($gst->text == request()->pengeluaranstok_id) {
            $salahSatuDari = Rule::requiredIf(function () use ($gst) {
                if ((empty($this->input('trado')) && empty($this->input('gandengan')) && $this->input('pengeluaranstok_id')) == $gst->text) {
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
                'penerimaanstok_nobukti' => 'required',
                'statuspotongretur' => 'required',
                
            ];
            $potongKas = DB::table('parameter')->where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
            if (request()->statuspotongretur == $potongKas->id) {
                $returRules = array_merge($returRules,['bank_id' => 'required','bank' => 'required']);
            }
        }
        $afkirRules = [];
        if($afkir->id == request()->pengeluaranstok_id) {
            $afkirRules = [
                'pengeluarantrucking_nobukti' => function ($attribute, $value, $fail){
                    if(request()->detail_vulkanisirke[0] > 3){
                        $fail('pengeluaran trucking '.app(ErrorController::class)->geterror('WI')->keterangan);
                    }
                }  
            ];
        }

        
        $rules = array_merge($rules, $gudangTradoGandengan,$returRules,$spkRules,$afkirRules);
        
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
            'bank_id' => 'bank',
            'bank' => 'bank',
            'penerimaanstok_nobukti' => 'penerimaan stok no bukti',
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
