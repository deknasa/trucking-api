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

class StoreTradoRequest extends FormRequest
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

        $ruleKeterangan = Rule::requiredIf(function () {
            $kodetrado = request()->kodetrado;
            $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp like '%STATUS APPROVAL%'")
                ->whereRaw("text like '%NON APPROVAL%'")
                ->first();
            $cekValidasi = DB::table('approvaltradoketerangan')->from(DB::raw("approvaltradoketerangan with (readuncommitted)"))
                ->select('kodetrado', 'tglbatas','statusapproval')
                ->whereRaw("kodetrado in ('$kodetrado')")
                ->first();
            if ($cekValidasi != '') {
                if ($cekValidasi->statusapproval == $nonApp->id) {
                    return false;
                } else {
                    if (date('Y-m-d') < $cekValidasi->tglbatas) {
                        return false;
                    }
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
            'kodetrado' => ['required','unique:trado'],
            'statusaktif' => [$ruleKeterangan, Rule::in($status)],
            'tahun' => [$ruleKeterangan,'min:4','max:4','nullable'],
            'merek' => $ruleKeterangan,
            'norangka' => [$ruleKeterangan, 'max:20', 'unique:trado'],
            'nomesin' =>  [$ruleKeterangan,'max:20', 'unique:trado'],
            'nama' => [$ruleKeterangan],
            'nostnk' =>  [$ruleKeterangan, 'max:50', 'unique:trado'],
            'alamatstnk' => [$ruleKeterangan],
            'statusjenisplat' => [$ruleKeterangan],
            'tglpajakstnk' => [$ruleKeterangan],
            'tipe' => [$ruleKeterangan],
            'jenis' => [$ruleKeterangan],
            'isisilinder' => [$ruleKeterangan,'numeric','min:1','digits_between:1,5','nullable'],
            'warna' => [$ruleKeterangan],
            'jenisbahanbakar' => [$ruleKeterangan],
            'jumlahsumbu' => [$ruleKeterangan,'numeric','min:1','digits_between:1,2','nullable'],
            'jumlahroda' => [$ruleKeterangan,'numeric','min:1','digits_between:1,2','nullable'],
            'model' => [$ruleKeterangan],
            'nobpkb' => [$ruleKeterangan, 'max:15', 'unique:trado'],
            'jumlahbanserap' => [$ruleKeterangan,'numeric','min:1','digits_between:1,2','nullable'],
            'statusgerobak' => [$ruleKeterangan],
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
            'kodetrado' => 'Kode trado',
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
            'jumlahroda' => 'Jumlah BAN',
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
