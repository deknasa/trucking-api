<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\Supir;
use App\Rules\SupirResign;
use App\Rules\SupirBlackListKtp;
use App\Rules\SupirBlackListSim;
use App\Rules\ValidasiKtpPemutihan;
use App\Rules\ValidasiNoHPSupir;
use App\Rules\ValidasiSimSupir;

class StoreSupirRequest extends FormRequest
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

            $ruleGambar = Rule::requiredIf(function () {
                $noktp = request()->noktp;
                $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->whereRaw("grp like '%STATUS APPROVAL%'")
                    ->whereRaw("text like '%NON APPROVAL%'")
                    ->first();
                $nonAktif = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->select('id')
                    ->where("grp","STATUS AKTIF")
                    ->where("text","NON AKTIF")
                    ->first();
                $cekValidasi = DB::table('approvalsupirgambar')->from(DB::raw("approvalsupirgambar with (readuncommitted)"))
                    ->select('noktp', 'tglbatas', 'statusapproval')
                    ->whereRaw("noktp in ('$noktp')")
                    ->first();
                if ($nonAktif->id == request()->statusaktif) {
                    return false;
                }
                if ($cekValidasi != '') {
                    if ($cekValidasi->statusapproval == $nonApp->id) {
                        return true;
                    } else {
                        if (date('Y-m-d') < $cekValidasi->tglbatas) {
                            return false;
                        }
                    }
                }
                return true;
            });

            $parameter = new Parameter();
            $dataPostingTnl = $parameter->getcombodata('STATUS POSTING TNL', 'STATUS POSTING TNL');
            $dataPostingTnl = json_decode($dataPostingTnl, true);
            foreach ($dataPostingTnl as $item) {
                $statusPostingTnl[] = $item['id'];
            }
            $statuspostingtnl = $this->statuspostingtnl;
            $rulesStatusPostingTnl = [];
            if ($statuspostingtnl != null) {
                $rulesStatusPostingTnl = [
                    'statuspostingtnl' => ['required', Rule::in($statusPostingTnl)]
                ];
            } else if ($statuspostingtnl == null && $this->statuspostingtnlnama != '') {
                $rulesStatusPostingTnl = [
                    'statuspostingtnl' => ['required', Rule::in($statusPostingTnl)]
                ];
            }

            $parameterStatusAktif = new Parameter();
            $data = $parameterStatusAktif->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
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

            $ruleKeterangan = Rule::requiredIf(function () {
                $noktp = request()->noktp;
                $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->whereRaw("grp like '%STATUS APPROVAL%'")
                    ->whereRaw("text like '%NON APPROVAL%'")
                    ->first();
                $nonAktif = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->select('id')
                    ->where("grp","STATUS AKTIF")
                    ->where("text","NON AKTIF")
                    ->first();
                $cekValidasi = DB::table('approvalsupirketerangan')->from(DB::raw("approvalsupirketerangan with (readuncommitted)"))
                    ->select('noktp', 'tglbatas', 'statusapproval')
                    ->whereRaw("noktp in ('$noktp')")
                    ->first();
                if ($nonAktif->id == request()->statusaktif) {
                    return false;
                }
                if ($cekValidasi != '') {
                    if ($cekValidasi->statusapproval == $nonApp->id) {
                        return true;
                    } else {
                        if (date('Y-m-d') < $cekValidasi->tglbatas) {
                            return false;
                        }
                    }
                }
                return true;
            });

            // $ruleKeterangan = Rule::requiredIf(function () {

            $tglbatasakhir = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MINIMAL USIA SUPIR', 'MINIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));
            $tglbatasawal = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MAXIMAL USIA SUPIR', 'MAXIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));

            //validasi supir resign
            $noktp = request()->noktp;
            $dataSupir = (new Supir())->validationSupirResign($noktp);
            if ($dataSupir == true) {
                $cekSupir = true;
            } else {
                $cekSupir = false;
            }

            $rulePemutihan = Rule::requiredIf(function () {
                $noktp = request()->noktp;
                $pemutihan = DB::table("pemutihansupirheader")->from(DB::raw("pemutihansupirheader with (readuncommitted)"))
                    ->select(DB::raw("supir.noktp"))
                    ->join(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
                    ->where('supir.noktp', $noktp)
                    ->first();
                if ($pemutihan != '') {
                    return true;
                }
                return false;
            });
            $rules = [
                'namasupir' => ['required'],
                'alamat' => [$ruleKeterangan],
                'namaalias' => [$ruleKeterangan],
                'kota' => [$ruleKeterangan],
                'telp' => [$ruleKeterangan, new ValidasiNoHPSupir, 'min:8', 'max:50', 'nullable'],
                'statusaktifnama' => ['required'],
                'tglmasuk' => 'required',
                'tglexpsim' => [$ruleKeterangan],
                'nosim' => [$ruleKeterangan, new ValidasiSimSupir(), 'min:12', 'max:15', 'nullable'],
                'noktp' => ['required', new ValidasiKtpPemutihan(), 'min:16', 'max:16'],
                'nokk' => [$ruleKeterangan, 'min:16', 'max:16', 'nullable'],
                'tgllahir' => [
                    $ruleKeterangan, 'date_format:d-m-Y',
                    'after_or_equal:' . $tglbatasawal,
                    'before_or_equal:' . $tglbatasakhir, 'nullable'
                ],
                'statuspostingtnlnama' => ['required'],
                'tglterbitsim' => [$ruleKeterangan],
                'pemutihansupir_nobukti' => $rulePemutihan,
            ];

            $rulesGambar = [];
            if (request()->from == null) {
                $rulesGambar = [

                    'pdfsuratperjanjian' => [$ruleGambar, 'array'],
                    'pdfsuratperjanjian.*' => [$ruleGambar, 'mimes:pdf'],
                    'photovaksin' => ['array'],
                    'photovaksin.*' => ['image', 'min:100'],
                    'photodomisili' => ['array'],
                    'photodomisili.*' => ['image', 'min:100'],
                    'photoskck' => ['array'],
                    'photoskck.*' => ['image', 'min:100'],
                    'photokk' => [$ruleGambar, 'array'],
                    'photokk.*' => [$ruleGambar, 'image', 'min:100'],
                    'photosim' => [$ruleGambar, 'array'],
                    'photosim.*' => [$ruleGambar, 'image', 'min:100'],
                    'photoktp' => [$ruleGambar, 'array'],
                    'photoktp.*' => [$ruleGambar, 'image', 'min:100'],
                    'photosupir' => [$ruleGambar, 'array'],
                    'photosupir.*' => [$ruleGambar, 'image'],
                ];
            }
            if(request()->statusaktif == 2){
                $rulesGambar = [];
            }
            $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'SUPIR')->first();
            $getListTampilan = json_decode($getListTampilan->memo);
            if ($getListTampilan->INPUT != '') {
                $getListTampilan = (explode(",", $getListTampilan->INPUT));
                foreach ($getListTampilan as $value) {
                    if (array_key_exists(trim(strtolower($value)), $rules) == true) {
                        unset($rules[trim(strtolower($value))]);
                    }
                }
            }
            $rules = array_merge(
                $rules,
                $rulesGambar,
                $rulesStatusAktif,
                $rulesStatusPostingTnl
            );
        } else {
            $rules = [];
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'namasupir' => 'Nama Supir',
            'alamat' => 'Alamat',
            'kota' => 'Kota',
            'namaalias' => 'nama alias',
            'telp' => 'Telp',
            'statusaktifnama' => 'Status Aktif',
            'tglmasuk' => 'Tanggal Masuk',
            'tglexpsim' => 'Tanggal Exp SIM',
            'nosim' => 'No SIM',
            'noktp' => 'No KTP',
            'nokk' => 'No KK',
            'tgllahir' => 'Tanggal Lahir',
            'tglterbitsim' => 'Tanggal Terbit SIM',
            'pdfsuratperjanjian.*' => 'SURAT PERJANJIAN',
            'photovaksin' => 'foto vaksin',
            'photovaksin.*' => 'foto vaksin',
            'photosupir' => 'foto supir',
            'photosupir.*' => 'foto supir',
            'photoktp' => 'foto ktp',
            'photoktp.*' => 'foto ktp',
            'photosim' => 'foto sim',
            'photosim.*' => 'foto sim',
            'photokk' => 'foto kk',
            'photokk.*' => 'foto kk',
            'photoskck' => 'foto skck',
            'photoskck.*' => 'foto skck',
            'photodomisili' => 'foto domisili',
            'photodomisili.*' => 'foto domisili',
        ];
    }
    public function messages()
    {
        $controller = new ErrorController;
        $tglbatasakhir = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MINIMAL USIA SUPIR', 'MINIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));
        $tglbatasawal = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MAXIMAL USIA SUPIR', 'MAXIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));

        return [
            'telp.min' => 'Min. 8 karakter',
            'telp.max' => 'Max. 50 karakter',
            'noktp.max' => 'Max. 16 karakter',
            'noktp.min' => 'Min. 16 karakter',
            'nokk.max' => 'Max. 16 karakter',
            'nokk.min' => 'Min. 16 karakter',
            'nosim.max' => 'Max. 15 karakter',
            'nosim.min' => 'Min. 12 karakter',
            'tgllahir.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'tgllahir.before_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'pdfsuratperjanjian.*.mimes' => 'TYPE FILE :attribute  HARUS PDF'
        ];
    }
}
