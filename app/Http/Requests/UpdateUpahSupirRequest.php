<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\UpahSupir;
use App\Rules\ExistKota;
use App\Rules\ExistTarif;
use App\Rules\ExistUpahSupir;
use App\Rules\ExistZona;
use App\Rules\SimpanKandangUpahSupir;
use App\Rules\UniqueUpahSupirKotaSampaiEdit;
use App\Rules\UniqueUpahSupirSampaiEdit;
use App\Rules\ValidasiKotaUpahZona;
use App\Rules\ValidasiPenyesuaianUpahSupir;
use App\Rules\ValidasiZonaUpahZona;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateUpahSupirRequest extends FormRequest
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
        $upahSupir = new UpahSupir();
        $dataUpahSupir = $upahSupir->findAll(request()->id);
        $check = (new UpahSupir())->cekValidasi(request()->id);

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
                    'parent_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new ExistUpahSupir(), ($check['kondisi']) ? Rule::in($dataUpahSupir->parent_id) : '']
                ];
            } else {
                if ($this->parent == '') {
                    $rulesParent_id = [
                        'parent' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), ($check['kondisi']) ? Rule::in($dataUpahSupir->parent) : '']
                    ];
                }
            }
        } else if ($parent_id == null && $this->parent != '') {
            $rulesParent_id = [
                'parent_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new ExistUpahSupir(), ($check['kondisi']) ? Rule::in($dataUpahSupir->parent_id) : '']
            ];
        }

        $tarif_id = $this->tarif_id;
        $rulesTarif_id = [];
        if ($tarif_id != null) {
            $rulesTarif_id = [
                'tarif_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new ExistTarif(), ($check['kondisi']) ? Rule::in($dataUpahSupir->tarif_id) : '']
            ];
        } else if ($tarif_id == null && $this->tarif != '') {
            $rulesTarif_id = [
                'tarif_id' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new ExistTarif(), ($check['kondisi']) ? Rule::in($dataUpahSupir->tarif_id) : '']
            ];
        }

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => ['required', 'numeric', 'min:1', new ExistZona(), ($check['kondisi']) ? Rule::in($dataUpahSupir->zona_id) : '']
                ];
            } else {
                if ($this->zona == '') {
                    $rulesZona_id = [
                        'zona' => ['required', ($check['kondisi']) ? Rule::in($dataUpahSupir->zona) : '']
                    ];
                }
            }
        } else if ($zona_id == null && $this->zona != '') {
            $rulesZona_id = [
                'zona_id' => ['required', 'numeric', 'min:1', new ExistZona(), ($check['kondisi']) ? Rule::in($dataUpahSupir->zona_id) : '']
            ];
        }

        $kotadari_id = $this->kotadari_id;
        $rulesKotaDari_id = [];
        if ($kotadari_id != null) {
            if ($kotadari_id == 0) {
                $rulesKotaDari_id = [
                    'kotadari_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotadari_id) : '']
                ];
            }
        } else if ($kotadari_id == null && $this->kotadari != '') {
            $rulesKotaDari_id = [
                'kotadari_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotadari_id) : '']
            ];
        }

        $kotasampai_id = $this->kotasampai_id;
        $rulesKotaSampai_id = [];
        if ($kotasampai_id != null) {
            if ($kotasampai_id == 0) {
                $rulesKotaSampai_id = [
                    'kotasampai_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new UniqueUpahSupirSampaiEdit(), new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotasampai_id) : '']
                ];
            }
        } else if ($kotasampai_id == null && $this->kotasampai != '') {
            $rulesKotaSampai_id = [
                'kotasampai_id' => ['required_if:statusupahzona,=' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), 'numeric', 'min:1', new UniqueUpahSupirSampaiEdit(), new ExistKota(), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotasampai_id) : '']
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
            'kotadari' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotadari) : ''],
            'kotasampai' => ['required_if:statusupahzona,=,' . $getBukanUpahZona->id, new ValidasiKotaUpahZona($getBukanUpahZona->id), ($check['kondisi']) ? Rule::in($dataUpahSupir->kotasampai) : '', new UniqueUpahSupirKotaSampaiEdit()],
            'tarif' => [new ValidasiKotaUpahZona($getBukanUpahZona->id), ($check['kondisi']) ? Rule::in($dataUpahSupir->tarif) : ''],
            'penyesuaian' => [new UniqueUpahSupirSampaiEdit(), new ValidasiPenyesuaianUpahSupir(), new ValidasiKotaUpahZona($getBukanUpahZona->id), ($check['kondisi']) ? Rule::in(trim($dataUpahSupir->penyesuaian)) : ''],
            'jarak' => ['required', 'numeric', 'gt:0', 'max:' . (new ParameterController)->getparamid('BATAS KM UPAH SUPIR', 'BATAS KM UPAH SUPIR')->text],
            'jarakfullempty' => ['required', 'numeric', 'gt:0', 'max:' . (new ParameterController)->getparamid('BATAS KM UPAH SUPIR', 'BATAS KM UPAH SUPIR')->text],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'statussimpankandang' => [new SimpanKandangUpahSupir()],
            'statusupahzona' => ['required', Rule::in($statusUpahZona)],
            'zonadari' => ['required_if:statusupahzona,=,' . $getUpahZona->id, new ValidasiZonaUpahZona($getUpahZona->id)],
            'zonasampai' => ['required_if:statusupahzona,=,' . $getUpahZona->id, new ValidasiZonaUpahZona($getUpahZona->id)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
            'gambar.*' => ['image', 'min:50']
        ];
        $relatedRequests = [
            UpdateUpahSupirRincianRequest::class
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
                $rulesZonaSampai_id
            );
        }

        if ((request()->tarifmuatan_id != 0 || request()->tarifmuatan_id != '') && (request()->tarifbongkaran_id != 0 || request()->tarifbongkaran_id != '')) {
            unset($rules['tarif']);
        }
        $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'UPAHSUPIR')->first();
        $getListTampilan = json_decode($getListTampilan->memo);
        if ($getListTampilan->INPUT != '') {
            $getListTampilan = (explode(",", $getListTampilan->INPUT));
            foreach ($getListTampilan as $value) {
                if (array_key_exists(strtolower($value), $rules) == true) {
                    unset($rules[strtolower($value)]);
                }
            }
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
            'nominalsupir.*' => 'nominal supir',
            'gambar.*' => 'gambar'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        return [
            'jarak.max' => ':attribute ' . 'maximal jarak ' . (new ParameterController)->getparamid('BATAS KM UPAH SUPIR', 'BATAS KM UPAH SUPIR')->text,
            'jarak.gt' => ':attribute ' . (new ErrorController)->geterror('GT-ANGKA-0')->keterangan,
            'nominalsupir.*.gt' => ':attribute ' . (new ErrorController)->geterror('WI')->keterangan,
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
