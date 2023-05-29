<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;

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
        
        $rules = [
            "tglbukti" => [
                "required",
                new DateTutupBuku()
            ],
            'bank' => 'required',
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.$tglbatasawal,
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.$this->tgldari 
            ],
            'periode' => [
                'required', 'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
            ],
        ];
        $relatedRequests = [
            UpdatePendapatanSupirDetailRequest::class
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
            'tglbukti' => 'tanggal bukti',
            'tgldari' => 'tanggal dari',
            'tglsampai' => 'tanggal sampai',
            'supir.*' => 'supir',
            'nominal.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan'
        ];
    }

    public function messages()
    {
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        return [
            'nominal.*.gt' => 'tidak boleh kosong',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.before' => app(ErrorController::class)->geterror('NTLB')->keterangan. ' '.$tglbatasakhir,
            'tglsampai.before' => app(ErrorController::class)->geterror('NTLB')->keterangan. ' '.$tglbatasakhir,
            'periode.before' => app(ErrorController::class)->geterror('NTLB')->keterangan. ' '.$tglbatasakhir,
        ];
    }
}
