<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\DateTutupBuku;
use App\Rules\validasiStatusApprovalBukaTanggalTrip;
use App\Rules\validasiTglApprovalBukaTanggalTrip;
use App\Rules\ValidationJumlahTripApproval;
use App\Rules\ValidationTglBuktiSPUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateSuratPengantarApprovalInputTripRequest extends FormRequest
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
        $data = $parameter->getcombodata('STATUS APPROVAL', 'STATUS APPROVAL');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }

        $query = DB::table("suratpengantarapprovalinputtrip")->from(DB::raw("suratpengantarapprovalinputtrip with (readuncommitted)"))
        ->where('id', $this->id)
        ->first();
        return [
            'tglbukti' => ['required','date_format:d-m-Y', 'before:'. date('d-m-Y'), new validasiTglApprovalBukaTanggalTrip($query->tglbukti), new DateTutupBuku()],
            'jumlahtrip' => ['required','numeric','min:1', new ValidationJumlahTripApproval()],
            'statusapproval' => ['required', Rule::in($status), new validasiStatusApprovalBukaTanggalTrip($query->statusapproval)]
        ];
    }
}
