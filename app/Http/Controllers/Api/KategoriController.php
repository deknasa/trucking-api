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
      /**
     * @ClassName 
     */
    public function index()
    {
        $kategori = new Kategori();

        return response([
            'data' => $kategori->get(),
            'attributes' => [
                'totalRows' => $kategori->totalRows,
                'totalPages' => $kategori->totalPages
            ]
        ]);
    }

    public function create()
    {
        //
    }
      /**
     * @ClassName 
     */
    public function store(StoreKategoriRequest $request)
    {
        DB::beginTransaction();

        try {
            $kategori = new Kategori();
            $kategori->kodekategori = $request->kodekategori;
            $kategori->keterangan = $request->keterangan;
            $kategori->subkelompok_id = $request->subkelompok_id;
            $kategori->statusaktif = $request->statusaktif;
            $kategori->modifiedby = auth('api')->user()->name;
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

            $selected = $this->getPosition($kategori, $kategori->getTable());
            $kategori->position = $selected->position;
            $kategori->page = ceil($kategori->position / ($request->limit ?? 10));

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

    public function show($id)
    {
        $kategori = new Kategori();
        return response([
            'status' => true,
            'data' => $kategori->find($id)
        ]);
    }

    public function edit(Kategori $kategori)
    {
        //
    }
      /**
     * @ClassName 
     */
    public function update(StoreKategoriRequest $request, Kategori $kategori)
    {
        try {
            $kategori = Kategori::findOrFail($kategori->id);
            $kategori->kodekategori = $request->kodekategori;
            $kategori->keterangan = $request->keterangan;
            $kategori->subkelompok_id = $request->subkelompok_id;
            $kategori->statusaktif = $request->statusaktif;
            $kategori->modifiedby = auth('api')->user()->name;

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

               $selected = $this->getPosition($kategori, $kategori->getTable());
               $kategori->position = $selected->position;
               $kategori->page = ceil($kategori->position / ($request->limit ?? 10));

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
      /**
     * @ClassName 
     */
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
            /* Set position and page */

            $selected = $this->getPosition($kategori, $kategori->getTable(), true);
            
            $kategori->position = $selected->position;
            $kategori->id = $selected->id;
            $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            
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
            $query = DB::table((new Kategori)->getTable())->select(
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
            $query = DB::table((new Kategori)->getTable())->select(
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
                $query = DB::table((new Kategori)->getTable())->select(
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
                $query = DB::table((new Kategori)->getTable())->select(
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
