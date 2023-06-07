<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Rules\NotDecimal;
use App\Rules\ValidasiGambarTrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Parameter;
use App\Http\Controllers\Api\ParameterController;


class UpdateTradoRequest extends FormRequest
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
        $ruleGambar = Rule::requiredIf(function () {
            $kodeTrado = request()->kodetrado;
            $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp like '%STATUS APPROVAL%'")
                ->whereRaw("text like '%NON APPROVAL%'")
                ->first();
            $cekValidasi = DB::table('approvaltradogambar')->from(DB::raw("approvaltradogambar with (readuncommitted)"))
                ->select('kodetrado', 'tglbatas','statusapproval')
                ->whereRaw("kodetrado in ('$kodeTrado')")
                ->first();
            if ($cekValidasi != '') {
                if ($cekValidasi->statusapproval == $nonApp->id) {
                    return true;
                } else {
                    if (date('Y-m-d') > $cekValidasi->tglbatas) {
                        return true;
                    }
                    return false;
                }
            }
            return true;

        });

        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        } 
        return [
            'kodetrado' => ['required',Rule::unique('trado')->whereNotIn('id', [$this->id])],
            'statusaktif' => ['required', Rule::in($status)],
            'tahun' => 'required|min:4|max:4',
            'merek' => 'required',
            'norangka' => ['required', 'max:20', Rule::unique('trado')->whereNotIn('id', [$this->id])],
            'nomesin' =>  ['required','max:20', Rule::unique('trado')->whereNotIn('id', [$this->id])],
            'nama' => 'required',
            'nostnk' =>  ['required', 'max:50', Rule::unique('trado')->whereNotIn('id', [$this->id])],
            'alamatstnk' => 'required',
            'statusjenisplat' => 'required',
            'tglpajakstnk' => 'required',
            'tipe' => 'required',
            'jenis' => 'required',
            'isisilinder' => 'required|numeric|min:1|digits_between:1,5',
            'warna' => 'required',
            'jenisbahanbakar' => 'required',
            'jumlahsumbu' => 'required|numeric|min:1|digits_between:1,2',
            'jumlahroda' => 'required|numeric|min:1|digits_between:1,2',
            'model' => 'required',
            'nobpkb' => ['required', 'max:15', Rule::unique('trado')->whereNotIn('id', [$this->id])],
            'jumlahbanserap' => 'required|numeric|min:1|digits_between:1,2',
            'statusgerobak' => 'required',
            'nominalplusborongan' => [new NotDecimal()],
            'phototrado' => [$ruleGambar, 'array'],
            'phototrado.*' => [$ruleGambar, 'image'],
            'photobpkb' => [$ruleGambar, 'array'],
            'photobpkb.*' => [$ruleGambar, 'image'],
            'photostnk' => [$ruleGambar, 'array'],
            'photostnk.*' => [$ruleGambar, 'image'],
        ];
    }

    public function attributes()
    {
        return [
            'kodetrado' => 'kode trado',
            'statusaktif' => 'Status Aktif',
            'tahun' => 'Tahun',
            'merek' => 'Merek',
            'norangka' => 'No Rangka',
            'nomesin' => 'No Mesin',
            'nama' => 'Nama',
            'nostnk' => 'No STNK',
            'alamatstnk' => 'Alamat STNK',
            'statusjenisplat' => 'Jenis Plat',
            'tglpajakstnk' => 'Tgl Pajak STNK',
            'tipe' => 'Tipe',
            'jenis' => 'Jenis',
            'isisilinder' => 'Isi Silinder',
            'warna' => 'Warna',
            'jenisbahanbakar' => 'Jenis Bahan Bakar',
            'jumlahsumbu' => 'Jumlah Sumbu',
            'jumlahroda' => 'Jumlah Roda',
            'model' => 'Model',
            'nobpkb' => 'No BPKB',
            'jumlahbanserap' => 'Jumlah Ban Serap',
            'statusgerobak' => 'Status Gerobak'
        ];
    }

    public function messages()
    {
        return[
            'tahun.min' => 'Min. 4 karakter',
            'tahun.max' => 'Max. 4 karakter',

            'norangka.min' => 'Min. 8 karakter',
            'norangka.max' => 'Max. 20 karakter',

            'nomesin.min' => 'Min. 8 karakter',
            'nomesin.max' => 'Max. 20 karakter',

            'kodetrado.min' => 'Min. 8 karakter',
            'kodetrado.max' => 'Max. 12 karakter',

            'nostnk.min' => 'Min. 8 karakter',
            'nostnk.max' => 'Max. 12 karakter',
            
            'nobpkb.min' => 'Min. 8 karakter',
            'nobpkb.max' => 'Max. 15 karakter',

            'jumlahbanserap.min' => 'Min. 1 karakter',
            'jumlahbanserap.digits_between' => 'Max. 2 karakter',

            'jumlahroda.min' => 'Min. 1 karakter',
            'jumlahroda.digits_between' => 'Max. 2 karakter',
            'isisilinder.min' => 'Min. 1 karakter',
            'isisilinder.digits_between' => 'Max. 5 karakter',
            'jumlahsumbu.min' => 'Min. 1 karakter',
            'jumlahsumbu.digits_between' => 'Max. 2 karakter',
            
            'photobpkb.*.image' => app(ErrorController::class)->geterror('WG')->keterangan,
            'photostnk.*.image' => app(ErrorController::class)->geterror('WG')->keterangan,
            'phototrado.*.image' => app(ErrorController::class)->geterror('WG')->keterangan
        ];
    }
}