<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\BeforeTglSampaiGajiSupir;
use App\Rules\CekPendapatanKeRic;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistSupir;
use App\Rules\validasiContSPGajiSupir;
use App\Rules\ValidasiStatusContGajiSupir;
use App\Rules\ValidasiTambahanGajiSupir;
use App\Rules\ValidasiTripGajiSupir;

class StoreGajiSupirHeaderRequest extends FormRequest
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
        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $rules = [
            //
            'supir' => ['required', new ValidasiTripGajiSupir(), new validasiContSPGajiSupir(), new ValidasiTambahanGajiSupir(), new ValidasiStatusContGajiSupir()],
            'tgldari' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                'after_or_equal:' . $tglbatasawal,
                new CekPendapatanKeRic()
            ],
            'tglsampai' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglbatasakhir,
                'after_or_equal:' . $this->tgldari
            ],
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new DateTutupBuku(),
                'before_or_equal:' . date('d-m-Y')
            ],
        ];
        $relatedRequests = [
            StoreGajiSupirDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesSupir_id
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
            'rincianId' => 'trip',
            'nominalPS.*' => 'nominal pinjaman semua',
            'nominalPP.*' => 'nominal pinjaman pribadi',
        ];
    }
    public function messages()
    {
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        return [
            'supir_id.required' => ':attribute ' . app(ErrorController::class)->geterror('HPDL')->keterangan,
            'rincianId' => app(ErrorController::class)->geterror('WP')->keterangan,
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgldari.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
            'tglsampai.before' => app(ErrorController::class)->geterror('NTLB')->keterangan . ' ' . $tglbatasakhir,
        ];
    }
}
