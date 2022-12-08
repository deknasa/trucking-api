<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratPengantarRequest extends FormRequest
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
            'pelanggan_id' => 'required',
            'keterangan' => 'required',
            'dari_id' => 'required',
            'sampai_id' => 'required',
            'statusperalihan' => 'required',
            'container_id' => 'required',
            'nocont' => 'required',
            'statuscontainer_id' => 'required',
            'trado_id' => 'required',
            'supir_id' => 'required',
            'agen_id' => 'required',
            'nojob' => 'required',
            'statuslongtrip' => 'required',
            'omset' => 'required',
            'jenisorder_id' => 'required',
            'tarif_id' => 'required',
            'nosp' => 'required',
            'tglsp' => 'required',
            // 'statusritasiomset' => 'required',
            'cabang_id' => 'required',
            'statustrip' => 'required',
            'qtyton' => 'required|numeric|gt:0',
            'totalton' => 'required|numeric|gt:0',
            'statusnotif' => 'required',
            'statusoneway' => 'required',
            'statusedittujuan' => 'required',
            'tgldoor' => 'required',
            'upahbongkardepo' => 'required|numeric|gt:0',
            'upahmuatdepo' => 'required|numeric|gt:0',

        ];
    }

    public function attributes()
    {
        return [
            'jobtrucking' => 'Job Trucking',
            'tglbukti' => 'Tgl Bukti',
            'pelanggan_id' => 'Pelanggan',
            'keterangan' => 'Keterangan',
            'dari_id' => 'Dari',
            'sampai_id' => 'Sampai',
            'statusperalihan' => 'Status Peralihan',
            'container_id' => 'Container',
            'nocont' => 'NO Container',
            'statuscontainer_id' => 'Status Container',
            'trado_id' => 'Trado',
            'supir_id' => 'Supir',
            'agen_id' => 'Agen',
            'nojob' => 'NO Job',
            'statuslongtrip' => 'Status Longtrip',
            'omset' => 'Omset',
            'jenisorder_id' => 'Jenis Order',
            'tarif_id' => 'Tarif',
            'nosp' => 'NO SP',
            'tglsp' => 'Tgl SP',
            // 'statusritasiomset' => 'Status Ritasi Omset',
            'cabang_id' => 'Cabang',
            'statustrip' => 'Status Trip',
            'qtyton' => 'QTY Ton',
            'statusnotif' => 'Status Notif',
            'statusoneway' => 'Status One Way',
            'statusedittujuan' => 'Status Edit Tujuan',
            'tgldoor' => 'Tgl Door',
            'upahbongkardepo' => 'Upah Bongkar Depo',
            'upahmuatdepo' => 'Upah Muat Depo',

        ];
    }

    public function messages()
    {
        return [
            'qtyton.gt' => 'QTY Ton tidak boleh kosong',
            'upahbongkardepo.gt' => 'Upah Bongkar Depo tidak boleh kosong',
            'upahmuatdepo.gt' => 'Upah Muat Depo tidak boleh kosong',
        ];
    }
}
