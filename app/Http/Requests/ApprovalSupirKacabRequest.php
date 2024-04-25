<?php

namespace App\Http\Requests;

use App\Models\Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class ApprovalSupirKacabRequest extends FormRequest
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
                    $supir = DB::table('supir')->where('id', $value)->first();
                    $statusaktif = DB::table('parameter')->where('grp', 'STATUS AKTIF')->where('subgrp', 'STATUS AKTIF')->first();

                    if ($statusaktif->id != $supir->statusaktif) {
                        $fail('<b>'.$supir->namasupir .'</b> '. app(ErrorController::class)->geterror('SBA')->keterangan."<br> Porses Tidak Dilanjutkan");
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'Id.required' => 'Supir ' . app(ErrorController::class)->geterror('WP')->keterangan,
        ];
    }

    
    protected function withValidator($validator)
    {
        $validator->after(function ($validator)  { 
            $error = new Error();
            $keteranganerror = $error->cekKeteranganError('ASB') ?? '';
            // $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
            $id = $this->input('Id');
            $statusapproval = [];
            // $supirall = [];
            foreach ($id as $index => $key) {
                $supir = DB::table('supir')->where('id', $key)->first();
                $statusapproval[] = $supir->statusapproval;
                // $supirall[]=$supir;
            }
            if (count(array_unique($statusapproval)) > 1) {
                $validator->errors()->add('Supir', $keteranganerror.'<br>'.$keterangantambahanerror);
            }

        });
    }
}
