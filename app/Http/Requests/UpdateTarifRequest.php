<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\UniqueTarifEdit;
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
        $tglbatasawal = (date('Y-m-d'));
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $kota_id = $this->kota_id;
        $rulesKota_id = [];
        if ($kota_id != null) {
            if ($kota_id == 0) {
                $rulesKota_id = [
                    'kota_id' => 'required|numeric|min:1'
                ];
            } else {
                if ($this->kota == '') {
                    $rulesKota_id = [
                        'kota' => 'required',
                    ];
                }
            }
        } else if ($kota_id == null && $this->kota != '') {
            $rulesKota_id = [
                'kota_id' => ['required', 'numeric', 'min:1'],
            ];
        }

        $parent_id = $this->parent_id;
        $rulesParent_id = [];
        if ($parent_id != null) {
            if ($parent_id == 0) {
                $rulesParent_id = [
                    'parent_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->parent == '') {
                    $rulesParent_id = [
                        'parent' => ['required']
                    ];
                }
            }
        } else if ($parent_id == null && $this->parent != '') {
            $rulesParent_id = [
                'parent_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $upahsupir_id = $this->upahsupir_id;
        $rulesUpahSupir_id = [];
        if ($upahsupir_id != null) {
            if ($upahsupir_id == 0) {
                $rulesUpahSupir_id = [
                    'upahsupir_id' => ['required', 'numeric', 'min:1']
                ];
            } else {
                if ($this->upahsupir == '') {
                    $rulesUpahSupir_id = [
                        'upahsupir' => ['required']
                    ];
                }
            }
        } else if ($upahsupir_id == null && $this->upahsupir != '') {
            $rulesUpahSupir_id = [
                'upahsupir_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $zona_id = $this->zona_id;
        $rulesZona_id = [];
        if ($zona_id != null) {
            if ($zona_id == 0) {
                $rulesZona_id = [
                    'zona_id' => ['required', 'numeric', 'min:1']
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
                'zona_id' => ['required', 'numeric', 'min:1']
            ];
        }

        $rules = [
            'tujuan' =>  ['required', new UniqueTarifEdit()],
            'statusaktif' => ['required', Rule::in($statusAktif)],
            'statussistemton' => ['required', Rule::in($statusTon)],
            'tglmulaiberlaku' => [
                'required', 'date_format:d-m-Y',
                'after_or_equal:' . $tglbatasawal,
                'before:' . $tglbatasakhir,
            ],
            'statuspenyesuaianharga' => ['required', Rule::in($statusPenyesuaian)],
        ];

        $relatedRequests = [
            UpdateTarifRincianRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesParent_id,
                $rulesUpahSupir_id,
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
            'tgl.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'tgl.before' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
        ];
    }
}
