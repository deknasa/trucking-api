<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokHeader;
use App\Models\PengeluaranStokDetail;
use App\Models\PenerimaanStokDetail;
use App\Models\PenerimaanStokHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\HutangHeader;
use App\Models\PengeluaranStokDetailFifo;
use App\Models\StokPersediaan;
use App\Models\Stok;
use App\Models\Bank;
use App\Models\Error;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\GetIndexRangeRequest;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranStokHeaderRequest;
use App\Http\Requests\UpdatePengeluaranStokHeaderRequest;
use App\Http\Requests\DestroyPengeluaranStokHeaderRequest;
use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\StorePengeluaranStokDetailFifoRequest;
use App\Http\Requests\StoreHutangBayarHeaderRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanDetail;
use App\Models\HutangBayarHeader;
use App\Models\HutangBayarDetail;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;


class PengeluaranStokHeaderController extends Controller
{
    /**
     * @ClassName 
     * PengeluaranStokHeader
     * @Detail PengeluaranStokDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        if ($request->reload == '') {

            $pengeluaranStokHeader = new PengeluaranStokHeader();
            $pengeluaranStokHeader->updateApproval();
            return response([
                'data' => $pengeluaranStokHeader->get(),
                'attributes' => [
                    'totalRows' => $pengeluaranStokHeader->totalRows,
                    'totalPages' => $pengeluaranStokHeader->totalPages
                ]
            ]);
        } else {
            return response([
                'data' => [],
                'attributes' => [
                    'totalRows' => [],
                    'totalPages' => []
                ]
            ]);
        }
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranStokHeaderRequest $request)
    {
        DB::beginTransaction();
        try {


            $data = [
                "tglbukti" => $request->tglbukti,
                "pengeluaranstok" => $request->pengeluaranstok,
                "pengeluaranstok_id" => $request->pengeluaranstok_id,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
                "pengeluarantrucking_nobukti" => $request->pengeluarantrucking_nobukti,
                "supplier" => $request->supplier,
                "supplier_id" => $request->supplier_id,
                "kerusakan" => $request->kerusakan,
                "kerusakan_id" => $request->kerusakan_id,
                "supir" => $request->supir,
                "supir_id" => $request->supir_id,
                "servicein_nobukti" => $request->servicein_nobukti,
                "trado" => $request->trado,
                "trado_id" => $request->trado_id,
                "gudang" => $request->gudang,
                "gudang_id" => $request->gudang_id,
                "gandengan" => $request->gandengan,
                "gandengan_id" => $request->gandengan_id,
                "statuspotongretur" => $request->statuspotongretur,
                "bank" => $request->bank,
                "bank_id" => $request->bank_id,
                "tglkasmasuk" => $request->tglkasmasuk,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,

                "detail_stok" => $request->detail_stok,
                "detail_stok_id" => $request->detail_stok_id,
                "jlhhari" => $request->jlhhari,
                "detail_statusoli" => $request->detail_statusoli,
                "detail_vulkanisirke" => $request->detail_vulkanisirke,
                "detail_keterangan" => $request->detail_keterangan,
                "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
                "detail_qty" => $request->detail_qty ?? $request->qty_afkir,
                "detail_harga" => $request->detail_harga,
                "detail_persentasediscount" => $request->detail_persentasediscount,
                "totalItem" => $request->totalItem,
            ];
            /* Store header */
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processStore($data);
            /* Set position and page */
            $pengeluaranStokHeader->position = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / (10));
            } else {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranStokHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranStokHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengeluaranStokHeader->find($id),
            'detail' => PengeluaranStokDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    {

        DB::beginTransaction();

        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "pengeluaranstok" => $request->pengeluaranstok,
                "pengeluaranstok_id" => $request->pengeluaranstok_id,
                "penerimaanstok_nobukti" => $request->penerimaanstok_nobukti,
                "pengeluarantrucking_nobukti" => $request->pengeluarantrucking_nobukti,
                "pengeluaranstok_nobukti" => $request->pengeluaranstok_nobukti,
                "supplier" => $request->supplier,
                "supplier_id" => $request->supplier_id,
                "kerusakan" => $request->kerusakan,
                "kerusakan_id" => $request->kerusakan_id,
                "supir" => $request->supir,
                "supir_id" => $request->supir_id,
                "servicein_nobukti" => $request->servicein_nobukti,
                "trado" => $request->trado,
                "trado_id" => $request->trado_id,
                "gudang" => $request->gudang,
                "gudang_id" => $request->gudang_id,
                "gandengan" => $request->gandengan,
                "gandengan_id" => $request->gandengan_id,
                "statuspotongretur" => $request->statuspotongretur,
                "bank" => $request->bank,
                "bank_id" => $request->bank_id,
                "tglkasmasuk" => $request->tglkasmasuk,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,

                "detail_stok" => $request->detail_stok,
                "detail_stok_id" => $request->detail_stok_id,
                "jlhhari" => $request->jlhhari,
                "detail_statusoli" => $request->detail_statusoli,
                "detail_vulkanisirke" => $request->detail_vulkanisirke,
                "detail_statusban" => ($request->statusban) ? $request->statusban : $request->detail_statusban,
                "detail_qty" => $request->detail_qty ?? $request->qty_afkir,
                "detail_keterangan" => $request->detail_keterangan,
                "detail_harga" => $request->detail_harga,
                "detail_persentasediscount" => $request->detail_persentasediscount,
                "totalItem" => $request->totalItem,
            ];
            // dd($data['detail_statusban']);
            /* Store header */
            $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processUpdate($pengeluaranStokHeader, $data);
            /* Set position and page */
            $pengeluaranStokHeader->position = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / (10));
            } else {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }

            $pengeluaranStokHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranStokHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyPengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable(), true);
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / (10));
            } else {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranStokHeader->tgldariheader = date('Y-m-01', strtotime($pengeluaranStokHeader->tglbukti));
            $pengeluaranStokHeader->tglsampaiheader = date('Y-m-t', strtotime($pengeluaranStokHeader->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStokHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function storePenerimaan($penerimaanHeader)
    {
        try {


            $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
            $header = app(PenerimaanHeaderController::class)->store($penerimaan);


            return [
                'status' => true,
                'data' => $header->original['data'],
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storePembayaranHutang($penerimaanHeader)
    {
        try {
            $penerimaan = new StoreHutangBayarHeaderRequest($penerimaanHeader);
            $header = app(HutangBayarHeaderController::class)->store($penerimaan);
            return [
                'status' => true,
                'data' => $header->original['data'],
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
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

    public function cekvalidasi($id)
    {
        // $pengeluaran = PengeluaranStokHeader::findOrFail($id);
        $pengeluaran  = new PengeluaranStokHeader();
        $pengeluaran  = $pengeluaran->findOrFail($id);


        // cek hak
        $pengeluaranstok_id= $pengeluaran->pengeluaranstok_id;
        $aco_id=db::table("pengeluaranstok")->from(db::raw("pengeluaranstok a with (readuncommitted)"))
        ->select(
            'a.aco_id'
        )->where('a.id', $pengeluaranstok_id)
        ->first()->aco_id ?? 0;

        $user_id=auth('api')->user()->id;
        $user=auth('api')->user()->user;
        $role=db::table("userrole")->from(db::raw("userrole a with (readuncommitted)"))
        ->select(
            'a.id'
        )
        ->join(db::raw("acl b with (readuncommitted)"),'a.role_id','b.role_id')
        ->where('a.user_id',$user_id)
        ->where('b.aco_id',$aco_id)
        // ->tosql();
        ->first();

    
        $passes = true;
        // dd($role);
        if (!isset($role)) {
            $acl=db::table('useracl')->from(db::raw("useracl a with (readuncommitted)"))
            ->select(
                'a.id'
            )->where('a.user_id',$user_id)
            ->where('a.aco_id',$aco_id)
            ->first();

            if (!isset($acl)) {
                $query = DB::table('error')
                ->select(db::raw("'USER " .$user ." '+keterangan as keterangan"))
                ->where('kodeerror', '=', 'TPH')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => $keterangan,
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ]; 
                  $passes = false;
                  return response($data);
                  goto selesai;
            }
        } 

        // 

        $aksi = request()->aksi ?? '';
        if ($aksi == 'PRINTER BESAR' || $aksi == 'PRINTER KECIL') {

            $peneimaan = $pengeluaran->findOrFail($id);
            $statusdatacetak = $peneimaan->statuscetak;
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
            if ($statusdatacetak == $statusCetak->id) {
                $query = DB::table('error')
                    ->select('keterangan')
                    ->where('kodeerror', '=', 'SDC')
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
            } else {
                $data = [
                    'message' => '',
                    'errors' => 'bisa',
                    'kodestatus' => '0',
                    'kodenobukti' => '1'
                ];
            }

            return response($data);
        } else {

            if ($pengeluaran->isInUsed($id)) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select('keterangan')
                    ->whereRaw("kodeerror = 'SATL'")
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'Penerimaan stok',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
                return response($data);
            }
            if ($pengeluaran->isNobuktiApprovedJurnal($id)) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select(DB::raw("keterangan + ' (APPROVAL JURNAL)' as keterangan"))
                    ->whereRaw("kodeerror = 'SAP'")
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'Penerimaan stok',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
                return response($data);
            }
            if ($pengeluaran->isKMTApprovedJurnal($id)) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select(DB::raw("keterangan + ' (APPROVAL JURNAL)' as keterangan"))
                    ->whereRaw("kodeerror = 'SAP'")
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'Penerimaan stok',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
                return response($data);
            }
            if ($pengeluaran->isPPHApprovedJurnal($id)) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select(DB::raw("keterangan + ' (APPROVAL JURNAL)' as keterangan"))
                    ->whereRaw("kodeerror = 'SAP'")
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'APPROVAL JURNAL',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
                return response($data);
            }
            $printValidation = $pengeluaran->printValidation($id);
            if ($printValidation) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select('keterangan')
                    ->whereRaw("kodeerror = 'SDC'")
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];

                return response($data);
            } 
            $todayValidation = $pengeluaran->todayValidation($pengeluaran->tglbukti);
            if (!$todayValidation) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select('keterangan')
                    ->whereRaw("kodeerror = 'SDC'")
                    ->get();
                // $keterangan = $query['0'];
                $keterangan = ['keterangan' => 'transaksi Sudah berbeda tanggal']; //$query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];

            } 
            $isEditAble = $pengeluaran->isEditAble($id);
            $isKeteranganEditAble = $pengeluaran->isKeteranganEditAble($id);
            if ((!$isEditAble)||(!$isKeteranganEditAble)) {
                $query = Error::from(DB::raw("error with (readuncommitted)"))
                    ->select('keterangan')
                    ->whereRaw("kodeerror = 'SDC'")
                    ->get();
                // $keterangan = $query['0'];
                $keterangan = ['keterangan' => 'Transaksi Tidak Bisa diedit']; //$query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah cetak',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];
            }
            
        //    dd($pengeluaran->tglbukti,$isEditAble,$printValidation);

            if ($todayValidation || (($isEditAble || $isKeteranganEditAble) && !$printValidation)) {
                $data = [
                    'message' => '',
                    'errors' => 'bisa',
                    'kodestatus' => '0',
                    'kodenobukti' => '1'
                ];
            }else {
                return response($data);
            }
            

            return response($data);
            selesai:;
        }
    }

    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();

        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];

            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $detail = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($detail);
                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detailLog[] = $datadetails['detail']->toArray();
            }
            // dd($detail);
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = PengeluaranStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranStokHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranStokHeader->statuscetak = $statusSudahCetak->id;
                $pengeluaranStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaranStokHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranStokHeader->jumlahcetak = $pengeluaranStokHeader->jumlahcetak + 1;
                if ($pengeluaranStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN STOK HEADER',
                        'idtrans' => $pengeluaranStokHeader->id,
                        'nobuktitrans' => $pengeluaranStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranStokHeader->toArray(),
                        'modifiedby' => $pengeluaranStokHeader->modifiedby
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }


            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export()
    {
    }


    /**
     * @ClassName 
     * @Keterangan APPROVAL EDIT DATA
     */
    public function approvalEdit($id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranStokHeader = PengeluaranStokheader::lockForUpdate()->findOrFail($id);

            $statusBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $statusTidakBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($pengeluaranStokHeader->statusapprovaledit == $statusBolehEdit->id) {
                $pengeluaranStokHeader->statusapprovaledit = $statusTidakBolehEdit->id;
                $pengeluaranStokHeader->tglbatasedit = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
                $tglbatasedit = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
                $pengeluaranStokHeader->tglbatasedit = $tglbatasedit;
                $pengeluaranStokHeader->statusapprovaledit = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }
            $pengeluaranStokHeader->tglapprovaledit = date("Y-m-d", strtotime('today'));
            $pengeluaranStokHeader->userapprovaledit = auth('api')->user()->name;

            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'UN/APPROVED EDIT',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $pengeluaranStokHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL EDIT KETERANGAN
     */
    public function approvalEditKeterangan($id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranStokHeader = PengeluaranStokheader::lockForUpdate()->findOrFail($id);

            $statusBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
            $statusTidakBolehEdit = DB::table('pengeluaranstokheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            // statusapprovaleditabsensi,tglapprovaleditabsensi,userapprovaleditabsensi 
            if ($pengeluaranStokHeader->statusapprovaleditketerangan == $statusBolehEdit->id) {
                $pengeluaranStokHeader->statusapprovaleditketerangan = $statusTidakBolehEdit->id;
                $pengeluaranStokHeader->tglbataseditketerangan = null;
                $aksi = $statusTidakBolehEdit->text;
            } else {
                $jambatas = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('text')->where('grp', '=', 'JAMBATASAPPROVAL')->where('subgrp', '=', 'JAMBATASAPPROVAL')->first();
                $tglbatasedit = date('Y-m-d') . ' ' . $jambatas->text ?? '00:00:00';
                $pengeluaranStokHeader->tglbataseditketerangan = $tglbatasedit;
                $pengeluaranStokHeader->statusapprovaleditketerangan = $statusBolehEdit->id;
                $aksi = $statusBolehEdit->text;
            }
            $pengeluaranStokHeader->tglapprovaleditketerangan = date("Y-m-d", strtotime('today'));
            $pengeluaranStokHeader->userapprovaleditketerangan = auth('api')->user()->name;

            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'UN/APPROVED EDIT KETERANGAN',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => $aksi,
                    'datajson' => $pengeluaranStokHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     * @Keterangan SPK 
     */
    public function pengeluaranstokspkstok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan RETUR
     */
    public function pengeluaranstokreturstok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan KOREKSI STOK MINUS
     */
    public function pengeluaranstokkoreksistok()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENJUALAN STOK AFKIR
     */
    public function pengeluaranstokpenjualanstokafkir()
    {
    }
    /**
     * @ClassName 
     * @Keterangan SPAREPART GANTUNG
     */
    public function pengeluaranstoksparepartgantungtrucking()
    {
    }
    /**
     * @ClassName 
     * @Keterangan KOREKSI VULKAN MINUS
     */
    public function pengeluaranstokkoreksivulkan()
    {
    }
    /**
     * @ClassName
     * @Keterangan STATUS AFKIR 
     */
    public function pengeluaranstoksetstatusafkir()
    {
    }

        /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }
}
