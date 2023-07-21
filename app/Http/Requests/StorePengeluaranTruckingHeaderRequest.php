<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistBank;
use App\Rules\ExistSupir;
use App\Rules\ExistSupirForPengeluaranTrucking;
use App\Rules\ValidasiDetail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StorePengeluaranTruckingHeaderRequest extends FormRequest
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
        $parameter = new Parameter();
        $getBatas = $parameter->getBatasAwalTahun();
        $tglbatasawal = $getBatas->text;
        $tglbatasakhir = (date('Y') + 1) . '-01-01';

        if (!request()->pengeluarantrucking_id) {
            return ["pengeluarantrucking_id"=>['required']];
        }
        $requiredTglPriode = Rule::requiredIf(function () {
            
            $bst = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                ->where('kodepengeluaran',"BST")
                ->first();
            $kbbm = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                ->where('kodepengeluaran',"KBBM")
                ->first();
                
            if (($bst->id ==request()->pengeluarantrucking_id)|| ($kbbm->id ==request()->pengeluarantrucking_id)) {
                return true;
            }
            return false;
        });
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
        $ruleStatusPosting = Rule::requiredIf(function () {
            $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                // ->where('id',$this->pengeluarantrucking_id)
                ->where('kodepengeluaran', "KLAIM")
                ->first();
                
                if ($this->pengeluarantrucking_id) {
                    if ($klaim) {
                        if ($klaim->id ==  $this->pengeluarantrucking_id) {
                            return false;
                        }
                    }
                }
            return true;
        });
        $bank_id = $this->bank_id;


        $rulseKlaim=[];
        
        if ($this->pengeluarantrucking_id) {
            $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                    // ->where('id',$this->pengeluarantrucking_id)
                    ->where('keterangan','LIKE', "%klaim%")
                    ->first();
            if ($klaim) {
                if ($klaim->id ==  $this->pengeluarantrucking_id) {
                    $rulseKlaim =[
                        "supirheader_id" =>"required",
                        "supirheader" =>"required",
                        "tradoheader_id" =>"required",
                        "trado" =>"required",
                        "postingpinjaman" =>"required",
                    ];    
                }
            }
        }

        $rulesBank_id = [];
        if ($bank_id != null) {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        } else if ($bank_id == null && $this->bank != '') {
            $rulesBank_id = [
                'bank_id' => ['required', 'numeric', 'min:1', new ExistBank()]
            ];
        }

        $rules = [
            "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'date_equals:'.date('d-m-Y'),
                new DateTutupBuku()
            ],
            'pengeluarantrucking' => 'required','numeric', 'min:1',
            'statusposting' => [$ruleStatusPosting],
            'bank' => [$ruleBank],
            'tgldari' => [
                $requiredTglPriode, 'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.$tglbatasawal,
            ],
            'tglsampai' => [
                $requiredTglPriode, 'date_format:d-m-Y',
                'before:'.$tglbatasakhir,
                'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
            ],
            // 'keterangancoa' => 'required',
        ];
        $pengeluarantrucking_id = $this->pengeluarantrucking_id;
        $rulespengeluaran_id = [];

        $pengeluaranTrucking = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                ->whereRaw("id = ".$pengeluarantrucking_id)
                ->first();

        $kodepengeluaran    = $pengeluaranTrucking->kodepengeluaran;

        $rulesSupir_id = [];

        if($kodepengeluaran == 'KBBM' ){
            $rules = [
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'date_equals:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                'statusposting' => 'required',
                'bank' => [$ruleBank],
                'tgldari' => [
                    'required', 'date_format:d-m-Y',
                    'before:'.$tglbatasakhir,
                    'after_or_equal:'.$tglbatasawal,
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y',
                    'before:'.$tglbatasakhir,
                    'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
                ],
                // 'keterangancoa' => 'required',
            ];
        }elseif($kodepengeluaran == 'BST'){
            $rules = [
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'date_equals:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                'statusposting' => 'required',
                'bank' => [$ruleBank],
                'tgldari' => [
                    'required', 'date_format:d-m-Y',
                    'before:'.$tglbatasakhir,
                    'after_or_equal:'.$tglbatasawal,
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y',
                    'before:'.$tglbatasakhir,
                    'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
                ],
                // 'keterangancoa' => 'required',
            ];
        }elseif($kodepengeluaran == 'TDE'){
            $supirheader_id = $this->supirheader_id;
            if ($supirheader_id != null) {
                $rulesSupir_id = [
                    'supirheader_id' => ['required', 'numeric', 'min:1', new ExistSupirForPengeluaranTrucking()]
                ];
            } else if ($supirheader_id == null && $this->supirheader != '') {
                $rulesSupir_id = [
                    'supirheader_id' => ['required', 'numeric', 'min:1', new ExistSupirForPengeluaranTrucking()]
                ];
            }
            $jumlahdetail = $this->jumlahdetail ?? 0;
            $rules = [
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'date_equals:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                'statusposting' => 'required',
                'bank' => [$ruleBank],
                'supirheader' => ['required',  new ValidasiDetail($jumlahdetail)],
                // 'keterangancoa' => 'required',
            ];
        }else{
            $rules = [
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'date_equals:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                'statusposting' => 'required',
                'bank' => [$ruleBank],
                // 'tgldari' => [
                //     'required', 'date_format:d-m-Y',
                //     'before:'.$tglbatasakhir,
                //     'after_or_equal:'.$tglbatasawal,
                // ],
                // 'tglsampai' => [
                //     'required', 'date_format:d-m-Y',
                //     'before:'.$tglbatasakhir,
                //     'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
                // ],
                // 'keterangancoa' => 'required',
            ];
        }

       
        $relatedRequests = [
            StorePengeluaranTruckingDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                $rules,
                (new $relatedRequest)->rules(),
                $rulesBank_id,
                $rulseKlaim,
                $rulesSupir_id
            );
        }

        // dd($rules);
        return $rules;
    }

    public function attributes()
    {
        $attributes = [

            'tglbukti' => 'Tgl Bukti',
            'keterangancoa' => 'nama perkiraan',
            'pengeluarantrucking' => 'Kode Pengeluaran',
            'supirheader' => 'supir',
            'supirhaeader_id' => 'supir',
            'trado' => 'trado',
            'tradoheader_id' => 'trado',
            'postingpinjaman' => 'posting pinjaman',
            'keterangan.*' => 'keterangan'
        ];
        $relatedRequests = [
            StorePengeluaranTruckingDetailRequest::class
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
            StorePengeluaranTruckingDetailRequest::class
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
