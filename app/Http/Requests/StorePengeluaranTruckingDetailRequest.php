<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorePengeluaranTruckingDetailRequest extends FormRequest
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
        $requiredKeterangan = Rule::requiredIf(function () {
            $idpengeluaran = request()->pengeluarantrucking_id;
            if ($idpengeluaran != '') {
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                if ($fetchFormat->kodepengeluaran == 'TDE' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'KBBM') {
                    return false;
                } else {
                    return true;
                }
            }
            return true;
        });
        $requiredTDE = Rule::requiredIf(function () {
            $idpengeluaran = request()->pengeluarantrucking_id;
            if ($idpengeluaran != '') {
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                if ($fetchFormat->kodepengeluaran == 'TDE') {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        });
        $requiredBST = Rule::requiredIf(function () {
            $idpengeluaran = request()->pengeluarantrucking_id;
            if ($idpengeluaran != '') {
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                if ($fetchFormat->kodepengeluaran == 'BST') {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        });
        $requiredKBBM = Rule::requiredIf(function () {
            $idpengeluaran = request()->pengeluarantrucking_id;
            if ($idpengeluaran != '') {
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                if ($fetchFormat->kodepengeluaran == 'KBBM') {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        });
        $requiredPJT = Rule::requiredIf(function () {
            $idpengeluaran = request()->pengeluarantrucking_id;
            if ($idpengeluaran != '') {
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                if ($fetchFormat->kodepengeluaran == 'PJT' || $fetchFormat->kodepengeluaran == 'BSB') {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        });
        $sisaNominus = '';
        if (request()->pengeluarantrucking != '') {
            $idpengeluaran = request()->pengeluarantrucking_id;
            $fetchFormat =  DB::table('pengeluarantrucking')
                ->where('id', $idpengeluaran)
                ->first();
            $sisaNominus = Rule::when((($fetchFormat->kodepengeluaran == 'TDE' || $fetchFormat->kodepengeluaran == 'KBBM')), 'numeric|min:0');
        }


        return [
            'tde_id' => [$requiredTDE, 'array'],
            'tde_id.*' => $requiredTDE,
            'kbbm_id' => [$requiredKBBM, 'array'],
            'kbbm_id.*' => $requiredKBBM,
            'id_detail' => [$requiredBST, 'array'],
            'id_detail.*' => $requiredBST,
            'sisa.*' => [$requiredTDE, $requiredKBBM, $sisaNominus],
            'supir.*' => $requiredPJT,
            'nominal' => 'required|array',
            'nominal.*' => ['required', 'numeric', 'gt:0'],
            'keterangan' => [$requiredKeterangan, 'array'],
            'keterangan.*' => $requiredKeterangan
        ];
    }

    public function attributes()
    {
        return [
            'tde_id' => 'deposito',
            'kbbm_id' => 'bbm',
            'id_detail' => 'invoice',
            'supir.*' => 'supir',
            'nominal.*' => 'nominal',
            'keterangan.*' => 'keterangan',
        ];
    }

    public function messages()
    {
        return [
            'tde_id.required' => 'deposito ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'kbbm_id.required' => 'bbm ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'id_detail.required' => 'invoice ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'nominal.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
        ];
    }
}
