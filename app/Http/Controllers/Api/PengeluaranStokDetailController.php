<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokDetail;
use App\Models\PengeluaranStokHeader;
use App\Models\Parameter;
use App\Models\StokPersediaan;
use App\Models\Stok;

use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\UpdatePengeluaranStokDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PengeluaranStokDetailController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $pengeluaranStokDetail = new PengeluaranStokDetail();
        return response([
            'data' => $pengeluaranStokDetail->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStokDetail->totalRows,
                'totalPages' => $pengeluaranStokDetail->totalPages,
                'totalNominal' => $pengeluaranStokDetail->totalNominal

            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make(
            $request->all(),
            [
                'stok_id' => [
                    'required',
                    Rule::unique('pengeluaranstokdetail')->where(function ($query) use ($request) {
                        return $query->where('pengeluaranstokheader_id', $request->pengeluaranstokheader_id);
                    })
                ],
                'pengeluaranstokheader_id' => 'required',
                // 'harga' => "required|numeric|gt:0",
                'persentasediscount' => "numeric|max:100",
                'detail_keterangan' => 'required',
                // 'vulkanisirke' => 'required',
                'qty' => "required|numeric|gt:0",
            ],
            [
                'stok_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'stok_id.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
                'pengeluaranstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'qty.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'qty.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                //  'harga.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                //  'harga.gt' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'persentasediscount.max' => ':attribute' . ' ' . app(ErrorController::class)->geterror('MAX')->keterangan,
            ],
            [
                'stok_id' => 'stok',
                //  'keterangan' => 'keterangan Detail',
                'qty' => 'qty',
                'persentasediscount' => 'persentase discount',
            ],
        );
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        $total = $request->qty * $request->harga;
        $nominaldiscount = $total * ($request->persentasediscount / 100);
        $total -= $nominaldiscount;
        $pengeluaranstokheader = PengeluaranStokHeader::where('id', $request->pengeluaranstokheader_id)->first();

        try {
            
            $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
            ->where('format', '=', $pengeluaranstokheader->statusformat)
            ->first();
            $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
            
            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
            $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
            $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();

            if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                if (($pengeluaranstokheader->pengeluaranstok_id == $spk->text)||($pengeluaranstokheader->pengeluaranstok_id == $kor->text)||($pengeluaranstokheader->pengeluaranstok_id == $rtr->text)) {
                    $persediaan = $this->persediaan($pengeluaranstokheader->gudang_id,$pengeluaranstokheader->trado_id,$pengeluaranstokheader->gandengan_id);
                    $dari = $this->persediaanDari($request->stok_id,$persediaan['column'].'_id',$persediaan['value'],$request->qty);

                    if (!$dari) {
                        return [
                            'error' => true,
                            'errors' => [
                                "qty"=>"qty tidak cukup",
                                ] ,
                        ];
                    }
                }
            }
            $pengeluaranStokDetail = new PengeluaranStokDetail();
            $pengeluaranStokDetail->pengeluaranstokheader_id = $request->pengeluaranstokheader_id;
            $pengeluaranStokDetail->nobukti = $request->nobukti;
            $pengeluaranStokDetail->stok_id = $request->stok_id;
            $pengeluaranStokDetail->qty = $request->qty;
            $pengeluaranStokDetail->harga = $request->harga ?? 0;
            $pengeluaranStokDetail->nominaldiscount = $nominaldiscount;
            $pengeluaranStokDetail->total = $total ?? 0;
            $pengeluaranStokDetail->persentasediscount = $request->persentasediscount ?? 0;
            $pengeluaranStokDetail->vulkanisirke = $request->vulkanisirke ?? 0;
            $pengeluaranStokDetail->keterangan = $request->detail_keterangan;

            $pengeluaranStokDetail->modifiedby = auth('api')->user()->name;

            $pengeluaranStokDetail->save();



            
            // $spk = DB::table('parameter')->from(
            //     DB::raw("parameter with (readuncommitted)")
            // )
            //     ->where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')
            //     ->where('text', '=', $pengeluaranstokheader->pengeluaranstok_id)
            //     ->first();
            // if (isset($spk)) {
            //     // $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

            //     if ($pengeluaranstokheader->pengeluaranstok_id == $spk->text) {
            //         $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
            //         ->where('format', '=', $pengeluaranstokheader->statusformat)
            //         ->first();
                    
            //         $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
            //         if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            //             $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
            //             ->where($gudang['column'], $gudang['value'])->firstorFail();
            //         }
            //     }
            //     // return [
            //     //     'error' => true,
            //     //     'errors' => ['asd'=>$stokpersediaan]
            //     // ];

            //     goto prosesstokpersediaan;
            // }

            // $kor = DB::table('parameter')->from(
            //     DB::raw("parameter with (readuncommitted)")
            // )
            //     ->where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')
            //     ->where('text', '=', $pengeluaranstokheader->pengeluaranstok_id)
            //     ->first();

            // if (isset($kor)) {

            //     if ($pengeluaranstokheader->pengeluaranstok_id == $kor->text) {

            //         $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
            //             ->where('format', '=', $pengeluaranstokheader->statusformat)
            //             ->first();

            //         $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
            //         if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            //             $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
            //                 ->where($gudang['column'], $gudang['value'])->firstorFail();
            //         }
            //     }
            //     goto prosesstokpersediaan;
            // }

            // $rtr = DB::table('parameter')->from(
            //     DB::raw("parameter with (readuncommitted)")
            // )
            //     ->where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')
            //     ->where('text', '=', $pengeluaranstokheader->pengeluaranstok_id)
            //     ->first();

            // if (isset($rtr)) {
            //     if ($pengeluaranstokheader->pengeluaranstok_id == $rtr->text) {

            //         $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
            //             ->where('format', '=', $pengeluaranstokheader->statusformat)
            //             ->first();

            //         $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();
            //         if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
            //             $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $request->stok_id)
            //                 ->where($gudang['column'], $gudang['value'])->firstorFail();
            //         }
            //     }
            //     goto prosesstokpersediaan;
            // }


            // prosesstokpersediaan:;
            // // dd($spk);
            // $stokpersediaan->qty -= $request->qty;
            // // return [
            // //     'error' => true,
            // //     'errors' => ['asd'=>$stokpersediaan]
            // // ];
            //         $stokpersediaan->save();
               

            DB::commit();


            return [
                'error' => false,
                'id' => $pengeluaranStokDetail->id,
                'tabel' => $pengeluaranStokDetail->getTable(),
                'detail' => $pengeluaranStokDetail
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }

    public function persediaan($gudang,$trado,$gandengan)
    {
        $kolom = null;
        $value = 0;
        if(!empty($gudang)) {
            $kolom = "gudang";
            $value = $gudang;
          } elseif(!empty($trado)) {
            $kolom = "trado";
            $value = $trado;
          } elseif(!empty($gandengan)) {
            $kolom = "gandengan";
            $value = $gandengan;
          }
          return [
            "column"=>$kolom,
            "value"=>$value
        ];
    }

    public function persediaanDari($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty){ //check qty
            return false;
        }
        $result = $stokpersediaan->qty - $qty;
        $stokpersediaan->update(['qty'=> $result]);
        return $stokpersediaan;
    }
    public function persediaanKe($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            $stokpersediaangudang= StokPersediaan::create(["stok_id"=> $stokId, $persediaan => $persediaanId]);
        }
        $stokpersediaangudang->qty += $qty;
        $stokpersediaangudang->save();
        return $stokpersediaangudang;
    }
    public function checkTempat($stokId,$persediaan,$persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false :$result;
    }
}
