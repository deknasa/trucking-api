<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueTglBukaPenerimaanStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreBukaPenerimaanStokRequest extends FormRequest
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

        $penerimaanStok = DB::table('PenerimaanStok')->select('id','kodepenerimaan')->get();
        
        $data = json_decode($penerimaanStok, true);
        foreach ($data as $item) {
            $kode[] = $item['id'];
            $kodepenerimaan[] = $item['kodepenerimaan'];
        }
        return [
            "tglbukti"=> [
                'required', 'date_format:d-m-Y', 
                'before_or_equal:' . date('d-m-Y'),
                new UniqueTglBukaPenerimaanStok
            ],
            "penerimaanstok" => ["required",Rule::in($kodepenerimaan)],
        ];
    }
}
