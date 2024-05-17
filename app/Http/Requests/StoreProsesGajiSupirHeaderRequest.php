<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ValidasiHutangList;
use App\Rules\validasiRicProsesGajiSupir;

class StoreProsesGajiSupirHeaderRequest extends FormRequest
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
        $tglbatasawal = date('Y-m-01');
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        $bank_id = $this->bank_id;
        $rulesBank_id = [];
        if ($bank_id != null) {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        $jumlahdetail = $this->jumlahdetail ?? 0;
        // First day of the month.
        $awalPeriode = date('Y-m-01', strtotime(request()->tgldari));
        $rules = [

            'bank' => [
                'required',
                new ValidasiHutangList($jumlahdetail),
                new validasiRicProsesGajiSupir()
            ],
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                new DateTutupBuku()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                'after_or_equal:' . $this->tgldari
            ],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'statusjeniskendaraan' => 'required'
        ];

        // dd($rules);
        $relatedRequests = [
            StoreProsesGajiSupirDetailRequest::class
        ];
        // dd($rules);
        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesBank_id
            );
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'tgldari' => 'Tanggal Dari',
            'tglsampai' => 'Tanggal Sampai',
            'tglbukti' => 'Tanggal Bukti',
            'rincianId' => 'rincian',
            'nomPR' => 'nominal posting rincian'
        ];
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'periode.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tglsampai.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
