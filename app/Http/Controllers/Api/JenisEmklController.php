<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisEmkl;
use App\Http\Requests\StoreJenisEmklRequest;
use App\Http\Requests\UpdateJenisEmklRequest;
use App\Http\Requests\DestroyJenisEmklRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class JenisEmklController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenisemkl = new JenisEmkl();

        return response([
            'data' => $jenisemkl->get(),
            'attributes' => [
                'totalRows' => $jenisemkl->totalRows,
                'totalPages' => $jenisemkl->totalPages
            ]
        ]);
    }
    public function cekValidasi($id)
    {
        $jenisEmkl = new JenisEmkl();
        $cekdata = $jenisEmkl->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
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

    public function default()
    {
        $jenisEmkl = new JenisEmkl();
        return response([
            'status' => true,
            'data' => $jenisEmkl->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreJenisEmklRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenisemkl = new JenisEmkl();
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan ?? '';
            $jenisemkl->statusaktif = $request->statusaktif;
            $jenisemkl->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'ENTRY JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable());
            $jenisemkl->position = $selected->position;
            $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenisemkl
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(JenisEmkl $jenisemkl)
    {
        return response([
            'status' => true,
            'data' => $jenisemkl
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateJenisEmklRequest $request, JenisEmkl $jenisemkl)
    {
        DB::beginTransaction();
        try {
            $jenisemkl->kodejenisemkl = $request->kodejenisemkl;
            $jenisemkl->keterangan = $request->keterangan ?? '';
            $jenisemkl->modifiedby = auth('api')->user()->name;
            $jenisemkl->statusaktif = $request->statusaktif;

            if ($jenisemkl->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jenisemkl->getTable()),
                    'postingdari' => 'EDIT JENISEMKL',
                    'idtrans' => $jenisemkl->id,
                    'nobuktitrans' => $jenisemkl->id,
                    'aksi' => 'EDIT',
                    'datajson' => $jenisemkl->toArray(),
                    'modifiedby' => $jenisemkl->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($jenisemkl, $jenisemkl->getTable());
            $jenisemkl->position = $selected->position;
            $jenisemkl->page = ceil($jenisemkl->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $jenisemkl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyJenisEmklRequest $request, $id)
    {
        DB::beginTransaction();

        $jenisEmkl = new JenisEmkl();
        $jenisEmkl = $jenisEmkl->lockAndDestroy($id);
        if ($jenisEmkl) {
            $logTrail = [
                'namatabel' => strtoupper($jenisEmkl->getTable()),
                'postingdari' => 'DELETE JENISEMKL',
                'idtrans' => $jenisEmkl->id,
                'nobuktitrans' => $jenisEmkl->id,
                'aksi' => 'DELETE',
                'datajson' => $jenisEmkl->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($jenisEmkl, $jenisEmkl->getTable(), true);
            $jenisEmkl->position = $selected->position;
            $jenisEmkl->id = $selected->id;
            $jenisEmkl->page = ceil($jenisEmkl->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenisEmkl
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenisemkl')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo()
    {
        $jenisemkls = JenisEmkl::where('statusaktif', '=', 1)
            ->get();

        dd($jenisemkls);
        return response([
            'data' => $jenisemkls
        ]);
    }
    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $jenisemkls = $decodedResponse['data'];

        $judulLaporan = $jenisemkls[0]['judulLaporan'];

        $i = 0;
        foreach ($jenisemkls as $index => $params) {

            $statusaktif = $params['statusaktif'];

            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $jenisemkls[$i]['statusaktif'] = $statusaktif;


            $i++;
        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Kode Jenis EMKL',
                'index' => 'kodejenisemkl',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel($judulLaporan, $jenisemkls, $columns);
    }
    }
}
