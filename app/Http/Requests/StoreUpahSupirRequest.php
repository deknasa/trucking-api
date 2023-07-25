<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\ExistKota;
use App\Rules\ExistTarif;
use App\Rules\ExistUpahSupir;
use App\Rules\ExistZona;
use App\Rules\UniqueUpahSupir;
use App\Rules\UniqueUpahSupirDari;
use App\Rules\UniqueUpahSupirSampai;
use App\Rules\ValidasiDariSimpanKandangUpahSupir;
use App\Rules\ValidasiKotaUpahZona;
use App\Rules\ValidasiZonaUpahZona;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreUpahSupirRequest extends FormRequest
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
        $parameter = new Parameter();
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }
        $dataLuarKota = $parameter->getcombodata('UPAH SUPIR LUAR KOTA', 'UPAH SUPIR LUAR KOTA');
        $dataLuarKota = json_decode($dataLuarKota, true);
        foreach ($dataLuarKota as $item) {
            $statusLuarKota[] = $item['id'];
        }
        $dataSimpanKandang = $parameter->getcombodata('STATUS SIMPAN KANDANG', 'STATUS SIMPAN KANDANG');
        $dataSimpanKandang = json_decode($dataSimpanKandang, true);
        foreach ($dataSimpanKandang as $item) {
            $statusSimpanKandang[] = $item['id'];
        }

        $dataUpahZona = $parameter->getcombodata('STATUS UPAH ZONA', 'STATUS UPAH ZONA');
        $dataUpahZona = json_decode($dataUpahZona, true);
        foreach ($dataUpahZona as $item) {
            $statusUpahZona[] = $item['id'];
        }

        $getBukanUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'NON UPAH ZONA')->first();
        $getUpahZona = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS UPAH ZONA')->where('text', 'UPAH ZONA')->first();

        $parent_id = $this->parent_id;
        $rulesParent_id = [];
        if ($parent_id != null) {
            if ($parent_id == 0) {
                $rulesParent_id = [
                    'parent_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, 'numeric', 'min:1', new ExistUpahSupir(), new ValidasiKotaUpahZona($getBukanUpahZona->id)]
                ];
            } else {
                if ($this->parent == '') {
                    $rulesParent_id = [
                        'parent' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id]
                    ];
                }
            }
        } else if ($parent_id == null && $this->parent != '') {
            $rulesParent_id = [
                'parent_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, 'numeric', 'min:1', new ExistUpahSupir(), new ValidasiKotaUpahZona($getBukanUpahZona->id)]
            ];
        }

        $tarif_id = $this->tarif_id;
        $rulesTarif_id = [];
        if ($tarif_id != null) {
            if ($tarif_id == 0) {
                $rulesTarif_id = [
                    'tarif_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, 'numeric', 'min:1', new ExistTarif()]
                ];
            } else {
                if ($this->tarif == '') {
                    $rulesTarif_id = [
                        'tarif' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id]
                    ];
                }
            }
        } else if ($tarif_id == null && $this->tarif != '') {
            $rulesTarif_id = [
                'tarif_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, 'numeric', 'min:1', new ExistTarif()]
            ];
        }

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => ['required', 'numeric', 'min:1', new ExistZona()]
                ];
            } else {
                if ($this->zona == '') {
                    $rulesZona_id = [
                        'zona' => ['required']
                    ];
                }
            }
        } else if ($zona_id == null && $this->zona != '') {
            $rulesZona_id = [
                'zona_id' => ['required', 'numeric', 'min:1', new ExistZona()]
            ];
        }

        $kotadari_id = $this->kotadari_id;
        $rulesKotaDari_id = [];
        if ($kotadari_id != null) {
            if ($kotadari_id == 0) {
                $rulesKotaDari_id = [
                    'kotadari_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, 'numeric', 'min:1', new ExistKota(), new ValidasiKotaUpahZona($getBukanUpahZona->id)]
                ];
            }
        } else if ($kotadari_id == null && $this->kotadari != '') {
            $rulesKotaDari_id = [
                'kotadari_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, 'numeric', 'min:1', new ExistKota(), new ValidasiKotaUpahZona($getBukanUpahZona->id)]
            ];
        }

        $kotasampai_id = $this->kotasampai_id;
        $rulesKotaSampai_id = [];
        if ($kotasampai_id != null) {
            if ($kotasampai_id == 0) {
                $rulesKotaSampai_id = [
                    'kotasampai_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, 'numeric', 'min:1', new UniqueUpahSupirSampai(), new ExistKota(), new ValidasiKotaUpahZona($getBukanUpahZona->id)]
                ];
            }
        } else if ($kotasampai_id == null && $this->kotasampai != '') {
            $rulesKotaSampai_id = [
                'kotasampai_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, 'numeric', 'min:1', new UniqueUpahSupirSampai(), new ExistKota(), new ValidasiKotaUpahZona($getBukanUpahZona->id)]
            ];
        }

        $zonadari_id = $this->zonadari_id;
        $rulesZonaDari_id = [];
        if ($zonadari_id != null) {
            if ($zonadari_id == 0) {
                $rulesZonaDari_id = [
                    'zonadari_id' => ['required_if:statusupahzona,=' . $getUpahZona->id, 'numeric', 'min:1', new ExistZona(), new ValidasiZonaUpahZona($getUpahZona->id)]
                ];
            }
        } else if ($zonadari_id == null && $this->zonadari != '') {
            $rulesZonaDari_id = [
                'zonadari_id' => ['required_if:statusupahzona,=' . $getUpahZona->id, 'numeric', 'min:1', new ExistZona(), new ValidasiZonaUpahZona($getUpahZona->id)]
            ];
        }

        $zonasampai_id = $this->zonasampai_id;
        $rulesZonaSampai_id = [];
        if ($zonasampai_id != null) {
            if ($zonasampai_id == 0) {
                $rulesZonaSampai_id = [
                    'zonasampai_id' => ['required_if:statusupahzona,=' . $getUpahZona->id, 'numeric', 'min:1', new ExistZona(), new ValidasiZonaUpahZona($getUpahZona->id)]
                ];
            }
        } else if ($zonasampai_id == null && $this->zonasampai != '') {
            $rulesZonaSampai_id = [
                'zonasampai_id' => ['required_if:statusupahzona,=' . $getUpahZona->id, 'numeric', 'min:1', new ExistZona(), new ValidasiZonaUpahZona($getUpahZona->id)]
            ];
        }

        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglBatasAkhir = (date('Y') + 1) . '-01-01';
        $rules =  [
            'kotadari' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiDariSimpanKandangUpahSupir(), new ValidasiKotaUpahZona($getBukanUpahZona->id)],
            'kotasampai' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id)],
            'zonadari' => ['required_if:statusupahzona,=,' . $getUpahZona->id, new ValidasiZonaUpahZona($getUpahZona->id)],
            'zonasampai' => ['required_if:statusupahzona,=,' . $getUpahZona->id, new ValidasiZonaUpahZona($getUpahZona->id)],
            'tarif' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id)],
            'penyesuaian' => [new UniqueUpahSupirSampai(), new ValidasiKotaUpahZona($getBukanUpahZona->id)],
            'jarak' => ['required', 'numeric', 'gt:0', 'max:' . (new ParameterController)->getparamid('BATAS KM UPAH SUPIR', 'BATAS KM UPAH SUPIR')->text],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'statusupahzona' => ['required', Rule::in($statusUpahZona)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
                'before:' . $tglBatasAkhir,
                'after_or_equal:' . $tglbatasawal
            ],
            'gambar.*' => 'image'
        ];

        if (request()->statusupahzona == $getUpahZona->id) {

            $tidakSimpanKandang = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS SIMPAN KANDANG')->where('text', 'TIDAK SIMPAN KANDANG')->first();
            $rulesStatusSimpanKandang = [
                'statussimpankandang' => ['required', Rule::in($tidakSimpanKandang->id)],
            ];
        } else {
            $rulesStatusSimpanKandang = [
                'statussimpankandang' => ['required', Rule::in($statusSimpanKandang)],
            ];
        }

        $relatedRequests = [
            StoreUpahSupirRincianRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesZona_id,
                $rulesParent_id,
                $rulesTarif_id,
                $rulesKotaDari_id,
                $rulesKotaSampai_id,
                $rulesZonaDari_id,
                $rulesZonaSampai_id,
                $rulesStatusSimpanKandang
            );
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'kotadari' => 'kota dari',
            'kotasampai' => 'kota sampai',
            'statusaktif' => 'status aktif',
            'statusluarkota' => 'status luar kota',
            'tglmulaiberlaku' => 'tanggal mulai berlaku',
            'tglakhirberlaku' => 'tanggal akhir berlaku',
            'container.*' => 'container',
            'statuscontainer.*' => 'container',
            'nominalsupir.*' => 'nominal supir'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'jarak.max' => ':attribute ' . 'maximal jarak ' . (new ParameterController)->getparamid('BATAS KM UPAH SUPIR', 'BATAS KM UPAH SUPIR')->text,
            'jarak.gt' => ':attribute ' . (new ErrorController)->geterror('GT-ANGKA-0')->keterangan,
            'kotadari_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kotasampai_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'parent_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'tarif_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'zona_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kotadari.required_if' => ':attribute ' . $controller->geterror('WI')->keterangan,
            'kotasampai.required_if' => ':attribute ' . $controller->geterror('WI')->keterangan,
            'zonadari.required_if' => ':attribute ' . $controller->geterror('WI')->keterangan,
            'zonasampai.required_if' => ':attribute ' . $controller->geterror('WI')->keterangan,
            'tarif.required_if' => ':attribute ' . $controller->geterror('WI')->keterangan,
        ];
    }
}
