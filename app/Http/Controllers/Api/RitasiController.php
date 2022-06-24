<?php

namespace App\Http\Controllers\Api;

use App\Models\Ritasi;
use App\Http\Requests\StoreRitasiRequest;
use App\Http\Requests\UpdateRitasiRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Supir;
use App\Models\Trado;
use App\Models\Kota;
use App\Models\SuratPengantar;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RitasiController extends Controller
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

        $totalRows = DB::table((new Ritasi())->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new Ritasi())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Ritasi())->getTable())->select(
                'ritasi.id',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'parameter.text as statusritasi',
                'suratpengantar.nobukti as suratpengantar_nobukti',
                'supir.namasupir as supir_id',
                'trado.keterangan as trado_id',
                'ritasi.jarak',
                'ritasi.gaji',
                'dari.keterangan as dari_id',
                'sampai.keterangan as sampai_id',
                'ritasi.modifiedby',
                'ritasi.created_at',
                'ritasi.updated_at'
            )
            ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
            ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id')
            ->orderBy('ritasi.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'nobukti' or $params['sortIndex'] == 'tglbukti') {
            $query = DB::table((new Ritasi())->getTable())->select(
                'ritasi.id',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'parameter.text as statusritasi',
                'suratpengantar.nobukti as suratpengantar_nobukti',
                'supir.namasupir as supir_id',
                'trado.keterangan as trado_id',
                'ritasi.jarak',
                'ritasi.gaji',
                'dari.keterangan as dari_id',
                'sampai.keterangan as sampai_id',
                'ritasi.modifiedby',
                'ritasi.created_at',
                'ritasi.updated_at'
            )
            ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
            ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
            ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
            ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
            ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
            ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('ritasi.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Ritasi())->getTable())->select(
                    'ritasi.id',
                    'ritasi.nobukti',
                    'ritasi.tglbukti',
                    'parameter.text as statusritasi',
                    'suratpengantar.nobukti as suratpengantar_nobukti',
                    'supir.namasupir as supir_id',
                    'trado.keterangan as trado_id',
                    'ritasi.jarak',
                    'ritasi.gaji',
                    'dari.keterangan as dari_id',
                    'sampai.keterangan as sampai_id',
                    'ritasi.modifiedby',
                    'ritasi.created_at',
                    'ritasi.updated_at'
                )
                ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
                ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
                ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
                ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
                ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
                ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('ritasi.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Ritasi())->getTable())->select(
                    'ritasi.id',
                    'ritasi.nobukti',
                    'ritasi.tglbukti',
                    'parameter.text as statusritasi',
                    'suratpengantar.nobukti as suratpengantar_nobukti',
                    'supir.namasupir as supir_id',
                    'trado.keterangan as trado_id',
                    'ritasi.jarak',
                    'ritasi.gaji',
                    'dari.keterangan as dari_id',
                    'sampai.keterangan as sampai_id',
                    'ritasi.modifiedby',
                    'ritasi.created_at',
                    'ritasi.updated_at'
                )
                ->leftJoin('parameter', 'ritasi.statusritasi', '=', 'parameter.id')
                ->leftJoin('suratpengantar', 'ritasi.suratpengantar_nobukti', '=', 'suratpengantar.nobukti')
                ->leftJoin('supir', 'ritasi.supir_id', '=', 'supir.id')
                ->leftJoin('trado', 'ritasi.trado_id', '=', 'trado.id')
                ->leftJoin('kota as dari', 'ritasi.dari_id', '=', 'dari.id')
                ->leftJoin('kota as sampai', 'ritasi.sampai_id', '=', 'sampai.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('ritasi.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusritasi') {
                            $query = $query->where('parameter.text', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'dari_id') {
                            $query = $query->where('dari.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'sampai_id') {
                            $query = $query->where('sampai.keterangan', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->where('ritasi.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusritasi') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'supir_id') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'trado_id') {
                            $query = $query->where('trado.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'dari_id') {
                            $query = $query->where('dari.keterangan', 'LIKE', "%$search[data]%");
                        } elseif ($search['field'] == 'sampai_id') {
                            $query = $query->where('sampai.keterangan', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('ritasi.'.$search['field'], 'LIKE', "%$search[data]%");
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

        $ritasi = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $ritasi,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }
   /**
     * @ClassName 
     */
    public function store(StoreRitasiRequest $request)
    {
        DB::beginTransaction();

        try {
            $content = new Request();
            $content['group'] = 'RITASI';
            $content['subgroup'] = 'RITASI';
            $content['table'] = 'ritasi';

            $ritasi = new Ritasi();
            $ritasi->tglbukti = date('Y-m-d',strtotime($request->tglbukti));
            $ritasi->statusritasi = $request->statusritasi;
            $ritasi->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $ritasi->supir_id = $request->supir_id;
            $ritasi->trado_id = $request->trado_id;
            $ritasi->dari_id = $request->dari_id;
            $ritasi->sampai_id = $request->sampai_id;
            $upahRitasi = UpahDB::table((new Ritasi())->getTable())->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();
            if ($upahRitasi == '') {
                return response([
                    'status' => false,
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ]);
            }
            $ritasi->jarak = $upahRitasi->upahritasiRincian()->first()->liter;
            $ritasi->gaji = $upahRitasi->upahritasiRincian()->first()->nominalsupir;
            $ritasi->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $ritasi->nobukti = $nobukti;

            if ($ritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($ritasi->getTable()),
                    'postingdari' => 'ENTRY RITASI',
                    'idtrans' => $ritasi->id,
                    'nobuktitrans' => $ritasi->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $ritasi->toArray(),
                    'modifiedby' => $ritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($ritasi->id, $request, $del);
            $ritasi->position = $data->row;

            if (isset($request->limit)) {
                $ritasi->page = ceil($ritasi->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $ritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Ritasi $ritasi)
    {
        return response([
            'status' => true,
            'data' => $ritasi
        ]);
    }

    public function edit(Ritasi $ritasi)
    {
        //
    }
   /**
     * @ClassName 
     */
    public function update(StoreRitasiRequest $request, Ritasi $ritasi)
    {
        try {
            $ritasi->tglbukti = $request->tglbukti;
            $ritasi->statusritasi = $request->statusritasi;
            $ritasi->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $ritasi->supir_id = $request->supir_id;
            $ritasi->trado_id = $request->trado_id;
            $upahRitasi = UpahDB::table((new Ritasi())->getTable())->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();
            if ($upahRitasi == '') {
                return response([
                    'status' => false,
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ]);
            }
            $ritasi->jarak = $upahRitasi->upahritasiRincian()->first()->liter;
            $ritasi->gaji = $upahRitasi->upahritasiRincian()->first()->nominalsupir;
            $ritasi->dari_id = $request->dari_id;
            $ritasi->sampai_id = $request->sampai_id;
            $ritasi->modifiedby = auth('api')->user()->name;

            if ($ritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($ritasi->getTable()),
                    'postingdari' => 'EDIT RITASI',
                    'idtrans' => $ritasi->id,
                    'nobuktitrans' => $ritasi->id,
                    'aksi' => 'EDIT',
                    'datajson' => $ritasi->toArray(),
                    'modifiedby' => $ritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $ritasi->position = $this->getid($ritasi->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $ritasi->page = ceil($ritasi->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $ritasi
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
    public function destroy(Ritasi $ritasi, Request $request)
    {
        $del = 1;
        if ($ritasi->delete()) {
            $logTrail = [
                'namatabel' => strtoupper($ritasi->getTable()),
                'postingdari' => 'DELETE RITASI',
                'idtrans' => $ritasi->id,
                'nobuktitrans' => $ritasi->id,
                'aksi' => 'DELETE',
                'datajson' => $ritasi->toArray(),
                'modifiedby' => $ritasi->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($ritasi->id, $request, $del);
            $ritasi->position = @$data->row  ?? 0;
            $ritasi->id = @$data->id  ?? 0;
            if (isset($request->limit)) {
                $ritasi->page = ceil($ritasi->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $ritasi
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('ritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getPosition($ritasi, $request)
    {
        return DB::table((new Ritasi())->getTable())->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $ritasi->{$request->sortname})
            /* Jika sortname modifiedby atau ada data duplikat */
            // ->where('id', $request->sortorder == 'desc' ? '>=' : '<=', $parameter->id)
            ->count();
    }

    public function combo(Request $request)
    {
        $data = [
            'statusritasi' => Parameter::where(['grp'=>'status ritasi'])->get(),
            'suratpengantar' => SuratPengantar::all(),
            'supir' => Supir::all(),
            'trado' => Trado::all(),
            'kota' => Kota::all(),
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
            $table->string('nobukti', 50)->default('');
            $table->string('tglbukti', 50)->default('');
            $table->string('statusritasi', 50)->default('');
            $table->string('suratpengantar_nobukti', 50)->default('');
            $table->string('supir_id', 50)->default('');
            $table->string('trado_id', 50)->default('');
            $table->string('jarak', 50)->default('');
            $table->string('gaji', 50)->default('');
            $table->string('dari_id', 50)->default('');
            $table->string('sampai_id', 50)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = DB::table((new Ritasi())->getTable())->select(
                'ritasi.id as id_',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'ritasi.statusritasi',
                'ritasi.suratpengantar_nobukti',
                'ritasi.supir_id',
                'ritasi.trado_id',
                'ritasi.jarak',
                'ritasi.gaji',
                'ritasi.dari_id',
                'ritasi.sampai_id',
                'ritasi.modifiedby',
                'ritasi.created_at',
                'ritasi.updated_at'
            )
                ->orderBy('ritasi.id', $params['sortorder']);
        } else if ($params['sortname'] == 'nobukti' or $params['sortname'] == 'tglbukti') {
            $query = DB::table((new Ritasi())->getTable())->select(
                'ritasi.id as id_',
                'ritasi.nobukti',
                'ritasi.tglbukti',
                'ritasi.statusritasi',
                'ritasi.suratpengantar_nobukti',
                'ritasi.supir_id',
                'ritasi.trado_id',
                'ritasi.jarak',
                'ritasi.gaji',
                'ritasi.dari_id',
                'ritasi.sampai_id',
                'ritasi.modifiedby',
                'ritasi.created_at',
                'ritasi.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('ritasi.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = DB::table((new Ritasi())->getTable())->select(
                    'ritasi.id as id_',
                    'ritasi.nobukti',
                    'ritasi.tglbukti',
                    'ritasi.statusritasi',
                    'ritasi.suratpengantar_nobukti',
                    'ritasi.supir_id',
                    'ritasi.trado_id',
                    'ritasi.jarak',
                    'ritasi.gaji',
                    'ritasi.dari_id',
                    'ritasi.sampai_id',
                    'ritasi.modifiedby',
                    'ritasi.created_at',
                    'ritasi.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('ritasi.id', $params['sortorder']);
            } else {
                $query = DB::table((new Ritasi())->getTable())->select(
                    'ritasi.id as id_',
                    'ritasi.nobukti',
                    'ritasi.tglbukti',
                    'ritasi.statusritasi',
                    'ritasi.suratpengantar_nobukti',
                    'ritasi.supir_id',
                    'ritasi.trado_id',
                    'ritasi.jarak',
                    'ritasi.gaji',
                    'ritasi.dari_id',
                    'ritasi.sampai_id',
                    'ritasi.modifiedby',
                    'ritasi.created_at',
                    'ritasi.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('ritasi.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'nobukti', 'tglbukti', 'statusritasi','suratpengantar_nobukti','supir_id','trado_id','jarak','gaji','dari_id','sampai_id', 'modifiedby', 'created_at', 'updated_at'], $query);


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
