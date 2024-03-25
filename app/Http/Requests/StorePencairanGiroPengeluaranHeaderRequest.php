<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiHutangList;
use App\Rules\validasiNobuktiPencairan;
use App\Rules\validasiPencairanGiro;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Parameter;

class StorePencairanGiroPengeluaranHeaderRequest extends FormRequest
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

        $jumlahdetail = $this->jumlahdetail ?? 0;
        return [
            'periode' => ['required', new ValidasiHutangList($jumlahdetail), new validasiNobuktiPencairan()],
            'detail' => [new validasiPencairanGiro()]
        ];
    }

    public function attributes()
    {
        return [
            'pengeluaranId' => 'Transaksi'
        ];
    }

    public function messages()
    {
        return [
            'pengeluaranId.required' => ':attribute ' .  app(ErrorController::class)->geterror('WP')->keterangan
        ];
    }
}
