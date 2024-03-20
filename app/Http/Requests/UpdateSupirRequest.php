<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Supir;
use App\Rules\SupirBlackListKtp;
use App\Rules\SupirBlackListSim;
use App\Rules\SupirResign;

class UpdateSupirRequest extends FormRequest
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
            $noktp = request()->noktp;
            $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp like '%STATUS APPROVAL%'")
                ->whereRaw("text like '%NON APPROVAL%'")
                ->first();
            $cekValidasi = DB::table('approvalsupirgambar')->from(DB::raw("approvalsupirgambar with (readuncommitted)"))
                ->select('noktp', 'tglbatas', 'statusapproval')
                ->whereRaw("noktp in ('$noktp')")
                ->first();
            $nonAktif = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('id')
                ->where("grp","STATUS AKTIF")
                ->where("text","NON AKTIF")
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

        $ruleKeterangan = Rule::requiredIf(function () {
            $noktp = request()->noktp;
            $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp like '%STATUS APPROVAL%'")
                ->whereRaw("text like '%NON APPROVAL%'")
                ->first();
            $cekValidasi = DB::table('approvalsupirketerangan')->from(DB::raw("approvalsupirketerangan with (readuncommitted)"))
                ->select('noktp', 'tglbatas', 'statusapproval')
                ->whereRaw("noktp in ('$noktp')")
                ->first();
            $nonAktif = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->select('id')
                ->where("grp","STATUS AKTIF")
                ->where("text","NON AKTIF")
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

        $tglbatasakhir = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MINIMAL USIA SUPIR', 'MINIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));
        $tglbatasawal = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MAXIMAL USIA SUPIR', 'MAXIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));

        //validasi supir resign
        $noktp = request()->noktp;
        $id = request()->id;
        $dataSupir = (new Supir())->validationSupirResign($noktp, $id);
        if ($dataSupir == true) {
            $cekSupir = true;
        } else {
            $cekSupir = false;
        }

        $rulePemutihan = Rule::requiredIf(function () {
            if (request()->statusaktif != 2) {
                $noktp = request()->noktp;
                $pemutihan = DB::table("pemutihansupirheader")->from(DB::raw("pemutihansupirheader with (readuncommitted)"))
                    ->select(DB::raw("supir.noktp"))
                    ->join(DB::raw("supir with (readuncommitted)"), 'pemutihansupirheader.supir_id', 'supir.id')
                    ->where('supir.noktp', $noktp)
                    ->first();
                if ($pemutihan != '') {
                    return true;
                }
            }
            return false;
        });

        $rules = [
            'namasupir' => [$ruleKeterangan],
            'alamat' => [$ruleKeterangan],
            'namaalias' => $ruleKeterangan,
            'kota' => [$ruleKeterangan],
            'telp' => [$ruleKeterangan, 'min:8', 'max:50', new SupirResign($cekSupir),'nullable'],
            'statusaktif' => [$ruleKeterangan, 'int', 'exists:parameter,id'],
            'tglmasuk' => [$ruleKeterangan],
            'tglexpsim' => [$ruleKeterangan],
            'nosim' => [$ruleKeterangan, 'min:12', 'max:15', new SupirResign($cekSupir), new SupirBlackListSim(),'nullable'], //.',nosim',
            'noktp' => ['required', 'min:16', 'max:16', new SupirResign($cekSupir), new SupirBlackListKtp()], //.',noktp',
            'nokk' => [$ruleKeterangan, 'min:16', 'max:16', 'nullable'],
            'tgllahir' => [
                $ruleKeterangan, 'date_format:d-m-Y',
                'after_or_equal:' . $tglbatasawal,
                'before_or_equal:' . $tglbatasakhir, 'nullable'
            ],
            'pemutihansupir' => $rulePemutihan,
            'tglterbitsim' => [$ruleKeterangan],


        ];
        $rulesGambar = [
            'photosupir' => [$ruleGambar, 'array'],
            'photosupir.*' => [$ruleGambar, 'image'],
            'photoktp' => [$ruleGambar, 'array'],
            'photoktp.*' => [$ruleGambar, 'image','min:100'],
            'photosim' => [$ruleGambar, 'array'],
            'photosim.*' => [$ruleGambar, 'image','min:100'],
            'photokk' => [$ruleGambar, 'array'],
            'photokk.*' => [$ruleGambar, 'image','min:100'],
            'photoskck' => [$ruleGambar, 'array'],
            'photoskck.*' => [$ruleGambar, 'image','min:100'],
            'photodomisili' => [$ruleGambar, 'array'],
            'photodomisili.*' => [$ruleGambar, 'image','min:100'],
            'photovaksin' => [$ruleGambar, 'array'],
            'photovaksin.*' => [$ruleGambar, 'image','min:100'],
            'pdfsuratperjanjian' => [$ruleGambar, 'array'],
            'pdfsuratperjanjian.*' => [$ruleGambar, 'mimes:pdf']
        ];
        if (request()->statusaktif == 2) {
            $rulesGambar = [];
        }
        $rules = array_merge(
            $rules,
            $rulesGambar
        );
        return  $rules;
    }

    public function attributes()
    {
        return [
            'namasupir' => 'Nama Supir',
            'namaalias' => 'nama alias',
            'alamat' => 'Alamat',
            'kota' => 'Kota',
            'telp' => 'Telp',
            'statusaktif' => 'Status Aktif',
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
            'nosim.unique' => ':attribute Sudah digunakan',
            'noktp.unique' => ':attribute Sudah digunakan',
            'pemutihansupir.required' => $controller->geterror('SPM')->keterangan,
            'tgllahir.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'tgllahir.before_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasawal)) . ' dan ' . $controller->geterror('NTLB')->keterangan . ' ' . date('d-m-Y', strtotime($tglbatasakhir)),
            'pdfsuratperjanjian.*.mimes' => 'TYPE FILE :attribute  HARUS PDF'
        ];
    }
}
