<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdatePenjualRequest extends FormRequest
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
        $coaQuery = DB::table('akunpusat')->from(DB::raw('akunpusat with (readuncommitted)'))->select('akunpusat.coa');
        $coaResults = $coaQuery->get();

        $coaName = [];
        foreach ($coaResults as $coa) {
            $coaName[] = $coa->coa;
        }
        // dd($coaName);

        $coa = Rule::in($coaName);

        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }

       $rules = [
            'namapenjual' => ['required',Rule::unique('penjual')->whereNotIn('id', [$this->id])],
            'alamat' => 'required',
            'nohp' => ['required','min:10','max:50'],
            'coa' => ['required', Rule::in($coaName)],
            'statusaktif' => ['required',Rule::in($status),'numeric','min:1'],
        ];

        return $rules;
    }

    
    public function attributes()
    {
        return [
            'namapenjual' => 'nama penjual',
            'alamat' => 'alamat',
            'nohp' => 'no hp',
            'coa' => 'keterangan coa',
            'statusaktif' => 'status aktif',
        ];
    }

    public function messages()
    {
        $controller = new ErrorController;

        return [
            'namapenjual.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'alamat.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'nohp.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'coa.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'statusaktif.required' => ':attribute' . ' ' . $controller->geterror('WI')->keterangan,
            'nohp.max' => 'max 13 karakter',
        ];
    }
}
