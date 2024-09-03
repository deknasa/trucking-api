<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\Kelompok;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\ParameterController;
use App\Rules\MinNull;
use App\Rules\NotDecimal;
use App\Rules\NumberMax;
use Illuminate\Foundation\Http\FormRequest;

class StoreStokRequest extends FormRequest
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
        if (request()->from == 'tas') {
            return [];
        }
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS AKTIF', 'STATUS AKTIF');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        $kelompokBan = Kelompok::where("kodekelompok","BAN")->first();
        // dd(($kelompokBan->id == $this->input('kelompok_id')));


        return [
            "namastok" => ['required','unique:stok,namastok'],
            "kelompok"=>'required',
            "subkelompok"=>'required',
            "kategori"=>'required',
            'statusaktif' => ['required', Rule::in($status)],
            // "namaterpusat"=>'required',
            "satuan"=>'required',
            "satuan_id"=>'required',
            "qtymin"=> [new NotDecimal(), new MinNull(),'numeric','max:10000'],
            "qtymax"=> [new NotDecimal(), new NumberMax(),'numeric','max:10000'],
            "hargabelimin"=> [new NotDecimal(), new MinNull(),'numeric'],
            "hargabelimax"=> [new NotDecimal(), new NumberMax(),'numeric'],
            'statusban' => [Rule::requiredIf($kelompokBan->id == $this->input('kelompok_id'))],
            'gambar' => 'array',
            'gambar.*' => ['image']
        ];
    }

    public function messages()
    {
        return [
            'gambar.*.image' => app(ErrorController::class)->geterror('WG')->keterangan
        ];
    }
}
