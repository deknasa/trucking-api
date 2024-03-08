<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PenerimaanHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\DestroyPenerimaan;
use App\Rules\ExistPelanggan;
use App\Rules\ValidasiTotalDetail;
use Illuminate\Validation\Rule;
use App\Rules\ValidasiDestroyPenerimaanHeader ;

class UpdatePenerimaanHeaderRequest extends FormRequest
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
        $penerimaanHeader = new PenerimaanHeader();
        $getDataPenerimaan = $penerimaanHeader->findAll(request()->id);

        $pelanggan_id = $this->pelanggan_id;
        $rulesPelanggan_id = [];
        if ($pelanggan_id != null) {

            $rulesPelanggan_id = [
                'pelanggan' => ['required'],
                'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
            ];
        } else if ($pelanggan_id == null && $this->pelanggan != '') {
            $rulesPelanggan_id = [
                'pelanggan_id' => ['required', 'numeric', 'min:1', new ExistPelanggan()]
            ];
        }
        $rules = [
            'id' => [ new ValidasiDestroyPenerimaanHeader()],
            // 'nobukti' => [Rule::in($getDataPenerimaan->nobukti), new DestroyPenerimaan()],
            'nobukti' => [Rule::in($getDataPenerimaan->nobukti)],
            'tglbukti' => [
                'required','date_format:d-m-Y',
                'before_or_equal:'.date('d-m-Y'),
                new DateTutupBuku()
            ],
            'tgllunas'  => ['required','before_or_equal:'.date('d-m-Y'),],
            'bank'   => ['required', new ValidasiTotalDetail()],
            'bank_id' => ['required', Rule::in($getDataPenerimaan->bank_id)]
        ];
        $relatedRequests = [
            UpdatePenerimaanDetailRequest::class
        ];
        
        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesPelanggan_id
            );
        }

        return $rules;

    }
    public function attributes()
    {
        return [
            'tgllunas' => 'tanggal lunas',
            // 'statuskas' => 'status kas',
            // 'nowarkat.*' => 'no warkat',
            'tgljatuhtempo.*' => 'tanggal jatuh tempo',
            'nominal_detail.*' => 'nominal',
            'keterangan_detail.*' => 'keterangan detail',
            'ketcoakredit.*' => 'nama perkiraan'
        ];
    }
    public function messages()
    {
        return [
            'nominal_detail.*.gt' => 'nominal wajib di isi',
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
            'tgljatuhtempo.*.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
