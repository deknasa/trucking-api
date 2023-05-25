<?php

namespace App\Http\Controllers\Api;

use App\Models\Kota;
use App\Http\Requests\StoreKotaRequest;
use App\Http\Requests\UpdateKotaRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Zona;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Hamcrest\Type\IsDouble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class KotaController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $kota = new Kota();

        return response([
            'data' => $kota->get(),
            'attributes' => [
                'totalRows' => $kota->totalRows,
                'totalPages' => $kota->totalPages
            ]
        ]);
    }

    public function cekValidasi($id) {
        $kota= new Kota();
        $cekdata=$kota->cekvalidasihapus($id);
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
    

    Public function default()
    {
        $kota = new Kota();
        return response([
            'status' => true,
            'data' => $kota->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreKotaRequest $request)
    {
  
        DB::beginTransaction();

        try {
            $kota = new Kota();
            $kota->kodekota = $request->kodekota;
            $kota->keterangan = $request->keterangan ?? '';
            $kota->zona_id = $request->zona_id;
            $kota->statusaktif = $request->statusaktif;
            $kota->modifiedby = auth('api')->user()->name;

            if ($kota->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kota->getTable()),
                    'postingdari' => 'ENTRY KOTA',
                    'idtrans' => $kota->id,
                    'nobuktitrans' => $kota->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kota->toArray(),
                    'modifiedby' => $kota->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($kota, $kota->getTable());
            $kota->position = $selected->position;
            $kota->page = ceil($kota->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kota
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
  

        $data = Kota::findAll($id);
        // dd($data);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateKotaRequest $request, Kota $kota)
    {

        DB::beginTransaction();

        try {
            $kota = Kota::find($request->id);
            $kota->kodekota = $request->kodekota;
            $kota->keterangan = $request->keterangan ?? '';
            $kota->zona_id = $request->zona_id;
            $kota->statusaktif = $request->statusaktif;
            $kota->modifiedby = auth('api')->user()->name;
            $kota->save();
            if ($kota->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kota->getTable()),
                    'postingdari' => 'EDIT KOTA',
                    'idtrans' => $kota->id,
                    'nobuktitrans' => $kota->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kota->toArray(),
                    'modifiedby' => $kota->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($kota, $kota->getTable());
            $kota->position = $selected->position;
            $kota->page = ceil($kota->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kota
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

        $kota = new Kota();
        $kota = $kota->lockAndDestroy($id);

        if ($kota) {
            $logTrail = [
                'namatabel' => strtoupper($kota->getTable()),
                'postingdari' => 'DELETE KOTA',
                'idtrans' => $kota->id,
                'nobuktitrans' => $kota->id,
                'aksi' => 'DELETE',
                'datajson' => $kota->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();
            $selected = $this->getPosition($kota, $kota->getTable(), true);
            $kota->position = $selected->position;
            $kota->id = $selected->id;
            $kota->page = ceil($kota->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kota
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kota')->getColumns();

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
            'zona' => Zona::all(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $kotas = $decodedResponse['data'];

        $i = 0;
        foreach ($kotas as $index => $params) {

            $statusaktif = $params['statusaktif'];

            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $kotas[$i]['statusaktif'] = $statusaktif;

        
            $i++;


        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Kode Kota',
                'index' => 'kodekota',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'Zona',
                'index' => 'zona_id',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
        ];

        $this->toExcel('Kota', $kotas, $columns);
    }
}
