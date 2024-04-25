<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\Tarif;
use App\Rules\UniqueTarifEdit;
use App\Rules\ValidasiKotaTarif;
use App\Rules\ValidasiPenyesuaianTarif;
use App\Rules\ValidasiTujuanTarif;
use App\Rules\ValidasiTujuanTarifDariUpahSupir;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTarifRequest extends FormRequest
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
        $tarif = new Tarif();
        $dataTarif = $tarif->findAll($this->id);
        $check = (new Tarif())->cekvalidasihapus(request()->id);

        $parameter = new Parameter();
        $dataAktif = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $dataAktif = json_decode($dataAktif, true);
        foreach ($dataAktif as $item) {
            $statusAktif[] = $item['id'];
        }
        $dataTon = $parameter->getcombodata('SISTEM TON', 'SISTEM TON');
        $dataTon = json_decode($dataTon, true);
        foreach ($dataTon as $item) {
            $statusTon[] = $item['id'];
        }
        $dataPenyesuaian = $parameter->getcombodata('PENYESUAIAN HARGA', 'PENYESUAIAN HARGA');
        $dataPenyesuaian = json_decode($dataPenyesuaian, true);
        foreach ($dataPenyesuaian as $item) {
            $statusPenyesuaian[] = $item['id'];
        }

        $kota_id = $this->kota_id;
        $rulesKota_id = [];
        if ($kota_id != null) {
            $rulesKota_id = [
                'kota_id' => ['required', 'numeric', 'min:1', ($check['kondisi']) ? Rule::in($dataTarif->kota_id) : '']
            ];
        } else if ($kota_id == null && $this->kota != '') {
            $rulesKota_id = [
                'kota_id' => ['required', 'numeric', 'min:1', ($check['kondisi']) ? Rule::in($dataTarif->kota_id) : ''],
            ];
        }

        $parent_id = $this->parent_id;
        $rulesParent_id = [];
        if ($parent_id != null) {
            if ($parent_id == 0) {
                $rulesParent_id = [
                    'parent_id' => ['required', 'numeric', 'min:1',  ($check['kondisi']) ? Rule::in($dataTarif->parent_id) : '']
                ];
            } else {
                if ($this->parent == '') {
                    $rulesParent_id = [
                        'parent' => ['required',  ($check['kondisi']) ? Rule::in($dataTarif->parent) : '']
                    ];
                }
            }
        } else if ($parent_id == null && $this->parent != '') {
            $rulesParent_id = [
                'parent_id' => ['required', 'numeric', 'min:1',  ($check['kondisi']) ? Rule::in($dataTarif->parent_id) : '']
            ];
        }

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => ['required', 'numeric', 'min:1',  ($check['kondisi']) ? Rule::in($dataTarif->zona_id) : '']
                ];
            } else {
                if ($this->zona == '') {
                    $rulesZona_id = [
                        'zona' => ['required',  ($check['kondisi']) ? Rule::in($dataTarif->zona) : '']
                    ];
                }
            }
        } else if ($zona_id == null && $this->zona != '') {
            $rulesZona_id = [
                'zona_id' => ['required', 'numeric', 'min:1',  ($check['kondisi']) ? Rule::in($dataTarif->zona_id) : '']
            ];
        }

        $rules = [
            'tujuan' =>  ['required', ($check['kondisi']) ? Rule::in($dataTarif->tujuan) : ''],
            'penyesuaian' => [new UniqueTarifEdit(), ($check['kondisi']) ? Rule::in($dataTarif->penyesuaian) : ''],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'statussistemton' => ['required', Rule::in($statusTon)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
            ],
            'kota' => ['required', ($check['kondisi']) ? Rule::in($dataTarif->kota) : ''],
        ];

        $relatedRequests = [
            UpdateTarifRincianRequest::class
        ];
        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesParent_id,
                $rulesKota_id,
                $rulesZona_id
            );
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'statussistemton' => 'Status Sistem Ton',
            'tglmulaiberlaku' => 'Tanggal Mulai Berlaku',
            'statuspenyesuaianharga' => 'Status Penyesuaian Harga',
            'nominal.*' => 'nominal'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        $tglbatasawal = (date('Y-m-d'));
        $tglbatasakhir = (date('Y') - 1) . '-01-01';
        return [
            'parent_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kota_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'zona_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'tgl.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'tgl.before' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
        ];
    }
}
