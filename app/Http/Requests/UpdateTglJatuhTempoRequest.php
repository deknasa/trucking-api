<?php

namespace App\Http\Requests;

use App\Rules\DateTutupBuku;
use App\Rules\ValidasiHutangList;
use App\Rules\validasiTglJatuhTempoSudahCair;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTglJatuhTempoRequest extends FormRequest
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
            'tgljatuhtempo' => [
                'required','date_format:d-m-Y',
                // 'before_or_equal:' . date('d-m-Y'),
                new DateTutupBuku()
            ],
            'jumlahdetail' => new ValidasiHutangList($jumlahdetail),
            'detail' => new validasiTglJatuhTempoSudahCair()
        ];
    }

    public function attributes()
    {
        return [ 
            'tgljatuhtempo' => 'tgl jatuh tempo'
        ];
    }
}
