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
        $kasgantungHeader = new KasGantungHeader();

        return response([
            'data' => $kasgantungHeader->get(),
            'attributes' => [
                'totalRows' => $kasgantungHeader->totalRows,
                'totalPages' => $kasgantungHeader->totalPages
            ]
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

            $group = 'KAS GANTUNG';
            $subgroup = 'NOMOR KAS GANTUNG';
            $format = DB::table('parameter')
            ->where('grp', $group )
            ->where('subgrp', $subgroup)
            ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'kasgantungheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


            $kasgantungHeader = new KasGantungHeader();
            $kasgantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $kasgantungHeader->penerima_id = $request->penerima_id;
            $kasgantungHeader->keterangan = $request->keterangan ?? '';
            $kasgantungHeader->bank_id = $request->bank_id ?? 0;
            $kasgantungHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $kasgantungHeader->coakaskeluar = $bank->coa ?? '';
            $kasgantungHeader->postingdari = 'ENTRY KAS GANTUNG';
            $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($request->tglkaskeluar));
            $kasgantungHeader->modifiedby = auth('api')->user()->name;
            $kasgantungHeader->statusformat = $format->id;
            
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

            $total = 0;
            
            for ($i = 0; $i < count($request->nominal); $i++) {
                
                $nominal = str_replace(',','',str_replace('.00','',$request->nominal[$i]));
                $datadetail = [
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $kasgantungHeader->nobukti,
                    'nominal' => $nominal,
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => auth('api')->user()->name,
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
                    'nominal' => $nominal,
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->updated_at)),
                    ];
                $detaillog[]=$datadetaillog;

                $total += $nominal;
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
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            
//  
            if ($kasgantungHeader->save() && $kasgantungHeader->kasgantungDetail) {
                if ($request->bank_id != '') {
                    
                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');

                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','09.01.01.03');

                    dd('here');


                    $content = new Request();
                    $content['group'] = 'PENGELUARAN KAS';
                    $content['subgroup'] = 'NOMOR  PENGELUARAN KAS';
                    $content['table'] = 'pengeluaranheader';
                    $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];
                    
                    
                    $kasgantungHeader->pengeluaran_nobukti = $nobuktikaskeluar;
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
                        'statusformat' => '0',
                        'modifiedby' =>  auth('api')->user()->name
                    ];

                    $pengeluaranDetail = [
                        'nobukti' => $nobuktikaskeluar,
                        'alatbayar_id' => 2,
                        'nowarkat' => '',
                        'tgljatuhtempo' => '',
                        'nominal' => $total,
                        'coadebet' => $bank->coa,
                        'coakredit' => $coaKasKeluar->text,
                        'keterangan' => $request->keterangan,
                        'bulanbeban' => '',
                        'modifiedby' =>  auth('api')->user()->name
                    ];
                    dd('here');

                    $pengeluaran = $this->storePengeluaran($pengeluaranHeader,$pengeluaranDetail);
                    
                    if (!$pengeluaran['status'] AND @$pengeluaran['errorCode'] == 2601) {
                        goto ATAS;
                    }

                    if (!$pengeluaran['status']) {
                        throw new \Throwable($pengeluaran['message']);
                    }

                }
            

                DB::commit();

                /* Set position and page */

                $selected = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable());
                $kasgantungHeader->position = $selected->position;
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));

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
            $kasgantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $kasgantungHeader->penerima_id = $request->penerima_id;
            $kasgantungHeader->keterangan = $request->keterangan ?? '';
            $kasgantungHeader->bank_id = $request->bank_id ?? 0;
            $kasgantungHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $kasgantungHeader->coakaskeluar = $bank->coa ?? '';
            $kasgantungHeader->postingdari = 'ENTRY KAS GANTUNG';
            $kasgantungHeader->tglkaskeluar = date('Y-m-d', strtotime($request->tglkaskeluar));
            $kasgantungHeader->modifiedby = auth('api')->user()->name;
            
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
            $total=0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',','',str_replace('.','',$request->nominal[$i]));
                $datadetail = [
                    'kasgantung_id' => $kasgantungHeader->id,
                    'nobukti' => $kasgantungHeader->nobukti,
                    'nominal' => $nominal,
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => auth('api')->user()->name,
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
                    'nominal' => $nominal,
                    'coa' => $bank->coa ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s',strtotime($kasgantungHeader->updated_at)),
                    ];
                $detaillog[]=$datadetaillog;

                $total += $nominal;
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
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($kasgantungHeader && $kasgantungHeader->kasgantungDetail) {
                $kasgantungHeader->pengeluaran_nobukti = '-';
                $kasgantungHeader->save();

                if ($request->bank_id != '') {
                    $parameterController = new ParameterController;
                    $statusApp = $parameterController->getparameterid('STATUS APPROVAL','STATUS APPROVAL','NON APPROVAL');
                    $coaKasKeluar = $parameterController->getparameterid('COA','COAKASKELUAR','09.01.01.03');

                    $content = new Request();
                    $content['group'] = 'PENGELUARAN KAS';
                    $content['subgroup'] = 'NOMOR PENGELUARAN KAS';
                    $content['table'] = 'pengeluaranheader';
                    $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                    
                    ATAS:
                    $nobuktikaskeluar = app(Controller::class)->getRunningNumber($content)->original['data'];

                    $kasgantungHeader->pengeluaran_nobukti = $nobuktikaskeluar;
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
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $pengeluaranDetail = [
                        'nobukti' => $nobuktikaskeluar,
                        'alatbayar_id' => 2,
                        'nowarkat' => '',
                        'tgljatuhtempo' => '',
                        'nominal' => $total,
                        'coadebet' => $coaKasKeluar->text,
                        'coakredit' => $coaKasKeluar->text,
                        'keterangan' => $request->keterangan,
                        'bulanbeban' => '',
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $jurnalHeader = [
                        'nobukti' => $nobuktikaskeluar,
                        'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                        'keterangan' => $request->keterangan,
                        'postingdari' => "ENTRY KAS GANTUNG",
                        'statusapproval' => $statusApp->id,
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $jurnalDetail = [
                        [
                            'nobukti' => $nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $coaKasKeluar->text,
                            'nominal' => $total,
                            'keterangan' => $request->keterangan,
                            'modifiedby' => auth('api')->user()->name,
                        ],
                        [
                            'nobukti' => $nobuktikaskeluar,
                            'tgl' => date('Y-m-d', strtotime($request->tglkaskeluar)),
                            'coa' => $bank->coa ?? '',
                            'nominal' => -$total,
                            'keterangan' => $request->keterangan,
                            'modifiedby' => auth('api')->user()->name,
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
                $selected = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable());
                $kasgantungHeader->position = $selected->position;
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));

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

                $selected = $this->getPosition($kasGantungHeader, $kasGantungHeader->getTable(), true);
                $kasGantungHeader->position = $selected->position;
                $kasGantungHeader->id = $selected->id;
                $kasGantungHeader->page = ceil($kasGantungHeader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $kasGantungHeader
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kasgantungheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
