<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdatePenerimaanStokHeaderRequest extends FormRequest
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
        
        $requiredSupplier = Rule::requiredIf((request()->penerimaanstok_id == $spb->text)||(request()->penerimaanstok_id == $po->text)||(request()->penerimaanstok_id == $do->text)||(request()->penerimaanstok_id == $reuse->text));
        // $requiredPenerimaanStokNobukti = Rule::requiredIf((request()->penerimaanstok_id == $spb->text));
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
        $rules = [
            'tglbukti' => [
                'required',
                new DateTutupBuku()
            ],
            "supplier" => $requiredSupplier,
            // "keterangan"=> "required", 
            // "penerimaanstok" => "required",
            // "penerimaanstok_id" => "required",
            // "penerimaanstok_nobukti" => $requiredPenerimaanStokNobukti,
            'trado' => $salahsatu,
            'gandengan' => $salahsatu,
            'gudang' => $salahsatu,
            'tradodari' => $salahSatuDari,
            'gandengandari' => $salahSatuDari,
            'gudangdari' => $salahSatuDari,
            'tradoke' => $salahSatuKe,
            'gandenganke' => $salahSatuKe,
            'gudangke' => $salahSatuKe,
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
        ];

        $relatedRequests = [
            StorePenerimaanTruckingDetailRequest::class
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
