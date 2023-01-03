<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTrucking;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranTruckingRequest;
use App\Http\Requests\UpdatePengeluaranTruckingRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranTruckingController extends Controller
{
   /**
     * @ClassName 
     */
    public function index()
    {
        $pengeluaranTrucking = new PengeluaranTrucking();
        return response([
            'data' => $pengeluaranTrucking->get(),
            'attributes' => [
                'totalRows' => $pengeluaranTrucking->totalRows,
                'totalPages' => $pengeluaranTrucking->totalPages
            ]
        ]);
    }

   /**
     * @ClassName 
     */
    public function store(StorePengeluaranTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $pengeluaranTrucking = new PengeluaranTrucking();
            $pengeluaranTrucking->kodepengeluaran = $request->kodepengeluaran;
            $pengeluaranTrucking->keterangan = $request->keterangan;
            $pengeluaranTrucking->coa = $request->coa;
            $pengeluaranTrucking->statusformat = $request->statusformat;
            $pengeluaranTrucking->modifiedby = auth('api')->user()->name;

            TOP:
            if ($pengeluaranTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                    'postingdari' => 'ENTRY PENGELUARAN TRUCKING',
                    'idtrans' => $pengeluaranTrucking->id,
                    'nobuktitrans' => $pengeluaranTrucking->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranTrucking->toArray(),
                    'modifiedby' => $pengeluaranTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            }
            
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable());
            $pengeluaranTrucking->position = $selected->position;
            $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));
            

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranTrucking
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
            return response($th->getMessage());
        }
    }

    public function show(PengeluaranTrucking $pengeluaranTrucking)
    {
        return response([
            'status' => true,
            'data' => $pengeluaranTrucking
        ]);
    }

   /**
     * @ClassName 
     */
    public function update(StorePengeluaranTruckingRequest $request, PengeluaranTrucking $pengeluaranTrucking)
    {
        DB::beginTransaction();
        try {
            $pengeluaranTrucking->kodepengeluaran = $request->kodepengeluaran;
            $pengeluaranTrucking->keterangan = $request->keterangan;
            $pengeluaranTrucking->coa = $request->coa;
            $pengeluaranTrucking->statusformat = $request->statusformat;
            $pengeluaranTrucking->modifiedby = auth('api')->user()->name;

            if ($pengeluaranTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN TRUCKING',
                    'idtrans' => $pengeluaranTrucking->id,
                    'nobuktitrans' => $pengeluaranTrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranTrucking->toArray(),
                    'modifiedby' => $pengeluaranTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

            } 
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable());
            $pengeluaranTrucking->position = $selected->position;
            $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));
        
            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $pengeluaranTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();  
            throw $th;
            return response($th->getMessage());
        }
    }
   /**
     * @ClassName 
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranTrucking = PengeluaranTrucking::lockForUpdate()->findOrFail($id);
            $delete = $pengeluaranTrucking->delete();
            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                    'postingdari' => 'DELETE PENGELUARAN TRUCKING',
                    'idtrans' => $pengeluaranTrucking->id,
                    'nobuktitrans' => $pengeluaranTrucking->id,
                    'aksi' => 'DELETE',
                    'datajson' => $pengeluaranTrucking->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable(), true);
                $pengeluaranTrucking->position = $selected->position;
                $pengeluaranTrucking->id = $selected->id;
                $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $pengeluaranTrucking
                ]);
            } 
        } catch (\Throwable $th) {
            DB::rollBack();         
            throw $th;
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $pengeluaranTruckings = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Pengeluaran',
                'index' => 'kodepengeluaran',
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
                'index' => 'statusformat',
            ],
        ];

        $this->toExcel('Pengeluaran Trucking', $pengeluaranTruckings, $columns);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluarantrucking')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getid($id, $request, $del)
    {
        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('kodepengeluaran', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('coa', 300)->default('');
            $table->string('statusformat', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = PengeluaranTrucking::select(
                'pengeluarantrucking.id as id_',
                'pengeluarantrucking.kodepengeluaran',
                'pengeluarantrucking.keterangan',
                'pengeluarantrucking.coa',
                'pengeluarantrucking.statusformat',
                'pengeluarantrucking.modifiedby',
                'pengeluarantrucking.created_at',
                'pengeluarantrucking.updated_at'
            )
                ->orderBy('pengeluarantrucking.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = PengeluaranTrucking::select(
                    'pengeluarantrucking.id as id_',
                    'pengeluarantrucking.kodepengeluaran',
                    'pengeluarantrucking.keterangan',
                    'pengeluarantrucking.coa',
                    'pengeluarantrucking.statusformat',
                    'pengeluarantrucking.modifiedby',
                    'pengeluarantrucking.created_at',
                    'pengeluarantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('pengeluarantrucking.id', $params['sortorder']);
            } else {
                $query = PengeluaranTrucking::select(
                    'pengeluarantrucking.id as id_',
                    'pengeluarantrucking.kodepengeluaran',
                    'pengeluarantrucking.keterangan',
                    'pengeluarantrucking.coa',
                    'pengeluarantrucking.statusformat',
                    'pengeluarantrucking.modifiedby',
                    'pengeluarantrucking.created_at',
                    'pengeluarantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('pengeluarantrucking.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing([
            'id_',
            'kodepengeluaran',
            'keterangan',
            'coa',
            'statusformat',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $query);


        if ($del == 1) {
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
    }

    
}
