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
                if ($fetchFormat->kodepengeluaran == 'TDE' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'KBBM' || $fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT') {
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
        $rulseKlaim = [];
        if (request()->pengeluarantrucking_id) {
            $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                // ->where('id', request()->pengeluarantrucking_id)
                ->where('keterangan', 'LIKE', "%klaim%")
                ->first();
            if ($klaim->id ==  request()->pengeluarantrucking_id) {
                $rulseKlaim = [
                    "stok_id.*"  => ["required",],
                    "pengeluaranstok_nobukti.*"  => ["required",],
                    "qty.*"  => ["required",],
                    "harga.*"  => ["required",],
                ];
            }
        }
        $min = '';
        $idpengeluaran = request()->pengeluarantrucking_id;
        $fetchFormat =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
        if ($idpengeluaran != '') {

            if ($fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT') {
                $min = Rule::when((($fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT')), 'numeric|min:0');
            } else {
                $min = Rule::when((($fetchFormat->kodepengeluaran != 'BLL' || $fetchFormat->kodepengeluaran != 'BLN' || $fetchFormat->kodepengeluaran != 'BTU' || $fetchFormat->kodepengeluaran != 'BPT' || $fetchFormat->kodepengeluaran != 'BGS' || $fetchFormat->kodepengeluaran != 'BIT')), 'numeric|gt:0');
            }
        }
        $rules = [
            'kbbm_id' => [$requiredKBBM, 'array'],
            'kbbm_id.*' => $requiredKBBM,
            'id_detail' => [$requiredBST, 'array'],
            'id_detail.*' => $requiredBST,
            'sisa.*' => [$requiredTDE, $requiredKBBM, $sisaNominus],
            'supir.*' => $requiredPJT,
            // 'nominal' => ['array','required', 'numeric', 'gt:0'],
            'nominal.*' => ['required', $min],
            'keterangan' => [$requiredKeterangan, 'array'],
            'keterangan.*' => $requiredKeterangan
        ];

        $rules = array_merge(
            $rules,
            $rulseKlaim
        );

        // dd($rules);
        return $rules;
    }

    public function attributes()
    {
        return [
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
            'kbbm_id.required' => 'bbm ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'id_detail.required' => 'invoice ' . app(ErrorController::class)->geterror('WP')->keterangan,
            'nominal.*.gt' => app(ErrorController::class)->geterror('GT-ANGKA-0')->keterangan,
            'sisa.*.min' => 'SISA ' . app(ErrorController::class)->geterror('NTM')->keterangan,
        ];
    }
}
