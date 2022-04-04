<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Controllers\Controller;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
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

class KasGantungHeaderController extends Controller
{

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

    public function store(StoreKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $bank = Bank::find($request->bank_id);

            $kasgantungHeader = new KasGantungHeader();
            $kasgantungHeader->nobukti = $request->nobukti;
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
                    'postingdari' => 'ENTRY KAS GANTUNG',
                    'idtrans' => $kasgantungHeader->id,
                    'nobuktitrans' => $kasgantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $kasgantungHeader->toArray(),
                    'modifiedby' => $kasgantungHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }
            
            /* Store detail */
            $detaillog=[];
            for ($i = 0; $i < count($request->nominal); $i++) {
                $datadetail = [
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $request->nobukti,
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
                    'nobukti' => $request->nobukti,
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
            ->where('nobuktitrans', '=', $request->nobukti)
            ->where('namatabel', '=', $kasgantungHeader->getTable())
            ->orderBy('id', 'DESC')
            ->first(); 

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY KAS GANTUNG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $request->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kasgantungHeader->save() && $kasgantungHeader->kasgantungDetail) {
                if ($request->nobuktikaskeluar != '') {
                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','BELUM APPROVED');

                    $jurnalHeader = [
                        'nobukti' => $request->nobuktikaskeluar,
                        'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'keterangan' => $request->keterangan,
                        'postingdari' => "ENTRY KAS GANTUNG",
                        'statusapproval' => $statusApp->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => $request->modifiedby,
                    ];

                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','1.2.3.4');

                    $jurnalDetail = [
                        [
                            'nobukti' => $request->nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $coaKasKeluar->text,
                            'nominal' => array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ],
                        [
                            'nobukti' => $request->nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $bank->coa,
                            'nominal' => -array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ]
                    ];

                    $jurnal = $this->storeJurnal($jurnalHeader , $jurnalDetail);

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

    public function update(StoreKasGantungHeaderRequest $request, KasGantungHeader $kasGantungHeader, $id)
    {
        DB::beginTransaction();

        try {
            $bank = Bank::find($request->bank_id);

            /* Store header */
            $kasgantungHeader = KasGantungHeader::findOrFail($id);
            $kasgantungHeader->nobukti = $request->nobukti;
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
                    'postingdari' => 'ENTRY KAS GANTUNG',
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
            JurnalUmumDetail::where('nobukti',$request->nobuktikaskeluar)->delete();
            JurnalUmumHeader::where('nobukti',$request->nobuktikaskeluar)->delete();

            /* Store detail */
            $detaillog=[];
            for ($i = 0; $i < count($request->nominal); $i++) {
                
                $datadetail = [
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $request->nobukti,
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
                    'nobukti' => $request->nobukti,
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
            ->where('nobuktitrans', '=', $request->nobukti)
            ->where('namatabel', '=', $kasgantungHeader->getTable())
            ->orderBy('id', 'DESC')
            ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT ENTRY KAS GANTUNG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $request->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kasgantungHeader && $kasgantungHeader->kasgantungDetail) {
                if ($request->nobuktikaskeluar != '') {
                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','BELUM APPROVED');

                    $jurnalHeader = [
                        'nobukti' => $request->nobuktikaskeluar,
                        'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'keterangan' => $request->keterangan,
                        'postingdari' => "ENTRY KAS GANTUNG",
                        'statusapproval' => $statusApp->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => $request->modifiedby,
                    ];

                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','1.2.3.4');

                    $jurnalDetail = [
                        [
                            'nobukti' => $request->nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $coaKasKeluar->text,
                            'nominal' => array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ],
                        [
                            'nobukti' => $request->nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $bank->coa,
                            'nominal' => -array_sum($request->nominal),
                            'keterangan' => $request->keterangan,
                            'modifiedby' => $request->modifiedby,
                        ]
                    ];

                    $jurnal = $this->storeJurnal($jurnalHeader , $jurnalDetail);

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

    public function destroy(KasGantungHeader $kasGantungHeader, $id, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = KasGantungHeader::find($id);
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

    private function storeJurnal($header,$detail) {
        try {
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

        } catch (\Throwable $th) {
            return [
                'status' => true,
                'message' => $th->getMessage(),
            ];
        }
    }
}
