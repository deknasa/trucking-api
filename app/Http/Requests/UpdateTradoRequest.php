<?php

namespace App\Http\Requests;

use App\Rules\NotDecimal;
use App\Rules\ValidasiGambarTrado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                }
            }
            return true;

        });

        return [
            'kodetrado' => 'required',
            'statusaktif' => 'required',
            'tahun' => 'required',
            'merek' => 'required',
            'norangka' => 'required',
            'nomesin' => 'required',
            'nama' => 'required',
            'nostnk' => 'required',
            'alamatstnk' => 'required',
            'statusjenisplat' => 'required',
            'tglpajakstnk' => 'required',
            'tipe' => 'required',
            'jenis' => 'required',
            'isisilinder' => 'required',
            'warna' => 'required',
            'jenisbahanbakar' => 'required',
            'jumlahsumbu' => 'required',
            'jumlahroda' => 'required',
            'model' => 'required',
            'nobpkb' => 'required',
            'jumlahbanserap' => 'required',
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
}
