<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Controllers\Controller;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\Bank;
use App\Models\Penerima;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\JurnalUmumHeaderRequest;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;

class KasGantungHeaderController extends Controller
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
            'withRelations' => $request->withRelations ?? false,
        ];

        $totalRows = KasGantungHeader::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = KasGantungHeader::orderBy($params['sortIndex'], $params['sortOrder']);

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
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

        $parameters = $params['withRelations'] == true
            ? $query->with(
                'kasgantungDetail',
                'bank',
            )->get()
            : $query->with(
                'kasgantungDetail',
                'bank',
                'penerima'
            )->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows,
            'totalPages' => $totalPages
        ];

        return response([
            'status' => true,
            'data' => $parameters,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        
    }
      /**
     * @ClassName 
     */
    public function store(StoreKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $bank = Bank::find($request->bank_id);

            $content = new Request();
            $content['group'] = 'KASGANTUNG';
            $content['subgroup'] = 'KASGANTUNG';
            $content['table'] = 'kasgantungheader';

            
            $kasgantungHeader = new KasGantungHeader();
            $kasgantungHeader->tgl = date('Y-m-d', strtotime($request->tgl));
            $kasgantungHeader->penerima_id = $request->penerima_id;
            $kasgantungHeader->keterangan = $request->keterangan ?? '';
            $kasgantungHeader->bank_id = $request->bank_id ?? 0;
            $kasgantungHeader->nobuktikaskeluar = $request->nobuktikaskeluar ?? '';
            $kasgantungHeader->coakaskeluar = $bank->coa ?? '';
            $kasgantungHeader->postingdari = 'ENTRY KAS GANTUNG';
            $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($request->tglkaskeluar));
            $kasgantungHeader->modifiedby = $request->modifiedby;
            
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $kasgantungHeader->nobukti = $nobukti;

            try {
                $kasgantungHeader->save();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($kasgantungHeader->getTable()),
                'postingdari' => 'ENTRY KAS GANTUNG',
                'idtrans' => $kasgantungHeader->id,
                'nobuktitrans' => $kasgantungHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $kasgantungHeader->toArray(),
                'modifiedby' => $kasgantungHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            /* Store detail */
            $detaillog=[];
            for ($i = 0; $i < count($request->nominal); $i++) {
                $datadetail = [
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $kasgantungHeader->nobukti,
                    'nominal' => $request->nominal[$i],
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $request->modifiedby,
                    ];
                $data = new StoreKasGantungDetailRequest($datadetail);
                $datadetails = app(KasGantungDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail=$datadetails['id'];
                    $tabeldetail=$datadetails['tabel'];
                }

                $datadetaillog = [
                    'id' => $iddetail,
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $kasgantungHeader->nobukti,
                    'nominal' => $request->nominal[$i],
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $request->modifiedby,
                    'created_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->updated_at)),
                    ];
                $detaillog[]=$datadetaillog;
            }

            $dataid = LogTrail::select('id')
            ->where('nobuktitrans', '=', $kasgantungHeader->nobukti)
            ->where('namatabel', '=', $kasgantungHeader->getTable())
            ->orderBy('id', 'DESC')
            ->first(); 
            
            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY KAS GANTUNG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $kasgantungHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kasgantungHeader->save() && $kasgantungHeader->kasgantungDetail) {
                if ($request->bank_id != '') {
                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');
                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','09.01.01.03');

                    $content = new Request();
                    $content['group'] = 'NOBUKTI';
                    $content['subgroup'] = 'KASKELUAR';
                    $content['table'] = 'pengeluaranheader';

                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];
                    
                    $kasgantungHeader->nobuktikaskeluar = $nobuktikaskeluar;
                    $kasgantungHeader->save();

                    $pengeluaranHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'pelanggan_id' => 0,
                        'keterangan' => $request->keterangan,
                        'statusjenistransaksi' => 0,
                        'postingdari' => 'ENTRY KAS GANTUNG',
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
                        'nominal' => array_sum($request->nominal),
                        'coadebet' => $coaKasKeluar->text,
                        'coakredit' => $coaKasKeluar->text,
                        'keterangan' => $request->keterangan,
                        'bulanbeban' => '',
                        'modifiedby' => $request->modifiedby,
                    ];

                    $jurnalHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'keterangan' => $request->keterangan,
                        'postingdari' => "ENTRY KAS GANTUNG",
                        'statusapproval' => $statusApp->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => $request->modifiedby,
                    ];

                    $jurnalDetail = [
                        [
                            'nobukti' => $nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $coaKasKeluar->text,
                            'nominal' => array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ],
                        [
                            'nobukti' => $nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $bank->coa ?? '',
                            'nominal' => -array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ]
                    ];

                    $jurnal = $this->storeJurnal($pengeluaranHeader,$pengeluaranDetail,$jurnalHeader , $jurnalDetail);
                    
                    if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                        goto ATAS;
                    }

                    if (!$jurnal['status']) {
                        throw new \Throwable($jurnal['message']);
                    }

                }

                DB::commit();

                /* Set position and page */
                $kasgantungHeader->position = KasGantungHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $kasgantungHeader->{$request->sortname})
                    ->where('id', '<=', $kasgantungHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $kasgantungHeader->page = ceil($kasgantungHeader->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $kasgantungHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($kasgantungHeader->kasgantungDetail);
    }

    public function show(KasGantungHeader $kasGantungHeader,$id)
    {
        $data = KasGantungHeader::with(
            'kasgantungDetail',
            // 'absensiSupirDetail.trado',
            // 'absensiSupirDetail.supir',
            // 'absensiSupirDetail.absenTrado',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function edit(KasGantungHeader $kasGantungHeader)
    {
        //
    }
      /**
     * @ClassName 
     */
    public function update(StoreKasGantungHeaderRequest $request, KasGantungHeader $kasGantungHeader, $id)
    {
        DB::beginTransaction();

        try {
            $bank = Bank::find($request->bank_id);

            /* Store header */
            $kasgantungHeader = KasGantungHeader::findOrFail($id);
            $kasgantungHeader->tgl = date('Y-m-d', strtotime($request->tgl));
            $kasgantungHeader->penerima_id = $request->penerima_id;
            $kasgantungHeader->keterangan = $request->keterangan ?? '';
            $kasgantungHeader->bank_id = $request->bank_id ?? 0;
            $kasgantungHeader->nobuktikaskeluar = $request->nobuktikaskeluar ?? '';
            $kasgantungHeader->coakaskeluar = $bank->coa ?? '';
            $kasgantungHeader->postingdari = 'ENTRY KAS GANTUNG';
            $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($request->tglkaskeluar));
            $kasgantungHeader->modifiedby = $request->modifiedby;
            
            if ($kasgantungHeader->save()) {
           
                $logTrail = [
                    'namatabel' => strtoupper($kasgantungHeader->getTable()),
                    'postingdari' => 'EDIT KAS GANTUNG',
                    'idtrans' => $kasgantungHeader->id,
                    'nobuktitrans' => $kasgantungHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $kasgantungHeader->toArray(),
                    'modifiedby' => $kasgantungHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }
            
            
            /* Delete existing detail */
            $kasgantungHeader->kasgantungDetail()->delete();
            PengeluaranDetail::where('nobukti',$request->nobuktikaskeluar)->delete();
            PengeluaranHeader::where('nobukti',$request->nobuktikaskeluar)->delete();
            JurnalUmumDetail::where('nobukti',$request->nobuktikaskeluar)->delete();
            JurnalUmumHeader::where('nobukti',$request->nobuktikaskeluar)->delete();

            /* Store detail */
            $detaillog=[];
            for ($i = 0; $i < count($request->nominal); $i++) {
                
                $datadetail = [
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $kasgantungHeader->nobukti,
                    'nominal' => $request->nominal[$i],
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $request->modifiedby,
                    ];
                $data = new StoreKasGantungDetailRequest($datadetail);
                $datadetails = app(KasGantungDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail=$datadetails['id'];
                    $tabeldetail=$datadetails['tabel'];
                }

                $datadetaillog = [
                    'id' => $iddetail,
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $kasgantungHeader->nobukti,
                    'nominal' => $request->nominal[$i],
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $request->modifiedby,
                    'created_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->updated_at)),
                    ];
                $detaillog[]=$datadetaillog;
            }

            $dataid = LogTrail::select('id')
            ->where('nobuktitrans', '=', $kasgantungHeader->nobukti)
            ->where('namatabel', '=', $kasgantungHeader->getTable())
            ->orderBy('id', 'DESC')
            ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT ENTRY KAS GANTUNG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $kasgantungHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kasgantungHeader && $kasgantungHeader->kasgantungDetail) {
                $kasgantungHeader->nobuktikaskeluar = '-';
                $kasgantungHeader->save();

                if ($request->bank_id != '') {
                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');
                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','09.01.01.03');

                    $content = new Request();
                    $content['group'] = 'NOBUKTI';
                    $content['subgroup'] = 'KASKELUAR';
                    $content['table'] = 'pengeluaranheader';
                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];

                    $kasgantungHeader->nobuktikaskeluar = $nobuktikaskeluar;
                    $kasgantungHeader->save();
                    
                    $pengeluaranHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'pelanggan_id' => 0,
                        'keterangan' => $request->keterangan,
                        'statusjenistransaksi' => 0,
                        'postingdari' => 'ENTRY KAS GANTUNG',
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
                        'nominal' => array_sum($request->nominal),
                        'coadebet' => $coaKasKeluar->text,
                        'coakredit' => $coaKasKeluar->text,
                        'keterangan' => $request->keterangan,
                        'bulanbeban' => '',
                        'modifiedby' => $request->modifiedby,
                    ];

                    $jurnalHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'keterangan' => $request->keterangan,
                        'postingdari' => "ENTRY KAS GANTUNG",
                        'statusapproval' => $statusApp->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => $request->modifiedby,
                    ];

                    $jurnalDetail = [
                        [
                            'nobukti' => $nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $coaKasKeluar->text,
                            'nominal' => array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ],
                        [
                            'nobukti' => $nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $bank->coa ?? '',
                            'nominal' => -array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ]
                    ];

                    $jurnal = $this->storeJurnal($pengeluaranHeader,$pengeluaranDetail,$jurnalHeader , $jurnalDetail);
                    
                    if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                        goto ATAS;
                    }

                    if (!$jurnal['status']) {
                        throw new \Throwable($jurnal['message']);
                    }
                }

                DB::commit();

                /* Set position and page */
                $kasgantungHeader->position = KasGantungHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $kasgantungHeader->{$request->sortname})
                    ->where('id', '<=', $kasgantungHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $kasgantungHeader->page = ceil($kasgantungHeader->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $kasgantungHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($kasgantungHeader->kasgantungDetail);
    }
      /**
     * @ClassName 
     */
    public function destroy(KasGantungHeader $kasGantungHeader, $id, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = KasGantungHeader::find($id);
            $delete = PengeluaranDetail::where('nobukti',$get->nobuktikaskeluar)->delete();
            $delete = PengeluaranHeader::where('nobukti',$get->nobuktikaskeluar)->delete();
            $delete = JurnalUmumDetail::where('nobukti',$get->nobuktikaskeluar)->delete();
            $delete = JurnalUmumHeader::where('nobukti',$get->nobuktikaskeluar)->delete();
            $delete = KasGantungDetail::where('kasgantung_id',$id)->delete();
            $delete = KasGantungHeader::destroy($id);
            
            $datalogtrail = [
                'namatabel' => $kasGantungHeader->getTable(),
                'postingdari' => 'HAPUS KAS GANTUNG',
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
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus'
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
            'penerima' => Penerima::all(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    private function storeJurnal($pengeluaranHeader,$pengeluaranDetail,$header,$detail) {
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
            
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);
            
            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals['id'];

                $jurnal = new StoreJurnalUmumDetailRequest($value);
                app(JurnalUmumDetailController::class)->store($jurnal);
            }

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
