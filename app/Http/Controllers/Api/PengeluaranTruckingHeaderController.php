<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTruckingHeader;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;

class PengeluaranTruckingHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePengeluaranTruckingHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;
            
            if ($tanpaprosesnobukti == 0) {
                $idpengeluaran = $request->pengeluarantrucking_id;
                $fetchFormat =  DB::table('pengeluarantrucking')
                    ->where('id', $idpengeluaran)
                    ->first();
                $statusformat = $fetchFormat->format;

                $fetchGrp = Parameter::where('id', $statusformat)->first();

                $format = DB::table('parameter')
                    ->where('grp', $fetchGrp->grp)
                    ->where('subgrp', $fetchGrp->subgrp)
                    ->first();

                $content = new Request();
                $content['group'] = $fetchGrp->grp;
                $content['subgroup'] = $fetchGrp->subgrp;
                $content['table'] = 'pengeluarantruckingheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            }

            $pengeluarantruckingheader = new PengeluaranTruckingHeader();
            $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $pengeluarantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluarantruckingheader->pengeluarantrucking_id = $request->pengeluarantrucking_id;
            $pengeluarantruckingheader->bank_id = $request->bank_id;
            $pengeluarantruckingheader->statusposting = $statusPosting->id ?? 0;
            $pengeluarantruckingheader->coa = $request->coa;
            $pengeluarantruckingheader->pengeluaran_nobukti = $request->pengeluaran_nobukti ?? '';
            $pengeluarantruckingheader->statusformat = $request->statusformat ?? $format->id;
            $pengeluarantruckingheader->statuscetak = $statusCetak->id;
            $pengeluarantruckingheader->modifiedby = auth('api')->user()->name;
            if ($tanpaprosesnobukti == 0) {
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengeluarantruckingheader->nobukti = $nobukti;
            } else {
                $pengeluarantruckingheader->nobukti = $request->nobukti;
            }
            $pengeluarantruckingheader->save();

            /* Store detail */

            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal;
            }
            for ($i = 0; $i < count($counter); $i++) {

                $datadetail = [
                    'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                    'nobukti' => $pengeluarantruckingheader->nobukti,
                    'supir_id' => ($request->datadetail != '') ? $request->datadetail[$i]['supir_id']  :  $request->supir_id[$i] ?? 0,
                    'penerimaantruckingheader_nobukti' => ($request->datadetail != '') ? '' :  $request->penerimaantruckingheader_nobukti[$i] ?? '',
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan']  :  $request->keterangan[$i],
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal']  :  $request->nominal[$i],
                    'modifiedby' => $pengeluarantruckingheader->modifiedby,
                ];

                //STORE 
                $data = new StorePengeluaranTruckingDetailRequest($datadetail);

                $datadetails = app(PengeluaranTruckingDetailController::class)->store($data);
                // dd('tes');


                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detaillog[] = $datadetails['detail']->toArray();
            }


            if ($tanpaprosesnobukti != 2) {
                // SAVE TO PENERIMAAN
                $queryPengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpengeluaran',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $request->bank_id")
                    ->first();
                $group = $queryPengeluaran->grp;
                $subgroup = $queryPengeluaran->subgrp;
                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();
                $pengeluaranRequest = new Request();
                $pengeluaranRequest['group'] = $queryPengeluaran->grp;
                $pengeluaranRequest['subgroup'] = $queryPengeluaran->subgrp;
                $pengeluaranRequest['table'] = 'pengeluaranheader';
                $pengeluaranRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiPengeluaran = app(Controller::class)->getRunningNumber($pengeluaranRequest)->original['data'];

                $pengeluarantruckingheader->pengeluaran_nobukti = $nobuktiPengeluaran;
                $pengeluarantruckingheader->save();

                // LOGTRAIL HEADER
                $logTrail = [
                    'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENGELUARAN TRUCKING HEADER',
                    'idtrans' => $pengeluarantruckingheader->id,
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluarantruckingheader->toArray(),
                    'modifiedby' => $pengeluarantruckingheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENGELUARAN TRUCKING DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);


                $pengeluaranDetail = [];

                for ($i = 0; $i < count($counter); $i++) {

                    $detail = [];

                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $nobuktiPengeluaran,
                        'nowarkat' => '',
                        'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'coadebet' => $request->coa,
                        'coakredit' => $queryPengeluaran->coa,
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan[$i],
                        "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                        'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $pengeluaranDetail[] = $detail;
                }

                $pengeluaranHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiPengeluaran,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'pelanggan_id' => '',
                    'postingdari' => 'ENTRY PENGELUARAN TRUCKING',
                    'bank_id' => $request->bank_id,
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name,
                    'datadetail' => $pengeluaranDetail
                ];
                $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
                app(PengeluaranHeaderController::class)->store($pengeluaran);
            } else {

                // LOGTRAIL HEADER
                $logTrail = [
                    'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENGELUARAN TRUCKING HEADER',
                    'idtrans' => $pengeluarantruckingheader->id,
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluarantruckingheader->toArray(),
                    'modifiedby' => $pengeluarantruckingheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // LOGTRAIL DETAIL
                $datalogtrail = [
                    'namatabel' => strtoupper('PENGELUARANTRUCKINGDETAIL'),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENGELUARAN TRUCKING DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];
                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */


            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable());
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluarantruckingheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = PengeluaranTruckingHeader::findAll($id);
        $detail = PengeluaranTruckingDetail::getAll($id);

        // dd($details);
        // $datas = array_merge($data, $detail);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePengeluaranTruckingHeaderRequest $request, PengeluaranTruckingHeader $pengeluarantruckingheader)
    {
        DB::beginTransaction();

        try {

            $isUpdate = $request->isUpdate ?? 0;
            if ($isUpdate == 0) {


                $pengeluarantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $pengeluarantruckingheader->coa = $request->coa;
                $pengeluarantruckingheader->modifiedby = auth('api')->user()->name;
                $pengeluarantruckingheader->save();
            }

            $logTrail = [
                'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                'postingdari' => 'EDIT PENGELUARAN TRUCKING HEADER',
                'idtrans' => $pengeluarantruckingheader->id,
                'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pengeluarantruckingheader->toArray(),
                'modifiedby' => $pengeluarantruckingheader->modifiedby
            ];


            $validatedLogTrail = new StoreLogTrailRequest($logTrail);

            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluarantruckingheader->id)->delete();

            /* Store detail */

            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal;
            }

            for ($i = 0; $i < count($counter); $i++) {
                $datadetail = [
                    'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                    'nobukti' => $pengeluarantruckingheader->nobukti,
                    'supir_id' => ($request->datadetail != '') ? $request->datadetail[$i]['supir_id'] : $request->supir_id[$i] ?? 0,
                    'penerimaantruckingheader_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['penerimaantruckingheader_nobukti'] : $request->penerimaantruckingheader_nobukti[$i] ?? '',
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan[$i],
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                    'modifiedby' => $pengeluarantruckingheader->modifiedby,
                ];

                //STORE 
                $data = new StorePengeluaranTruckingDetailRequest($datadetail);
                $datadetails = app(PengeluaranTruckingDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detaillog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => $request->postingdari ?? 'EDIT PENGELUARAN TRUCKING DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);

            app(LogTrailController::class)->store($data);


            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            if ($isUpdate != 2) {
                $bank = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $pengeluarantruckingheader->bank_id)->first();
                $pengeluaranDetail = [];
                for ($i = 0; $i < count($counter); $i++) {

                    $detail = [];

                    $detail = [
                        'isUpdate' => 1,
                        'nowarkat' => '',
                        'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'coadebet' => $request->coa,
                        'coakredit' => $bank->coa,
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan[$i],
                        "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                        'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $pengeluaranDetail[] = $detail;
                }

                $pengeluaranHeader = [
                    'isUpdate' => 1,
                    'datadetail' => $pengeluaranDetail,
                    'postingdari' => 'EDIT PENGELUARAN TRUCKING',

                ];
                $get = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
                    ->where('pengeluaranheader.nobukti', $pengeluarantruckingheader->pengeluaran_nobukti)->first();
                $newPengeluaran = new PengeluaranHeader();
                $newPengeluaran = $newPengeluaran->findAll($get->id);
                $penerimaan = new UpdatePengeluaranHeaderRequest($pengeluaranHeader);
                app(PengeluaranHeaderController::class)->update($penerimaan, $newPengeluaran);
            }

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable());
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluarantruckingheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $gajiSupir = $request->gajisupir ?? 0;
        $getDetail = PengeluaranTruckingDetail::lockForUpdate()->where('pengeluarantruckingheader_id', $id)->get();


        $request['postingdari'] =  $request->postingdari ?? "DELETE PENGELUARAN TRUCKING";
        $pengeluaranTrucking = new PengeluaranTruckingHeader();
        $pengeluaranTrucking = $pengeluaranTrucking->lockAndDestroy($id);
        if ($pengeluaranTrucking) {
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranTrucking->getTable()),
                'postingdari' => $request->postingdari ?? 'DELETE PENGELUARAN TRUCKING HEADER',
                'idtrans' => $pengeluaranTrucking->id,
                'nobuktitrans' => $pengeluaranTrucking->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranTrucking->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENGELUARAN TRUCKING DETAIL
            $logTrailPengeluaranTruckingDetail = [
                'namatabel' => 'PENGELUARANTRUCKINGDETAIL',
                'postingdari' => $request->postingdari ?? 'DELETE PENGELUARAN TRUCKING DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pengeluaranTrucking->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranTruckingDetail = new StoreLogTrailRequest($logTrailPengeluaranTruckingDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengeluaranTruckingDetail);
            
            if ($gajiSupir == 0) {
                $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pengeluaranTrucking->pengeluaran_nobukti)->first();
                app(PengeluaranHeaderController::class)->destroy($request, $getPengeluaran->id);
            }
            DB::commit();

            $selected = $this->getPosition($pengeluaranTrucking, $pengeluaranTrucking->getTable(), true);
            $pengeluaranTrucking->position = $selected->position;
            $pengeluaranTrucking->id = $selected->id;
            $pengeluaranTrucking->page = ceil($pengeluaranTrucking->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranTrucking
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaran = PengeluaranTruckingHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaran->statuscetak != $statusSudahCetak->id) {
                $pengeluaran->statuscetak = $statusSudahCetak->id;
                $pengeluaran->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaran->userbukacetak = auth('api')->user()->name;
                $pengeluaran->jumlahcetak = $pengeluaran->jumlahcetak + 1;

                if ($pengeluaran->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaran->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN TRUCKING HEADER',
                        'idtrans' => $pengeluaran->id,
                        'nobuktitrans' => $pengeluaran->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaran->toArray(),
                        'modifiedby' => auth('api')->user()->name,
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

    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranTruckingHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
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


    public function cekValidasiAksi($id)
    {
        $pengeluaran = new PengeluaranTruckingHeader();
        $nobukti = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader"))->where('id', $id)->first();
        $cekdata = $pengeluaran->cekvalidasiaksi($nobukti->pengeluaran_nobukti);
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluarantruckingheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
