<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DatePenerimaanStokAllowed;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use App\Models\PenerimaanStok;

use Illuminate\Support\Facades\DB;

class StorePenerimaanStokHeaderRequest extends FormRequest
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
        $spb = DB::table('parameter')->where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
        $po = DB::table('parameter')->where('grp', 'PO STOK')->where('subgrp', 'PO STOK')->first();
        $do = DB::table('parameter')->where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
        $kor = DB::table('parameter')->where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
        $pg = DB::table('parameter')->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $reuse = DB::table('parameter')->where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
        $pst = DB::table('parameter')->where('grp', 'PST STOK')->where('subgrp', 'PST STOK')->first();
        $pspk = DB::table('parameter')->where('grp', 'PSPK STOK')->where('subgrp', 'PSPK STOK')->first();
        $korv = DB::table('penerimaanstok')->where('kodepenerimaan', 'KORV')->first();
        $spbp = DB::table('penerimaanstok')->where('kodepenerimaan', 'SPBP')->first();
        
        $requiredSupplier = Rule::requiredIf((request()->penerimaanstok_id == $spb->text)||(request()->penerimaanstok_id == $po->text)||(request()->penerimaanstok_id == $do->text)||(request()->penerimaanstok_id == $reuse->text));
        $requiredPenerimaanStokNobukti = Rule::requiredIf(request()->penerimaanstok_id == $spbp->id);
        // $requiredPenerimaanStokNobukti = Rule::requiredIf((request()->penerimaanstok_id == $spb->text));
        $requiredPengeluaranStokNobukti = Rule::requiredIf((request()->penerimaanstok_id == $pst->text) || (request()->penerimaanstok_id == $pspk->text));
        $salahsatu = Rule::requiredIf(function () use ($kor) {
            if ((empty($this->input('trado')) && empty($this->input('gandengan')) && empty($this->input('gudang'))) && $this->input('penerimaanstok_id') ==$kor->text) {
                return true;
            }
            return false;
        });
        $salahSatuDari = Rule::requiredIf(function () use ($pg) {
            if ((empty($this->input('tradodari')) && empty($this->input('gandengandari')) && empty($this->input('gudangdari'))) && $this->input('penerimaanstok_id') ==$pg->text) {
                return true;
            }
            return false;
        });
        $salahSatuKe = Rule::requiredIf(function () use ($pg) {
            if ((empty($this->input('tradoke')) && empty($this->input('gandenganke')) && empty($this->input('gudangke'))) && $this->input('penerimaanstok_id') ==$pg->text) {
                return true;
            }
            return false;
        });

        $penerimaanStok = DB::table('PenerimaanStok')->select('id','kodepenerimaan')->get();
        
        $data = json_decode($penerimaanStok, true);
        foreach ($data as $item) {
            $kode[] = $item['id'];
            $kodepenerimaan[] = $item['kodepenerimaan'];
        }
        
        // dd( [$requiredSupplier,$spb->text,$po->text,request()->penerimaanstok_id]);
        //     $idpengeluaran = request()->pengeluarantrucking_id;
        //     if ($idpengeluaran != '') {
        //         $fetchFormat =  DB::table('pengeluarantrucking')
        //             ->where('id', $idpengeluaran)
        //             ->first();
        //         if ($fetchFormat->kodepengeluaran == 'TDE' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'KBBM') {
        //             return false;
        //         } else {
        //             return true;
        //         }
        //     }
        //     return true;
        // });

        $rules = [
            'tglbukti' => [
                'required',
                new DatePenerimaanStokAllowed(),
                new DateTutupBuku()
            ],
            "supplier" => $requiredSupplier,
            // "keterangan"=> "required", 
            "penerimaanstok" => ["required",Rule::in($kodepenerimaan)],
            "penerimaanstok_id" => ["required",Rule::in($kode)],
            // "penerimaanstok_nobukti" => $requiredPenerimaanStokNobukti,
            "pengeluaranstok_nobukti" => $requiredPengeluaranStokNobukti,
            'trado' => $salahsatu,
            'gandengan' => $salahsatu,
            'gudang' => $salahsatu,
            'tradodari' => [$salahSatuDari,'nullable'],
            'gandengandari' => [$salahSatuDari,'nullable'],
            'gudangdari' => [$salahSatuDari,'nullable'],
            'tradoke' => [$salahSatuKe,'nullable','different:tradodari'],
            'gandenganke' => [$salahSatuKe,'nullable','different:gandengandari'],
            'gudangke' => [$salahSatuKe,'nullable','different:gudangdari'],
        ];

        $relatedRequests = [
            StorePenerimaanStokDetailRequest::class
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
        $attributes = [
            'tglbukti' => 'Tgl Bukti',
            // 'penerimaanstok' => 'nama Penerimaan',
            'penerimaanstok' => 'Kode Penerimaan',
            'trado' => 'trado',
            'gandengan' => 'gandengan',
            'gudang' => 'gudang',

            'tradodari' => 'trado dari',
            'gandengandari' => 'gandengan dari',
            'gudangdari' => 'gudang dari',
            
            'tradoke' => 'trado ke',
            'gandenganke' => 'gandengan ke',
            'gudangke' => 'gudang ke',
        ];

        $relatedRequests = [
            StorePenerimaanStokDetailRequest::class
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
            'tradodari.required' => app(ErrorController::class)->geterror('STGGD')->keterangan,
            'gandengandari.required' => app(ErrorController::class)->geterror('STGGD')->keterangan,
            'gudangdari.required' => app(ErrorController::class)->geterror('STGGD')->keterangan,
            'tradoke.required' => app(ErrorController::class)->geterror('STGGK')->keterangan,
            'gandenganke.required' => app(ErrorController::class)->geterror('STGGK')->keterangan,
            'gudangke.required' => app(ErrorController::class)->geterror('STGGK')->keterangan,
            'tradoke.different' => "trado ke Harus berbeda dengan trado dari",
            'gandenganke.different' => "gandengan ke Harus berbeda dengan gandengan dari",
            'gudangke.different' => "gudang ke Harus berbeda dengan gudang dari",
        ];

        $relatedRequests = [
            StorePenerimaanStokDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $messages = array_merge(
                $messages,
                (new $relatedRequest)->messages()
            );
        }

        return $messages;
    }
    protected function withValidator($validator)
    {
        $pg = DB::table('parameter')->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
        $validator->after(function ($validator) use ($pg){ 
            $kelompok = $this->input('detail_stok_kelompok');
            if($this->input('penerimaanstok_id') == $pg->text){
                // Check if all values in kelompok are the same
                if (count(array_unique($kelompok)) > 1) {
                    $validator->errors()->add('detail_stok_kelompok', 'Semua Stok harus dalam kelompok yang sama.');
                }
            }
        });
    }
}
