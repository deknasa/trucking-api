<?php

namespace App\Http\Controllers\Api;

use App\Models\ProsesAbsensiSupir;
use App\Http\Requests\StoreProsesAbsensiSupirRequest;
use App\Http\Requests\UpdateProsesAbsensiSupirRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\AbsensiSupirHeader;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use Illuminate\Database\QueryException;
class ProsesAbsensiSupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
        ];

        $totalRows = ProsesAbsensiSupir::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = ProsesAbsensiSupir::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = ProsesAbsensiSupir::select(
                'prosesabsensisupir.id',
                'prosesabsensisupir.nobukti',
                'prosesabsensisupir.tglbukti',
                'prosesabsensisupir.keterangan',
                'prosesabsensisupir.pengeluaran_nobukti',
                'prosesabsensisupir.absensisupir_nobukti',
                'prosesabsensisupir.nominal',
                'prosesabsensisupir.modifiedby',
                'prosesabsensisupir.created_at',
                'prosesabsensisupir.updated_at'
            )
            ->orderBy('prosesabsensisupir.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'nobukti' or $params['sortIndex'] == 'keterangan') {
            $query = ProsesAbsensiSupir::select(
                'prosesabsensisupir.id',
                'prosesabsensisupir.nobukti',
                'prosesabsensisupir.tglbukti',
                'prosesabsensisupir.keterangan',
                'prosesabsensisupir.pengeluaran_nobukti',
                'prosesabsensisupir.absensisupir_nobukti',
                'prosesabsensisupir.nominal',
                'prosesabsensisupir.modifiedby',
                'prosesabsensisupir.created_at',
                'prosesabsensisupir.updated_at'
            )
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('prosesabsensisupir.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = ProsesAbsensiSupir::select(
                'prosesabsensisupir.id',
                'prosesabsensisupir.nobukti',
                'prosesabsensisupir.tglbukti',
                'prosesabsensisupir.keterangan',
                'prosesabsensisupir.pengeluaran_nobukti',
                'prosesabsensisupir.absensisupir_nobukti',
                'prosesabsensisupir.nominal',
                'prosesabsensisupir.modifiedby',
                'prosesabsensisupir.created_at',
                'prosesabsensisupir.updated_at'
            )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('prosesabsensisupir.id', $params['sortOrder']);
            } else {
                $query = ProsesAbsensiSupir::select(
                    'prosesabsensisupir.id',
                    'prosesabsensisupir.nobukti',
                    'prosesabsensisupir.tglbukti',
                    'prosesabsensisupir.keterangan',
                    'prosesabsensisupir.pengeluaran_nobukti',
                    'prosesabsensisupir.absensisupir_nobukti',
                    'prosesabsensisupir.nominal',
                    'prosesabsensisupir.modifiedby',
                    'prosesabsensisupir.created_at',
                    'prosesabsensisupir.updated_at'
                )
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('prosesabsensisupir.id', 'asc');
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
                            $query = $query->where('prosesabsensisupir.'.$search['field'], 'LIKE', "%$search[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        if ($search['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter.text', 'LIKE', "%$search[data]%");
                        } else {
                            $query = $query->orWhere('prosesabsensisupir.'.$search['field'], 'LIKE', "%$search[data]%");
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

        $prosesabsensisupir = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $prosesabsensisupir,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreProsesAbsensiSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $bank = Bank::where('kodebank','KAS TRUCKING')->first();

            $content = new Request();
            $content['group'] = 'PROSESABSENSISUPIR';
            $content['subgroup'] = 'PROSESABSENSISUPIR';
            $content['table'] = 'prosesabsensisupir';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $prosesabsensisupir = new ProsesAbsensiSupir();
            $prosesabsensisupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesabsensisupir->keterangan = $request->keterangan;
            $prosesabsensisupir->absensisupir_nobukti = $request->absensisupir_nobukti;
            $prosesabsensisupir->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $prosesabsensisupir->nominal = $request->nominal ?? '';
            $prosesabsensisupir->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $prosesabsensisupir->nobukti = $nobukti;
            $isSave = $prosesabsensisupir->save();

            // try {
                
            // } catch (\Exception $e) {
            //     dd($e);
            //     $errorCode = @$e->errorInfo[1];
            //     if ($errorCode == 2601) {
            //         goto TOP;
            //     } else {
            //         DB::rollBack();
            //         throw $th;
            //     }
            // }

            $logTrail = [
                'namatabel' => strtoupper($prosesabsensisupir->getTable()),
                'postingdari' => 'ENTRY PROSES ABSENSI SUPIR',
                'idtrans' => $prosesabsensisupir->id,
                'nobuktitrans' => $prosesabsensisupir->id,
                'aksi' => 'ENTRY',
                'datajson' => $prosesabsensisupir->toArray(),
                'modifiedby' => $prosesabsensisupir->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            if ($isSave) {
                $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');
                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','09.01.01.03');

                    $content = new Request();
                    $content['group'] = 'PENGELUARAN KAS';
                    $content['subgroup'] = 'NOMOR  PENGELUARAN KAS';
                    $content['table'] = 'pengeluaranheader';
                    $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];
                    
                    $prosesabsensisupir->pengeluaran_nobukti = $nobuktikaskeluar;
                    $prosesabsensisupir->save();

                    $pengeluaranHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => 0,
                        'keterangan' => $request->keterangan,
                        'statusjenistransaksi' => 0,
                        'postingdari' => 'ENTRY PROSES ABSENSI SUPIR',
                        'statusapproval' => $statusApp->id,
                        'dibayarke' => '',
                        'cabang_id' => 1, // masih manual karena belum di catat di session
                        'bank_id' => $bank->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'transferkeac' => '',
                        'transferkean' => '',
                        'trasnferkebank' => '',
                        'modifiedby' => $request->modifiedby,
                    ];

                    $pengeluaranDetail = [
                        'nobukti' => $nobuktikaskeluar,
                        'alatbayar_id' => 2,
                        'nowarkat' => '',
                        'tgljatuhtempo' => '',
                        'nominal' => $request->nominal,
                        'coadebet' => $coaKasKeluar->text,
                        'coakredit' => $coaKasKeluar->text,
                        'keterangan' => $request->keterangan,
                        'bulanbeban' => '',
                        'modifiedby' => $request->modifiedby,
                    ];

                    $jurnal = $this->storeJurnal($pengeluaranHeader,$pengeluaranDetail);
                    
                    if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                        goto ATAS;
                    }

                    if (!$jurnal['status']) {
                        throw new \Throwable($jurnal['message']);
                    }
            }

            DB::commit();


            /* Set position and page */
            $del = 0;
            $data = $this->getid($prosesabsensisupir->id, $request, $del);
            $prosesabsensisupir->position = $data->row;

            if (isset($request->limit)) {
                $prosesabsensisupir->page = ceil($prosesabsensisupir->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesabsensisupir
            ], 201);
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(ProsesAbsensiSupir $prosesabsensisupir)
    {
        return response([
            'status' => true,
            'data' => $prosesabsensisupir
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdateProsesAbsensiSupirRequest $request, ProsesAbsensiSupir $prosesabsensisupir)
    {
        DB::beginTransaction();

        try {
            $bank = Bank::where('kodebank','KAS TRUCKING')->first();

            $prosesabsensisupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesabsensisupir->keterangan = $request->keterangan;
            $prosesabsensisupir->absensisupir_nobukti = $request->absensisupir_nobukti;
            $prosesabsensisupir->nominal = $request->nominal;
            $prosesabsensisupir->modifiedby = $request->modifiedby;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($prosesabsensisupir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($prosesabsensisupir->getTable()),
                    'postingdari' => 'EDIT PROSES ABSENSI SUPIR',
                    'idtrans' => $prosesabsensisupir->id,
                    'nobuktitrans' => $prosesabsensisupir->id,
                    'aksi' => 'EDIT',
                    'datajson' => $prosesabsensisupir->toArray(),
                    'modifiedby' => $prosesabsensisupir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Delete existing detail */
                PengeluaranDetail::where('nobukti',$prosesabsensisupir->pengeluaran_nobukti)->lockForUpdate()->delete();
                PengeluaranHeader::where('nobukti',$prosesabsensisupir->pengeluaran_nobukti)->lockForUpdate()->delete();
                JurnalUmumDetail::where('nobukti',$prosesabsensisupir->pengeluaran_nobukti)->lockForUpdate()->delete();
                JurnalUmumHeader::where('nobukti',$prosesabsensisupir->pengeluaran_nobukti)->lockForUpdate()->delete();

                $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');
                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','09.01.01.03');

                    $content = new Request();
                    $content['group'] = 'NOBUKTI';
                    $content['subgroup'] = 'KASKELUAR';
                    $content['table'] = 'pengeluaranheader';
                    $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];
                    
                    $prosesabsensisupir->pengeluaran_nobukti = $nobuktikaskeluar;
                    $prosesabsensisupir->save();

                    $pengeluaranHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => 0,
                        'keterangan' => $request->keterangan,
                        'statusjenistransaksi' => 0,
                        'postingdari' => 'EDIT PROSES ABSENSI SUPIR',
                        'statusapproval' => $statusApp->id,
                        'dibayarke' => '',
                        'cabang_id' => 1, // masih manual karena belum di catat di session
                        'bank_id' => $bank->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'transferkeac' => '',
                        'transferkean' => '',
                        'trasnferkebank' => '',
                        'modifiedby' => $request->modifiedby,
                    ];

                    $pengeluaranDetail = [
                        'nobukti' => $nobuktikaskeluar,
                        'alatbayar_id' => 2,
                        'nowarkat' => '',
                        'tgljatuhtempo' => '',
                        'nominal' => $request->nominal,
                        'coadebet' => $coaKasKeluar->text,
                        'coakredit' => $coaKasKeluar->text,
                        'keterangan' => $request->keterangan,
                        'bulanbeban' => '',
                        'modifiedby' => $request->modifiedby,
                    ];
                    $jurnal = $this->storePengeluaran($pengeluaranHeader,$pengeluaranDetail);
                    
                    if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                        goto ATAS;
                    }

                    if (!$jurnal['status']) {
                        throw new \Throwable($jurnal['message']);
                    }
            }

            DB::commit();


            /* Set position and page */
            $del = 0;
            $data = $this->getid($prosesabsensisupir->id, $request, $del);
            $prosesabsensisupir->position = $data->row;

            if (isset($request->limit)) {
                $prosesabsensisupir->page = ceil($prosesabsensisupir->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesabsensisupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = ProsesAbsensiSupir::find($id);
            $delete = PengeluaranDetail::where('nobukti',$get->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = PengeluaranHeader::where('nobukti',$get->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumDetail::where('nobukti',$get->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumHeader::where('nobukti',$get->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = ProsesAbsensiSupir::destroy($id);
            
            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'HAPUS PROSES ABSENSI SUPIR',
                'idtrans' => $id,
                'nobuktitrans' => $get->nobukti,
                'aksi' => 'HAPUS',
                'datajson' => '',
                'modifiedby' => $get->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            if ($delete) {
                DB::commit();
                $del = 1;
                $data = $this->getid($get->id, $request, $del);
                $get->position = @$data->row;
                $get->id = @$data->id;
                if (isset($request->limit)) {
                    $get->page = ceil($get->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $get
                ]);
            } else {
                DB::rollBack();
                return response([
                    'status' => false,
                    'message' => 'Gagal dihapus'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'absensisupir' => AbsensiSupirHeader::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('prosesabsensisupir')->getColumns();

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
            $table->string('nobukti', 50)->default('');
            $table->date('tglbukti')->default('');
            $table->string('keterangan', 50)->default('');
            $table->string('pengeluaran_nobukti', 50)->default('');
            $table->string('absensisupir_nobukti', 50)->default('');
            $table->integer('nominal')->default(0);
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = ProsesAbsensiSupir::select(
                'prosesabsensisupir.id as id_',
                'prosesabsensisupir.nobukti',
                'prosesabsensisupir.tglbukti',
                'prosesabsensisupir.keterangan',
                'prosesabsensisupir.pengeluaran_nobukti',
                'prosesabsensisupir.absensisupir_nobukti',
                'prosesabsensisupir.nominal',
                'prosesabsensisupir.modifiedby',
                'prosesabsensisupir.created_at',
                'prosesabsensisupir.updated_at'
            )
                ->orderBy('prosesabsensisupir.id', $params['sortorder']);
        } else if ($params['sortname'] == 'nobukti' or $params['sortname'] == 'keterangan') {
            $query = ProsesAbsensiSupir::select(
                'prosesabsensisupir.id as id_',
                'prosesabsensisupir.nobukti',
                'prosesabsensisupir.tglbukti',
                'prosesabsensisupir.keterangan',
                'prosesabsensisupir.pengeluaran_nobukti',
                'prosesabsensisupir.absensisupir_nobukti',
                'prosesabsensisupir.nominal',
                'prosesabsensisupir.modifiedby',
                'prosesabsensisupir.created_at',
                'prosesabsensisupir.updated_at'
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('prosesabsensisupir.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = ProsesAbsensiSupir::select(
                    'prosesabsensisupir.id as id_',
                    'prosesabsensisupir.nobukti',
                    'prosesabsensisupir.tglbukti',
                    'prosesabsensisupir.keterangan',
                    'prosesabsensisupir.pengeluaran_nobukti',
                    'prosesabsensisupir.absensisupir_nobukti',
                    'prosesabsensisupir.nominal',
                    'prosesabsensisupir.modifiedby',
                    'prosesabsensisupir.created_at',
                    'prosesabsensisupir.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('prosesabsensisupir.id', $params['sortorder']);
            } else {
                $query = ProsesAbsensiSupir::select(
                    'prosesabsensisupir.id as id_',
                    'prosesabsensisupir.nobukti',
                    'prosesabsensisupir.tglbukti',
                    'prosesabsensisupir.keterangan',
                    'prosesabsensisupir.pengeluaran_nobukti',
                    'prosesabsensisupir.absensisupir_nobukti',
                    'prosesabsensisupir.nominal',
                    'prosesabsensisupir.modifiedby',
                    'prosesabsensisupir.created_at',
                    'prosesabsensisupir.updated_at'
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('prosesabsensisupir.id', 'asc');
            }
        }



        DB::table($temp)->insertUsing(['id_', 'nobukti', 'tglbukti', 'keterangan', 'pengeluaran_nobukti', 'absensisupir_nobukti', 'nominal', 'modifiedby', 'created_at', 'updated_at'], $query);


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

    private function storePengeluaran($pengeluaranHeader,$pengeluaranDetail) {
        try {
            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            $pengeluarans = app(PengeluaranHeaderController::class)->store($pengeluaran);
            
            if (@$pengeluarans->original['error'] AND @$pengeluarans->original['errorCode'] == 2601) {
                return [
                    'status' => false,
                    'errorCode' => 2601,
                    'message' => 'Duplicate Nobukti',
                ];
            }
            
            $pengeluaranDetail['pengeluaran_id'] = $pengeluarans['id'];

            $pengeluaran = new StorePengeluaranDetailRequest($pengeluaranDetail);
            $pengeluarans = app(PengeluaranDetailController::class)->store($pengeluaran);
            

            return [
                'status' => true,
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
