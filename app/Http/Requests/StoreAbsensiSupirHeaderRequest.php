<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateAllowedAbsen;
use App\Rules\DateTutupBuku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
                'date_format:d-m-Y',
                function ($attribute, $value, $fail) {
                    // Ubah format tanggal dari input menjadi format yang ada di database
                    // $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                    $formattedDate = date('Y-m-d', strtotime($value));
                    
                    // Cek apakah ada data dengan tanggal yang sama dalam database
                    $existingRecord = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))->where('tglbukti', $formattedDate)->first();
                    
                    if ($existingRecord) {
                        $fail(app(ErrorController::class)->geterror('TSTB')->keterangan);
                    }	
                },
                new DateAllowedAbsen(),
                new DateTutupBuku(),
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
            'uangjalan.*.gt' => 'Nominal Tidak Boleh Kosong dan Harus Lebih Besar Dari 0',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan
        ];
    }
}
