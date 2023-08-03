<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueTglBukaPengeluaranStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreBukaPengeluaranStokRequest extends FormRequest
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
        $pengeluaranStok = DB::table('PengeluaranStok')->select('id','kodepengeluaran')->get();
        
        $data = json_decode($pengeluaranStok, true);
        foreach ($data as $item) {
            $kode[] = $item['id'];
            $kodepengeluaran[] = $item['kodepengeluaran'];
        }
        return [
            "tglbukti"=> [
                'required', 'date_format:d-m-Y', 
                'before_or_equal:' . date('d-m-Y'),
                new UniqueTglBukaPengeluaranStok
            ],
            "pengeluaranstok" => ["required",Rule::in($kodepengeluaran)],
        ];
    }
}
