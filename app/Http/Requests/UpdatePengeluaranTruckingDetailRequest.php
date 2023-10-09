<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Rules\ValidasiKlaimPenerimaanStok;
use App\Rules\ValidasiKlaimPengeluaranStok;
use App\Rules\ValidasiStatusTitipanEMKL;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdatePengeluaranTruckingDetailRequest extends FormRequest
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
                if ($fetchFormat->kodepengeluaran == 'TDE' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'KBBM' || $fetchFormat->kodepengeluaran == 'BLL') {
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
                if ($fetchFormat->kodepengeluaran == 'PJT') {
                    $getPosting = DB::table('pengeluarantruckingheader')
                        ->where('id', request()->id)
                        ->first();
                    if ($getPosting->statusposting == 84) {
                        return false;
                    } else {
                        return true;
                    }
                } else if ($fetchFormat->kodepengeluaran == 'BSB') {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        });
        $requiredBBT = Rule::requiredIf(function () {
            $idpengeluaran = request()->pengeluarantrucking_id;
            if ($idpengeluaran != '') {
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                if ($fetchFormat->kodepengeluaran == 'BBT') {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
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
        if ($this->pengeluarantrucking_id) {
            $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                ->where('id', request()->pengeluarantrucking_id)
                ->where('keterangan', 'LIKE', "%klaim%")
                ->first();
            if ($klaim->id ==  $this->pengeluarantrucking_id) {
                $rulseKlaim = [
                    "stok_id.*"  => ["required",],
                    "pengeluaranstok_nobukti.*"  => [new ValidasiKlaimPengeluaranStok()],
                    "penerimaanstok_nobukti.*"  => [new ValidasiKlaimPenerimaanStok()],
                    "qty.*"  => ["required",],
                    "nominaltambahan.*"  => ['numeric','min:0'],
                ];
            }
        }
        $min = '';
        $idpengeluaran = request()->pengeluarantrucking_id;
        $fetchFormat =  DB::table('pengeluarantrucking')
            ->where('id', $idpengeluaran)
            ->first();
        if ($idpengeluaran != '') {

            if ($fetchFormat->kodepengeluaran == 'BLL') {
                $min = Rule::when((($fetchFormat->kodepengeluaran == 'BLL')), 'numeric|min:0');
            } else {
                $min = Rule::when((($fetchFormat->kodepengeluaran != 'BLL')), 'numeric|gt:0');
            }
        }

        $rules = [
            'tde_id' => [$requiredTDE, 'array'],
            'tde_id.*' => $requiredTDE,
            'kbbm_id' => [$requiredKBBM, 'array'],
            'kbbm_id.*' => $requiredKBBM,
            'id_detail' => [$requiredBST, 'array'],
            'id_detail.*' => $requiredBST,
            'sisa.*' => [$requiredTDE, $requiredKBBM, $sisaNominus],
            'supir.*' => $requiredPJT,
            'nominal.*' => ['required', $min],
            'keterangan' => [$requiredKeterangan, 'array'],
            'keterangan.*' => $requiredKeterangan,
            'suratpengantar_nobukti' => [$requiredBBT, 'array'],
            'suratpengantar_nobukti.*' => [$requiredBBT],
            'detail_statustitipanemkl' => [$requiredBBT, 'array'],
            'detail_statustitipanemkl.*' => [$requiredBBT, new ValidasiStatusTitipanEMKL(request()->id)]
        ];

        if ( request()->pengeluarantrucking_id != '') {
            if ($fetchFormat->kodepengeluaran == 'BST') {
                
                $rules = [
                    "detail"=>"required"
                ];
            }
        }

        $rules = array_merge(
            $rules,
            $rulseKlaim
        );

        return $rules;
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
            'suratpengantar_nobukti.*' => 'no bukti SP',
            'detail_statustitipanemkl.*' => 'titipan emkl',
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
