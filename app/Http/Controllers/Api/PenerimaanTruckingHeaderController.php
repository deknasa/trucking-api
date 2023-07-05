<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanTruckingHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetPenerimaanTruckingHeaderRequest;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PenerimaanTruckingTruckingHeader;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;

class PenerimaanTruckingHeaderController extends Controller
{

      /**
     * @ClassName 
     * PenerimaanTruckingHeader
     * @Detail1 PenerimaanTruckingDetailController
     */
    public function index(GetPenerimaanTruckingHeaderRequest $request)
    {
        $penerimaantruckingheader = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaantruckingheader->get(),
            'attributes' => [
                'totalRows' => $penerimaantruckingheader->totalRows,
                'totalPages' => $penerimaantruckingheader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePenerimaanTruckingHeaderRequest $request)
    {

        DB::beginTransaction();
        try {
            /* Store header */
            $penerimaanTruckingHeader = (new PenerimaanTruckingHeader())->processStore($request->all());
            /* Set position and page */
            $penerimaanTruckingHeader->position = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable())->position;
            $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTruckingHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = PenerimaanTruckingHeader::findAll($id);
        $detail = PenerimaanTruckingDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePenerimaanTruckingHeaderRequest $request, PenerimaanTruckingHeader $penerimaantruckingheader)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            // PenerimaanTruckingHeader::findOrFail($id);
            $penerimaanTruckingHeader = (new PenerimaanTruckingHeader())->processUpdate($penerimaantruckingheader, $request->all());
            /* Set position and page */
            $penerimaanTruckingHeader->position = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable())->position;
            $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTruckingHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName
     */
    public function destroy(DestroyPenerimaanTruckingHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerimaanTruckingHeader = (new PenerimaanTruckingHeader())->processDestroy($id, "PENERIMAAN TRUCKING HEADER");
            $selected = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable(), true);
            $penerimaanTruckingHeader->position = $selected->position;
            $penerimaanTruckingHeader->id = $selected->id;
            $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanTruckingHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPengembalianPinjaman($id, $aksi)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $getSupir = $penerimaanTrucking->find($id);
        if ($aksi == 'edit') {
            $data = $penerimaanTrucking->getPengembalianPinjaman($id, $getSupir->supir_id);
        } else {
            $data = $penerimaanTrucking->getDeletePengembalianPinjaman($id, $getSupir->supir_id);
        }
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getPinjaman($supir_id)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaanTrucking->getPinjaman($supir_id)
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanTruckingHeader = PenerimaanTruckingHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanTruckingHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanTruckingHeader->statuscetak = $statusSudahCetak->id;
                $penerimaanTruckingHeader->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanTruckingHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanTruckingHeader->jumlahcetak = $penerimaanTruckingHeader->jumlahcetak + 1;
                if ($penerimaanTruckingHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanTruckingHeader->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN TRUCKING HEADER',
                        'idtrans' => $penerimaanTruckingHeader->id,
                        'nobuktitrans' => $penerimaanTruckingHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanTruckingHeader->toArray(),
                        'modifiedby' => $penerimaanTruckingHeader->modifiedby
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
        $penerimaanTrucking = PenerimaanTruckingHeader::find($id);
        $statusdatacetak = $penerimaanTrucking->statuscetak;
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

    public function cekValidasiAksi($id)
    {
        $penerimaan = new PenerimaanTruckingHeader();
        $nobukti = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader"))->where('id', $id)->first();
        $cekdata = $penerimaan->cekvalidasiaksi($nobukti->penerimaan_nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaantruckingheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    { }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $penerimaantruckingheader = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaantruckingheader->getExport($id)
        ]);
    }
}
