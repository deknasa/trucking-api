<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Http\Controllers\Api\RekapPenerimaanHeaderController;
use App\Models\RekapPenerimaanHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ValidasiDestroyRekapPenerimaanHeader;
use App\Rules\ValidasiUpdateRekapPenerimaanHeader;

class UpdateRekapPenerimaanHeaderRequest extends FormRequest
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
        $controller = new RekapPenerimaanHeaderController;
        $rekappenerimaanheader = new RekapPenerimaanHeader();
        $cekdata = $rekappenerimaanheader->cekvalidasiaksi($this->nobukti);
        $cekdatacetak = $controller->cekvalidasi($this->id);
        if ($cekdatacetak->original['kodestatus']=='1') {
                $cekdtcetak=true;
        } else {
            $cekdtcetak=false;
        }

        return [
            'id' => [ new ValidasiUpdateRekapPenerimaanHeader($cekdata['kondisi'],$cekdtcetak)],
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            "tgltransaksi"=>"required|date_format:d-m-Y",
            "bank"=>"required",
        ];
    }
    public function messages()
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgltransaksi.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
