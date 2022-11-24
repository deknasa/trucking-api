<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanStok;
use App\Http\Requests\StorePenerimaanStokRequest;
use App\Http\Requests\UpdatePenerimaanStokRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class PenerimaanStokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $penerimaanStok = new PenerimaanStok();
        return response([
            'data' => $penerimaanStok->get(),
            'attributes' => [
                'totalRows' => $penerimaanStok->totalRows,
                'totalPages' => $penerimaanStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StorePenerimaanStokRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerimaanStok = new PenerimaanStok();
            $penerimaanStok->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanStok->keterangan = $request->keterangan;
            $penerimaanStok->coa = $request->coa;
            $penerimaanStok->statusformat = $request->statusformat;
            $penerimaanStok->statushitungstok = $request->statushitungstok;
            $penerimaanStok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            if ($penerimaanStok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanStok->getTable()),
                    'postingdari' => 'ENTRY PENERIMAAN STOK',
                    'idtrans' => $penerimaanStok->id,
                    'nobuktitrans' => $penerimaanStok->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaanStok->toArray(),
                    'modifiedby' => $penerimaanStok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($penerimaanStok, $penerimaanStok->getTable());
            $penerimaanStok->position = $selected->position;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $penerimaanStok->page = ceil($penerimaanStok->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStok
            ], 201);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PenerimaanStok $penerimaanStok,$id)
    {
        $penerimaanStok = new PenerimaanStok();
        return response([
            'data' => $penerimaanStok->find($id),
            'attributes' => [
                'totalRows' => $penerimaanStok->totalRows,
                'totalPages' => $penerimaanStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaanStokRequest $request, PenerimaanStok $penerimaanStok,$id)
    {
        DB::beginTransaction();
        try {
            $penerimaanStok = PenerimaanStok::where('id',$id)->first();
            $penerimaanStok->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanStok->keterangan = $request->keterangan;
            $penerimaanStok->coa = $request->coa;
            $penerimaanStok->statusformat = $request->statusformat;
            $penerimaanStok->statushitungstok = $request->statushitungstok;
            $penerimaanStok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerimaanStok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanStok->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN STOK',
                    'idtrans' => $penerimaanStok->id,
                    'nobuktitrans' => $penerimaanStok->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanStok->toArray(),
                    'modifiedby' => $penerimaanStok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($penerimaanStok, $penerimaanStok->getTable());
            $penerimaanStok->position = $selected->position;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $penerimaanStok->page = ceil($penerimaanStok->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaanstok')->getColumns();

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
    public function destroy(PenerimaanStok $penerimaanStok,$id)
    {
        DB::beginTransaction();

        $penerimaanStok = PenerimaanStok::where('id',$id)->first();
        $delete = $penerimaanStok->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($penerimaanStok->getTable()),
                'postingdari' => 'DELETE PENERIMAAN STOK',
                'idtrans' => $penerimaanStok->id,
                'nobuktitrans' => $penerimaanStok->id,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanStok->toArray(),
                'modifiedby' => $penerimaanStok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($penerimaanStok, $penerimaanStok->getTable(), true);
            $penerimaanStok->position = $selected->position;
            $penerimaanStok->id = $selected->id;
            $penerimaanStok->page = ceil($penerimaanStok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanStok
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        header('Access-Control-Allow-Origin: *');

        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $penerimaan = $decodedResponse['data'];
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'id',
                'index' => 'id',
            ],
            [
                'label' => 'kode penerimaan',
                'index' => 'kodepenerimaan',
            ],
            [
                'label' => 'keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'coa',
                'index' => 'coa',
            ],
            [
                'label' => 'status format',
                'index' => 'statusformat',
            ],
            [
                'label' => 'status hitung stok',
                'index' => 'statushitungstok',
            ],
            [
                'label' => 'modifiedby',
                'index' => 'modifiedby',
            ],
        ];
        $this->toExcel('Parameter', $penerimaan, $columns);
    }
}
