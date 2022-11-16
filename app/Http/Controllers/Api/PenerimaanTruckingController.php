<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTrucking;

use App\Http\Requests\StoreLogTrailRequest;
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

   /**
     * @ClassName 
     */
    public function store(StorePenerimaanTruckingRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerimaanTrucking = new PenerimaanTrucking();
            $penerimaanTrucking->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanTrucking->keterangan = $request->keterangan;
            $penerimaanTrucking->coa = $request->coa;
            $penerimaanTrucking->statusformat = $request->statusformat;
            $penerimaanTrucking->modifiedby = auth('api')->user()->name;

            if ($penerimaanTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                    'postingdari' => 'ENTRY PEnerimaan TRUCKING',
                    'idtrans' => $penerimaanTrucking->id,
                    'nobuktitrans' => $penerimaanTrucking->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaanTrucking->toArray(),
                    'modifiedby' => $penerimaanTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();
            /* Set position and page */
           
            $selected = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable());
            $penerimaanTrucking->position = $selected->position;
            $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTrucking
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
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
    public function update(UpdatePenerimaanTruckingRequest $request, PenerimaanTrucking $penerimaanTrucking)
    { 
        DB::beginTransaction();

        try {
            $penerimaanTrucking->kodepenerimaan = $request->kodepenerimaan;
            $penerimaanTrucking->keterangan = $request->keterangan;
            $penerimaanTrucking->coa = $request->coa;
            $penerimaanTrucking->statusformat = $request->statusformat;
            $penerimaanTrucking->modifiedby = auth('api')->user()->name;

            if ($penerimaanTrucking->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                    'postingdari' => 'EDIT PEnerimaan TRUCKING',
                    'idtrans' => $penerimaanTrucking->id,
                    'nobuktitrans' => $penerimaanTrucking->id,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanTrucking->toArray(),
                    'modifiedby' => $penerimaanTrucking->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
               
            } 

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

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
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(PenerimaanTrucking $penerimaanTrucking, Request $request)
    {
        
        DB::beginTransaction();
        try {
            $delete = PenerimaanTrucking::destroy($penerimaanTrucking->id);
            $del = 1;
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
                
                DB::rollBack();
                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();         
            return response($th->getMessage());   
        }
    }

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
                'index' => 'statusformat',
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
            $table->string('kodepenerimaan', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('coa', 300)->default('');
            $table->string('statusformat', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = PenerimaanTrucking::select(
                'penerimaantrucking.id as id_',
                'penerimaantrucking.kodepenerimaan',
                'penerimaantrucking.keterangan',
                'penerimaantrucking.coa',
                'penerimaantrucking.statusformat',
                'penerimaantrucking.modifiedby',
                'penerimaantrucking.created_at',
                'penerimaantrucking.updated_at'
            )
                ->orderBy('penerimaantrucking.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = PenerimaanTrucking::select(
                    'penerimaantrucking.id as id_',
                    'penerimaantrucking.kodepenerimaan',
                    'penerimaantrucking.keterangan',
                    'penerimaantrucking.coa',
                    'penerimaantrucking.statusformat',
                    'penerimaantrucking.modifiedby',
                    'penerimaantrucking.created_at',
                    'penerimaantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('penerimaantrucking.id', $params['sortorder']);
            } else {
                $query = PenerimaanTrucking::select(
                    'penerimaantrucking.id as id_',
                    'penerimaantrucking.kodepenerimaan',
                    'penerimaantrucking.keterangan',
                    'penerimaantrucking.coa',
                    'penerimaantrucking.statusformat',
                    'penerimaantrucking.modifiedby',
                    'penerimaantrucking.created_at',
                    'penerimaantrucking.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('penerimaantrucking.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing([
            'id_',
            'kodepenerimaan',
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
