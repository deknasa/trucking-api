<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DatePengeluaranStokAllowed;
use App\Rules\ValidasiDestroyPengeluaranStokHeader;
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
        $reuse = DB::table('parameter')->where('grp', 'STATUS REUSE')->where('text', 'REUSE')->first();
        $korv = DB::table('pengeluaranstok')->where('kodepengeluaran', 'KORV')->first();
        $afkir = DB::table('pengeluaranstok')->where('kodepengeluaran', 'AFKIR')->first();
        $gst = DB::table('parameter')->where('grp', 'GST STOK')->where('subgrp', 'GST STOK')->first();

        
        $rules = [
            'id' => [new ValidasiDestroyPengeluaranStokHeader ()],
            "tglbukti" => [
                "required",
                // new DatePengeluaranStokAllowed(),
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
                        //check statusreuse pada stok ,jika = reuse maka wajib
                        if ($reuse->id == $stok->statusreuse) {
                            if (auth('api')->user()->isUserPusat()) {//jika pusat gak wajib
                                return false;
                            }
                            return true;
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
                if ((empty($this->input('trado')) && empty($this->input('gandengan'))&& empty($this->input('gudang')) && $this->input('pengeluaranstok_id')) == $gst->text) {
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
                // 'statuspotongretur' => 'required',
                // 'bank_id' => 'required',
                // 'bank' => 'required'
            ];
            $potongHutang = DB::table('parameter')->where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();
            $potongKas = DB::table('parameter')->where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
            if (request()->statuspotongretur == $potongKas->id) {
                $returRules = array_merge($returRules,['bank_id' => 'required','bank' => 'required']);
            }
            if (request()->statuspotongretur ==$potongHutang->id) {
                $returRules = array_merge($returRules,["penerimaanstok_nobukti"=>'required']);
            }
        }

        $afkirRules = [];
        if($afkir->id == request()->pengeluaranstok_id) {
            $stok = DB::table('stok')->select('kelompok.id as kelompok_id')->where('stok.id', request()->detail_stok_id[0])->leftJoin("kategori", "kategori.id", "stok.kategori_id")->leftJoin("subkelompok", "subkelompok.id", "kategori.subkelompok_id")->leftJoin("kelompok", "kelompok.id", "subkelompok.kelompok_id")->first();
            $kelompok = DB::table('kelompok')->select('id')->where('kelompok.kodekelompok', 'AKI')->first();
            if ($stok) {
                $kolom = request()->detail_vulkanisirke[0];
                $batas = 2;
                if ($stok->kelompok_id == $kelompok->id) {
                    $kolom = request()->jlhhari;
                    $batas = 730;
                }

                $afkirRules = [
                    'pengeluarantrucking_nobukti' => Rule::requiredIf($kolom < $batas)
                ];
            }
        }

        $rules = array_merge($rules, $gudangTradoGandengan,$returRules,$spkRules);
        
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
