<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStokHeader;
use App\Models\PenerimaanStok;
use App\Models\Parameter;
use App\Models\StokPersediaan;
use App\Models\Stok;
use App\Http\Requests\StorePenerimaanStokDetailRequest;
use App\Http\Requests\UpdatePenerimaanStokDetailRequest;
use App\Models\HutangDetail;
use App\Models\HutangHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PenerimaanStokDetailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $penerimaanStokDetail = new PenerimaanStokDetail();
        return response([
            'data' => $penerimaanStokDetail->get(),
            'attributes' => [
                'totalRows' => $penerimaanStokDetail->totalRows,
                'totalPages' => $penerimaanStokDetail->totalPages,
                'totalNominal' => $penerimaanStokDetail->totalNominal
            ]
        ]);
    }


    public function hutang(): JsonResponse
    {
        $hutangDetail = new HutangDetail();

        $query = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))
            ->select(
                'id'
            )
            ->where('nobukti', request()->nobukti)->first();

        if (isset($query)) {
            $hutang_id = $query->id;
        } else {
            $hutang_id = 0;
        }

        // dd($hutang_id);
        if ($hutang_id != 0) {
            if (request()->nobukti != 'false' && request()->nobukti != null) {
                $fetch = HutangHeader::from(DB::raw("hutangheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
                request()->hutang_id = $fetch->id;
                // dd('test');
                return response()->json([
                    'data' => $hutangDetail->get(request()->hutang_id),
                    'attributes' => [
                        'totalRows' => $hutangDetail->totalRows,
                        'totalPages' => $hutangDetail->totalPages,
                        'totalNominal' => $hutangDetail->totalNominal
                    ]
                ]);
            } else {
                return response()->json([
                    'data' => [],
                    'attributes' => [
                        'totalRows' => $hutangDetail->totalRows,
                        'totalPages' => $hutangDetail->totalPages,
                        'totalNominal' => 0
                    ]
                ]);
            }
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $hutangDetail->totalRows,
                    'totalPages' => $hutangDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }


    public function store(StorePenerimaanStokDetailRequest $request)
    {
        DB::beginTransaction();
        $validator = Validator::make(
            $request->all(),
            [
                'stok_id' => [
                    'required',
                    Rule::unique('penerimaanstokdetail')->where(function ($query) use ($request) {
                        return $query->where('penerimaanstokheader_id', $request->penerimaanstokheader_id);
                    })
                ],
                'penerimaanstokheader_id' => 'required',
                'detail_keterangan' => 'required',
            ],
            [
                'stok_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'stok_id.unique' => ':attribute' . ' ' . app(ErrorController::class)->geterror('spi')->keterangan,
                'penerimaanstokheader_id.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
                'detail_keterangan.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            ],
            [
                'stok_id' => 'stok',
                'persentasediscount' => 'persentase discount',
                'detail_keterangan' => 'detail keterangan',
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
        $penerimaanstokheader = PenerimaanStokHeader::where('id', $request->penerimaanstokheader_id)->first();
        try {
            $stok = Stok::where('id', $request->stok_id)->first();
            $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();

            $reuse = false;
            if ($stok->statusreuse == $stokreuse->id) {
                $reuse = true;
            }

            $datahitungstok = PenerimaanStok::select('statushitungstok as statushitungstok_id')
                ->where('format', '=', $penerimaanstokheader->statusformat)
                ->first();
            $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

            $do = Parameter::where('grp', 'DO STOK')->where('subgrp', 'DO STOK')->first();
            $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();
            $kor = Parameter::where('grp', 'KOR STOK')->where('subgrp', 'KOR STOK')->first();
            $spbs = Parameter::where('grp', 'REUSE STOK')->where('subgrp', 'REUSE STOK')->first();
            $pg = Parameter::where('grp', 'PG STOK')->where('subgrp', 'PG STOK')->first();
            // dd($datahitungstok->statushitungstok_id);
            if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                if (($penerimaanstokheader->penerimaanstok_id == $spb->text) || $penerimaanstokheader->penerimaanstok_id == $kor->text) {
                    $persediaan = $this->persediaan($penerimaanstokheader->gudang_id, $penerimaanstokheader->trado_id, $penerimaanstokheader->gandengan_id);
                    $this->persediaanKe($request->stok_id, $persediaan['column'] . '_id', $persediaan['value'], $request->qty);
                }

                if (($penerimaanstokheader->penerimaanstok_id == $spbs->text) || ($penerimaanstokheader->penerimaanstok_id == $do->text)) {
                    $persediaanDari = $this->persediaan($penerimaanstokheader->gudangdari_id, $penerimaanstokheader->tradodari_id, $penerimaanstokheader->gandengandari_id);
                    $dari = $this->persediaanDari($request->stok_id, $persediaanDari['column'] . '_id', $persediaanDari['value'], $request->qty);

                    if (!$dari) {
                        return [
                            'error' => true,
                            'errors' => [
                                "qty" => "qty tidak cukup",
                            ],
                        ];
                    }
                    $persediaanKe = $this->persediaan($penerimaanstokheader->gudangke_id, $penerimaanstokheader->tradoke_id, $penerimaanstokheader->gandenganke_id);
                    $ke = $this->persediaanKe($request->stok_id, $persediaanKe['column'] . '_id', $persediaanKe['value'], $request->qty);
                }


                if ($penerimaanstokheader->penerimaanstok_id == $pg->text) {
                    if ($reuse) {
                        $persediaanDari = $this->persediaan($penerimaanstokheader->gudangdari_id, $penerimaanstokheader->tradodari_id, $penerimaanstokheader->gandengandari_id);
                        $dari = $this->persediaanDari($request->stok_id, $persediaanDari['column'] . '_id', $persediaanDari['value'], $request->qty);

                        if (!$dari) {
                            return [
                                'error' => true,
                                'errors' => [
                                    "qty" => "qty tidak cukup",
                                ],
                            ];
                        }
                        $persediaanKe = $this->persediaan($penerimaanstokheader->gudangke_id, $penerimaanstokheader->tradoke_id, $penerimaanstokheader->gandenganke_id);
                        $ke = $this->persediaanKe($request->stok_id, $persediaanKe['column'] . '_id', $persediaanKe['value'], $request->qty);
                    }
                }
            }

            $penerimaanStokDetail = new PenerimaanStokDetail();
            $penerimaanStokDetail->penerimaanstokheader_id = $request->penerimaanstokheader_id;
            $penerimaanStokDetail->nobukti = $request->nobukti;
            $penerimaanStokDetail->stok_id = $request->stok_id;
            $penerimaanStokDetail->qty = $request->qty;
            $penerimaanStokDetail->harga = $request->harga;
            $penerimaanStokDetail->nominaldiscount = $nominaldiscount;
            $penerimaanStokDetail->total = $total;
            $penerimaanStokDetail->penerimaanstok_nobukti = $request->detail_penerimaanstoknobukti;
            $penerimaanStokDetail->persentasediscount = $request->persentasediscount ?? 0;
            $penerimaanStokDetail->vulkanisirke = $request->vulkanisirke ?? '';
            $penerimaanStokDetail->keterangan = $request->detail_keterangan;

            $penerimaanStokDetail->modifiedby = auth('api')->user()->name;

            $penerimaanStokDetail->save();

            DB::commit();
            return [
                'error' => false,
                'id' => $penerimaanStokDetail->id,
                'tabel' => $penerimaanStokDetail->getTable(),
                'detail' => $penerimaanStokDetail
            ];
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
        }
    }

    public function addrow(StorePenerimaanStokDetailRequest $request)
    {
        return true;
    }
    public function deleterow(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'detail' => 'required',
            ],
            [
                'detail.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,
            ],
            [
                'detail' => 'stok',
            ],
        );
        if (!$validator->passes()) {
            return [
                'error' => true,
                'errors' => $validator->messages()
            ];
        }
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        $penerimaanStokDetail = PenerimaanStokDetail::where('penerimaanstokdetail.id',$request->detail)
        ->select('penerimaanstokheader.nobukti','penerimaanstokheader.penerimaanstok_id','penerimaanstokdetail.penerimaanstokheader_id','penerimaanstokdetail.stok_id')
        ->leftJoin('penerimaanstokheader', 'penerimaanstokdetail.penerimaanstokheader_id', 'penerimaanstokheader.id')
        ->first();
        if ($penerimaanStokDetail->penerimaanstok_id == $spb->text) {
            $validasiSPBMinus = (new PenerimaanStokDetail())->validasiSPBMinus(
                $penerimaanStokDetail->penerimaanstokheader_id,
                $penerimaanStokDetail->stok_id,
                0,
            );
            return $validasiSPBMinus;
        }
        return true;
    }

    public function persediaanDari($stokId, $persediaan, $persediaanId, $qty)
    {

        //check kartu stok
        $stok = db::table('kartustok')->from(db::raw("kartustok a with (readuncommitted)"))
            ->select(
                db::raw("sum(isnull(qtymasuk,0)-isnull(qtykeluar,0)) as qty")
            )
            ->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first()
            ->qty ?? 0;

        if ($stok == 0) {
            return false;
        }
        if ($qty > $stok) {
            return false;
        }

        // $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        // if (!$stokpersediaangudang) {
        //     return false;
        // }
        // $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        // if ($qty > $stokpersediaan->qty) { //
        //     return false;
        // }
        // $result = $stokpersediaan->qty - $qty;
        // $stokpersediaan->update(['qty' => $result]);
        // return $stokpersediaan;
        return true;
    }
    public function persediaanKe($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            $stokpersediaangudang = StokPersediaan::create(["stok_id" => $stokId, $persediaan => $persediaanId]);
        }
        $stokpersediaangudang->qty += $qty;
        $stokpersediaangudang->save();
        return $stokpersediaangudang;
    }

    public function persediaan($gudang, $trado, $gandengan)
    {
        $kolom = null;
        $value = 0;
        if (!empty($gudang)) {
            $kolom = "gudang";
            $value = $gudang;
        } elseif (!empty($trado)) {
            $kolom = "trado";
            $value = $trado;
        } elseif (!empty($gandengan)) {
            $kolom = "gandengan";
            $value = $gandengan;
        }
        return [
            "column" => $kolom,
            "value" => $value
        ];
    }

    public function checkTempat($stokId, $persediaan, $persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false : $result;
    }
}
