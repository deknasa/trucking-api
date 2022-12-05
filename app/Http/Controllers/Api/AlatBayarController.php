<?php

namespace App\Http\Controllers\Api;

use App\Models\AlatBayar;
use App\Models\Bank;
use App\Http\Requests\StoreAlatBayarRequest;
use App\Http\Requests\UpdateAlatBayarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class AlatBayarController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $alatbayar = new AlatBayar();

        return response([
            'data' => $alatbayar->get(),
            'attributes' => [
                'totalRows' => $alatbayar->totalRows,
                'totalPages' => $alatbayar->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreAlatBayarRequest $request)
    {
        DB::beginTransaction();
        try {
            $alatbayar = new AlatBayar();
            $alatbayar->kodealatbayar = $request->kodealatbayar;
            $alatbayar->namaalatbayar = $request->namaalatbayar;
            $alatbayar->keterangan = $request->keterangan;
            $alatbayar->statuslangsunggcair = $request->statuslangsunggcair;
            $alatbayar->statusdefault = $request->statusdefault;
            $alatbayar->bank_id = $request->bank_id;
            $alatbayar->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($alatbayar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($alatbayar->getTable()),
                    'postingdari' => 'ENTRY ALATBAYAR',
                    'idtrans' => $alatbayar->id,
                    'nobuktitrans' => $alatbayar->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $alatbayar->toArray(),
                    'modifiedby' => $alatbayar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($alatbayar, $alatbayar->getTable());
            $alatbayar->position = $selected->position;
            $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $alatbayar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show($id)
    {
        $data = AlatBayar::find($id);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAlatBayarRequest $request, AlatBayar $alatbayar)
    {
        DB::beginTransaction();
        try {
            $alatbayar->kodealatbayar = $request->kodealatbayar;
            $alatbayar->namaalatbayar = $request->namaalatbayar;
            $alatbayar->keterangan = $request->keterangan;
            $alatbayar->statuslangsunggcair = $request->statuslangsunggcair;
            $alatbayar->statusdefault = $request->statusdefault;
            $alatbayar->bank_id = $request->bank_id;
            $alatbayar->modifiedby = auth('api')->user()->name;

            if ($alatbayar->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($alatbayar->getTable()),
                    'postingdari' => 'EDIT ALATBAYAR',
                    'idtrans' => $alatbayar->id,
                    'nobuktitrans' => $alatbayar->id,
                    'aksi' => 'EDIT',
                    'datajson' => $alatbayar->toArray(),
                    'modifiedby' => $alatbayar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }
            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($alatbayar, $alatbayar->getTable());
            $alatbayar->position = $selected->position;
            $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $alatbayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(AlatBayar $alatbayar, Request $request)
    {
        DB::beginTransaction();
        try {
            $delete = AlatBayar::destroy($alatbayar->id);
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($alatbayar->getTable()),
                    'postingdari' => 'DELETE ALATBAYAR',
                    'idtrans' => $alatbayar->id,
                    'nobuktitrans' => $alatbayar->id,
                    'aksi' => 'DELETE',
                    'datajson' => $alatbayar->toArray(),
                    'modifiedby' => $alatbayar->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

            }
            DB::commit();

            $selected = $this->getPosition($alatbayar, $alatbayar->getTable(), true);
            $alatbayar->position = $selected->position;
            $alatbayar->id = $selected->id;
            $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $alatbayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'langsungcair' => Parameter::where(['grp' => 'status langsung cair'])->get(),
            'statusdefault' => Parameter::where(['grp' => 'status default'])->get(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('alatbayar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
