<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOutHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\Parameter;
use App\Models\Error;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\StoreServiceOutDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateServiceOutHeaderRequest;
use App\Models\LogTrail;
use App\Models\ServiceInHeader;
use App\Models\ServiceOutDetail;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use Illuminate\Http\JsonResponse;

class ServiceOutHeaderController extends Controller
{

    /**
     * @ClassName 
     * ServiceOutHeader
     * @Detail1 ServiceOutDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $serviceout = new ServiceOutHeader();

        return response([
            'data' => $serviceout->get(),
            'attributes' => [
                'totalRows' => $serviceout->totalRows,
                'totalPages' => $serviceout->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StoreServiceOutHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'trado_id' => $request->trado_id,
                'tglkeluar' => date('Y-m-d', strtotime($request->tglkeluar)),
                'servicein_nobukti' => $request->servicein_nobukti,
                'keterangan_detail' => $request->keterangan_detail
            ];

            $serviceOutHeader = (new ServiceOutHeader())->processStore($data);
            $serviceOutHeader->position = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable())->position;
            $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $serviceOutHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = ServiceOutHeader::findAll($id);
        $detail = ServiceOutDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdateServiceOutHeaderRequest $request, ServiceOutHeader $serviceoutheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'trado_id' => $request->trado_id ?? 0,
                'tglkeluar' => date('Y-m-d', strtotime($request->tglkeluar)) ?? '',
                'servicein_nobukti' => $request->servicein_nobukti ?? '',
                'keterangan_detail' => $request->keterangan_detail ?? ''
            ];

            $serviceoutheader = (new ServiceOutHeader())->processUpdate($serviceoutheader, $data);
            $serviceoutheader->position = $this->getPosition($serviceoutheader, $serviceoutheader->getTable())->position;
            $serviceoutheader->page = ceil($serviceoutheader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $serviceoutheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            $serviceOutHeader = (new ServiceOutHeader())->processDestroy($id);
            $selected = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable(), true);
            $serviceOutHeader->position = $selected->position;
            $serviceOutHeader->id = $selected->id;
            $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $serviceOutHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceOutHeader::findOrFail($id);
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
    public function combo(Request $request)
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
            'serviceout' => ServiceOutDetail::all(),
            'servicein' => ServiceInHeader::all()
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceoutheader')->getColumns();

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
    {
    }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $serviceOut = new ServiceOutHeader();
        return response([
            'data' => $serviceOut->getExport($id)
        ]);
    }
}
