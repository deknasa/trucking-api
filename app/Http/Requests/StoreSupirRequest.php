<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;

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
        $ruleGambar = Rule::requiredIf(function () {
            $noktp = request()->noktp;
            $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp like '%STATUS APPROVAL%'")
                ->whereRaw("text like '%NON APPROVAL%'")
                ->first();
            $cekValidasi = DB::table('approvalsupirgambar')->from(DB::raw("approvalsupirgambar with (readuncommitted)"))
                ->select('noktp', 'tglbatas','statusapproval')
                ->whereRaw("noktp in ('$noktp')")
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

        $ruleKeterangan = Rule::requiredIf(function () {
            $noktp = request()->noktp;
            $nonApp = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp like '%STATUS APPROVAL%'")
                ->whereRaw("text like '%NON APPROVAL%'")
                ->first();
            $cekValidasi = DB::table('approvalsupirketerangan')->from(DB::raw("approvalsupirketerangan with (readuncommitted)"))
                ->select('noktp', 'tglbatas','statusapproval')
                ->whereRaw("noktp in ('$noktp')")
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
        
        // $ruleKeterangan = Rule::requiredIf(function () {
            
        $tglbatasakhir = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MINIMAL USIA SUPIR', 'MINIMAL USIA SUPIR')->text . ' years', strtotime( date('Y-m-d'))));
        $tglbatasawal = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MAXIMAL USIA SUPIR', 'MAXIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));

        return [
            'namasupir' => [$ruleKeterangan],
            'alamat' => [$ruleKeterangan],
            'namaalias' => [$ruleKeterangan],
            'kota' => [$ruleKeterangan],
            'telp' => [$ruleKeterangan,'unique:supir','min:8','max:50','nullable'],
            'statusaktif' => [$ruleKeterangan,'int','exists:parameter,id'],
            'tglmasuk' => [$ruleKeterangan],
            'tglexpsim' => [$ruleKeterangan],
            'nosim' => [$ruleKeterangan,'unique:supir','min:12','max:12','nullable'],
            'noktp' => ['required','unique:supir','min:16','max:16'],
            'nokk' => [$ruleKeterangan,'min:16','max:16','nullable'],
            'tgllahir' => [
                $ruleKeterangan, 'date_format:d-m-Y', 
                'after_or_equal:' . $tglbatasawal, 
                'before_or_equal:' . $tglbatasakhir,'nullable'
            ],
            'tglterbitsim' => [$ruleKeterangan],
            'photosupir' => [$ruleGambar,'array'],
            'photosupir.*' => [$ruleGambar,'image'],
            'photoktp' => [$ruleGambar,'array'],
            'photoktp.*' => [$ruleGambar,'image'],
            'photosim' => [$ruleGambar,'array'],
            'photosim.*' => [$ruleGambar,'image'],
            'photokk' => [$ruleGambar,'array'],
            'photokk.*' => [$ruleGambar,'image'],
            'photoskck' => [$ruleGambar,'array'],
            'photoskck.*' => [$ruleGambar,'image'],
            'photodomisili' => [$ruleGambar,'array'],
            'photodomisili.*' => [$ruleGambar,'image'],
            'photovaksin' => [$ruleGambar,'array'],
            'photovaksin.*' => [$ruleGambar,'image'],
            'pdfsuratperjanjian' => [$ruleGambar,'array'],
            'pdfsuratperjanjian.*' => [$ruleGambar,'mimes:pdf']
        ];
    }

    public function attributes()
    {
        return [
            'namasupir' => 'Nama Supir',
            'alamat' => 'Alamat',
            'kota' => 'Kota',
            'namaalias' => 'nama alias',
            'telp' => 'Telp',
            'statusaktif' => 'Status Aktif',
            'tglmasuk' => 'Tanggal Masuk',
            'tglexpsim' => 'Tanggal Exp SIM',
            'nosim' => 'No SIM',
            'noktp' => 'No KTP',
            'nokk' => 'No KK',
            'tgllahir' => 'Tanggal Lahir',
            'tglterbitsim' => 'Tanggal Terbit SIM',
        ];
    }
    public function messages() 
    {
        $controller = new ErrorController;
        $tglbatasakhir = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MINIMAL USIA SUPIR', 'MINIMAL USIA SUPIR')->text . ' years', strtotime( date('Y-m-d'))));
        $tglbatasawal = date('Y-m-d', strtotime('-' . (new ParameterController)->getparamid('MAXIMAL USIA SUPIR', 'MAXIMAL USIA SUPIR')->text . ' years', strtotime(date('Y-m-d'))));

        return [
            'telp.min' => 'Min. 8 karakter',
            'telp.max' => 'Max. 50 karakter',
            'noktp.max' => 'Max. 16 karakter',
            'noktp.min' => 'Min. 16 karakter',
            'nokk.max' => 'Max. 16 karakter',
            'nokk.min' => 'Min. 16 karakter',
            'nosim.max' => 'Max. 12 karakter',
            'nosim.min' => 'Min. 12 karakter',
            'nosim.unique' => ':attribute Sudah digunakan',
            'noktp.unique' => ':attribute Sudah digunakan',
            'tgllahir.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. date('d-m-Y', strtotime($tglbatasawal)). ' dan '. $controller->geterror('NTLB')->keterangan.' '. date('d-m-Y', strtotime($tglbatasakhir)),            
            'tgllahir.before_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. date('d-m-Y', strtotime($tglbatasawal)). ' dan '. $controller->geterror('NTLB')->keterangan.' '. date('d-m-Y', strtotime($tglbatasakhir)),  
        ];
    }
}
