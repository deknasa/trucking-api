<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokDetail;
use App\Models\PenerimaanStokDetail;
use App\Models\PengeluaranStokHeader;
use App\Models\Parameter;
use App\Models\StokPersediaan;
use App\Models\HutangBayarHeader;
use App\Models\HutangBayarDetail;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranDetail;
use App\Models\JurnalUmumDetail;
use App\Models\PengeluaranStokDetailFifo;
use App\Models\Stok;

use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\UpdatePengeluaranStokDetailRequest;
use App\Models\PelunasanHutangDetail;
use App\Models\PelunasanHutangHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class PengeluaranStokDetailController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $pengeluaranStokDetail = new PengeluaranStokDetail();
        if ($request->penerimaanstokheader_id != '' && $request->penerimaanstokheader_id != 0 || $request->penerimaanstokheader_id && 'undefined') {

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
        return response([
            'data' => $pengeluaranStokDetail->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStokDetail->totalRows,
                'totalPages' => $pengeluaranStokDetail->totalPages,
                'totalNominal' => $pengeluaranStokDetail->totalNominal

            ]
        ]);
    }

    public function hutangbayar(): JsonResponse
    {
        $PelunasanHutangDetail = new PelunasanHutangDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {
            $fetch = PelunasanHutangHeader::from(DB::raw("pelunasanhutangheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
            request()->PelunasanHutang_id = $fetch->id;
            return response()->json([
                'data' => $PelunasanHutangDetail->get(request()->PelunasanHutang_id),
                'attributes' => [
                    'totalRows' => $PelunasanHutangDetail->totalRows,
                    'totalPages' => $PelunasanHutangDetail->totalPages,
                    'totalNominal' => $PelunasanHutangDetail->totalNominal
                ]
            ]);
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $PelunasanHutangDetail->totalRows,
                    'totalPages' => $PelunasanHutangDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }
    public function pengeluaran(): JsonResponse
    {
        $pengeluaranDetail = new PengeluaranDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {
            $HutangBayar = PelunasanHutangHeader::from(DB::raw("pelunasanhutangheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();

            $fetch = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $HutangBayar->pengeluaran_nobukti)->first();
            if (isset($fetch)) {
                request()->pengeluaran_id = $fetch->id;
                return response()->json([
                    'data' => $pengeluaranDetail->get(request()->pengeluaran_id),
                    'attributes' => [
                        'totalRows' => $pengeluaranDetail->totalRows,
                        'totalPages' => $pengeluaranDetail->totalPages,
                        'totalNominal' => $pengeluaranDetail->totalNominal
                    ]
                ]);
            } else {
                return response()->json([
                    'data' => [],
                    'attributes' => [
                        'totalRows' => $pengeluaranDetail->totalRows,
                        'totalPages' => $pengeluaranDetail->totalPages,
                        'totalNominal' => $pengeluaranDetail->totalNominal
                    ]
                ]);
            }
        } else {
            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $pengeluaranDetail->totalRows,
                    'totalPages' => $pengeluaranDetail->totalPages,
                    'totalNominal' => 0
                ]
            ]);
        }
    }

    public function jurnal(): JsonResponse
    {
        $jurnalDetail = new JurnalUmumDetail();
        if (request()->nobukti != 'false' && request()->nobukti != null) {
            if (request()->statuspotong == 219) { //penerimaan
                $nobukti = request()->nobukti;

                return response()->json([
                    'data' => $jurnalDetail->jurnalForRetur($nobukti, request()->statuspotong),
                    'attributes' => [
                        'totalRows' => $jurnalDetail->totalRows,
                        'totalPages' => $jurnalDetail->totalPages,
                        'totalNominalDebet' => $jurnalDetail->totalNominalDebet,
                        'totalNominalKredit' => $jurnalDetail->totalNominalKredit,
                    ]
                ]);
            } elseif (request()->statuspotong == 220) { // potong hutangbayar
                $hutangBayar = PelunasanHutangHeader::from(DB::raw("pelunasanhutangheader with (readuncommitted)"))->where('nobukti', request()->nobukti)->first();
                $pengeluaranHeader = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $hutangBayar->pengeluaran_nobukti)->first();
                if (isset($pengeluaranHeader)) {
                    $nobukti = $pengeluaranHeader->nobukti ?? '';
                } else {
                    $pengeluaranstokbukti = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                        ->select('nobukti')->where('hutangbayar_nobukti', request()->nobukti)
                        ->first()->nobukti ?? '';
                    $nobukti = $pengeluaranstokbukti;
                }
            }

            return response()->json([
                'data' => $jurnalDetail->getJurnalFromAnotherTable($nobukti),
                'attributes' => [
                    'totalRows' => $jurnalDetail->totalRows,
                    'totalPages' => $jurnalDetail->totalPages,
                    'totalNominalDebet' => $jurnalDetail->totalNominalDebet,
                    'totalNominalKredit' => $jurnalDetail->totalNominalKredit,
                ]
            ]);
        } else {

            return response()->json([
                'data' => [],
                'attributes' => [
                    'totalRows' => $jurnalDetail->totalRows,
                    'totalPages' => $jurnalDetail->totalPages,
                    'totalNominalDebet' => 0,
                    'totalNominalKredit' => 0,
                ]
            ]);
        }
    }

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

        $stok = Stok::where('id', $request->stok_id)->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();

        $reuse = false;
        if ($stok->statusreuse == $stokreuse->id) {
            $reuse = true;
        }


        try {

            $datahitungstok = PengeluaranStok::select('statushitungstok as statushitungstok_id')
                ->where('format', '=', $pengeluaranstokheader->statusformat)
                ->first();
            $statushitungstok = Parameter::where('grp', 'STATUS HITUNG STOK')->where('text', 'HITUNG STOK')->first();

            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
            $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
            $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

            if ($datahitungstok->statushitungstok_id == $statushitungstok->id) {
                if ($pengeluaranstokheader->pengeluaranstok_id == $kor->text) {
                    $persediaan = $this->persediaan($pengeluaranstokheader->gudang_id, $pengeluaranstokheader->trado_id, $pengeluaranstokheader->gandengan_id);
                    $dari = $this->persediaanDari($request->stok_id, $persediaan['column'] . '_id', $persediaan['value'], $request->qty);
                } else {
                    $dari = $this->persediaanDari($request->stok_id, 'gudang_id', $gudangkantor->text, $request->qty);
                }
                if (!$dari) {
                    return [
                        'error' => true,
                        'errors' => [
                            "qty" => "qty tidak cukup",
                        ],
                    ];
                }
                if (($pengeluaranstokheader->pengeluaranstok_id != $spk->text)) {
                    if (!$reuse) {
                        return [
                            'error' => true,
                            'errors' => [
                                "stok" => "bukan stok reuse",
                            ],
                        ];
                    }
                }
                if ($pengeluaranstokheader->pengeluaranstok_id != ($kor->text || $rtr->text)) {
                    $persediaan = $this->persediaan($pengeluaranstokheader->gudang_id, $pengeluaranstokheader->trado_id, $pengeluaranstokheader->gandengan_id);
                    $ke = $this->persediaanKe($request->stok_id, $column, $value, $request->qty);
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
    public function addrow(StorePengeluaranStokDetailRequest $request)
    {
        return true;
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

    public function persediaanDari($stokId, $persediaan, $persediaanId, $qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId, $persediaan, $persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty) { //check qty
            return false;
        }
        $result = $stokpersediaan->qty - $qty;
        $stokpersediaan->update(['qty' => $result]);
        return $stokpersediaan;
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
    public function checkTempat($stokId, $persediaan, $persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false : $result;
    }


    public function resetQtyPenerimaan($id)
    {
        $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);

        // $pengeluaranStokHeader = PengeluaranStokHeader::find($id);
        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        $kor = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')->first();
        $rtr = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')->first();
        $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
        $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();

        foreach ($pengeluaranStokDetail as $detail) {
            /*Update  di stok persediaan*/
            $dari = true;
            if ($pengeluaranStokHeader->pengeluaranstok_id != ($kor->text || $rtr->text)) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $dari = $this->persediaanDari($detail->stok_id, $column, $value, $detail->qty);
            }

            if (!$dari) {
                return [
                    'error' => true,
                    'errors' => [
                        "qty" => "qty tidak cukup",
                    ],
                ];
            }
            if ($pengeluaranStokHeader->pengeluaranstok_id == $kor->text) {
                $persediaan = $this->persediaan($pengeluaranStokHeader->gudang_id, $pengeluaranStokHeader->trado_id, $pengeluaranStokHeader->gandengan_id);
                $ke = $this->persediaanKe($detail->stok_id, $persediaan['column'] . '_id', $persediaan['value'], $detail->qty);
            } else {
                $ke = $this->persediaanKe($detail->stok_id, 'gudang_id', $gudangkantor->text, $detail->qty);
            }
        }

        $pengeluaranStokDetailFifo = PengeluaranStokDetailFifo::where('nobukti', $pengeluaranStokHeader->nobukti)->get();
        foreach ($pengeluaranStokDetailFifo as $fifo) {
            $penerimaanStok = PenerimaanStokDetail::where('nobukti', $fifo->penerimaanstokheader_nobukti)->where('stok_id', $fifo->stok_id)->first();
            $penerimaanStok->qtykeluar -= $fifo->qty;
            $penerimaanStok->save();
        }
    }
}
