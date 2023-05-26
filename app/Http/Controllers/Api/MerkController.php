<?php

namespace App\Http\Controllers\Api;

use App\Models\Merk;
use App\Http\Requests\StoreMerkRequest;
use App\Http\Requests\UpdateMerkRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class MerkController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $merk = new Merk();

        return response([
            'data' => $merk->get(),
            'attributes' => [
                'totalRows' => $merk->totalRows,
                'totalPages' => $merk->totalPages
            ]
        ]);
    }

    public function cekValidasi($id) {
        $merk= new Merk();
        $cekdata=$merk->cekvalidasihapus($id);
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
        $merk = new Merk();
        return response([
            'status' => true,
            'data' => $merk->default()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreMerkRequest $request)
    {
        DB::beginTransaction();

        try {
            $merk = new Merk();
            $merk->kodemerk = $request->kodemerk;
            $merk->keterangan = $request->keterangan ?? '';
            $merk->statusaktif = $request->statusaktif;
            $merk->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($merk->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'ENTRY MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $merk->toArray(),
                    'modifiedby' => $merk->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($merk, $merk->getTable());
            $merk->position = $selected->position;
            $merk->page = ceil($merk->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $merk
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Merk $merk)
    {
        return response([
            'status' => true,
            'data' => $merk
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateMerkRequest $request, Merk $merk)
    {
        DB::beginTransaction();
        try {
            $merk = Merk::lockForUpdate()->findOrFail($merk->id);
            $merk->kodemerk = $request->kodemerk;
            $merk->keterangan = $request->keterangan ?? '';
            $merk->statusaktif = $request->statusaktif;
            $merk->modifiedby = auth('api')->user()->name;

            if ($merk->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($merk->getTable()),
                    'postingdari' => 'EDIT MERK',
                    'idtrans' => $merk->id,
                    'nobuktitrans' => $merk->id,
                    'aksi' => 'EDIT',
                    'datajson' => $merk->toArray(),
                    'modifiedby' => $merk->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }
            /* Set position and page */
            $selected = $this->getPosition($merk, $merk->getTable());
            $merk->position = $selected->position;
            $merk->page = ceil($merk->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $merk
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

        $merk = new Merk();
        $merk = $merk->lockAndDestroy($id);

        if ($merk) {
            $logTrail = [
                'namatabel' => strtoupper($merk->getTable()),
                'postingdari' => 'DELETE MERK',
                'idtrans' => $merk->id,
                'nobuktitrans' => $merk->id,
                'aksi' => 'DELETE',
                'datajson' => $merk->toArray(),
                'modifiedby' => $merk->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            $selected = $this->getPosition($merk, $merk->getTable(), true);
            $merk->position = $selected->position;
            $merk->id = $selected->id;
            $merk->page = ceil($merk->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $merk
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('merk')->getColumns();

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
        $merks = $decodedResponse['data'];

        $i = 0;
        foreach ($merks as $index => $params) {

            $statusaktif = $params['statusaktif'];

            $result = json_decode($statusaktif, true);

            $statusaktif = $result['MEMO'];


            $merks[$i]['statusaktif'] = $statusaktif;


            $i++;
        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Kode Merk',
                'index' => 'kodemerk',
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

        $this->toExcel('Kategori', $merks, $columns);
    }
}
