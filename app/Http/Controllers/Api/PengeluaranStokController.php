<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Controller;
use App\Models\PengeluaranStok;

use App\Http\Requests\StorePengeluaranStokRequest;
use App\Http\Requests\UpdatePengeluaranStokRequest;
use App\Http\Requests\DestroyPengeluaranStokRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class PengeluaranStokController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'data' => $pengeluaranStok->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStok->totalRows,
                'totalPages' => $pengeluaranStok->totalPages
            ]
        ]);
    }


    public function cekValidasi($id)
    {
        $pengeluaranStok = new PengeluaranStok();
        $cekdata = $pengeluaranStok->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
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
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'status' => true,
            'data' => $pengeluaranStok->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokRequest $request)
    {
        DB::beginTransaction();

        try {
            $pengeluaranStok = new PengeluaranStok();
            $pengeluaranStok->kodepengeluaran = $request->kodepengeluaran;
            $pengeluaranStok->keterangan = $request->keterangan ?? '';
            $pengeluaranStok->coa = $request->coa;
            $pengeluaranStok->format = $request->format;
            $pengeluaranStok->statushitungstok = $request->statushitungstok;
            $pengeluaranStok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            if ($pengeluaranStok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStok->getTable()),
                    'postingdari' => 'ENTRY PENERIMAAN STOK',
                    'idtrans' => $pengeluaranStok->id,
                    'nobuktitrans' => $pengeluaranStok->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranStok->toArray(),
                    'modifiedby' => $pengeluaranStok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable());
            $pengeluaranStok->position = $selected->position;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(PengeluaranStok $pengeluaranStok, $id)
    {
        $pengeluaranStok = new PengeluaranStok();
        return response([
            'data' => $pengeluaranStok->find($id),
            'attributes' => [
                'totalRows' => $pengeluaranStok->totalRows,
                'totalPages' => $pengeluaranStok->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdatePengeluaranStokRequest $request, PengeluaranStok $pengeluaranStok, $id)
    {
        DB::beginTransaction();
        try {
            $pengeluaranStok = PengeluaranStok::lockForUpdate()->where('id', $id)->first();
            $pengeluaranStok->kodepengeluaran = $request->kodepengeluaran;
            $pengeluaranStok->keterangan = $request->keterangan ?? '';
            $pengeluaranStok->coa = $request->coa;
            $pengeluaranStok->format = $request->format;
            $pengeluaranStok->statushitungstok = $request->statushitungstok;
            $pengeluaranStok->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pengeluaranStok->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStok->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN STOK',
                    'idtrans' => $pengeluaranStok->id,
                    'nobuktitrans' => $pengeluaranStok->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranStok->toArray(),
                    'modifiedby' => $pengeluaranStok->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable());
            $pengeluaranStok->position = $selected->position;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengeluaranStok->page = ceil($pengeluaranStok->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStok
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranstok')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyPengeluaranStokRequest $request, $id)
    {
        DB::beginTransaction();

        $pengeluaranStok = new PengeluaranStok();
        $pengeluaranStok = $pengeluaranStok->lockAndDestroy($id);

        if ($pengeluaranStok) {
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranStok->getTable()),
                'postingdari' => 'DELETE PENERIMAAN STOK',
                'idtrans' => $pengeluaranStok->id,
                'nobuktitrans' => $pengeluaranStok->id,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranStok->toArray(),
                'modifiedby' => $pengeluaranStok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable(), true);
            $pengeluaranStok->position = $selected->position;
            $pengeluaranStok->id = $selected->id;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStok
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        header('Access-Control-Allow-Origin: *');

        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $pengeluarans = $decodedResponse['data'];

        $i = 0;
        foreach ($pengeluarans as $index => $params) {

            $format = $params['format'];
            $statusHitungStok = $params['statushitungstok'];

            $result = json_decode($format, true);
            $resultHitungStok = json_decode($statusHitungStok, true);

            $format = $result['MEMO'];
            $statusHitungStok = $resultHitungStok['MEMO'];


            $pengeluarans[$i]['format'] = $format;
            $pengeluarans[$i]['statushitungstok'] = $statusHitungStok;


            $i++;
        }
        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Kode Pengeluaran',
                'index' => 'kodepengeluaran',
            ],
            [
                'label' => 'keterangan',
                'index' => 'keterangan',
            ],
            [
                'label' => 'coa',
                'index' => 'coa',
            ],
            [
                'label' => 'status format',
                'index' => 'format',
            ],
            [
                'label' => 'status hitung stok',
                'index' => 'statushitungstok',
            ],
        ];
        $this->toExcel('Pengeluaran Stok', $pengeluarans, $columns);
    }
}
