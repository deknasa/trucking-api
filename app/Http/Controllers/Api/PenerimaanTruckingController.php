<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PenerimaanTrucking;
use App\Http\Requests\StorePenerimaanTruckingRequest;
use App\Http\Requests\UpdatePenerimaanTruckingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanTruckingController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $penerimaanTrucking = new PenerimaanTrucking();

        return response([
            'data' => $penerimaanTrucking->get(),
            'attributes' => [
                'totalRows' => $penerimaanTrucking->totalRows,
                'totalPages' => $penerimaanTrucking->totalPages
            ]
        ]);
    }

    public function show(PenerimaanTrucking $penerimaanTrucking)
    {
        return response([
            'status' => true,
            'data' => $penerimaanTrucking
        ]);
    }

    /**
     * @ClassName 
     */
    // public function store(StorePenerimaanTruckingRequest $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $penerimaanTrucking = new PenerimaanTrucking();
    //         $penerimaanTrucking->keterangan = $request->keterangan;
    //         $penerimaanTrucking->coa = $request->coa;
    //         $penerimaanTrucking->modifiedby = auth('api')->user()->name;
    //         $request->sortname = $request->sortname ?? 'id';
    //         $request->sortorder = $request->sortorder ?? 'asc';

    //         if ($penerimaanTrucking->save()) {
    //             $logTrail = [
    //                 'namatabel' => strtoupper($penerimaanTrucking->getTable()),
    //                 'postingdari' => 'ENTRY PENERIMAAN TRUCKING',
    //                 'idtrans' => $penerimaanTrucking->id,
    //                 'nobuktitrans' => $penerimaanTrucking->id,
    //                 'aksi' => 'ENTRY',
    //                 'datajson' => $penerimaanTrucking->toArray(),
    //                 'modifiedby' => $penerimaanTrucking->modifiedby
    //             ];

    //             $validatedLogTrail = new StoreLogTrailRequest($logTrail);
    //             $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

    //             DB::commit();
    //         }

    //         /* Set position and page */
    //         $selected = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable());
    //         $penerimaanTrucking->position = $selected->position;
    //         $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));

    //         return response([
    //             'status' => true,
    //             'message' => 'Berhasil disimpan',
    //             'data' => $penerimaanTrucking
    //         ]);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }
    /**
     * @ClassName 
     */
    public function update(UpdatePenerimaanTruckingRequest $request, PenerimaanTrucking $penerimaanTrucking)
    {
        DB::beginTransaction();

        try {
            $penerimaanTrucking->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanTrucking->keterangan = $request->keterangan;
            $penerimaanTrucking->coa = $request->coa;
            $penerimaanTrucking->formatbukti = $request->formatbukti;
            $penerimaanTrucking->modifiedby = auth('api')->user()->name;

            if ($penerimaanTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN TRUCKING',
                    'idtrans' => $penerimaanTrucking->id,
                    'nobuktitrans' => $penerimaanTrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanTrucking->toArray(),
                    'modifiedby' => $penerimaanTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable());
                $penerimaanTrucking->position = $selected->position;
                $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $penerimaanTrucking
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(PenerimaanTrucking $penerimaanTrucking, Request $request)
    {
        DB::beginTransaction();

        $delete = $penerimaanTrucking->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                'postingdari' => 'DELETE PENERIMAAN TRUCKING',
                'idtrans' => $penerimaanTrucking->id,
                'nobuktitrans' => $penerimaanTrucking->id,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanTrucking->toArray(),
                'modifiedby' => $penerimaanTrucking->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable(), true);
            $penerimaanTrucking->position = $selected->position;
            $penerimaanTrucking->id = $selected->id;
            $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanTrucking
            ]);
        } else {
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
        $penerimaanTruckings = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Penerimaan',
                'index' => 'kodepenerimaan',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'COA',
                'index' => 'coa',
            ],
            [
                'label' => 'Format Bukti',
                'index' => 'formatbukti',
            ],
        ];

        $this->toExcel('Penerimaan Trucking', $penerimaanTruckings, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaantrucking')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
