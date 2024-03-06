<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GetKartuStokLamaRequest extends FormRequest
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
        $stokQuery = DB::table('stok')->select('stok.id')->get();
        $stokIds = [];
        foreach ($stokQuery as $stok) {
            $stokIds[] = $stok->id;
        }
        $stokIdRule = Rule::in($stokIds);
        
        $stokQuery2 = DB::table('stok')->select('stok.namastok')->get();
        $stoks = [];
        foreach ($stokQuery2 as $stok2) {
            $stoks[] = $stok2->namastok;
        }
        $stokNamaRule = Rule::in($stoks);
        
        $gudangQuery = DB::table('gudang')->select('gudang.id')->get();
        $gudangIds = [];
        foreach ($gudangQuery as $gudang) {
            $gudangIds[] = $gudang->id;
        }
        $gudangRuleId = Rule::in($gudangIds);
        
        $gudangQuery2 = DB::table('gudang')->select('gudang.gudang')->get();
        $gudangs = [];
        foreach ($gudangQuery2 as $gudang) {
            $gudangs[] = $gudang->gudang;
        }
        $gudangRule = Rule::in($gudangs);
        
        $parameter = new Parameter();

        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        $dataFilter = $parameter->getcombodata('STOK PERSEDIAAN', 'STOK PERSEDIAAN');
        $dataFilter = json_decode($dataFilter, true);
        $status = [];
        foreach ($dataFilter as $item) {
            $status[] = $item['id'];
        }
        $status[] = 0;
        
        $rules =  [
            'dari' => [
                'required',
                'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.$tglbatasawal,
            ],
            'sampai' => [
                'required',
                'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
            ],
            // 'stokdari_id' => [
            //     'required',
            //     $stokIdRule,
            //     'numeric',
            //     'min:1'
            // ],
            // 'stoksampai_id' => [
            //     'required',
            //     $stokIdRule,
            //     'numeric',
            //     'min:1'
            // ],
            // 'stokdari' => [
            //     'required',
            //     $stokNamaRule
            // ],
            // 'stoksampai' => [
            //     'required',
            //     $stokNamaRule
            // ],
            'filter' => [
                'required',
                Rule::in($status),
                'numeric',
                // 'min:1'
            ],
            'gudang' => [
                'required',
                $gudangRule
            ],
            'gudang_id' => [
                'required',
                $gudangRuleId,
                'numeric',
                'min:1'
            ]
        ];
        
        return $rules;
    }

    public function attributes()
    {
        return [
            'dari' => 'tanggal dari',
            'sampai' => 'tanggal sampai',
            'stokdari' => 'stok dari',
            'stoksampai' => 'stok sampai',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'sampai.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. $this->tgldari,
            
        ];
    }    
    
}
