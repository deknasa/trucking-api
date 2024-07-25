<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\DateTutupBuku;
use App\Rules\validasiTripBiayaExtraSupir;
use Illuminate\Foundation\Http\FormRequest;

class StoreBiayaExtraSupirHeaderRequest extends FormRequest
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
        $rules = [
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                new DateTutupBuku()
            ],
            'suratpengantar_nobukti' => ['required', new validasiTripBiayaExtraSupir()],
        ];
        $relatedRequests = [
            StoreBiayaExtraSupirDetailRequest::class
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
            'tglbukti' => 'tanggal bukti',
            'suratpengantar_nobukti' => 'No Bukti Surat Pengantar',
            'keteranganbiaya.*' => 'keterangan biaya',
            'nominaltagih.*' => 'nominal tagih',
        ];
    }
    
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
