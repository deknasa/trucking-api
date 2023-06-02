<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

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

        // First day of the month.
        $awalPeriode = date('Y-m-01', strtotime(request()->tgldari));
        $rules = [
            'periode' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                'after_or_equal:'.$awalPeriode,
            ],
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                'after_or_equal:'.$tglbatasawal,
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('Y-m-d'),
                'after_or_equal:'.$this->tgldari 
            ],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
        ];

        $relatedRequests = [
            StoreProsesGajiSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules()
            );
        }
        return $rules;
    }

    public function attributes() {
        return [
            'tgldari' => 'Tanggal Dari',
            'tglsampai' => 'Tanggal Sampai',
            'tglbukti' => 'Tanggal Bukti',
            'rincianId' => 'rincian',
            'nomPR' => 'nominal rekap ric'
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
