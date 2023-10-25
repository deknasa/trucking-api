<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PindahBuku;
use App\Http\Requests\StorePindahBukuRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePindahBukuRequest;
use App\Models\Bank;
use App\Models\Error;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PindahBukuController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $pindahBuku = new PindahBuku();

        return response([
            'data' => $pindahBuku->get(),
            'attributes' => [
                'totalRows' => $pindahBuku->totalRows,
                'totalPages' => $pindahBuku->totalPages,
            ]
        ]);
    }


    public function default()
    {
        $pindahBuku = new PindahBuku();
        return response([
            'status' => true,
            'data' => $pindahBuku->default(),
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePindahBukuRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'bankdari_id' => $request->bankdari_id,
                'bankke_id' => $request->bankke_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ];
            $pindahBuku = (new PindahBuku())->processStore($data);
            $pindahBuku->position = $this->getPosition($pindahBuku, $pindahBuku->getTable())->position;
            if ($request->limit == 0) {
                $pindahBuku->page = ceil($pindahBuku->position / (10));
            } else {
                $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));
            }
            $pindahBuku->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pindahBuku->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pindahBuku
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $pindahBuku = new PindahBuku();
        return response([
            'data' => $pindahBuku->findAll($id)
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePindahBukuRequest $request, PindahBuku $pindahbuku)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'bankdari_id' => $request->bankdari_id,
                'bankke_id' => $request->bankke_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ];
            $pindahBuku = (new PindahBuku())->processUpdate($pindahbuku, $data);
            $pindahBuku->position = $this->getPosition($pindahBuku, $pindahBuku->getTable())->position;
            if ($request->limit == 0) {
                $pindahBuku->page = ceil($pindahBuku->position / (10));
            } else {
                $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));
            }
            $pindahBuku->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pindahBuku->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $pindahBuku
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pindahBuku = (new PindahBuku())->processDestroy($id, 'DELETE PINDAH BUKU');
            $selected = $this->getPosition($pindahBuku, $pindahBuku->getTable(), true);
            $pindahBuku->position = $selected->position;
            $pindahBuku->id = $selected->id;
            if ($request->limit == 0) {
                $pindahBuku->page = ceil($pindahBuku->position / (10));
            } else {
                $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));
            }
            $pindahBuku->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pindahBuku->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pindahBuku
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
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

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => $header['postingdari'],
                'idtrans' =>  $jurnals->original['idlogtrail'],
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

    /**
     * @ClassName 
     */
    public function report()
    {
    }

    /**
     * @ClassName
     */
    public function export($id)
    {
        $pindahBuku = new PindahBuku();
        return response([
            'data' => $pindahBuku->getExport($id)
        ]);
    }
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pindahBuku = PindahBuku::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pindahBuku->statuscetak != $statusSudahCetak->id) {
                $pindahBuku->statuscetak = $statusSudahCetak->id;
                $pindahBuku->tglbukacetak = date('Y-m-d H:i:s');
                $pindahBuku->userbukacetak = auth('api')->user()->name;
                $pindahBuku->jumlahcetak = $pindahBuku->jumlahcetak + 1;
                if ($pindahBuku->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pindahBuku->getTable()),
                        'postingdari' => 'PRINT PINDAH BUKU',
                        'idtrans' => $pindahBuku->id,
                        'nobuktitrans' => $pindahBuku->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pindahBuku->toArray(),
                        'modifiedby' => $pindahBuku->modifiedby
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

    
    public function cekvalidasi($id)
    {
        $pengeluaran = PindahBuku::find($id);
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        if ($statusdatacetak == $statusCetak->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
