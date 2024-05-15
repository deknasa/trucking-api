<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;

class ApprovalSupirAktifRequest extends FormRequest
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
                function ($attribute, $value, $fail) {
                    $supir = DB::table('supir')->where('id', $value)->first();
                    $statusapproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('subgrp', 'STATUS APPROVAL')->first();
                    if ($statusapproval->id == $supir->statusapproval) {
                        $fail('<b>' . $supir->namasupir . '</b> ' . app(ErrorController::class)->geterror('SAP')->keterangan . '<br> Porses Tidak Dilanjutkan');
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
}
