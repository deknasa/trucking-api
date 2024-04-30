<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\GajiSupirHeader;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DestroyGajiSupirNobukti;
use App\Rules\validasiContSPGajiSupir;
use App\Rules\validasiPemutihanSupirRIC;
use App\Rules\ValidasiStatusContGajiSupir;
use App\Rules\ValidasiTambahanGajiSupir;
use App\Rules\validasiTglBuktiRIC;
use App\Rules\ValidasiTripGajiSupir;
use Illuminate\Validation\Rule;

class UpdateGajiSupirHeaderRequest extends FormRequest
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
        $gajiSupir = new GajiSupirHeader();
        $getDataGajiSupir = $gajiSupir->findAll(request()->id);
        $supir_id = $this->supir_id;
        $rulesSupir_id = [];
        if ($supir_id != null) {
            if ($supir_id == 0) {
                $rulesSupir_id = [
                    'supir_id' => ['required', 'numeric', 'min:1', Rule::in($getDataGajiSupir->supir_id)]
                ];
            }else{
                $rulesSupir_id = [
                    'supir_id' => ['required', 'numeric', 'min:1', Rule::in($getDataGajiSupir->supir_id)]
                ];
            }
        } else if ($supir_id == null && $this->supir != '') {
            $rulesSupir_id = [
                'supir_id' => ['required', 'numeric', 'min:1', Rule::in($getDataGajiSupir->supir_id)]
            ];
        }
        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        $rules = [
            'id' => new DestroyGajiSupirNobukti(),
            'nobukti' => [Rule::in($getDataGajiSupir->nobukti)],
            'supir' => ['required',  new ValidasiTripGajiSupir(), new validasiContSPGajiSupir(), new ValidasiTambahanGajiSupir(), new ValidasiStatusContGajiSupir(), new validasiPemutihanSupirRIC()],
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
            'tglbukti' => [
                'required', 'date_format:d-m-Y',
                new validasiTglBuktiRIC(),
                // 'date_equals:' . date('d-m-Y', strtotime($getDataGajiSupir->tglbukti)),
                new DateTutupBuku()
            ],
        ];
        $relatedRequests = [
            UpdateGajiSupirDetailRequest::class
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
            'tgldari.before' => app(ErrorController::class)->geterror('NTLB')->keterangan. ' '.$tglbatasakhir,
            'tglsampai.before' => app(ErrorController::class)->geterror('NTLB')->keterangan. ' '.$tglbatasakhir,
        ];
    }
}