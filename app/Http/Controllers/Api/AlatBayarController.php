<?php

namespace App\Http\Controllers\Api;

use App\Models\AlatBayar;
use App\Models\Bank;
use App\Http\Requests\StoreAlatBayarRequest;
use App\Http\Requests\UpdateAlatBayarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

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

    public function default()
    {
        $alatBayar = new AlatBayar();
        return response([
            'status' => true,
            'data' => $alatBayar->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreAlatBayarRequest $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        try {
            $statusCair = Parameter::where('grp', 'STATUS LANGSUNG CAIR')->where('text', 'TIDAK LANGSUNG CAIR')->first();
            $controller = new ErrorController;
            if ($request->statuslangsungcair == $statusCair->id) {


                $request->validate(
                    [
                        'coa' => [
                            "required"
                        ]
                    ],
                    [
                        'coa.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,

                    ],
                    [
                        'coa' => 'kode perkiraan',
                    ],
                );
            }


            $alatbayar = new AlatBayar();
            $alatbayar->kodealatbayar = $request->kodealatbayar;
            $alatbayar->namaalatbayar = $request->namaalatbayar;
            $alatbayar->keterangan = $request->keterangan;
            $alatbayar->statuslangsungcair = $request->statuslangsungcair;
            $alatbayar->statusdefault = $request->statusdefault;
            $alatbayar->bank_id = $request->bank_id;
            $alatbayar->coa = $request->coa ?? '';
            $alatbayar->statusaktif = $request->statusaktif;
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

            throw $th;
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
            $statusCair = Parameter::where('grp', 'STATUS LANGSUNG CAIR')->where('text', 'TIDAK LANGSUNG CAIR')->first();

            if ($request->statuslangsungcair == $statusCair->id) {
                $request->validate(
                    [
                        'coa' => [
                            "required"
                        ]
                    ],
                    [
                        'coa.required' => ':attribute' . ' ' . app(ErrorController::class)->geterror('WI')->keterangan,

                    ],
                    [
                        'coa' => 'kode perkiraan',
                    ],
                );
            }
            $alatbayar->kodealatbayar = $request->kodealatbayar;
            $alatbayar->namaalatbayar = $request->namaalatbayar;
            $alatbayar->keterangan = $request->keterangan;
            $alatbayar->statuslangsungcair = $request->statuslangsungcair;
            $alatbayar->statusdefault = $request->statusdefault;
            $alatbayar->bank_id = $request->bank_id;
            $alatbayar->coa = $request->coa ?? '';
            $alatbayar->statusaktif = $request->statusaktif;
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
            }
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

        $alatBayar = new AlatBayar();
        $alatBayar = $alatBayar->lockAndDestroy($id);

        if ($alatBayar) {
            $logTrail = [
                'namatabel' => strtoupper($alatBayar->getTable()),
                'postingdari' => 'DELETE ALATBAYAR',
                'idtrans' => $alatBayar->id,
                'nobuktitrans' => $alatBayar->id,
                'aksi' => 'DELETE',
                'datajson' => $alatBayar->toArray(),
                'modifiedby' => $alatBayar->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($alatBayar, $alatBayar->getTable(), true);
            $alatBayar->position = $selected->position;
            $alatBayar->id = $selected->id;
            $alatBayar->page = ceil($alatBayar->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $alatBayar
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
            'langsungcair' => Parameter::where(['grp' => 'status langsung cair'])->get(),
            'statusdefault' => Parameter::where(['grp' => 'status default'])->get(),
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
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
