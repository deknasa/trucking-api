<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreSupplierRequest extends FormRequest
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
        // dd(request()->from);
        if (request()->from == 'tas') {
            return [];
        }
        $coaQuery = DB::table('akunpusat')->from(DB::raw('akunpusat with (readuncommitted)'))->select('akunpusat.coa');
        $coaResults = $coaQuery->get();

        $coaName = [];
        foreach ($coaResults as $coa) {
            $coaName[] = $coa->coa;
        }

        $coa = Rule::in($coaName);


        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        $statusaktif = $this->statusaktif;
        $rulesStatusAktif = [];
        if ($statusaktif != null) {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        } else if ($statusaktif == null && $this->statusaktifnama != '') {
            $rulesStatusAktif = [
                'statusaktif' => ['required', Rule::in($status)]
            ];
        }

        $daftarharga = $parameter->getcombodata('STATUS DAFTAR HARGA', 'STATUS DAFTAR HARGA');
        $daftarharga = json_decode($daftarharga, true);
        foreach ($daftarharga as $item) {
            $statusDaftarHarga[] = $item['id'];
        }
        $statusdaftarharga = $this->statusdaftarharga;
            $rulesStatusDaftarHarga = [];
            if ($statusdaftarharga != null) {
                $rulesStatusDaftarHarga = [
                    'statusdaftarharga' => ['required', Rule::in($statusDaftarHarga)]
                ];
            } else if ($statusdaftarharga == null && $this->statusdaftarharganama != '') {
                $rulesStatusDaftarHarga = [
                    'statusdaftarharga' => ['required', Rule::in($statusDaftarHarga)]
                ];
            }

        return [
            'namasupplier' => ['required', 'unique:supplier'],
            'namakontak' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'top' => ['required', 'numeric', 'min:1'],
            'notelp1' => 'required|min:10|max:50',
            'email' => 'email:rfc,dns|nullable',
            'statusaktifnama' => ['required'],
            'namapemilik' => 'required',
            'jenisusaha' => 'required', 
            'ketcoa' => ['required'],
            'namarekening' => 'required',
            'statusdaftarharganama' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'namasupplier' => 'nama supplier',
            'namakontak' => 'nama kontak',
            'top' => 'syarat pembayaran',
            'alamat' => 'alamat',
            'kota' => 'kota',
            'kodepos' => 'kode pos',
            'notelp1' => 'no telp 1',
            'web' => 'web',
            'email' => 'email',
            'statusaktifnama' => 'status aktif',
            'namapemilik' => 'nama pemilik',
            'jenisusaha' => 'jenis usaha',
            'bank' => 'bank',
            'ketcoa' => 'nama perkiraan',
            'rekeningbank' => 'rekening bank',
            'namarekening' => 'nama rekening',
            'jabatan' => 'jabatan',
            'statusdaftarharganama' => 'status daftar arga',
            'kategoriusaha' => 'karegori usaha',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'namasupplier.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namakontak.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'alamat.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kota.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kodepos.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'notelp1.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'web.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'email.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktifnama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'top.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namapemilik.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'jenisusaha.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'bank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'rekeningbank.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'namarekening.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'jabatan.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusdaftarharganama.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'kategoriusaha.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'email.email' => ':attribute' . ' ' . $controller->geterror('EMAIL')->keterangan,
            'kodepos.max' => 'max 5 karakter',
            'kodepos.min' => 'min 3 karakter',
            'notelp1.max' => 'max 13 karakter',
            'notelp1.min' => 'min 11 karakter',
            'rekeningbank.max' => 'max 25 karakter',
            'rekeningbank.min' => 'min 3 karakter',
        ];
    }
}
