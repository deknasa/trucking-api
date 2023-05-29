<?php

namespace App\Http\Controllers\Api;

use App\Models\Mandor;
use App\Http\Requests\StoreMandorRequest;
use App\Http\Requests\UpdateMandorRequest;
use App\Http\Requests\DestroyMandorRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class MandorController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mandor = new Mandor();

        return response([
            'data' => $mandor->get(),
            'attributes' => [
                'totalRows' => $mandor->totalRows,
                'totalPages' => $mandor->totalPages
            ]
        ]);
    }
  
    public function cekValidasi($id) {
        $mandor= new Mandor();
        $cekdata=$mandor->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
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

        $mandor = new Mandor();
        return response([
            'status' => true,
            'data' => $mandor->default(),
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreMandorRequest $request)
    {
        DB::beginTransaction();

        try {
            $mandor = new Mandor();
            $mandor->namamandor = $request->namamandor;
            $mandor->keterangan = $request->keterangan ?? '';
            $mandor->statusaktif = $request->statusaktif;
            $mandor->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($mandor->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'ENTRY MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => $mandor->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($mandor, $mandor->getTable());
            $mandor->position = $selected->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mandor
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Mandor $mandor)
    {
        return response([
            'status' => true,
            'data' => $mandor
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMandorRequest $request, Mandor $mandor)
    {
        DB::beginTransaction();
        try {
            $mandor->namamandor = $request->namamandor;
            $mandor->keterangan = $request->keterangan ?? '';
            $mandor->statusaktif = $request->statusaktif;
            $mandor->modifiedby = auth('api')->user()->name;

            if ($mandor->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($mandor->getTable()),
                    'postingdari' => 'EDIT MANDOR',
                    'idtrans' => $mandor->id,
                    'nobuktitrans' => $mandor->id,
                    'aksi' => 'EDIT',
                    'datajson' => $mandor->toArray(),
                    'modifiedby' => $mandor->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($mandor, $mandor->getTable());
            $mandor->position = $selected->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $mandor
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyMandorRequest $request, $id)
    {
        DB::beginTransaction();

        $mandor = new Mandor();
        $mandor = $mandor->lockAndDestroy($id);

        if ($mandor) {
            $logTrail = [
                'namatabel' => strtoupper($mandor->getTable()),
                'postingdari' => 'DELETE MANDOR',
                'idtrans' => $mandor->id,
                'nobuktitrans' => $mandor->id,
                'aksi' => 'DELETE',
                'datajson' => $mandor->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            $selected = $this->getPosition($mandor, $mandor->getTable(), true);
            $mandor->position = $selected->position;
            $mandor->id = $selected->id;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mandor
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mandor')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
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
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $mandors = $decodedResponse['data'];


        $i = 0;
        foreach ($mandors as $index => $params) {

            $statusaktif = $params['statusaktif'];

            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $mandors[$i]['statusaktif'] = $statusaktif;

        
            $i++;


        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Nama Mandor',
                'index' => 'namamandor',
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

        $this->toExcel('Mandor', $mandors, $columns);
    }
}
