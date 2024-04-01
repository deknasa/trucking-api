<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\SisaNotMinus;
use App\Rules\ValidasiKaryawanDeposito;
use App\Rules\ValidasiKeteranganPenerimaanTrucking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorePenerimaanTruckingDetailRequest extends FormRequest
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
        $requiredDPOK = Rule::requiredIf(function () {
            $idpenerimaan = request()->penerimaantrucking_id;
            if ($idpenerimaan != '') {
                if ($idpenerimaan == 6) {
                    return true;
                }
            }
            return false;
        });

        $requiredKeterangan = Rule::requiredIf(function () {
            $idpenerimaan = request()->penerimaantrucking_id;
            if ($idpenerimaan != '') {
                $fetchFormat =  DB::table('penerimaantrucking')
                    ->where('id', $idpenerimaan)
                    ->first();
                if ($fetchFormat->kodepenerimaan == 'PJP' || $fetchFormat->kodepenerimaan == 'PBT') {
                    return false;
                } else {
                    return true;
                }
            }
            return true;
        });

        $requiredPJP = Rule::requiredIf(function () {
            $idpenerimaan = request()->penerimaantrucking_id;
            if ($idpenerimaan != '') {
                $fetchFormat =  DB::table('penerimaantrucking')
                    ->where('id', $idpenerimaan)
                    ->first();
                if ($fetchFormat->kodepenerimaan == 'PJP') {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        });
        $requiredNominal = Rule::requiredIf(function () {
            $idpenerimaan = request()->penerimaantrucking_id;
            if ($idpenerimaan != '') {
                $fetchFormat =  DB::table('penerimaantrucking')
                    ->where('id', $idpenerimaan)
                    ->first();
                if ($fetchFormat->kodepenerimaan == 'PBT') {
                    return false;
                } else {
                    return true;
                }
            }
            return true;
        });
        $sisaNominus = '';
        if (request()->pengeluarantrucking != '') {
            $idpenerimaan = request()->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();
            $sisaNominus = Rule::when((($fetchFormat->kodepengeluaran == 'PJP')), 'numeric|min:0');
        }
        $min = '';
        if (request()->pengeluarantrucking != '') {
            $idpenerimaan = request()->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();
            if ($fetchFormat->kodepenerimaan == 'PBT') {
                $min = Rule::when((($fetchFormat->kodepenerimaan == 'PBT')), 'numeric|min:0');
            } else {
                $min = Rule::when((($fetchFormat->kodepenerimaan == 'PBT')), 'numeric|gt:0');
            }
        }
        $idpenerimaan = request()->penerimaantrucking_id;
        $fetchFormat =  DB::table('penerimaantrucking')
            ->where('id', $idpenerimaan)
            ->first();
        return [
            'sisa.*' => [$requiredPJP, $sisaNominus],
            'nominal' => [$requiredNominal, 'array'],
            'nominal.*' => ['required', 'gt:0', 'numeric', $min],
            'karyawandetail.*' => [$requiredDPOK, new ValidasiKaryawanDeposito()],
            'keterangan' => [$requiredKeterangan, 'array'],
            'keterangan.*' => [$requiredKeterangan, new ValidasiKeteranganPenerimaanTrucking()]
        ];
    }

    public function attributes()
    {
        return [
            'nominal.*' => 'nominal',
            'karyawandetail.*' => 'karyawan',
            'keterangan.*' => 'keterangan',
        ];
    }

    public function messages()
    {
        return [
            'nominal.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
        ];
    }
}
