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
use Illuminate\Database\QueryException;
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
            ], 201);
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

      /**
     * @ClassName 
     */
    public function update(UpdateKategoriRequest $request, Kategori $kategori)
    {
        try {
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
        DB::beginTransaction();
        
        try {
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
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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

}
