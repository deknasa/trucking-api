<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateAllowedAbsen;
use App\Rules\DateTutupBuku;

class StoreAbsensiSupirHeaderRequest extends FormRequest
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
            'tglbukti' => [
                'required',
                new DateAllowedAbsen(),
                new DateTutupBuku()
            ],
        ];
        

        $relatedRequests = [
            StoreAbsensiSupirDetailRequest::class
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
            'tglbukti' => 'Tanggal Bukti',
            'trado.*' => 'Trado',
            'uangjalan.*' => 'Uang Jalan',
            'supir.*' => 'Supir',
            // 'absen_id.*' => 'Absen',
            // 'absen' => 'Absen',
            'jam.*' => 'Jam',
            'keterangan_detail.*' => 'Keterangan Detail'
        ];
    }

    public function messages() 
    {
        return [
            'uangjalan.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0'
        ];
    }
}
