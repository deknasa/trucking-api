<?php

namespace App\Http\Requests;

use App\Models\Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class ApprovalBatasPGRequest extends FormRequest
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
        return [
            'Id' => 'required',
            'Id.*' => [
                function ($attribute, $value, $fail){
                    $index = substr($attribute, 3);  // returns "cde"
                    $nobukti = $this->nobukti[$index];
                    $penerimaanstokheader = DB::table('penerimaanstokheader')->where('id', $value)->first();

                    if (!$penerimaanstokheader) {
                        $penerimaanstokheader = db::table("kartustoklama")->from(db::raw("kartustoklama a  with (readuncommitted)"))
                        ->select(
                            'a.*',
                            db::raw("(case when left(a.nobukti,3)='PG ' then 5
                                    when left(a.nobukti,3)='SPB' then 3
                                    when left(a.nobukti,3)='KOR' then 4
                                    else 0 end) as penerimaanstok_id"),
                        )
                        ->where('nobukti', $nobukti)->first();
                    }
                    $pg = DB::table('parameter')->where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();

                    if ($pg->text != $penerimaanstokheader->penerimaanstok_id) {
                        $fail('<b>'.$penerimaanstokheader->nobukti .'</b> '. app(ErrorController::class)->geterror('NPG')->keterangan."<br> Porses Tidak Dilanjutkan");
                    }
                },
            ],
        ];
    }
    public function messages()
    {
        return [
            'Id.required' => 'Penerimaan Stok ' . app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }
    // protected function withValidator($validator)
    // {
    //     $validator->after(function ($validator)  { 
    //         $error = new Error();
    //         $keteranganerror = $error->cekKeteranganError('ASB') ?? '';
    //         $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
    //         $id = $this->input('Id');
    //         $statusapprovalpindahgudangspk = [];
    //         // $penerimaanstokheaderall = [];
    //         foreach ($id as $index => $key) {
    //             $penerimaanstokheader = DB::table('penerimaanstokheader')->where('id', $key)->first();
    //             $statusapprovalpindahgudangspk[] = $penerimaanstokheader->statusapprovalpindahgudangspk;
    //             // $penerimaanstokheaderall[]=$penerimaanstokheader;
    //         }
    //         if (count(array_unique($statusapprovalpindahgudangspk)) > 1) {
    //             $validator->errors()->add('Penerimaan Stok ', $keteranganerror.'<br>'.$keterangantambahanerror);
    //         }

    //     });
    // }
}
