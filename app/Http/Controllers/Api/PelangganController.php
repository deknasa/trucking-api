<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Pelanggan;
use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PelangganController extends Controller
{
       /**
     * @ClassName 
     */
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        /* Sorting */
        $query = DB::table((new Pelanggan)->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        $totalRows = $query->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Pelanggan)->getTable())->select(
                'pelanggan.id',
                'pelanggan.kodepelanggan',
                'pelanggan.namapelanggan',
                'pelanggan.telp',
                'pelanggan.alamat',
                'pelanggan.alamat2',
                'pelanggan.kota',
                'pelanggan.kodepos',
                'pelanggan.keterangan',
                'pelanggan.modifiedby',
                'pelanggan.created_at',
                'pelanggan.updated_at'
            )->orderBy('pelanggan.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Pelanggan)->getTable())->select(
                    'pelanggan.id',
                    'pelanggan.kodepelanggan',
                    'pelanggan.namapelanggan',
                    'pelanggan.telp',
                    'pelanggan.alamat',
                    'pelanggan.alamat2',
                    'pelanggan.kota',
                    'pelanggan.kodepos',
                    'pelanggan.keterangan',
                    'pelanggan.modifiedby',
                    'pelanggan.created_at',
                    'pelanggan.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('pelanggan.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Pelanggan)->getTable())->select(
                    'pelanggan.id',
                    'pelanggan.kodepelanggan',
                    'pelanggan.namapelanggan',
                    'pelanggan.telp',
                    'pelanggan.alamat',
                    'pelanggan.alamat2',
                    'pelanggan.kota',
                    'pelanggan.kodepos',
                    'pelanggan.keterangan',
                    'pelanggan.modifiedby',
                    'pelanggan.created_at',
                    'pelanggan.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('pelanggan.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->where($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                default:

                    break;
            }

            $totalRows = $query->count();
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $pelanggans = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $pelanggans,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }
    
    
    public function show(Pelanggan $pelanggan)
    {
        return response([
            'status' => true,
            'data' => $pelanggan
        ]);
    }
       /**
     * @ClassName 
     */
    public function store(StorePelangganRequest $request)
    {
        DB::beginTransaction();

        try {
            $pelanggan = new Pelanggan();
            $pelanggan->kodepelanggan = $request->kodepelanggan;
            $pelanggan->namapelanggan = $request->namapelanggan;
            $pelanggan->telp = $request->telp;
            $pelanggan->alamat = $request->alamat;
            $pelanggan->alamat2 = $request->alamat2;
            $pelanggan->kota = $request->kota;
            $pelanggan->kodepos = $request->kodepos;
            $pelanggan->keterangan = $request->keterangan;
            $pelanggan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelanggan->getTable()),
                    'postingdari' => 'ENTRY PELANGGAN',
                    'idtrans' => $pelanggan->id,
                    'nobuktitrans' => $pelanggan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pelanggan->toArray(),
                    'modifiedby' => $pelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($pelanggan->id, $request, $del);
            $pelanggan->position = $data->row;

            if (isset($request->limit)) {
                $pelanggan->page = ceil($pelanggan->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelanggan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelanggan')->getColumns();

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
            $table->string('kodepelanggan', 300)->default('');
            $table->string('namapelanggan', 300)->default('');
            $table->string('telp', 300)->default('');
            $table->string('alamat', 300)->default('');
            $table->string('alamat2', 300)->default('');
            $table->string('kota', 300)->default('');
            $table->string('kodepos', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Pelanggan::select(
                'pelanggan.id as id_',
                'pelanggan.kodepelanggan',
                'pelanggan.namapelanggan',
                'pelanggan.telp',
                'pelanggan.alamat',
                'pelanggan.alamat2',
                'pelanggan.kota',
                'pelanggan.kodepos',
                'pelanggan.keterangan',
                'pelanggan.modifiedby',
                'pelanggan.created_at',
                'pelanggan.updated_at'
            )
                ->orderBy('pelanggan.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Pelanggan::select(
                    'pelanggan.id as id_',
                    'pelanggan.kodepelanggan',
                    'pelanggan.namapelanggan',
                    'pelanggan.telp',
                    'pelanggan.alamat',
                    'pelanggan.alamat2',
                    'pelanggan.kota',
                    'pelanggan.kodepos',
                    'pelanggan.keterangan',
                    'pelanggan.modifiedby',
                    'pelanggan.created_at',
                    'pelanggan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('pelanggan.id', $params['sortorder']);
            } else {
                $query = Pelanggan::select(
                    'pelanggan.id as id_',
                    'pelanggan.kodepelanggan',
                    'pelanggan.namapelanggan',
                    'pelanggan.telp',
                    'pelanggan.alamat',
                    'pelanggan.alamat2',
                    'pelanggan.kota',
                    'pelanggan.kodepos',
                    'pelanggan.keterangan',
                    'pelanggan.modifiedby',
                    'pelanggan.created_at',
                    'pelanggan.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('pelanggan.id', 'asc');
            }
        }

        DB::table($temp)->insertUsing([
            'id_',
            'kodepelanggan',
            'namapelanggan',
            'telp',
            'alamat',
            'alamat2',
            'kota',
            'kodepos',
            'keterangan',
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

       /**
     * @ClassName 
     */
    public function update(UpdatePelangganRequest $request, Pelanggan $pelanggan)
    {
        try {
            $pelanggan->kodepelanggan = $request->kodepelanggan;
            $pelanggan->namapelanggan = $request->namapelanggan;
            $pelanggan->telp = $request->telp;
            $pelanggan->alamat = $request->alamat;
            $pelanggan->alamat2 = $request->alamat2;
            $pelanggan->kota = $request->kota;
            $pelanggan->kodepos = $request->kodepos;
            $pelanggan->keterangan = $request->keterangan;
            $pelanggan->modifiedby = auth('api')->user()->name;

            if ($pelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelanggan->getTable()),
                    'postingdari' => 'EDIT PELANGGAN',
                    'idtrans' => $pelanggan->id,
                    'nobuktitrans' => $pelanggan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pelanggan->toArray(),
                    'modifiedby' => $pelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $pelanggan->position = $this->getid($pelanggan->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $pelanggan->page = ceil($pelanggan->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $pelanggan
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
    public function destroy(Pelanggan $pelanggan, Request $request)
    {
        $delete = Pelanggan::destroy($pelanggan->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pelanggan->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $pelanggan->id,
                'nobuktitrans' => $pelanggan->id,
                'aksi' => 'DELETE',
                'datajson' => $pelanggan->toArray(),
                'modifiedby' => $pelanggan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($pelanggan->id, $request, $del);
            $pelanggan->position = $data->row  ?? 0;
            $pelanggan->id = $data->id  ?? 0; 
            if (isset($request->limit)) {
                $pelanggan->page = ceil($pelanggan->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pelanggan
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $pelanggans = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Pelanggan',
                'index' => 'kodepelanggan',
            ],
            [
                'label' => 'Nama Pelanggan',
                'index' => 'namapelanggan',
            ],
            [
                'label' => 'Telp',
                'index' => 'telp',
            ],
            [
                'label' => 'Alamat',
                'index' => 'alamat',
            ],
            [
                'label' => 'Alamat2',
                'index' => 'alamat2',
            ],
            [
                'label' => 'Kota',
                'index' => 'kota',
            ],
            [
                'label' => 'Kode Pos',
                'index' => 'kodepos',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
        ];

        $this->toExcel('Pelanggan', $pelanggans, $columns);
    }
}
