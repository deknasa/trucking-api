<?php

namespace App\Http\Requests;

use App\Rules\ValidasiSupirPJT;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidasiKlaimSPKPGRule;
use App\Rules\ValidasiStatusTitipanEMKL;
use App\Rules\ValidasiKlaimPenerimaanStok;
use App\Rules\ValidasiKlaimPengeluaranStok;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Api\ErrorController;
use Illuminate\Validation\ValidationException;
use App\Rules\ValidasiKeteranganPengeluaranTrucking;

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
                if ($fetchFormat->kodepengeluaran == 'TDE' || $fetchFormat->kodepengeluaran == 'TDEK' || $fetchFormat->kodepengeluaran == 'BST' || $fetchFormat->kodepengeluaran == 'KBBM' || $fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT' || $fetchFormat->kodepengeluaran == 'OTOK' || $fetchFormat->kodepengeluaran == 'OTOL' || $fetchFormat->kodepengeluaran == 'BSM') {
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
                if ($fetchFormat->kodepengeluaran == 'PJT'){
                    if(request()->statusposting == 84){
                        return false;
                    }else{
                        return true;
                    }
                } else if($fetchFormat->kodepengeluaran == 'BSB') {
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
        if (request()->pengeluarantrucking_id) {
            $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                // ->where('id', request()->pengeluarantrucking_id)
                ->where('keterangan', 'LIKE', "%klaim%")
                ->first();
            if ($klaim->id ==  request()->pengeluarantrucking_id) {
                $rulseKlaim = [
                    "stok_id.*"  => ["required",
                    new ValidasiKlaimSPKPGRule(
                        request()->stok_id,
                        request()->pengeluaranstok_nobukti,
                        request()->penerimaanstok_nobukti
                    )
                ],
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

            if ($fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT' || $fetchFormat->kodepengeluaran == 'BSM') {
                $min = Rule::when((($fetchFormat->kodepengeluaran == 'BLL' || $fetchFormat->kodepengeluaran == 'BLN' || $fetchFormat->kodepengeluaran == 'BTU' || $fetchFormat->kodepengeluaran == 'BPT' || $fetchFormat->kodepengeluaran == 'BGS' || $fetchFormat->kodepengeluaran == 'BIT' || $fetchFormat->kodepengeluaran == 'BSM')), 'numeric|min:0');
            } else {
                $min = Rule::when((($fetchFormat->kodepengeluaran != 'BLL' || $fetchFormat->kodepengeluaran != 'BLN' || $fetchFormat->kodepengeluaran != 'BTU' || $fetchFormat->kodepengeluaran != 'BPT' || $fetchFormat->kodepengeluaran != 'BGS' || $fetchFormat->kodepengeluaran != 'BIT' || $fetchFormat->kodepengeluaran != 'BSM')), 'numeric|gt:0');
            }
        }
        $rules = [
            'kbbm_id' => [$requiredKBBM, 'array'],
            'kbbm_id.*' => $requiredKBBM,
            'id_detail' => [$requiredBST, 'array'],
            'id_detail.*' => $requiredBST,
            'sisa.*' => [$requiredKBBM, $sisaNominus],
            'supir.*' => [$requiredPJT, new ValidasiSupirPJT()],
            // 'nominal' => ['array','required', 'numeric', 'gt:0'],
            'nominal.*' => ['required', $min],
            'keterangan' => [$requiredKeterangan, 'array'],
            'keterangan.*' => [$requiredKeterangan, new ValidasiKeteranganPengeluaranTrucking()],
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

        // dd($rules);
        return $rules;
    }

    public function attributes()
    {
        return [
            'kbbm_id' => 'bbm',
            'id_detail' => 'invoice',
            'supir.*' => 'supir',
            'stok_id.*' => 'stok',
            'qty.*' => 'qty',
            'nominal.*' => 'nominal',
            'keterangan.*' => 'keterangan',
            'suratpengantar_nobukti.*' => 'no bukti SP',
            'detail_statustitipanemkl.*' => 'titipan emkl',
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
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->getMessages();

        $newErrors = [];
    
        // Iterasi melalui semua pesan kesalahan
        foreach ($errors as $key => $messages) {
            // Jika kunci error adalah stok_id.N, ganti dengan stok.N
            if (preg_match('/stok_id\.\d+/', $key)) {
                $newKey = preg_replace('/stok_id/', 'stok', $key);
                $newErrors[$newKey] = $messages;
            } else {
                $newErrors[$key] = $messages;
            }
        }
    
        // Lemparkan ValidationException dengan pesan kesalahan yang disesuaikan
        throw new ValidationException($validator, response()->json([  "message"=>"asdasdas."
            ,"errors"=>$newErrors], 422));
    }
}
