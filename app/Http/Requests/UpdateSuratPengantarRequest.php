<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuratPengantarRequest extends FormRequest
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
            'jobtrucking' => 'required',
            'tglbukti' => 'required',
            'pelanggan' => 'required',
            'keterangan' => 'required',
            'dari' => 'required',
            'sampai' => 'required',
            'statusperalihan' => 'required',
            'container' => 'required',
            'nocont' => 'required',
            'statuscontainer' => 'required',
            'trado' => 'required',
            'supir' => 'required',
            'agen' => 'required',
            'nojob' => 'required',
            'statuslongtrip' => 'required',
            'omset' => 'required',
            'jenisorder' => 'required',
            'tarif' => 'required',
            'nosp' => 'required',
            'tglsp' => 'required',
            // 'statusritasiomset' => 'required',
            'cabang' => 'required',
            'statustrip' => 'required',
            // 'qtyton' => 'required|numeric|gt:0',
            // 'totalton' => 'required|numeric|gt:0',
            'statusnotif' => 'required',
            'statusoneway' => 'required',
            'statusedittujuan' => 'required',
            // 'tgldoor' => 'required',
            // 'upahbongkardepo' => 'required|numeric|gt:0',
            // 'upahmuatdepo' => 'required|numeric|gt:0',

        ];
    }

    public function attributes()
    {
        return [
            'jobtrucking' => 'Job Trucking',
            'tglbukti' => 'Tgl Bukti',
            'keterangan' => 'Keterangan',
            'statusperalihan' => 'Status Peralihan',
            'nocont' => 'NO Container',
            'statuscontainer' => 'Status Container',
            'nojob' => 'NO Job',
            'statuslongtrip' => 'Status Longtrip',
            'omset' => 'Omset',
            'jenisorder' => 'Jenis Order',
            'nosp' => 'NO SP',
            'tglsp' => 'Tgl SP',
            // 'statusritasiomset' => 'Status Ritasi Omset',
            'statustrip' => 'Status Trip',
            // 'qtyton' => 'QTY Ton',
            'statusnotif' => 'Status Notif',
            'statusoneway' => 'Status One Way',
            'statusedittujuan' => 'Status Edit Tujuan',
            // 'tgldoor' => 'Tgl Door',
            // 'upahbongkardepo' => 'Upah Bongkar Depo',
            // 'upahmuatdepo' => 'Upah Muat Depo',

        ];
    }

    // public function messages()
    // {
    //     return [
    //         'qtyton.gt' => 'QTY Ton tidak boleh kosong',
    //         'upahbongkardepo.gt' => 'Upah Bongkar Depo tidak boleh kosong',
    //         'upahmuatdepo.gt' => 'Upah Muat Depo tidak boleh kosong',
    //     ];
    // }
}
