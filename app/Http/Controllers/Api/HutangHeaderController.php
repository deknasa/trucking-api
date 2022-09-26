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

        $data = HutangHeader::find($id);
        $detail = HutangDetail::getAll($id);

        // dd($details);
        // $datas = array_merge($data, $detail);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
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
    public function update(StoreHutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $hutangHeader = HutangHeader::findOrFail($id);

            $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->coa = $request->akunpusat ?? '';
            $hutangHeader->pelanggan_id = $request->pelanggan_id;
            // $hutangHeader->total = $request->total ?? '';
            $hutangHeader->postingdari = $request->postingdari ?? 'HUTANG HEADER';
            $hutangHeader->modifiedby = auth('api')->user()->name;

            // $sum = 0;
            // for($i=0; $i < count($request->nominal_detail); $i++){
            //     $nominal = str_replace('.00','',$request->nominal_detail[$i]);
            //     $nominal = str_replace(',','',$nominal);
            //     $sum += $nominal;
            // }
            // $hutangHeader->nominal = $sum;

            if ($hutangHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangHeader->getTable()),
                    'postingdari' => 'EDIT HUTANG HEADER',
                    'idtrans' => $hutangHeader->id,
                    'nobuktitrans' => $hutangHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $hutangHeader->toArray(),
                    'modifiedby' => $hutangHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                HutangDetail::where('hutang_id', $id)->delete();

                /* Store detail */

                $detaillog = [];
                //    for ($i = 0; $i < count($request->nominal_detail); $i++) {
                $total = str_replace('.00', '', $request->total_detail);
                $cicilan = str_replace('.00', '', $request->cicilan_detail);
                $totalbayar = str_replace('.00', '', $request->totalbayar_detail);

                $datadetail = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supplier_id' => $request->supplier_id,
                    'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgljatuhtempo)),
                    'total' => str_replace(',', '', $total),
                    'cicilan' => str_replace(',', '', $cicilan),
                    'totalbayar' => str_replace(',', '', $totalbayar),
                    'keterangan' => $request->keterangan_detail,
                    'modifiedby' => $hutangHeader->modifiedby,
                ];


                //STORE

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
                    'supplier_id' => $request->supplier_id,
                    'tgljatuhtempo' => $request->tgljatuhtempo,
                    'total' => str_replace(',', '', $total),
                    'cicilan' => str_replace(',', '', $cicilan),
                    'totalbayar' => str_replace(',', '', $totalbayar),
                    'keterangan' => $request->keterangan_detail,
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
                //  }
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            //  JurnalUmumHeader::where('nobukti', $hutangHeader->nobukti)->delete();
            //  JurnalUmumDetail::where('nobukti', $hutangHeader->nobukti)->delete();

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            $coahutang = DB::table('parameter')
                ->where('grp', 'COA HUTANG MANUAL')->get();

            //     $jurnalHeader = [
            //         'tanpaprosesnobukti' => 1,
            //         'nobukti' => $hutangHeader->nobukti,
            //         'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
            //         'keterangan' => $request->keterangan,
            //         'postingdari' => "ENTRY HUTANG",
            //         'statusapproval' => $statusApp->id,
            //         'userapproval' => "",
            //         'tglapproval' => "",
            //         'modifiedby' => auth('api')->user()->name,
            //         'statusformat' => "0",
            //     ];

            // $jurnaldetail = [];

            //   for ($i = 0; $i < count($request->nominal_detail); $i++) {
            $detail = [];

            // foreach ($coahutang as $key => $coa) {
            //     $a = 0;
            //     $getcoa = DB::table('akunpusat')
            //         ->where('id', $coa->text)->first();


            //         // $total = str_replace('.00','', $request->total_detail);
            //         // $cicilan = str_replace('.00','', $request->cicilan_detail);
            //         // $totalbayar = str_replace('.00','', $request->totalbayar_detail);


            //         $jurnalDetail = [ 
            //             [
            //             'nobukti' => $hutangHeader->nobukti,
            //             'tglbukti' => date('Y-m-d', strtotime($hutangHeader->tglbukti)),
            //             'coa' =>  $getcoa->coa,
            //             'keterangan' => $request->keterangan_detail,
            //             'modifiedby' => auth('api')->user()->name,
            //             'baris' => '',
            //             ]
            //         ];
            //         if($coa->subgrp == 'KREDIT'){
            //             $jurnalDetail[$a]['total'] = str_replace(',', '',$total);
            //             $jurnalDetail[$a]['cicilan'] = str_replace(',', '',$cicilan);
            //             $jurnalDetail[$a]['totalbayar'] = str_replace(',', '',$totalbayar);

            //         }else{
            //             $jurnalDetail[$a]['total'] = str_replace(',', '',$total);
            //             $jurnalDetail[$a]['cicilan'] = str_replace(',', '',$cicilan);
            //             $jurnalDetail[$a]['totalbayar'] = str_replace(',', '',$totalbayar);

            //         }
            //     $detail = array_merge($detail, $jurnalDetail);
            //     $a++;
            // }
            // $jurnaldetail = array_merge($jurnaldetail, $detail);
            // }

            // $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

            // if (!$jurnal['status']) {
            //     throw new \Throwable($jurnal['message']);
            // }

            DB::commit();

              /* Set position and page */
              $selected = $this->getPosition($hutangHeader, $hutangHeader->getTable());
              $hutangHeader->position = $selected->position;
              $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // private function storeJurnal($header, $detail)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $jurnal = new StoreJurnalUmumHeaderRequest($header);
    //         $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);
    //         $nobukti = $header['nobukti'];
    //         $fetchId = JurnalUmumHeader::select('id')
    //         ->where('nobukti','=',$nobukti)
    //         ->first();
    //         $id = $fetchId->id;
    //         $details = [];

    //         foreach ($detail as $value) {
    //             $value['jurnalumum_id'] = $id;
    //             $detail = new StoreJurnalUmumDetailRequest($value);
    //              app(JurnalUmumDetailController::class)->store($detail);
    //             $details = $detail;
    //         }
    //         // die;
    //         DB::commit();
    //         return [
    //             'status' => true,
    //             'head' => $jurnals,
    //             'det' => $details,
    //         ];

    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response($th->getMessage()); 
    //     }
    // }


    /**
     * @ClassName destroy
     */
    public function destroy($id, $hutangHeader, Request $request)
    {
        DB::beginTransaction();
        $hutangheader = new HutangHeader();
        try {
            $delete = HutangDetail::where('hutang_id', $id)->delete();
            $delete = HutangHeader::destroy($id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangheader->getTable()),
                    'postingdari' => 'DELETE HUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $hutangheader->toArray(),
                    'modifiedby' => $hutangheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($hutangheader, $hutangheader->getTable(), true);
                $hutangheader->position = $selected->position;
                $hutangheader->id = $selected->id;
                $hutangheader->page = ceil($hutangheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $hutangheader
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
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'hutangheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader = new HutangHeader();

            $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->coa = $request->akunpusat;
            $hutangHeader->pelanggan_id = $request->pelanggan_id;

            //            $hutangHeader->pelanggan_id = $request->pelanggan_id;
            // $hutangHeader->total = $request->total ?? '';
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
            //   for ($i = 0; $i < count($request->nominal_detail); $i++) {

            $total = str_replace('.00', '', $request->total_detail);
            $cicilan = str_replace('.00', '', $request->cicilan_detail);
            $totalbayar = str_replace('.00', '', $request->totalbayar_detail);


            $datadetail = [
                'hutang_id' => $hutangHeader->id,
                'nobukti' => $hutangHeader->nobukti,
                'supplier_id' => $request->supplier_id,
                'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgljatuhtempo)),
                'total' => str_replace(',', '', $total),
                'cicilan' => str_replace(',', '', $cicilan),
                'totalbayar' => str_replace(',', '', $totalbayar),
                'keterangan' => $request->keterangan_detail,
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
                'supplier_id' => $request->supplier_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'total' => str_replace(',', '', $total),
                'cicilan' => str_replace(',', '', $cicilan),
                'totalbayar' => str_replace(',', '', $totalbayar),
                'keterangan' => $request->keterangan_detail,
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

            // }

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

            //  for ($i = 0; $i < count($request->total_detail); $i++) {
            $detail = [];

            foreach ($coahutang as $key => $coa) {
                $a = 0;
                $getcoa = DB::table('akunpusat')
                    ->where('id', $coa->text)->first();

                $total = str_replace('.00', '', $request->total_detail);
                $cicilan = str_replace('.00', '', $request->cicilan_detail);
                $totalbayar = str_replace('.00', '', $request->totalbayar_detail);


                $jurnalDetail = [
                    [
                        'nobukti' => $hutangHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($hutangHeader->tglbukti)),
                        'coa' =>  $getcoa->coa,
                        'keterangan' => $request->keterangan_detail,
                        'modifiedby' => auth('api')->user()->name,
                        'baris' => '',
                    ]
                ];
                if ($coa->subgrp == 'KREDIT') {
                    $jurnalDetail[$a]['total'] = str_replace(',', '', $total);
                    $jurnalDetail[$a]['cicilan'] = str_replace(',', '', $cicilan);
                    $jurnalDetail[$a]['totalbayar'] = str_replace(',', '', $totalbayar);
                } else {
                    $jurnalDetail[$a]['total'] = str_replace(',', '', $total);
                    $jurnalDetail[$a]['cicilan'] = str_replace(',', '', $cicilan);
                    $jurnalDetail[$a]['totalbayar'] = str_replace(',', '', $totalbayar);
                }

                $detail = array_merge($detail, $jurnalDetail);
                $a++;
            }
            $jurnaldetail = array_merge($jurnaldetail, $detail);
            //  }

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
            $selected = $this->getPosition($hutangHeader, $hutangHeader->getTable());
            $hutangHeader->position = $selected->position;
            $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));


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
