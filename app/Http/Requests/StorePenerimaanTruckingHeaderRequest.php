<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\PenerimaanTruckingHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistSupirDPOPenerimaanTrucking;
use App\Rules\SupirDPOPenerimaanTrucking;
use App\Rules\ValidasiDetail;
use App\Rules\validasiPenembalianPinjamanDetail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidasiPenerimaanTrucking;
use App\Rules\validasiPengembalianPinjamanKaryawanDetail;
use App\Rules\ValidasiSupirDeposito;

class StorePenerimaanTruckingHeaderRequest extends FormRequest
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
        $supirQuery = DB::table('supir')->from(DB::raw('supir with (readuncommitted)'))->select('supir.namasupir');
        $supirResults = $supirQuery->get();

        $supirName = [];
        foreach ($supirResults as $supir) {
            $supirName[] = $supir->namasupir;
        }

        $supir = Rule::in($supirName);

        $penerimaan = DB::table('penerimaantrucking')->from(DB::raw('penerimaantrucking with (readuncommitted)'))->select('penerimaantrucking.id');
        $penerimaanResults = $penerimaan->get();

        $penerimaanId = [];
        foreach ($penerimaanResults as $pt) {
            $penerimaanId[] = $pt->id;
        }

        $penerimaanQuerys = DB::table('penerimaantrucking')->from(DB::raw('penerimaantrucking with (readuncommitted)'))->select('penerimaantrucking.keterangan')->get();

        $penerimaanName = [];
        foreach ($penerimaanQuerys as $pt) {
            $penerimaanName[] = $pt->keterangan;
        }


        $supirQuery2 = DB::table('supir')->from(DB::raw('supir with (readuncommitted)'))->select('supir.id');
        $supirResults2 = $supirQuery2->get();

        $supirId = [];
        foreach ($supirResults2 as $supir2) {
            $supirId[] = $supir2->id;
        }

        $supirId = Rule::in($supirId);

        $rulespenerimaan_id = [];
        $kodepenerimaan = "";
        if ($this->penerimaantrucking_id) {
            $penerimaantrucking_id = $this->penerimaantrucking_id;
            $penerimaanTrucking = DB::table('penerimaantrucking')->from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->whereRaw("id = " . $penerimaantrucking_id)
                ->first();
            $kodepenerimaan = $penerimaanTrucking->kodepenerimaan;
        }


        $penerimaanTruckingHeader = new PenerimaanTruckingHeader();
        $getDatapenerimaan = $penerimaanTruckingHeader->findAll(request()->id);

        $bankQuery = DB::table('bank')->from(DB::raw('bank with (readuncommitted)'))->select('bank.namabank');
        $bankResults = $bankQuery->get();

        $bankName = [];
        foreach ($bankResults as $bank) {
            $bankName[] = $bank->namabank;
        }

        $bank = Rule::in($bankName);

        $bankQueryId = DB::table('bank')->from(DB::raw('bank with (readuncommitted)'))->select('bank.id');
        $bankResults1 = $bankQueryId->get();

        $bankIds = [];
        foreach ($bankResults1 as $bankId) {
            $bankIds[] = $bankId->id;
        }

        $ruleBank = Rule::requiredIf(function () {
            $postingParameter = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->whereRaw("grp = 'STATUS POSTING'")
                ->whereRaw("text = 'POSTING'")
                ->first();
            if ($postingParameter->id ==  request()->statusposting) {
                return true;
            }
            return false;
        });
        $penerimaantrucking_id = $this->penerimaantrucking_id;
        if ($penerimaantrucking_id != null) {
            $rulespenerimaan_id = [
                'penerimaantrucking_id' => ['required', 'numeric', 'min:1', Rule::in($penerimaanId)]
            ];
        } else if ($penerimaantrucking_id == null && $this->penerimaantrucking != '') {
            $rulespenerimaan_id = [
                'penerimaantrucking_id' => ['required', 'numeric', 'min:1', Rule::in($penerimaanId)]
            ];
        }

        if ($kodepenerimaan == 'PJP') {
            $jumlahdetail = $this->jumlahdetail ?? 0;
            $rules = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:' . date('d-m-Y'),
                    new DateTutupBuku(),
                ],
                'penerimaantrucking' => ['required', Rule::in($penerimaanName)],
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1', 'numeric'],
                'supir' => [ new validasiPenembalianPinjamanDetail()
                    // new ValidasiDetail($jumlahdetail),
                // new ValidasiPenerimaanTrucking()
                ],
                // 'supirheader_id' => [
                //     'required', $supirId, 'numeric', 'min:1'
                // ],
                // 'keterangancoa' => 'required'
            ];
        } elseif ($kodepenerimaan == 'PJPK') {
            $jumlahdetail = $this->jumlahdetail ?? 0;
            $rules = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:' . date('d-m-Y'),
                    new DateTutupBuku(),
                ],
                'penerimaantrucking' => ['required', Rule::in($penerimaanName)],
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1', 'numeric'],
                'karyawan' => [ new validasiPengembalianPinjamanKaryawanDetail()
                    // new ValidasiDetail($jumlahdetail),
                // new ValidasiPenerimaanTrucking()
                ],
                // 'supirheader_id' => [
                //     'required', $supirId, 'numeric', 'min:1'
                // ],
                // 'keterangancoa' => 'required'
            ];
        } elseif ($kodepenerimaan == 'DPO') {
            $rules = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:' . date('d-m-Y'),
                    new DateTutupBuku(),
                ],
                'penerimaantrucking' => ['required', Rule::in($penerimaanName)],
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1'],
                'supir.*' => ['required', $supir, new ValidasiSupirDeposito()],
                'supir_id.*' => [new SupirDPOPenerimaanTrucking, new ExistSupirDPOPenerimaanTrucking()],
                // 'keterangancoa' => 'required'
            ];
        } elseif ($kodepenerimaan == 'PBT') {

            $jumlahdetail = $this->jumlahdetail ?? 0;
            $rules = [
                'tglbukti' => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:' . date('d-m-Y'),
                    new DateTutupBuku(),
                ],
                'penerimaantrucking' => ['required', Rule::in($penerimaanName)],
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1'],
                'jenisorder' => ['required', new ValidasiDetail($jumlahdetail)],
                'periodedari' => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:' . date('d-m-Y'),
                ],
                'periodesampai' => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:' . date('d-m-Y'),
                ],
                'keteranganheader' => 'required'
            ];
        } else {
            $rules = [
                'tglbukti' => [
                    'required', 'date_format:d-m-Y',
                    new DateTutupBuku(),
                    'before_or_equal:' . date('d-m-Y')
                ],
                'penerimaantrucking' => ['required', Rule::in($penerimaanName)],
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1'],
                // 'keterangancoa' => 'required'
            ];
        };

        $relatedRequests = [
            StorePenerimaanTruckingDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulespenerimaan_id
            );
        }
        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'tglbukti' => 'Tgl Bukti',
            'keterangancoa' => 'nama perkiraan',
            'penerimaantrucking' => 'Kode Penerimaan',
            'supir.*' => 'supir'
        ];

        $relatedRequests = [
            StorePenerimaanTruckingDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $attributes = array_merge(
                $attributes,
                (new $relatedRequest)->attributes()
            );
        }

        return $attributes;
    }

    public function messages()
    {
        $messages = [
            'tglbukti.date_format' => app(ErrorController::class)->geterror('DF')->keterangan,
        ];

        $relatedRequests = [
            StorePenerimaanTruckingDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $messages = array_merge(
                $messages,
                (new $relatedRequest)->messages()
            );
        }

        return $messages;
    }
}
