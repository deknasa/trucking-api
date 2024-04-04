<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalJurnalUmumRequest;
use App\Http\Requests\DestroyJurnalUmumRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;

use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumPusatDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Error;
use App\Models\JurnalUmumPusatDetail;
use App\Models\JurnalUmumPusatHeader;
use App\Models\LogTrail;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Throwable;

class JurnalUmumHeaderController extends Controller
{
    /**
     * @ClassName 
     * JurnalUmumHeader
     * @Detail JurnalUmumDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreJurnalUmumHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'nominal_detail' => $request->nominal_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'coadebet_detail' => $request->coadebet_detail,
                'coakredit_detail' => $request->coakredit_detail,
            ];
            $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($data);
            $jurnalUmumHeader->position = $this->getPosition($jurnalUmumHeader, $jurnalUmumHeader->getTable())->position;
            if ($request->limit == 0) {
                $jurnalUmumHeader->page = ceil($jurnalUmumHeader->position / (10));
            } else {
                $jurnalUmumHeader->page = ceil($jurnalUmumHeader->position / ($request->limit ?? 10));
            }
            $jurnalUmumHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $jurnalUmumHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $jurnalUmumHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $jurnalUmumHeader = JurnalUmumHeader::find($id);
        $jurnalUmumDetail = (new JurnalUmumDetail)->findAll($jurnalUmumHeader['nobukti']);

        return response([
            'status' => true,
            'data' => $jurnalUmumHeader,
            'detail' => $jurnalUmumDetail
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateJurnalUmumHeaderRequest $request, JurnalUmumHeader $jurnalumumheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'nobukti' => $request->nobukti,
                'tglbukti' => $request->tglbukti,
                'nominal_detail' => $request->nominal_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'coadebet_detail' => $request->coadebet_detail,
                'coakredit_detail' => $request->coakredit_detail,
            ];
            $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($jurnalumumheader, $data);
            $jurnalumumHeader->position = $this->getPosition($jurnalumumHeader, $jurnalumumHeader->getTable())->position;
            if ($request->limit == 0) {
                $jurnalumumHeader->page = ceil($jurnalumumHeader->position / (10));
            } else {
                $jurnalumumHeader->page = ceil($jurnalumumHeader->position / ($request->limit ?? 10));
            }
            $jurnalumumHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $jurnalumumHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $jurnalumumHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyJurnalUmumRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $jurnalUmumHeader = (new JurnalUmumHeader())->processDestroy($id, 'DELETE JURNAL UMUM');
            $selected = $this->getPosition($jurnalUmumHeader, $jurnalUmumHeader->getTable(), true);
            $jurnalUmumHeader->position = $selected->position;
            $jurnalUmumHeader->id = $selected->id;
            if ($request->limit == 0) {
                $jurnalUmumHeader->page = ceil($jurnalUmumHeader->position / (10));
            } else {
                $jurnalUmumHeader->page = ceil($jurnalUmumHeader->position / ($request->limit ?? 10));
            }
            $jurnalUmumHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $jurnalUmumHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $jurnalUmumHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function cekValidasiAksi($id)
    {
        $jurnalumumHeader = new JurnalUmumHeader();
        $nobukti = JurnalUmumHeader::from(DB::raw("jurnalumumheader"))->where('id', $id)->first();
        $cekdata = $jurnalumumHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {
            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalJurnalUmumRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'jurnalId' => $request->jurnalId
            ];
            $jurnalumumHeader = (new JurnalUmumHeader())->processApproval($data);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $jurnalumumHeader
            ]);
            // $statusApproval = Parameter::from(
            //     DB::raw("parameter with (readuncommitted)")
            // )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            // $statusNonApproval = Parameter::from(
            //     DB::raw("parameter with (readuncommitted)")
            // )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            // for ($i = 0; $i < count($request->jurnalId); $i++) {

            //     $jurnalumum = JurnalUmumHeader::find($request->jurnalId[$i]);

            //     if ($jurnalumum->statusapproval == $statusApproval->id) {
            //         $jurnalumum->statusapproval = $statusNonApproval->id;
            //         $jurnalumum->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
            //         $jurnalumum->userapproval = '';
            //         $aksi = $statusNonApproval->text;
            //     } else {
            //         $jurnalumum->statusapproval = $statusApproval->id;
            //         $aksi = $statusApproval->text;
            //         $jurnalumum->tglapproval = date('Y-m-d H:i:s');
            //         $jurnalumum->userapproval = auth('api')->user()->name;
            //     }


            //     $jurnalumum->save();
            //     $logTrail = [
            //         'namatabel' => strtoupper($jurnalumum->getTable()),
            //         'postingdari' => 'APPROVAL JURNAL UMUM',
            //         'idtrans' => $jurnalumum->id,
            //         'nobuktitrans' => $jurnalumum->nobukti,
            //         'aksi' => $aksi,
            //         'datajson' => $jurnalumum->toArray(),
            //         'modifiedby' => auth('api')->user()->name
            //     ];

            //     $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            //     $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            //     // PROSES JURNAL UMUM PUSAT
            //     $jurnalUmumPusat = JurnalUmumPusatHeader::from(DB::raw("jurnalumumpusatheader with (readuncommitted)"))->where('nobukti', $jurnalumum->nobukti)->first();
            //     if ($jurnalUmumPusat != null) {

            //         $getDetail = JurnalUmumPusatDetail::where('jurnalumumpusat_id', $jurnalUmumPusat->id)->get();
            //         $jurnalumumDelete = new JurnalUmumPusatHeader();
            //         $jurnalumumDelete = $jurnalumumDelete->lockAndDestroy($jurnalUmumPusat->id);
            //         $logTrail = [
            //             'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
            //             'postingdari' => 'DELETE JURNAL UMUM PUSAT HEADER',
            //             'idtrans' => $jurnalUmumPusat->id,
            //             'nobuktitrans' => $jurnalUmumPusat->nobukti,
            //             'aksi' => 'DELETE',
            //             'datajson' => $jurnalUmumPusat->toArray(),
            //             'modifiedby' => auth('api')->user()->name
            //         ];

            //         $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            //         $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            //         // DELETE JURNAL DETAIL

            //         $logTrailJurnalDetail = [
            //             'namatabel' => 'JURNALUMUMPUSATDETAIL',
            //             'postingdari' => 'DELETE JURNAL UMUM PUSAT DETAIL',
            //             'idtrans' => $storedLogTrail['id'],
            //             'nobuktitrans' => $jurnalUmumPusat->nobukti,
            //             'aksi' => 'DELETE',
            //             'datajson' => $getDetail->toArray(),
            //             'modifiedby' => auth('api')->user()->name
            //         ];

            //         $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            //         app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);
            //     } else {
            //         $jurnalUmumPusat = new JurnalUmumPusatHeader();
            //         $jurnalUmumPusat->nobukti = $jurnalumum->nobukti;
            //         $jurnalUmumPusat->tglbukti = $jurnalumum->tglbukti;
            //         $jurnalUmumPusat->postingdari = $jurnalumum->postingdari;
            //         $jurnalUmumPusat->statusapproval = $jurnalumum->statusapproval;
            //         $jurnalUmumPusat->userapproval = auth('api')->user()->name;
            //         $jurnalUmumPusat->tglapproval = date('Y-m-d H:i:s');
            //         $jurnalUmumPusat->statusformat = $jurnalumum->statusformat;
            //         $jurnalUmumPusat->modifiedby = auth('api')->user()->name;

            //         $jurnalUmumPusat->save();

            //         $logTrail = [
            //             'namatabel' => strtoupper($jurnalUmumPusat->getTable()),
            //             'postingdari' => 'ENTRY JURNAL UMUM PUSAT HEADER',
            //             'idtrans' => $jurnalUmumPusat->id,
            //             'nobuktitrans' => $jurnalUmumPusat->nobukti,
            //             'aksi' => 'ENTRY',
            //             'datajson' => $jurnalUmumPusat->toArray(),
            //             'modifiedby' => $jurnalUmumPusat->modifiedby
            //         ];

            //         $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            //         $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            //         $detaillog = [];

            //         $jurnalDetail = JurnalUmumDetail::where('jurnalumum_id', $request->jurnalId[$i])->get();

            //         foreach ($jurnalDetail as $index => $value) {
            //             $datadetail = [
            //                 'jurnalumumpusat_id' => $jurnalUmumPusat->id,
            //                 'nobukti' => $jurnalUmumPusat->nobukti,
            //                 'tglbukti' => $jurnalUmumPusat->tglbukti,
            //                 'coa' => $value->coa,
            //                 'nominal' => $value->nominal,
            //                 'keterangan' => $value->keterangan,
            //                 'modifiedby' => $jurnalUmumPusat->modifiedby,
            //                 'baris' => $value->baris,
            //             ];

            //             //STORE 
            //             $data = new StoreJurnalUmumPusatDetailRequest($datadetail);

            //             $datadetails = app(JurnalUmumPusatDetailController::class)->store($data);

            //             if ($datadetails['error']) {
            //                 return response($datadetails, 422);
            //             } else {
            //                 $iddetail = $datadetails['id'];
            //                 $tabeldetail = $datadetails['tabel'];
            //             }

            //             $detaillog[] = $datadetails['detail']->toArray();
            //         }
            //         $datalogtrail = [
            //             'namatabel' => strtoupper($tabeldetail),
            //             'postingdari' => 'ENTRY JURNAL UMUM PUSAT DETAIL',
            //             'idtrans' =>  $storedLogTrail['id'],
            //             'nobuktitrans' => $jurnalUmumPusat->nobukti,
            //             'aksi' => 'ENTRY',
            //             'datajson' => $detaillog,
            //             'modifiedby' => $jurnalUmumPusat->modifiedby,
            //         ];

            //         $data = new StoreLogTrailRequest($datalogtrail);
            //         app(LogTrailController::class)->store($data);
            //     }
            // }

            // DB::commit();
            // return response([
            //     'message' => 'Berhasil'
            // ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     * @Keterangan COPY DATA JURNAL
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
            )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $jurnalumum->nobukti = $nobukti;
            $jurnalumum->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $jurnalumum->postingdari = 'ENTRY JURNAL UMUM';
            $jurnalumum->statusapproval = $statusApproval->id;
            $jurnalumum->userapproval = auth('api')->user()->name;
            $jurnalumum->tglapproval = date('Y-m-d H:i:s');
            $jurnalumum->statusformat = $format->id;
            $jurnalumum->modifiedby = auth('api')->user()->name;
            $jurnalumum->save();


            $logTrail = [
                'namatabel' => strtoupper($jurnalumum->getTable()),
                'postingdari' => 'ENTRY JURNAL UMUM HEADER',
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
        $status = $jurnalumum->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $jurnalumum->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $tutupBuku = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'TUTUP BUKU')->first();

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            if ($jurnalumum->statusformat == 115) {
                if (date('Y-m-d', strtotime($jurnalumum->tglbukti)) < date('Y-m-d', strtotime($tutupBuku->text))) {
                    $query = DB::table('error')
                        ->select('keterangan')
                        ->where('kodeerror', '=', 'STB')
                        ->get();
                    $keterangan = $query['0'];
                    $data = [
                        'message' => $keterangan,
                        'errors' => 'sudah tutup buku',
                        'kodestatus' => '1',
                        'kodenobukti' => '1'
                    ];
                } else {
                    $data = [
                        'message' => '',
                        'errors' => 'belum approve',
                        'kodestatus' => '0',
                        'kodenobukti' => '1'
                    ];
                }
            } else {

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
            }
            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $jurnalUmum = JurnalUmumHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($jurnalUmum->statuscetak != $statusSudahCetak->id) {
                $jurnalUmum->statuscetak = $statusSudahCetak->id;
                $jurnalUmum->tglbukacetak = date('Y-m-d H:i:s');
                $jurnalUmum->userbukacetak = auth('api')->user()->name;
                $jurnalUmum->jumlahcetak = $jurnalUmum->jumlahcetak + 1;
                if ($jurnalUmum->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($jurnalUmum->getTable()),
                        'postingdari' => 'PRINT JURNAL UMUM HEADER',
                        'idtrans' => $jurnalUmum->id,
                        'nobuktitrans' => $jurnalUmum->id,
                        'aksi' => 'PRINT',
                        'datajson' => $jurnalUmum->toArray(),
                        'modifiedby' => $jurnalUmum->modifiedby
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    DB::commit();
                }
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
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $jurnalumum = new JurnalUmumHeader();
        return response([
            'data' => $jurnalumum->getExport($id)
        ]);
    }
    
    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }    

}
