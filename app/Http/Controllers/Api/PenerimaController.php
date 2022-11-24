<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Penerima;
use App\Http\Requests\StorePenerimaRequest;
use App\Http\Requests\UpdatePenerimaRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class PenerimaController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $penerima = new Penerima();

        return response([
            'data' => $penerima->get(),
            'attributes' => [
                'totalRows' => $penerima->totalRows,
                'totalPages' => $penerima->totalPages
            ]
        ]);
    }

    public function show(Penerima $penerima)
    {
        return response([
            'status' => true,
            'data' => $penerima
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePenerimaRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerima = new Penerima();
            $penerima->namapenerima = $request->namapenerima;
            $penerima->npwp = $request->npwp;
            $penerima->noktp = $request->noktp;
            $penerima->statusaktif = $request->statusaktif;
            $penerima->statuskaryawan = $request->statuskaryawan;
            $penerima->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            if ($penerima->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerima->getTable()),
                    'postingdari' => 'ENTRY PENERIMA',
                    'idtrans' => $penerima->id,
                    'nobuktitrans' => $penerima->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerima->toArray(),
                    'modifiedby' => $penerima->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($penerima, $penerima->getTable());
            $penerima->position = $selected->position;
            $penerima->page = ceil($penerima->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerima
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
    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaRequest $request, Penerima $penerima)
    {
        DB::beginTransaction();

        try {
            $penerima->namapenerima = $request->namapenerima;
            $penerima->npwp = $request->npwp;
            $penerima->noktp = $request->noktp;
            $penerima->statusaktif = $request->statusaktif;
            $penerima->statuskaryawan = $request->statuskaryawan;
            $penerima->modifiedby = auth('api')->user()->name;

            if ($penerima->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerima->getTable()),
                    'postingdari' => 'EDIT PENERIMA',
                    'idtrans' => $penerima->id,
                    'nobuktitrans' => $penerima->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerima->toArray(),
                    'modifiedby' => $penerima->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($penerima, $penerima->getTable());
                $penerima->position = $selected->position;
                $penerima->page = ceil($penerima->position / ($request->limit ?? 10));

                if (isset($request->limit)) {
                    $penerima->page = ceil($penerima->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $penerima
                ]);
            } else {
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Penerima $penerima, Request $request)
    {
        DB::beginTransaction();

        $delete = $penerima->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($penerima->getTable()),
                'postingdari' => 'DELETE PENERIMA',
                'idtrans' => $penerima->id,
                'nobuktitrans' => $penerima->id,
                'aksi' => 'DELETE',
                'datajson' => $penerima->toArray(),
                'modifiedby' => $penerima->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($penerima, $penerima->getTable(), true);
            $penerima->position = $selected->position;
            $penerima->id = $selected->id;
            $penerima->page = ceil($penerima->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerima
            ]);
        } else {
            DB::rollBack();
            
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerima')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $penerimas = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Nama Penerima',
                'index' => 'namapenerima',
            ],
            [
                'label' => 'NPWP',
                'index' => 'npwp',
            ],
            [
                'label' => 'No KTP',
                'index' => 'noktp',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
            [
                'label' => 'Status Karyawan',
                'index' => 'statuskaryawan',
            ],
        ];

        $this->toExcel('Penerima', $penerimas, $columns);
    }
}
