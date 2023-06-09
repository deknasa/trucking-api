<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreErrorRequest;
use App\Http\Requests\UpdateErrorRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\LogTrailController;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\Error;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ErrorController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $error = new Error();

        return response([
            'data' => $error->get(),
            'attributes' => [
                'totalRows' => $error->totalRows,
                'totalPages' => $error->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreErrorRequest $request)
    {
        DB::beginTransaction();
        try {
            $error = new Error();
            $error->kodeerror = $request->kodeerror;
            $error->keterangan = $request->keterangan;
            $error->modifiedby = auth('api')->user()->name;

            if ($error->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($error->getTable()),
                    'postingdari' => 'ENTRY ERROR',
                    'idtrans' => $error->id,
                    'nobuktitrans' => $error->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $error->toArray(),
                    'modifiedby' => $error->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($error, $error->getTable());
            $error->position = $selected->position;
            $error->page = ceil($error->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $error
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show(Error $error)
    {
        return response([
            'status' => true,
            'data' => $error
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateErrorRequest $request, Error $error)
    {
        DB::beginTransaction();
        try {
            $error->kodeerror = $request->kodeerror;
            $error->keterangan = $request->keterangan;
            $error->modifiedby = auth('api')->user()->name;

            if ($error->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($error->getTable()),
                    'postingdari' => 'EDIT ERROR',
                    'idtrans' => $error->id,
                    'nobuktitrans' => $error->id,
                    'aksi' => 'EDIT',
                    'datajson' => $error->toArray(),
                    'modifiedby' => $error->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($error, $error->getTable());
            $error->position = $selected->position;
            $error->page = ceil($error->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $error
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $error = new Error();
        $error = $error->lockAndDestroy($id);
        if ($error) {
            $logTrail = [
                'namatabel' => strtoupper($error->getTable()),
                'postingdari' => 'DELETE ERROR',
                'idtrans' => $error->id,
                'nobuktitrans' => $error->id,
                'aksi' => 'DELETE',
                'datajson' => $error->toArray(),
                'modifiedby' => $error->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($error, $error->getTable(), true);
            $error->position = $selected->position;
            $error->id = $selected->id;
            $error->page = ceil($error->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $error
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
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
            $errors = $decodedResponse['data'];

            $judulLaporan = $errors[0]['judulLaporan'];

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Error',
                    'index' => 'kodeerror',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
            ];

            $this->toExcel($judulLaporan, $errors, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('error')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function geterror($kodeerror)
    {
        // dd($request->aco_id);

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->id();
            $table->string('keterangan', 50)->nullable();
        });

        DB::table($temp)->insert(
            [
                'keterangan' => 'kode error belum terdaftar',
            ]
        );

        if (Error::select('keterangan')
            ->where('kodeerror', '=', $kodeerror)
            ->exists()
        ) {
            $data = Error::select('keterangan')
                ->where('kodeerror', '=', $kodeerror)
                ->first();
        } else {
            $data = DB::table($temp)
                ->select('keterangan')
                ->first();
        }

        return $data;
    }
}
