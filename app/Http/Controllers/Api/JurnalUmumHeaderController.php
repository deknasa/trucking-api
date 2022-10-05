<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;

use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Error;
use App\Models\LogTrail;


class JurnalUmumHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {


        $jurnalumum = new JurnalUmumHeader();
        return response([
            'data' => $jurnalumum->get(),
            'attributes' => [
                'totalRows' => $jurnalumum->totalRows,
                'totalPages' => $jurnalumum->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreJurnalUmumHeaderRequest $request)
    {
        DB::beginTransaction();

        $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;
       
        try {
            
            if ($tanpaprosesnobukti == 0) {
                $group = 'JURNAL UMUM BUKTI';
                $subgroup = 'JURNAL UMUM BUKTI';
                $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'jurnalumumheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            }

            $jurnalumum = new JurnalUmumHeader();
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            if ($tanpaprosesnobukti == 1) {
                $jurnalumum->nobukti = $request->nobukti;
            }
            
            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->keterangan = $request->keterangan;
            $jurnalumum->postingdari = $request->postingdari ?? '';
            $jurnalumum->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $jurnalumum->userapproval = '';
            $jurnalumum->tglapproval = '';
            $jurnalumum->statusformat =  $format->id ?? $request->statusformat;
            $jurnalumum->modifiedby = auth('api')->user()->name;

            TOP:
            if ($tanpaprosesnobukti == 0) {

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $jurnalumum->nobukti = $nobukti;
            }
            
            try {
               
                $jurnalumum->save();

                if ($tanpaprosesnobukti == 1) {
                    DB::commit();
                }
            } catch (\Exception $e) {
                throw $e;
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }


            $logTrail = [
                'namatabel' => strtoupper($jurnalumum->getTable()),
                'postingdari' => 'ENTRY JURNAL UMUM',
                'idtrans' => $jurnalumum->id,
                'nobuktitrans' => $jurnalumum->id,
                'aksi' => 'ENTRY',
                'datajson' => $jurnalumum->toArray(),
                'modifiedby' => $jurnalumum->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */


            if ($tanpaprosesnobukti == 0) {

                // for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detaillog = [];
                    for ($x = 0; $x <= 1; $x++) {
                        if ($x == 1) {
                            $datadetail = [
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coakredit_detail,
                                'nominal' => '-' . $request->nominal_detail,
                                'keterangan' => $request->keterangan_detail,
                                'modifiedby' => $jurnalumum->modifiedby,
                                // 'baris' => $i,
                                'baris' => 0
                            ];
                        } else {
                            $datadetail = [
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coadebet_detail,
                                'nominal' => $request->nominal_detail,
                                'keterangan' => $request->keterangan_detail,
                                'modifiedby' => $jurnalumum->modifiedby,
                                // 'baris' => $i,
                                'baris' => 0
                            ];
                        }

                        //STORE 
                        $data = new StoreJurnalUmumDetailRequest($datadetail);

                        $datadetails = app(JurnalUmumDetailController::class)->store($data);
                        // dd('tes');


                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }

                        if ($x == 1) {
                            $datadetaillog = [
                                'id' => $iddetail,
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coakredit_detail,
                                'nominal' => $request->nominal_detail,
                                'keterangan' => $request->keterangan_detail,
                                'modifiedby' => $jurnalumum->modifiedby,
                                'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                                'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                                // 'baris' => $i,
                                'baris' => 0
                            ];
                        } else {
                            $datadetaillog = [
                                'id' => $iddetail,
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coadebet_detail,
                                'nominal' => $request->nominal_detail,
                                'keterangan' => $request->keterangan_detail,
                                'modifiedby' => $jurnalumum->modifiedby,
                                'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                                'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                                // 'baris' => $i,
                                'baris' => 0
                            ];
                        }



                        $detaillog[] = $datadetaillog;


                        $dataid = LogTrail::select('id')
                            ->where('idtrans', '=', $jurnalumum->id)
                            ->where('namatabel', '=', $jurnalumum->getTable())
                            ->orderBy('id', 'DESC')
                            ->first();

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'ENTRY JURNAL UMUM',
                            'idtrans' =>  $dataid->id,
                            'nobuktitrans' => $jurnalumum->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $detaillog,
                            'modifiedby' => $request->modifiedby,
                        ];

                        $data = new StoreLogTrailRequest($datalogtrail);
                        app(LogTrailController::class)->store($data);
                    }
                // }


                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();

                /* Set position and page */

                $selected = $this->getPosition($jurnalumum, $jurnalumum->getTable());
                $jurnalumum->position = $selected->position;
                $jurnalumum->page = ceil($jurnalumum->position / ($request->limit ?? 10));

                
                // dd('test');


            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalumum
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    public function show($id)
    {

        $data = JurnalUmumHeader::find($id);

        $nobukti = $data['nobukti'];

        $query = DB::table('jurnalumumdetail AS A')
            ->select(['A.coa as coadebet', 'b.coa as coakredit', 'A.nominal', 'A.keterangan'])
            ->join(
                DB::raw("(SELECT baris,coa FROM jurnalumumdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                function ($join) {
                    $join->on('A.baris', '=', 'B.baris');
                }
            )
            ->where([
                ['A.nobukti', '=', $nobukti],
                ['A.nominal', '>=', '0']
            ])
            ->get();


        return response([
            'status' => true,
            'data' => $data,
            'detail' => $query
        ]);
    }


    /**
     * @ClassName
     */
    public function update(StoreJurnalUmumHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $jurnalumum = JurnalUmumHeader::findOrFail($id);
            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->keterangan = $request->keterangan;
            $jurnalumum->modifiedby = auth('api')->user()->name;


            if ($jurnalumum->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($jurnalumum->getTable()),
                    'postingdari' => 'ENTRY JURNAL UMUM',
                    'idtrans' => $jurnalumum->id,
                    'nobuktitrans' => $jurnalumum->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $jurnalumum->toArray(),
                    'modifiedby' => $jurnalumum->modifiedby
                ];


                $validatedLogTrail = new StoreLogTrailRequest($logTrail);

                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                JurnalUmumDetail::where('jurnalumum_id', $id)->delete();

                /* Store detail */


                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detaillog = [];
                    for ($x = 0; $x <= 1; $x++) {
                        if ($x == 1) {
                            $datadetail = [
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coakredit_detail[$i],
                                'nominal' => '-' . $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => $jurnalumum->modifiedby,
                                'baris' => $i,
                            ];
                        } else {
                            $datadetail = [
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coadebet_detail[$i],
                                'nominal' => $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => $jurnalumum->modifiedby,
                                'baris' => $i,
                            ];
                        }

                        //STORE 
                        $data = new StoreJurnalUmumDetailRequest($datadetail);

                        $datadetails = app(JurnalUmumDetailController::class)->store($data);
                        // dd('here');

                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }

                        if ($x == 1) {
                            $datadetaillog = [
                                'id' => $iddetail,
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coakredit_detail[$i],
                                'nominal' => $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => $jurnalumum->modifiedby,
                                'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                                'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                                'baris' => $i,
                            ];
                        } else {
                            $datadetaillog = [
                                'id' => $iddetail,
                                'jurnalumum_id' => $jurnalumum->id,
                                'nobukti' => $jurnalumum->nobukti,
                                'tglbukti' => $jurnalumum->tglbukti,
                                'coa' => $request->coadebet_detail[$i],
                                'nominal' => $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => $jurnalumum->modifiedby,
                                'created_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->created_at)),
                                'updated_at' => date('d-m-Y H:i:s', strtotime($jurnalumum->updated_at)),
                                'baris' => $i,
                            ];
                        }

                        $detaillog[] = $datadetaillog;


                        $dataid = LogTrail::select('id')
                            ->where('idtrans', '=', $jurnalumum->id)
                            ->where('namatabel', '=', $jurnalumum->getTable())
                            ->orderBy('id', 'DESC')
                            ->first();

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'ENTRY JURNAL UMUM',
                            'idtrans' =>  $dataid->id,
                            'nobuktitrans' => $jurnalumum->id,
                            'aksi' => 'ENTRY',
                            'datajson' => $detaillog,
                            'modifiedby' => $request->modifiedby,
                        ];

                        $data = new StoreLogTrailRequest($datalogtrail);

                        app(LogTrailController::class)->store($data);
                    }
                }
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();


            

            $selected = $this->getPosition($jurnalumum, $jurnalumum->getTable());
            $jurnalumum->position = $selected->position;
            $jurnalumum->page = ceil($jurnalumum->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalumum
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName
     */
    public function destroy($id, Request $request)
    {


        DB::beginTransaction();
        $jurnalumum = new JurnalUmumHeader();
        try {


            $delete = JurnalUmumDetail::where('jurnalumum_id', $id)->delete();
            $delete = JurnalUmumHeader::destroy($id);
            // $delete = $jurnalumum->delete($id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($jurnalumum->getTable()),
                    'postingdari' => 'DELETE JURNAL UMUM',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $jurnalumum->toArray(),
                    'modifiedby' => $jurnalumum->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($jurnalumum, $jurnalumum->getTable(), true);
                $jurnalumum->position = $selected->position;
                $jurnalumum->id = $selected->id;
                $jurnalumum->page = ceil($jurnalumum->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $jurnalumum
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
    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $jurnalumum = JurnalUmumHeader::find($id);
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($jurnalumum->statusapproval == $statusApproval->id) {
                $jurnalumum->statusapproval = $statusNonApproval->id;
            } else {
                $jurnalumum->statusapproval = $statusApproval->id;
            }

            $jurnalumum->tglapproval = date('Y-m-d h:i:s');
            $jurnalumum->userapproval = auth('api')->user()->name;

            if ($jurnalumum->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($jurnalumum->getTable()),
                    'postingdari' => 'UN/APPROVE Jurnal Umum',
                    'idtrans' => $jurnalumum->id,
                    'nobuktitrans' => $jurnalumum->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $jurnalumum->toArray(),
                    'modifiedby' => $jurnalumum->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function combo(Request $request)
    {
        $data = [
            'coa' => AkunPusat::all()
        ];

        return response([
            'data' => $data
        ]);
    }
    public function cekapproval($id)
    {
        $jurnalumum = JurnalUmumHeader::find($id);
        $statusformat = $jurnalumum->statusformat;
        $status = $jurnalumum->statusapproval;

        if ($statusformat != 0) {
            if ($status == '3') {
                $query = DB::table('error')
                    ->select('keterangan')
                    ->where('kodeerror', '=', 'SAP')
                    ->get();
                $keterangan = $query['0'];
                $data = [
                    'message' => $keterangan,
                    'errors' => 'sudah approve',
                    'kodestatus' => '1',
                    'kodenobukti' => '1'
                ];

                return response($data);
            } else {
                $data = [
                    'message' => '',
                    'errors' => 'belum approve',
                    'kodestatus' => '0',
                    'kodenobukti' => '1'
                ];

                return response($data);
            }
        } else {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'BADJ')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'bukan adj',
                'kodenobukti' => '0'
            ];

            return response($data);
        }
    }
}
