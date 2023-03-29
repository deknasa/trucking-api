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
use Illuminate\Database\QueryException;
use Throwable;

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
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'jurnalumumheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            }

            $jurnalumum = new JurnalUmumHeader();
            $statusApproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            if ($tanpaprosesnobukti == 1) {
                $jurnalumum->nobukti = $request->nobukti;
            }

            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->postingdari = $request->postingdari ?? 'ENTRY JURNAL UMUM';
            $jurnalumum->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $jurnalumum->userapproval = '';
            $jurnalumum->tglapproval = '';
            $jurnalumum->statusformat = $request->statusformat ?? $format->id;
            $jurnalumum->modifiedby = auth('api')->user()->name;

            if ($tanpaprosesnobukti == 0) {

                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $jurnalumum->nobukti = $nobukti;
            }

            $jurnalumum->save();

            if ($tanpaprosesnobukti == 1) {
                DB::commit();
            }

            $logTrail = [
                'namatabel' => strtoupper($jurnalumum->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY JURNAL UMUM HEADER',
                'idtrans' => $jurnalumum->id,
                'nobuktitrans' => $jurnalumum->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $jurnalumum->toArray(),
                'modifiedby' => $jurnalumum->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */


            if ($tanpaprosesnobukti == 0) {

                $detaillog = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {

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
                        // dd('tes');


                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }

                        $detaillog[] = $datadetails['detail']->toArray();
                    }
                }

                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'ENTRY JURNAL UMUM DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $jurnalumum->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);


                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();

                /* Set position and page */

                $selected = $this->getPosition($jurnalumum, $jurnalumum->getTable());
                $jurnalumum->position = $selected->position;
                $jurnalumum->page = ceil($jurnalumum->position / ($request->limit ?? 10));
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'idlogtrail' => $storedLogTrail['id'],
                'data' => $jurnalumum
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = JurnalUmumHeader::find($id);
        $nobukti = $data['nobukti'];
        return response([
            'status' => true,
            'data' => $data,
            'detail' => JurnalUmumDetail::findAll($nobukti)
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdateJurnalUmumHeaderRequest $request, JurnalUmumHeader $jurnalumumheader)
    {
        DB::beginTransaction();

        try {
            $isUpdate = $request->isUpdate ?? 0;
            $jurnalumumheader->modifiedby = auth('api')->user()->name;


            if ($jurnalumumheader->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($jurnalumumheader->getTable()),
                    'postingdari' => $request->postingdari ?? 'EDIT JURNAL UMUM HEADER',
                    'idtrans' => $jurnalumumheader->id,
                    'nobuktitrans' => $jurnalumumheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $jurnalumumheader->toArray(),
                    'modifiedby' => $jurnalumumheader->modifiedby
                ];


                $validatedLogTrail = new StoreLogTrailRequest($logTrail);

                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                JurnalUmumDetail::where('jurnalumum_id', $jurnalumumheader->id)->delete();

                /* Store detail */

                $detaillog = [];

                if ($request->datadetail != '') {
                    for ($i = 0; $i < count($request->datadetail); $i++) {
                        $datadetail = [
                            'jurnalumum_id' => $jurnalumumheader->id,
                            'nobukti' => $jurnalumumheader->nobukti,
                            'tglbukti' => $jurnalumumheader->tglbukti,
                            'coa' => $request->datadetail[$i]['coa'],
                            'nominal' => $request->datadetail[$i]['nominal'],
                            'keterangan' => $request->datadetail[$i]['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ];

                        // STORE 
                        $data = new StoreJurnalUmumDetailRequest($datadetail);

                        $datadetails = app(JurnalUmumDetailController::class)->store($data);
                        // dd('here');

                        if ($datadetails['error']) {
                            return response($datadetails, 422);
                        } else {
                            $iddetail = $datadetails['id'];
                            $tabeldetail = $datadetails['tabel'];
                        }
                        $detaillog[] = $datadetails['detail']->toArray();
                    }

                } else {
                    $counter = $request->nominal_detail;
                    for ($i = 0; $i < count($request->nominal_detail); $i++) {
                        for ($x = 0; $x <= 1; $x++) {

                            if ($x == 1) {

                                $datadetail = [
                                    'jurnalumum_id' => $jurnalumumheader->id,
                                    'nobukti' => $jurnalumumheader->nobukti,
                                    'tglbukti' => $jurnalumumheader->tglbukti,
                                    'coa' => $request->coakredit_detail[$i],
                                    'nominal' => '-' . $request->nominal_detail[$i],
                                    'keterangan' => $request->keterangan_detail[$i],
                                    'modifiedby' => auth('api')->user()->name,
                                    'baris' => $i,
                                ];
                            } else {
                                $datadetail = [
                                    'jurnalumum_id' => $jurnalumumheader->id,
                                    'nobukti' => $jurnalumumheader->nobukti,
                                    'tglbukti' => $jurnalumumheader->tglbukti,
                                    'coa' => $request->coadebet_detail[$i],
                                    'nominal' => $request->nominal_detail[$i],
                                    'keterangan' => $request->keterangan_detail[$i],
                                    'modifiedby' => auth('api')->user()->name,
                                    'baris' => $i,
                                ];
                            }
                            // STORE 
                            $data = new StoreJurnalUmumDetailRequest($datadetail);

                            $datadetails = app(JurnalUmumDetailController::class)->store($data);
                            // dd('here');

                            if ($datadetails['error']) {
                                return response($datadetails, 422);
                            } else {
                                $iddetail = $datadetails['id'];
                                $tabeldetail = $datadetails['tabel'];
                            }
                            $detaillog[] = $datadetails['detail']->toArray();
                        }
                    }
                }


                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => $request->postingdari ?? 'EDIT JURNAL UMUM DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $jurnalumumheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);

                app(LogTrailController::class)->store($data);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();
            $selected = $this->getPosition($jurnalumumheader, $jurnalumumheader->getTable());
            $jurnalumumheader->position = $selected->position;
            $jurnalumumheader->page = ceil($jurnalumumheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jurnalumumheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $getDetail = JurnalUmumDetail::lockForUpdate()->where('jurnalumum_id', $id)->get();

        $jurnalumumheader = new JurnalUmumHeader();
        $jurnalumumheader = $jurnalumumheader->lockAndDestroy($id);

        if ($jurnalumumheader) {
            $logTrail = [
                'namatabel' => strtoupper($jurnalumumheader->getTable()),
                'postingdari' => 'DELETE JURNAL UMUM HEADER',
                'idtrans' => $jurnalumumheader->id,
                'nobuktitrans' => $jurnalumumheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $jurnalumumheader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE JURNAL DETAIL

            $logTrailJurnalDetail = [
                'namatabel' => 'JURNALUMUMDETAIL',
                'postingdari' => 'DELETE JURNAL UMUM DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $jurnalumumheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            DB::commit();

            $selected = $this->getPosition($jurnalumumheader, $jurnalumumheader->getTable(), true);
            $jurnalumumheader->position = $selected->position;
            $jurnalumumheader->id = $selected->id;
            $jurnalumumheader->page = ceil($jurnalumumheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jurnalumumheader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $jurnalumum = JurnalUmumHeader::find($id);
            $statusApproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($jurnalumum->statusapproval == $statusApproval->id) {
                $jurnalumum->statusapproval = $statusNonApproval->id;
            } else {
                $jurnalumum->statusapproval = $statusApproval->id;
            }

            $jurnalumum->tglapproval = date('Y-m-d H:i:s');
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

    /**
     * @ClassName
     */
    public function copy(StoreJurnalUmumHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            $group = 'JURNAL UMUM BUKTI';
            $subgroup = 'JURNAL UMUM BUKTI';
            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'jurnalumumheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $jurnalumum = new JurnalUmumHeader();
            $statusApproval = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $jurnalumum->nobukti = $nobukti;
            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->postingdari = $request->postingdari ?? 'ENTRY JURNAL UMUM';
            $jurnalumum->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $jurnalumum->userapproval = '';
            $jurnalumum->tglapproval = '';
            $jurnalumum->statusformat = $request->statusformat ?? $format->id;
            $jurnalumum->modifiedby = auth('api')->user()->name;
            $jurnalumum->save();


            $logTrail = [
                'namatabel' => strtoupper($jurnalumum->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY JURNAL UMUM HEADER',
                'idtrans' => $jurnalumum->id,
                'nobuktitrans' => $jurnalumum->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $jurnalumum->toArray(),
                'modifiedby' => $jurnalumum->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            $detaillog = [];
            for ($i = 0; $i < count($request->nominal_detail); $i++) {

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
                    // dd('tes');


                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY JURNAL UMUM DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $jurnalumum->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jurnalumumheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
