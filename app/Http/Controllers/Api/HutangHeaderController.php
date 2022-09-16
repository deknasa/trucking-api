<?php

namespace App\Http\Controllers\Api;

use App\Models\HutangHeader;
use App\Models\HutangDetail;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use PhpParser\Builder\Param;

class HutangHeaderController extends Controller
{
    /**
     * @ClassName index
     */
    public function index()
    {
        $hutang = new HutangHeader();

        return response([
            'data' => $hutang->get(),
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    public function show($id)
    {
        $data = HutangHeader::with(
            'hutangdetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'bank'          => Bank::all(),
            'coa'           => AkunPusat::all(),
            'parameter'     => Parameter::all(),

            'statuskas'     => Parameter::where('grp', 'STATUS KAS')->get(),
            'statusapproval' => Parameter::where('grp', 'STATUS APPROVAL')->get(),
            'statusberkas'  => Parameter::where('grp', 'STATUS BERKAS')->get(),

        ];

        return response([
            'data' => $data
        ]);
    }


    /**
     * @ClassName update
     */
    public function update(StoreHutangHeaderRequest $request, HutangHeader $hutangHeader, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $hutangHeader = HutangHeader::findOrFail($id);
            $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->postingdari = $request->postingdari ?? 'ENTRU HUTANG';
            $hutangHeader->diterimadari = $request->diterimadari ?? '';
            $hutangHeader->bank_id = $request->bank_id ?? '';
            $hutangHeader->coa = $request->coa ?? '';
            $hutangHeader->modifiedby = auth('api')->user()->name;

            if ($hutangHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangHeader->getTable()),
                    'postingdari' => 'EDIT HUTANG',
                    'idtrans' => $hutangHeader->id,
                    'nobuktitrans' => $hutangHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $hutangHeader->toArray(),
                    'modifiedby' => $hutangHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            /* Delete existing detail */
            $hutangHeader->hutangDetail()->delete();
            JurnalUmumDetail::where('nobukti', $hutangHeader->nobukti)->delete();
            JurnalUmumHeader::where('nobukti', $hutangHeader->nobukti)->delete();

            /* Store detail */
            $detaillog = [];

            $total = 0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supir_id' => $hutangHeader->supir,
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreHutangDetailRequest($datadetail);
                $datadetails = app(HutangDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supir_id' => $hutangHeader->supir_id[$i],
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $hutangHeader->nobukti)
                ->where('namatabel', '=', $hutangHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT HUTANG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();

            /* Set position and page */
            $hutangHeader->position = DB::table((new HutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $hutangHeader->{$request->sortname})
                ->where('id', '<=', $hutangHeader->id)
                ->count();

            if (isset($request->limit)) {
                $hutangHeader->page = ceil($hutangHeader->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response($hutangHeader->hutangdetail());
    }

    /**
     * @ClassName destroy
     */
    public function destroy($id, JurnalUmumHeader $jurnalumumheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = HutangHeader::find($id);
            // $get = JurnalUmumDetail::find($id);
            // $get = JurnalUmumHeader::find($id);

            $delete = HutangDetail::where('hutang_id', $id)->delete();
            // $delete = JurnalUmumHeader::where('nobukti', $get->nobukti)->delete();
            // $delete = JurnalUmumDetail::where('nobukti', $get->nobukti)->delete();

            $delete = HutangHeader::destroy($id);
            // $delete = JurnalUmumHeader::destroy($id);
            // $delete = JurnalUmumDetail::destroy($id);


            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE HUTANG',
                'idtrans' => $id,
                'nobuktitrans' => '',
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
            throw $th;
        }
    }

    /**
     * @ClassName store
     */
    public function store(StoreHutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $group = 'HUTANG BUKTI';
            $subgroup = 'HUTANG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            
            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'hutangheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $hutangHeader = new HutangHeader();

            $hutangHeader->tgl = date('Y-m-d', strtotime($request->tgl));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->coa = $request->coa ?? '';
            $hutangHeader->total = $request->total ?? '';
            $hutangHeader->postingdari = $request->postingdari ?? 'HUTANG HEADER';
            $hutangHeader->modifiedby = auth('api')->user()->name;
            $hutangHeader->statusformat = $format->id;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $hutangHeader->nobukti = $nobukti;

            try {
                $hutangHeader->save();
            } catch (\Exception $e) {
                dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($hutangHeader->getTable()),
                'postingdari' => 'ENTRY HUTANG HEADER',
                'idtrans' => $hutangHeader->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $hutangHeader->toArray(),
                'modifiedby' => $hutangHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];

            $detaillog = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {
            
                $total = str_replace('.00','', $request->total_detail[$i]);
                $cicilan = str_replace('.00','', $request->cicilan_detail[$i]);
                $totalbayar = str_replace('.00','', $request->totalbayar_detail[$i]);


                $datadetail = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supplier_id' => $hutangHeader->supplier_id,
                    'tgljatuhtempo' => $hutangHeader->tgljatuhtempo,
                    'total' => str_replace(',', '',$total),
                    'cicilan' => str_replace(',', '',$cicilan),
                    'totalbayar' => str_replace(',', '',$totalbayar),
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $hutangHeader->modifiedby,
                ];

                $data = new StoreHutangDetailRequest($datadetail);
                $datadetails = app(HutangDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'id' => $iddetail,
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supplier_id' => $hutangHeader->supplier_id,
                    'tgljatuhtempo' => $hutangHeader->tgljatuhtempo,
                    'total' => str_replace(',', '',$total),
                    'cicilan' => str_replace(',', '',$cicilan),
                    'totalbayar' => str_replace(',', '',$totalbayar),
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $hutangHeader->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->updated_at)),
                    
                ];

                $detaillog[] = $datadetaillog;

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY HUTANG DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $hutangHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $hutangHeader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

           
            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $coahutang = DB::table('parameter')
            ->where('grp', 'COA HUTANG MANUAL')->get();

            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $hutangHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'keterangan' => $request->keterangan,
                'postingdari' => "ENTRY HUTANG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [];

            for ($i = 0; $i < count($request->total_detail); $i++) {
                $detail = [];

                foreach($coahutang as $key => $coa){
                    $a = 0;
                    $getcoa = DB::table('akunpusat')
                    ->where('id', $coa->text)->first();
                    
                    $total = str_replace('.00','', $request->total_detail[$i]);
                    $cicilan = str_replace('.00','', $request->cicilan_detail[$i]);
                    $totalbayar = str_replace('.00','', $request->totalbayar_detail[$i]);
    
                    
                    $jurnalDetail = [ 
                        [
                        'nobukti' => $hutangHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($hutangHeader->tglbukti)),
                        'coa' =>  $getcoa->coa,
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => $i,
                        ]
                    ];
                    if($coa->subgrp == 'KREDIT'){
                        $jurnalDetail[$a]['total'] = str_replace(',', '',$total);
                        $jurnalDetail[$a]['cicilan'] = str_replace(',', '',$cicilan);
                        $jurnalDetail[$a]['totalbayar'] = str_replace(',', '',$totalbayar);

                    }else{
                        $jurnalDetail[$a]['total'] = str_replace(',', '',$total);
                        $jurnalDetail[$a]['cicilan'] = str_replace(',', '',$cicilan);
                        $jurnalDetail[$a]['totalbayar'] = str_replace(',', '',$totalbayar);

                    }
                
                    $detail = array_merge($detail, $jurnalDetail);
                    $a++;
                }
                    $jurnaldetail = array_merge($jurnaldetail, $detail);
            }

            ///

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $hutangHeader->nobukti)
                ->where('namatabel', '=', $hutangHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];
            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();

            /* Set position and page */
            $hutangHeader->position = DB::table((new HutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $hutangHeader->{$request->sortname})
                ->where('id', '<=', $hutangHeader->id)
                ->count();

            if (isset($request->limit)) {
                $hutangHeader->page = ceil($hutangHeader->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response($hutangHeader->hutangdetail());
    }
}
