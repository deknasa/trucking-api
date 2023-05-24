<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                    return true;
                } else {
                    if (date('Y-m-d') > $cekValidasi->tglbatas) {
                        return true;
                    }
                }
            }
        });
        return [
            'namasupir' => 'required',
            'alamat' => 'required',
            'namaalias' => 'required',
            'kota' => 'required',
            'telp' => 'required',
            'statusaktif' => 'required|int|exists:parameter,id',
            'tglmasuk' => 'required',
            'tglexpsim' => 'required',
            'nosim' => 'required|unique:supir|min:12|max:12',
            'noktp' => 'required|unique:supir|min:16|max:16',
            'nokk' => 'required|min:16|max:16',
            'tgllahir' => 'required',
            'tglterbitsim' => 'required',
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
        return [
            'noktp.max' => 'Max. 16 karakter',
            'noktp.min' => 'Min. 16 karakter',
            'nokk.max' => 'Max. 16 karakter',
            'nokk.min' => 'Min. 16 karakter',
            'nosim.max' => 'Max. 12 karakter',
            'nosim.min' => 'Min. 12 karakter',
            'nosim.unique' => ':attribute Sudah digunakan',
            'noktp.unique' => ':attribute Sudah digunakan',
        ];
    }
}
