<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ParameterController;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GetIndexHistoriPengeluaranRequest extends FormRequest
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

        $filterQuery = DB::table('pengeluaranstok')->select('pengeluaranstok.id')->get();
        $filters = [];
        foreach ($filterQuery as $filter) {
            $filters[] = $filter->id;
        }
        $filterRule = Rule::in($filters);
        
        $parameter = new Parameter();

        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';
        
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
                'after_or_equal:'.date('Y-m-d', strtotime($this->dari))
            ],
            'stokdari_id' => [
                'required',
                $stokIdRule,
                'numeric',
                'min:1'
            ],
            'stoksampai_id' => [
                'required',
                $stokIdRule,
                'numeric',
                'min:1'
            ],
            'stokdari' => [
                'required',
                $stokNamaRule
            ],
            'stoksampai' => [
                'required',
                $stokNamaRule
            ],
            'filter' => [
                'required',
                $filterRule
            ],
            // 'stoksampai' => [
            //     'required',
            //     $stokNamaRule
            // ],
            
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
            'sampai.after_or_equal' => ':attribute ' . $controller->geterror('NTLK')->keterangan.' '. $this->dari,
            
        ];
    }    

}
