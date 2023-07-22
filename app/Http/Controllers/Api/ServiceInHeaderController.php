<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\Parameter;
use App\Models\Error;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\DestroyServiceInHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateServiceInHeaderRequest;
use App\Models\ServiceInDetail;
use App\Http\Requests\GetIndexRangeRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreLogTrailRequest;

class ServiceInHeaderController extends Controller
{
     /**
     * @ClassName 
     * ServiceInHeaderHeader
     * @Detail1 ServiceInDetailController
     */
    public function index()
    {
        $serviceInHeader = new ServiceInHeader();

        return response([
            'data' => $serviceInHeader->get(),
            'attributes' => [
                'totalRows' => $serviceInHeader->totalRows,
                'totalPages' => $serviceInHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreServiceInHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'trado_id' => $request->trado_id,
                'tglmasuk' => $request->tglmasuk,
                'karyawan_id' => $request->karyawan_id,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $serviceInHeader = (new ServiceInHeader())->processStore($data);
            $serviceInHeader->position = $this->getPosition($serviceInHeader, $serviceInHeader->getTable())->position;
            $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $serviceInHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $serviceInHeader = (new ServiceInHeader)->findAll($id);
        $serviceInDetails = (new ServiceInDetail)->getAll($id);

        return response([
            'status' => true,
            'data' => $serviceInHeader,
            'detail' => $serviceInDetails
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateServiceInHeaderRequest $request, ServiceInHeader $serviceInHeader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'trado_id' => $request->trado_id,
                'tglmasuk' => $request->tglmasuk,
                'karyawan_id' => $request->karyawan_id,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $serviceInHeader = (new ServiceInHeader())->processUpdate($serviceInHeader, $data);
            $serviceInHeader->position = $this->getPosition($serviceInHeader, $serviceInHeader->getTable())->position;
            $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $serviceInHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroyServiceInHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $serviceInHeader = (new ServiceInHeader())->processDestroy($id);
            $selected = $this->getPosition($serviceInHeader, $serviceInHeader->getTable(), true);
            $serviceInHeader->position = $selected->position;
            $serviceInHeader->id = $selected->id;
            $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $serviceInHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceInHeader::findOrFail($id);
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

    public function combo()
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceinheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $serviceInHeader = ServiceInHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($serviceInHeader->statuscetak != $statusSudahCetak->id) {
                $serviceInHeader->statuscetak = $statusSudahCetak->id;
                $serviceInHeader->tglbukacetak = date('Y-m-d H:i:s');
                $serviceInHeader->userbukacetak = auth('api')->user()->name;
                $serviceInHeader->jumlahcetak = $serviceInHeader->jumlahcetak + 1;
                if ($serviceInHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($serviceInHeader->getTable()),
                        'postingdari' => 'PRINT SERVICE IN HEADER',
                        'idtrans' => $serviceInHeader->id,
                        'nobuktitrans' => $serviceInHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $serviceInHeader->toArray(),
                        'modifiedby' => $serviceInHeader->modifiedby
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
     */
    public function export($id)
    {
        $serviceInHeader = new ServiceInHeader();
        return response([
            'data' => $serviceInHeader->getExport($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
