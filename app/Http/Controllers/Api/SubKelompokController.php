<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\SubKelompok;
use App\Http\Requests\StoreSubKelompokRequest;
use App\Http\Requests\UpdateSubKelompokRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;


class SubKelompokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $subKelompok = new SubKelompok();

        return response([
            'data' => $subKelompok->get(),
            'attributes' => [
                'totalRows' => $subKelompok->totalRows,
                'totalPages' => $subKelompok->totalPages
            ]
        ]);
    }

    public function default()
    {
        $subKelompok = new SubKelompok();
        return response([
            'status' => true,
            'data' => $subKelompok->default()
        ]);
    }
    public function show($id)
    {
        $subKelompok = SubKelompok::select('subkelompok.*', 'kelompok.keterangan as kelompok')->leftJoin('kelompok', 'subkelompok.kelompok_id', 'kelompok.id')->where('subkelompok.id', $id)->first();
        return response([
            'status' => true,
            'data' => $subKelompok
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreSubKelompokRequest $request)
    {

   
        DB::beginTransaction();

        try {
            $subKelompok = new SubKelompok();
            $subKelompok->kodesubkelompok = $request->kodesubkelompok;
            $subKelompok->keterangan = $request->keterangan;
            $subKelompok->kelompok_id = $request->kelompok_id;
            $subKelompok->statusaktif = $request->statusaktif;
            $subKelompok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($subKelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($subKelompok->getTable()),
                    'postingdari' => 'ENTRY PARAMETER',
                    'idtrans' => $subKelompok->id,
                    'nobuktitrans' => $subKelompok->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $subKelompok->toArray(),
                    'modifiedby' => $subKelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($subKelompok, $subKelompok->getTable());
            $subKelompok->position = $selected->position;
            $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $subKelompok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSubKelompokRequest $request, SubKelompok $subKelompok)
    {
        DB::beginTransaction();
        try {
            $subKelompok->kodesubkelompok = $request->kodesubkelompok;
            $subKelompok->keterangan = $request->keterangan;
            $subKelompok->kelompok_id = $request->kelompok_id;
            $subKelompok->statusaktif = $request->statusaktif;
            $subKelompok->modifiedby = auth('api')->user()->name;

            if ($subKelompok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($subKelompok->getTable()),
                    'postingdari' => 'EDIT PARAMETER',
                    'idtrans' => $subKelompok->id,
                    'nobuktitrans' => $subKelompok->id,
                    'aksi' => 'EDIT',
                    'datajson' => $subKelompok->toArray(),
                    'modifiedby' => $subKelompok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($subKelompok, $subKelompok->getTable());
            $subKelompok->position = $selected->position;
            $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $subKelompok
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

        $subKelompok = new SubKelompok();
        $subKelompok = $subKelompok->lockAndDestroy($id);
        if ($subKelompok) {
            $logTrail = [
                'namatabel' => strtoupper($subKelompok->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $subKelompok->id,
                'nobuktitrans' => $subKelompok->id,
                'aksi' => 'DELETE',
                'datajson' => $subKelompok->toArray(),
                'modifiedby' => $subKelompok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($subKelompok, $subKelompok->getTable(), true);
            $subKelompok->position = $selected->position;
            $subKelompok->id = $selected->id;
            $subKelompok->page = ceil($subKelompok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $subKelompok
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    /**
     * @ClassName
     */
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $subKelompoks = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Subkelompok',
                'index' => 'kodesubkelompok',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Kelompok',
                'index' => 'kelompok_id',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Sub Kelompok', $subKelompoks, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('subkelompok')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
