<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Ritasi;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistSuratPengantarRitasi;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateRitasiRequest extends FormRequest
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
        $ritasiQuery = DB::table('ritasi')->from(DB::raw('ritasi with (readuncommitted)'))->select('ritasi.id');
        $ritasiResults = $ritasiQuery->get();

        $ritasiName = [];
        foreach ($ritasiResults as $ritasi) {
            $ritasiName[] = $ritasi->id;
        }

        $ritasi = Rule::in($ritasiName);


        $ritasi = new Ritasi();
        $getData = $ritasi->find(request()->id);
       

        return [
            'nobukti' => [Rule::in($getData->nobukti)],
            "tglbukti" => [
                "required",'date_format:d-m-Y',
                'date_equals:'.date('d-m-Y', strtotime($getData->tglbukti)),
                new DateTutupBuku()
            ],
            'statusritasi' => 'required','numeric', 'min:1',
            'suratpengantar_nobukti' => [new ExistSuratPengantarRitasi()],
            'dari' => 'required','numeric', 'min:1',
            'sampai' => 'required','numeric', 'min:1',
            'trado' => 'required','numeric', 'min:1',
            'supir' => 'required','numeric', 'min:1',
        ];
    }

    public function attributes()
    {
        return [
            'tglbukti' => 'tanggal bukti',
            'statusritasi' => 'status ritasi',
            'suratpengantar_nobukti' => 'No bukti surat pengantar',
        ];
    }
    public function messages() 
    {
        return [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];
    }
}
