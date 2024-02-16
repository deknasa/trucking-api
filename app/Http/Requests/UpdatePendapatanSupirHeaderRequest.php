<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\PendapatanSupirHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistSupir;
use App\Rules\ValidasiDestroyPendapatanSupirHeader;
use App\Rules\ValidasiHutangList;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidasiPendapatanSupir;
use App\Rules\validasiTripKomisi;

class UpdatePendapatanSupirHeaderRequest extends FormRequest
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
        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $pendapatanSupir = new PendapatanSupirHeader();
        $getData = $pendapatanSupir->findUpdate(request()->id);

        $jumlahdetail = $this->jumlahdetail ?? 0;
        $bank_id = $this->bank_id;
        $ruleBank_id = [];
        if ($bank_id != null) {
            $ruleBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $ruleBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', new ExistSupir()]
            ];
        }

        $rules = [
            'id' => [new ValidasiDestroyPendapatanSupirHeader()],
            'nobukti' => [Rule::in($getData->nobukti)],
            "tglbukti" => [
                "required",
                'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-t'),
                new DateTutupBuku()
            ], 
            'bank' => 'required',
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-t'),                
                new ValidasiHutangList($jumlahdetail),
                new ValidasiPendapatanSupir(),
                new validasiTripKomisi()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . date('d-m-Y', strtotime($this->tgldari)),
                'before_or_equal:' . date('Y-m-t'),
                new ValidasiPendapatanSupir()


            ],
        ];
        $rules = array_merge(
            $rules,
            $ruleBank_id,
            // $rulesSupir_id
        );
        $cekBank = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PENDAPATAN SUPIR')->where('subgrp', 'BANK')->first();
        if($cekBank->text == 'TIDAK'){
            unset($rules['bank']);
            unset($rules['bank_id']);
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'tanggal bukti',
            'tgldari' => 'tanggal dari',
            'tglsampai' => 'tanggal sampai',
        ];
    }

    public function messages()
    {
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
            'tglsampai.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
        ];
    }
}
