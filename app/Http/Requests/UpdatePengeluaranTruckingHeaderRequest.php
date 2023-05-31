<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\ErrorController;
use App\Models\Parameter;
use App\Models\PengeluaranTruckingHeader;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DateTutupBuku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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


        $kodepengeluaran = $pengeluaranTrucking->kodepengeluaran;

        $bankQueryId = DB::table('bank')->from(DB::raw('bank with (readuncommitted)'))->select('bank.id');
        $bankResults1 = $bankQueryId->get();

        $bankIds = [];
        foreach ($bankResults1 as $bankId) {
            $bankIds[] = $bankId->id;
        }

        $pengeluaranTruckingHeader = new PengeluaranTruckingHeader();
        $getDataPengeluaran = $pengeluaranTruckingHeader->findAll(request()->id);

        if ($kodepengeluaran == 'BST') {
            $rules = [
                'nobukti' => [Rule::in($getDataPengeluaran->nobukti)],
                "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'date_equals:' . date('d-m-Y', strtotime($getDataPengeluaran->tglbukti)),
                new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required',
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1'],
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
                'nobukti' => [Rule::in($getDataPengeluaran->nobukti)],
                "tglbukti" => [
                "required", 'date_format:d-m-Y',
                'date_equals:' . date('d-m-Y', strtotime($getDataPengeluaran->tglbukti)),
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
        }else{
            $rules = [
                'nobukti' => [Rule::in($getDataPengeluaran->nobukti)],
                "tglbukti" => [
                    "required", 'date_format:d-m-Y',
                    'date_equals:' . date('d-m-Y',strtotime($getDataPengeluaran->tglbukti)),
                    new DateTutupBuku()
                ],
                'pengeluarantrucking' => 'required',
                'bank' => [$ruleBank, $bank, 'required'],
                'bank_id' => [Rule::in($bankIds), 'required', 'min:1'],
                // 'keterangancoa' => 'required',
            ];
        };
       
        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            
            'tglbukti' => 'Tgl Bukti',
            'keterangancoa' => 'nama perkiraan',
            'pengeluarantrucking' => 'Kode Pengeluaran',
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
