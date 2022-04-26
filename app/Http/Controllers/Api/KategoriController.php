<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\SubKelompok;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KategoriController extends Controller
{

    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = Kategori::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = Kategori::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = Kategori::select(
                'kategori.id',
                'kategori.kodekategori',
                'kategori.keterangan',
                'parameter.text as statusaktif',
                'p.keterangan as subkelompok',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
            ->leftJoin('parameter', 'kategori.statusaktif', '=', 'parameter.id')
            ->leftJoin('subkelompok AS p', 'kategori.subkelompok_id', '=', 'p.id')
            ->orderBy('kategori.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kodekategori') {
            $query = Kategori::select(
                'kategori.id',
                'kategori.kodekategori',
                'kategori.keterangan',
                'parameter.text as statusaktif',
                'p.keterangan as subkelompok',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
                ->leftJoin('parameter', 'kategori.statusaktif', '=', 'parameter.id')
                ->leftJoin('subkelompok AS p', 'kategori.subkelompok_id', '=', 'p.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('kategori.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = Kategori::select(
                    'kategori.id',
                    'kategori.kodekategori',
                    'kategori.keterangan',
                    'parameter.text as statusaktif',
                    'p.keterangan as subkelompok',
                    'kategori.modifiedby',
                    'kategori.created_at',
                    'kategori.updated_at'
                )
                    ->leftJoin('parameter', 'kategori.statusaktif', '=', 'parameter.id')
                    ->leftJoin('subkelompok AS p', 'kategori.subkelompok_id', '=', 'p.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('kategori.id', $params['sortOrder']);
            } else {
                $query = Kategori::select(
                    'kategori.id',
                    'kategori.kodekategori',
                    'kategori.keterangan',
                    'parameter.text as statusaktif',
                    'p.keterangan as subkelompok',
                    'kategori.modifiedby',
                    'kategori.created_at',
                    'kategori.updated_at'
                )
                    ->leftJoin('parameter', 'kategori.statusaktif', '=', 'parameter.id')
                    ->leftJoin('subkelompok AS p', 'kategori.subkelompok_id', '=', 'p.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('kategori.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('gudang.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('gudang.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }
                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $kategori = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $kategori,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }

    public function store(StoreKategoriRequest $request)
    {
        DB::beginTransaction();

        try {
            $kategori = new Kategori();
            $kategori->kodekategori = $request->kodekategori;
            $kategori->keterangan = $request->keterangan;
            $kategori->subkelompok_id = $request->subkelompok_id;
            $kategori->statusaktif = $request->statusaktif;
            $kategori->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kategori->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kategori->getTable()),
                    'postingdari' => 'ENTRY KATEGORI',
                    'idtrans' => $kategori->id,
                    'nobuktitrans' => $kategori->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $kategori->toArray(),
                    'modifiedby' => $kategori->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($kategori->id, $request, $del);
            $kategori->position = @$data->row;

            if (isset($request->limit)) {
                $kategori->page = ceil($kategori->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Kategori $kategori)
    {
        return response([
            'status' => true,
            'data' => $kategori
        ]);
    }

    public function edit(Kategori $kategori)
    {
        //
    }

    public function update(StoreKategoriRequest $request, Kategori $kategori)
    {
        try {
            $kategori = Kategori::findOrFail($kategori->id);
            $kategori->kodekategori = $request->kodekategori;
            $kategori->keterangan = $request->keterangan;
            $kategori->subkelompok_id = $request->subkelompok_id;
            $kategori->statusaktif = $request->statusaktif;
            $kategori->modifiedby = $request->modifiedby;

            if ($kategori->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($kategori->getTable()),
                    'postingdari' => 'EDIT KATEGORI',
                    'idtrans' => $kategori->id,
                    'nobuktitrans' => $kategori->id,
                    'aksi' => 'EDIT',
                    'datajson' => $kategori->toArray(),
                    'modifiedby' => $kategori->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $kategori->position = $this->getid($kategori->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $kategori->page = ceil($kategori->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $kategori
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

    public function destroy(Kategori $kategori, Request $request)
    {
        $delete = Kategori::destroy($kategori->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($kategori->getTable()),
                'postingdari' => 'DELETE KATEGORI',
                'idtrans' => $kategori->id,
                'nobuktitrans' => $kategori->id,
                'aksi' => 'DELETE',
                'datajson' => $kategori->toArray(),
                'modifiedby' => $kategori->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($kategori->id, $request, $del);
            $kategori->position = @$data->row;
            $kategori->id = @$data->id;
            if (isset($request->limit)) {
                $kategori->page = ceil($kategori->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kategori
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kategori')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($kategori, $request)
    {
        return Kategori::where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $kategori->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp'=>'status aktif'])->get(),
            'subkelompok' => SubKelompok::all(),
        ];

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
            $table->string('kodekategori', 50)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('subkelompok_id', 300)->default('');
            $table->string('statusaktif', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Kategori::select(
                'kategori.id as id_',
                'kategori.kodekategori',
                'kategori.keterangan',
                'kategori.subkelompok_id',
                'kategori.statusaktif',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
                ->orderBy('kategori.id', $params['sortorder']);
        } else if ($params['sortname'] == 'kodekategori' or $params['sortname'] == 'keterangan') {
            $query = Kategori::select(
                'kategori.id as id_',
                'kategori.kodekategori',
                'kategori.keterangan',
                'kategori.subkelompok_id',
                'kategori.statusaktif',
                'kategori.modifiedby',
                'kategori.created_at',
                'kategori.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('kategori.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Kategori::select(
                    'kategori.id as id_',
                    'kategori.kodekategori',
                    'kategori.keterangan',
                    'kategori.subkelompok_id',
                    'kategori.statusaktif',
                    'kategori.modifiedby',
                    'kategori.created_at',
                    'kategori.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kategori.id', $params['sortorder']);
            } else {
                $query = Kategori::select(
                    'kategori.id as id_',
                    'kategori.kodekategori',
                    'kategori.keterangan',
                    'kategori.subkelompok_id',
                    'kategori.statusaktif',
                    'kategori.modifiedby',
                    'kategori.created_at',
                    'kategori.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('kategori.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'kodekategori', 'keterangan', 'subkelompok_id','statusaktif', 'modifiedby', 'created_at', 'updated_at'], $query);


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
