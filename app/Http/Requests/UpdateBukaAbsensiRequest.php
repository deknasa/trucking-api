<?php

namespace App\Http\Requests;

use App\Models\BukaAbsensi;
use App\Rules\DateTutupBuku;
use App\Rules\DateAllowedAbsenMandor;
use App\Rules\UniqueTglBukaAbsensiEdit;
use App\Rules\ApprovalAbsensiFinal;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBukaAbsensiRequest extends FormRequest
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
        $bukaAbsensi = BukaAbsensi::find(request()->id);
        return [
            "tglabsensi"=> [
                'required', 'date_format:d-m-Y', 
                'before_or_equal:' . date('d-m-Y', strtotime($bukaAbsensi->tglabsensi)),
                new ApprovalAbsensiFinal,
                new UniqueTglBukaAbsensiEdit,
                new DateTutupBuku(),
            ]
        ];
    }
}
