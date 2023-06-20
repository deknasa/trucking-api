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
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        return response([
            'data' => $pengeluaranStokHeader->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStokHeader->totalRows,
                'totalPages' => $pengeluaranStokHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokHeaderRequest $request)
    {
        DB::beginTransaction();
        try {


            $data =[
                "tglbukti" =>$request->tglbukti ,
                "pengeluaranstok" =>$request->pengeluaranstok ,
                "pengeluaranstok_id" =>$request->pengeluaranstok_id ,
                "penerimaanstok_nobukti" =>$request->penerimaanstok_nobukti ,
                "pengeluaranstok_nobukti" =>$request->pengeluaranstok_nobukti ,
                "supplier" =>$request->supplier ,
                "supplier_id" =>$request->supplier_id ,
                "kerusakan" =>$request->kerusakan ,
                "kerusakan_id" =>$request->kerusakan_id ,
                "supir" =>$request->supir ,
                "supir_id" =>$request->supir_id ,
                "servicein_nobukti" =>$request->servicein_nobukti ,
                "trado" =>$request->trado ,
                "trado_id" =>$request->trado_id ,
                "gudang" =>$request->gudang ,
                "gudang_id" =>$request->gudang_id ,
                "gandengan" =>$request->gandengan ,
                "gandengan_id" =>$request->gandengan_id ,
                "statuspotongretur" =>$request->statuspotongretur ,
                "bank" =>$request->bank ,
                "bank_id" =>$request->bank_id ,
                "tglkasmasuk" =>$request->tglkasmasuk ,
                "penerimaan_nobukti" =>$request->penerimaan_nobukti ,

                "detail_stok" => $request->detail_stok ,
                "detail_stok_id" => $request->detail_stok_id ,
                "detail_vulkanisirke" => $request->detail_vulkanisirke ,
                "detail_keterangan" => $request->detail_keterangan ,
                "detail_qty" => $request->detail_qty ,
                "detail_harga" => $request->detail_harga ,
                "detail_persentasediscount" => $request->detail_persentasediscount ,
                "totalItem" => $request->totalItem ,
                ];
            /* Store header */
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processStore($data);
            /* Set position and page */
            $pengeluaranStokHeader->position = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable())->position;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }

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
     */
    public function update(UpdatePengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    {

        DB::beginTransaction();

        try {
            $data =[
                "tglbukti" =>$request->tglbukti ,
                "pengeluaranstok" =>$request->pengeluaranstok ,
                "pengeluaranstok_id" =>$request->pengeluaranstok_id ,
                "penerimaanstok_nobukti" =>$request->penerimaanstok_nobukti ,
                "pengeluaranstok_nobukti" =>$request->pengeluaranstok_nobukti ,
                "supplier" =>$request->supplier ,
                "supplier_id" =>$request->supplier_id ,
                "kerusakan" =>$request->kerusakan ,
                "kerusakan_id" =>$request->kerusakan_id ,
                "supir" =>$request->supir ,
                "supir_id" =>$request->supir_id ,
                "servicein_nobukti" =>$request->servicein_nobukti ,
                "trado" =>$request->trado ,
                "trado_id" =>$request->trado_id ,
                "gudang" =>$request->gudang ,
                "gudang_id" =>$request->gudang_id ,
                "gandengan" =>$request->gandengan ,
                "gandengan_id" =>$request->gandengan_id ,
                "statuspotongretur" =>$request->statuspotongretur ,
                "bank" =>$request->bank ,
                "bank_id" =>$request->bank_id ,
                "tglkasmasuk" =>$request->tglkasmasuk ,
                "penerimaan_nobukti" =>$request->penerimaan_nobukti ,

                "detail_stok" => $request->detail_stok ,
                "detail_stok_id" => $request->detail_stok_id ,
                "detail_vulkanisirke" => $request->detail_vulkanisirke ,
                "detail_keterangan" => $request->detail_keterangan ,
                "detail_qty" => $request->detail_qty ,
                "detail_harga" => $request->detail_harga ,
                "detail_persentasediscount" => $request->detail_persentasediscount ,
                "totalItem" => $request->totalItem ,
                ];
                
            /* Store header */
            $pengeluaranStokHeader = PengeluaranStokHeader::findOrFail($id);
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processUpdate($pengeluaranStokHeader,$data);
            /* Set position and page */
            $pengeluaranStokHeader->position = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable())->position;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));
            }

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
     */
    public function destroy(Request $request,PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    // public function destroy(DestroyPengeluaranStokHeaderRequest $request,PengeluaranStokHeader $pengeluaranStokHeader, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = (new PengeluaranStokHeader())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable(), true);
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->id = $selected->id;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));

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
    
    public function storePembayaranHutang($penerimaanHeader){
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
    public function checkTempat($stokId,$persediaan,$persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false :$result;
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranStokHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
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
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
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
                        'postingdari' => 'PRINT INVOICE EXTRA',
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
}
