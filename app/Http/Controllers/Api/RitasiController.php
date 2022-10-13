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
        $ritasi = new Ritasi();
        return response([
            'data' => $ritasi->get(),
            'attributes' => [
                'totalRows' => $ritasi->totalRows,
                'totalPages' => $ritasi->totalPages
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
    public function store(StoreRitasiRequest $request)
    {
        DB::beginTransaction();

        try {
            
            $group = 'RITASI';
            $subgroup = 'RITASI';
            $format = DB::table('parameter')
            ->where('grp', $group )
            ->where('subgrp', $subgroup)
            ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'ritasi';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $ritasi = new Ritasi();
            $ritasi->tglbukti = date('Y-m-d',strtotime($request->tglbukti));
            $ritasi->statusritasi = $request->statusritasi;
            $ritasi->suratpengantar_nobukti = $request->suratpengantar_nobukti;
            $ritasi->supir_id = $request->supir_id;
            $ritasi->trado_id = $request->trado_id;
            $ritasi->dari_id = $request->dari_id;
            $ritasi->sampai_id = $request->sampai_id;
            // $upahRitasi = UpahDB::table((new Ritasi())->getTable())->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();

            $upahRitasi = DB::table('upahritasi')->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();

            if ($upahRitasi == '') {
                return response([
                    'status' => false,
                    'errors' => [
                        'dari' => 'Kota Dari belum terdaftar di master Upah Ritasi',
                        'sampai' => 'Kota Sampai belum terdaftar di master Upah Ritasi'
                    ],
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ], 422);
            } else {
                $upahRitasiId = $upahRitasi->id;
                $upahRitasiRincian = DB::table('upahritasirincian')->where('upahritasi_id', $upahRitasiId)->first();
                // $ritasi->jarak = $upahRitasi->upahritasiRincian()->first()->liter;
                // $ritasi->gaji = $upahRitasi->upahritasiRincian()->first()->nominalsupir;
                
                $ritasi->jarak = $upahRitasiRincian->liter;
                $ritasi->gaji = $upahRitasiRincian->nominalsupir;
                $ritasi->modifiedby = auth('api')->user()->name;
                $ritasi->statusformat = $format->id;
    
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
                $selected = $this->getPosition($ritasi, $ritasi->getTable());
                $ritasi->position = $selected->position;
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
    
                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $ritasi
                ]);
            }
            
            
           
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = Ritasi::find($id);
        return response([
            'status' => true,
            'data' => $data
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
            // $upahRitasi = UpahDB::table((new Ritasi())->getTable())->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();

            $upahRitasi = DB::table('upahritasi')->where('kotadari_id',$request->dari_id)->where('kotasampai_id',$request->sampai_id)->first();

            if ($upahRitasi == '') {
                header("HTTP/1.1 400 Bad Request");
                return response([
                    'status' => false,
                    'errors' => 'Kota belum terdaftar',
                    'message' => 'Kota Dari dan Sampai Belum terdaftar di master Upah Ritasi'
                ], 422);
            }
            $upahRitasiId = $upahRitasi->id;
            $upahRitasiRincian = DB::table('upahritasirincian')->where('upahritasi_id', $upahRitasiId)->first();
            // $ritasi->jarak = $upahRitasi->upahritasiRincian()->first()->liter;
            // $ritasi->gaji = $upahRitasi->upahritasiRincian()->first()->nominalsupir;
            
            $ritasi->jarak = $upahRitasiRincian->liter;
            $ritasi->gaji = $upahRitasiRincian->nominalsupir;
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
                $selected = $this->getPosition($ritasi, $ritasi->getTable());
                $ritasi->position = $selected->position;
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));

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

            $selected = $this->getPosition($ritasi, $ritasi->getTable(), true);
            $ritasi->position = $selected->position;
            $ritasi->id = $selected->id;
            $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));

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
