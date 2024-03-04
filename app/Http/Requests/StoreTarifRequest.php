<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Rules\UniqueTarif;
use App\Rules\ExistKota;
use App\Rules\ExistTarif;
use App\Rules\ExistUpahSupir;
use App\Rules\ExistZona;
use App\Rules\ValidasiTujuanKota;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\UniqueTarifEdit;
use App\Rules\ValidasiTujuanTarifDariUpahSupir;
use Illuminate\Support\Facades\DB;

class StoreTarifRequest extends FormRequest
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
        if (request()->from == '') {
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
            $dataPostingTnl = $parameter->getcombodata('STATUS POSTING TNL', 'STATUS POSTING TNL');
            $dataPostingTnl = json_decode($dataPostingTnl, true);
            foreach ($dataPostingTnl as $item) {
                $statusPostingTnl[] = $item['id'];
            }

            $tglbatasawal = (date('Y-m-d', strtotime('-7 days')));
            $tglbatasakhir = (date('Y-m-d', strtotime('+7 days')));
            $kota_id = $this->kota_id;
            $rulesKota_id = [];
            if ($kota_id != null) {
                $rulesKota_id = [
                    'kota_id' => ['required', 'numeric', 'min:1', new ExistKota()],
                ];
            } else if ($kota_id == null && $this->kota != '') {
                $rulesKota_id = [
                    'kota_id' => ['required', 'numeric', 'min:1', new ExistKota()],
                ];
            }

            $parent_id = $this->parent_id;
            $rulesParent_id = [];
            if ($parent_id != null) {
                if ($parent_id == 0) {
                    $rulesParent_id = [
                        'parent_id' => ['required', 'numeric', 'min:1', new ExistTarif()]
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
                    'parent_id' => ['required', 'numeric', 'min:1', new ExistTarif()]
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

            $rules = [
                'tujuan' =>  ['required', new ValidasiTujuanKota()],
                'penyesuaian' => [new UniqueTarif()],
                'statusaktif' => ['required', Rule::in($statusAktif)],
                'statussistemton' => ['required', Rule::in($statusTon)],
                'tglmulaiberlaku' => [
                    'required', 'date_format:d-m-Y',
                    'after:' . $tglbatasawal,
                    'before:' . $tglbatasakhir,
                ],
                'kota' => 'required',
                'statuspostingtnl' => ['required', Rule::in($statusPostingTnl)],
            ];
            $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'TARIF')->first();
            $getListTampilan = json_decode($getListTampilan->memo);
            if ($getListTampilan->INPUT != '') {
                $getListTampilan = (explode(",", $getListTampilan->INPUT));
                foreach ($getListTampilan as $value) {
                    if (array_key_exists(trim(strtolower($value)), $rules) == true) {
                        unset($rules[trim(strtolower($value))]);
                    }
                }
            }
            $relatedRequests = [
                StoreTarifRincianRequest::class
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
        } else {
            $rules = [];
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'statussistemton' => 'Status Sistem Ton',
            'tglmulaiberlaku' => 'Tanggal Mulai Berlaku',
            'statuspenyesuaianharga' => 'Status Penyesuaian Harga'
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;
        $tglbatasawal = (date('Y-m-d'));
        $tglbatasakhir = (date('Y') - 1) . '-01-01';
        return [
            'parent_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'upahsupir_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'kota_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'zona_id.required' => ':attribute ' . $controller->geterror('HPDL')->keterangan,
            'tgl.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'tgl.before' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),

        ];
    }
}
