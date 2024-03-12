<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\PengeluaranTruckingHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use App\Rules\ExistSupirForPengeluaranTrucking;
use App\Rules\ValidasiDetail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Rules\DestroyPengeluaranTruckingHeader;
use App\Rules\ExistKaryawanForPengeluaranTrucking;
use App\Rules\validasiJenisOrderanPengeluaranTrucking;
use App\Rules\ValidasiKlaimPosting;
use App\Rules\ValidasiDestroyPengeluaranTruckingHeader;

class UpdatePengeluaranTruckingHeaderRequest extends FormRequest
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

        $pengeluarantrucking_id = $this->pengeluarantrucking_id;
        $rulespengeluaran_id = [];

        $pengeluaranTrucking = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                ->whereRaw("id = ".$pengeluarantrucking_id)
                ->first();


        $kodepengeluaran    = $pengeluaranTrucking->kodepengeluaran;

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

        
        $rulseKlaim=[];
        
        if ($this->pengeluarantrucking_id) {
            $klaim = DB::table('pengeluarantrucking')->from(DB::raw("pengeluarantrucking with (readuncommitted)"))
                    // ->where('id',$this->pengeluarantrucking_id)
                    ->where('keterangan','LIKE', "%klaim%")
                    ->first();
            if ($klaim) {
                if ($klaim->id ==  $this->pengeluarantrucking_id) {
                    $salahSatuDari = Rule::requiredIf(function ()  {
                        if ( empty($this->input('tradoheader_id')) && empty($this->input('gandenganheader_id')) ) {
                            return true;
                        }
                        return false;
                    });
                    $rulseKlaim =[
                        "supirheader_id" =>"required",
                        "supirheader" =>"required",
                        "tradoheader_id" =>$salahSatuDari,
                        "gandenganheader_id" =>$salahSatuDari,
                        "trado" =>$salahSatuDari,
                        "gandengan" =>$salahSatuDari,
                        // "postingpinjaman" => ["required", new ValidasiKlaimPosting()],
                        "statuscabang" =>"required",
                    ];
                    $getListTampilan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'UBAH TAMPILAN')->where('text', 'PENGELUARAN TRUCKING HEADER')->first();

                    $getListTampilan = json_decode($getListTampilan->memo);
                    if ($getListTampilan->INPUT != '') {
                        $getListTampilan = (explode(",", $getListTampilan->INPUT));
                        foreach ($getListTampilan as $value) {
                            if ($value =="CABANG") {
                                $value ='statuscabang';
                            }
                            if (array_key_exists(trim(strtolower($value)), $rulseKlaim) == true) {
                                unset($rulseKlaim[trim(strtolower($value))]);
                            }
                        }
                    }
                }
            }
        }

        $pengeluaranTruckingHeader = new PengeluaranTruckingHeader();
        $getDataPengeluaran = $pengeluaranTruckingHeader->findAll(request()->id);

        $rulesSupir_id = [];
        if ($kodepengeluaran == 'BST') {
            $rules = [
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 

                'nobukti' => [Rule::in($getDataPengeluaran->nobukti)],
                "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'before_or_equal:'.date('d-m-Y'),
                new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required',
                
                'tgldari' => [
                    'required', 'date_format:d-m-Y',
                    'before:' . $tglbatasakhir,
                    'after_or_equal:' . $tglbatasawal,
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y',
                    'before:' . $tglbatasakhir,
                    'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))
                ],
            ];
        }elseif($kodepengeluaran == 'KBBM'){
            $rules = [
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 

                'nobukti' => [Rule::in($getDataPengeluaran->nobukti)],
                "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'before_or_equal:'.date('d-m-Y'),
                new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required',
                'tgldari' => [
                    'required', 'date_format:d-m-Y',
                    'before:' . $tglbatasakhir,
                    'after_or_equal:' . $tglbatasawal,
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y',
                    'before:' . $tglbatasakhir,
                    'after_or_equal:' . date('Y-m-d', strtotime($this->tgldari))
                ],
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
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'before_or_equal:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                // 'statusposting' => 'required',
                'bank' => [$ruleBank],
                'supirheader' => ['required',  new ValidasiDetail($jumlahdetail)],
                // 'keterangancoa' => 'required',
            ];
        }else if($kodepengeluaran == 'TDEK'){
            $karyawanheader_id = $this->karyawanheader_id;
            if ($karyawanheader_id != null) {
                $rulesSupir_id = [
                    'karyawanheader_id' => ['required', 'numeric', 'min:1', new ExistKaryawanForPengeluaranTrucking()]
                ];
            } else if ($karyawanheader_id == null && $this->supirheader != '') {
                $rulesSupir_id = [
                    'karyawanheader_id' => ['required', 'numeric', 'min:1', new ExistKaryawanForPengeluaranTrucking()]
                ];
            }
            $jumlahdetail = $this->jumlahdetail ?? 0;
            $rules = [
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'before_or_equal:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                // 'statusposting' => 'required',
                'bank' => [$ruleBank],
                'karyawanheader' => ['required',  new ValidasiDetail($jumlahdetail)],
                // 'keterangancoa' => 'required',
            ];
        }elseif($kodepengeluaran == 'BBT'){
            
            $rules = [
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'before_or_equal:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                'bank' => [$ruleBank],
                'jenisorderan' => ['required', new validasiJenisOrderanPengeluaranTrucking()]
                // 'keterangancoa' => 'required',
            ];
        }elseif($kodepengeluaran == 'OTOL' || $kodepengeluaran == 'OTOK'){
            
            $jumlahdetail = $this->jumlahdetail ?? 0;
            $rules = [
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'before_or_equal:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required','numeric', 'min:1',
                // 'statusposting' => 'required',
                'agen' => ['required', new ValidasiDetail($jumlahdetail)],
                'containerheader' => 'required',
                'bank' => [$ruleBank],
                'tgldari' => [
                    'required', 'date_format:d-m-Y',
                ],
                'tglsampai' => [
                    'required', 'date_format:d-m-Y',
                    'after_or_equal:'.date('Y-m-d', strtotime($this->tgldari))
                ],
                // 'keterangancoa' => 'required',
            ];
        }else{
            $rules = [
                'id' => [ new ValidasiDestroyPengeluaranTruckingHeader()],                 
                'nobukti' => [Rule::in($getDataPengeluaran->nobukti)],
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'before_or_equal:'.date('d-m-Y'),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required',
                
                // 'keterangancoa' => 'required',
            ];
        };

        $relatedRequests = [
            UpdatePengeluaranTruckingDetailRequest::class
        ];

        foreach ($relatedRequests as $relatedRequest) {
            $rules = array_merge(
                ['id' => new DestroyPengeluaranTruckingHeader() ],
                $rules,
                (new $relatedRequest)->rules(),
                $rulseKlaim,
                $rulesSupir_id
            );
        }
        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            
            'tglbukti' => 'Tgl Bukti',
            'keterangancoa' => 'nama perkiraan',
            'pengeluarantrucking' => 'Kode Pengeluaran',
            'supirhaeader' => 'supir',
            'supirhaeader_id' => 'supir',
            'trado' => 'trado',
            'tradoheader_id' => 'trado',
            'postingpinjaman' => 'posting pinjaman',
            'keterangan.*' => 'keterangan'
        ];
        $relatedRequests = [
            UpdatePengeluaranTruckingDetailRequest::class
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
            UpdatePengeluaranTruckingDetailRequest::class
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
