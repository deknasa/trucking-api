<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanStok;
use App\Models\PenerimaanStokHeader;
use App\Models\PenerimaanStokDetail;
use App\Models\HutangHeader;
use App\Models\HutangDetail;


use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanStokHeaderRequest;
use App\Http\Requests\UpdatePenerimaanStokHeaderRequest;
use App\Http\Requests\DestroyPenerimaanStokHeaderRequest;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\DestroyHutangHeaderRequest;
use App\Http\Requests\UpdateHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\GetIndexRangeRequest;

use App\Models\Parameter;
use App\Models\Error;
use App\Models\Gudang;
use App\Models\StokPersediaan;

use App\Http\Requests\StorePenerimaanStokDetailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;


class PenerimaanStokHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(GetIndexRangeRequest $request)
    {
        $penerimaanStokHeader = new PenerimaanStokHeader();
        return response([
            'data' => $penerimaanStokHeader->get(),
            'attributes' => [
                'totalRows' => $penerimaanStokHeader->totalRows,
                'totalPages' => $penerimaanStokHeader->totalPages
            ]
        ]);
    }



    /**
     * @ClassName 
     */
    public function store(StorePenerimaanStokHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $penerimaanStokHeader = (new PenerimaanStokHeader())->processStore($request->all());
            /* Set position and page */
            $penerimaanStokHeader->position = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable())->position;
            $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStokHeader
            ], 201);    
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PenerimaanStokHeader $penerimaanStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $penerimaanStokHeader->find($id),
            'detail' => PenerimaanStokDetail::getAll($id),
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaanStokHeaderRequest $request, PenerimaanStokHeader $penerimaanStokHeader, $id): JsonResponse
    {
        try {

           /* Store header */
           $penerimaanStokHeader = PenerimaanStokHeader::findOrFail($id);
           $penerimaanStokHeader = (new PenerimaanStokHeader())->processUpdate($penerimaanStokHeader,$request->all());
           /* Set position and page */
           $penerimaanStokHeader->position = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable())->position;
           $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
           if (isset($request->limit)) {
               $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));
           }

           DB::commit();
           return response()->json([
               'message' => 'Berhasil disimpan',
               'data' => $penerimaanStokHeader
           ]);    
       } catch (\Throwable $th) {
           DB::rollBack();

           throw $th;
       }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyPenerimaanStokHeaderRequest $request,PenerimaanStokHeader $penerimaanStokHeader, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $penerimaanStokHeader = (new PenerimaanStokHeader())->processDestroy($id);
            $selected = $this->getPosition($penerimaanStokHeader, $penerimaanStokHeader->getTable(), true);
            $penerimaanStokHeader->position = $selected->position;
            $penerimaanStokHeader->id = $selected->id;
            $penerimaanStokHeader->page = ceil($penerimaanStokHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanStokHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function persediaan($gudang,$trado,$gandengan)
    {
        $kolom = null;
        $nama = null;
        $value = 0;
        if(!empty($gudang)) {
            $kolom = "gudang";
            $nama = "GUDANG";
            $value = $gudang;
        } elseif(!empty($trado)) {
            $kolom = "trado";
            $nama = "TRADO";
            $value = $trado;
        } elseif(!empty($gandengan)) {
            $kolom = "gandengan";
            $nama = "GANDENGAN";
            $value = $gandengan;
        }
        return [
            "column"=>$kolom,
            "value"=>$value,
            "nama"=>$nama,
        ];
    }

    public function persediaanDariReturn($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaan = StokPersediaan::lockForUpdate()->find($stokpersediaangudang->id);
        if ($qty > $stokpersediaan->qty){ //check qty
            return false;
        }
        $result = $stokpersediaan->qty + $qty;
        $stokpersediaan->update(['qty'=> $result]);
        return $stokpersediaan;
    }
    public function persediaanKeReturn($stokId,$persediaan,$persediaanId,$qty)
    {
        $stokpersediaangudang = $this->checkTempat($stokId,$persediaan,$persediaanId); //stok persediaan 
        if (!$stokpersediaangudang) {
            return false;
        }
        $stokpersediaangudang->qty -= $qty;
        $stokpersediaangudang->save();
        return $stokpersediaangudang;
    }

    public function checkTempat($stokId,$persediaan,$persediaanId)
    {
        $result = StokPersediaan::lockForUpdate()->where("stok_id", $stokId)->where("$persediaan", $persediaanId)->first();
        return (!$result) ? false :$result;
    }



    public function cekvalidasi($id)
    {
        $penerimaanStokHeader  = new PenerimaanStokHeader();

        $peneimaan = $penerimaanStokHeader->findOrFail($id);
        $status = $peneimaan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $peneimaan->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $spb = Parameter::where('grp', 'SPB STOK')->where('subgrp', 'SPB STOK')->first();

        // dd($penerimaanStokHeader->isOutUsed($id));
        
        if ($penerimaanStokHeader->isOutUsed($id)) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SATL'")
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'Pengeluaran stok',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            return response($data);
        }

        if ($penerimaanStokHeader->isEhtUsed($id)) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SATL'")
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'Hutang',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];
            return response($data);
        }
        
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
        } else if ($penerimaanStokHeader->isPOUsed($id)) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SATL'")
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
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanStokHeader = PenerimaanStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanStokHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanStokHeader->statuscetak = $statusSudahCetak->id;
                $penerimaanStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanStokHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanStokHeader->jumlahcetak = $penerimaanStokHeader->jumlahcetak + 1;
                if ($penerimaanStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanStokHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA',
                        'idtrans' => $penerimaanStokHeader->id,
                        'nobuktitrans' => $penerimaanStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanStokHeader->toArray(),
                        'modifiedby' => $penerimaanStokHeader->modifiedby
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaanstokheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
