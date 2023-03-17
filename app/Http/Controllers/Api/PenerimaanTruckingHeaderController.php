<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PenerimaanTruckingTruckingHeader;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;

class PenerimaanTruckingHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $penerimaantruckingheader = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaantruckingheader->get(),
            'attributes' => [
                'totalRows' => $penerimaantruckingheader->totalRows,
                'totalPages' => $penerimaantruckingheader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePenerimaanTruckingHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;

            if ($tanpaprosesnobukti == 0) {


                $idpenerimaan = $request->penerimaantrucking_id;
                $fetchFormat =  DB::table('penerimaantrucking')
                    ->where('id', $idpenerimaan)
                    ->first();

                if ($fetchFormat->kodepenerimaan == 'PJP') {
                    $request->validate([
                        'pengeluarantruckingheader_nobukti' => 'required|array',
                        'pengeluarantruckingheader_nobukti.*' => 'required'
                    ], [
                        'pengeluarantruckingheader_nobukti.*.required' => 'pengeluaran trucking ' . app(ErrorController::class)->geterror('WI')->keterangan,
                    ]);
                }
                $statusformat = $fetchFormat->format;

                $fetchGrp = Parameter::where('id', $statusformat)->first();

                $format = DB::table('parameter')
                    ->where('grp', $fetchGrp->grp)
                    ->where('subgrp', $fetchGrp->subgrp)
                    ->first();

                $content = new Request();
                $content['group'] = $fetchGrp->grp;
                $content['subgroup'] = $fetchGrp->subgrp;
                $content['table'] = 'penerimaantruckingheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            }

            $penerimaantruckingheader = new PenerimaanTruckingHeader();
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $penerimaantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaantruckingheader->penerimaantrucking_id = $request->penerimaantrucking_id ?? $idpenerimaan;
            $penerimaantruckingheader->bank_id = $request->bank_id;
            $penerimaantruckingheader->coa = $request->coa ?? '';
            $penerimaantruckingheader->penerimaan_nobukti = $request->penerimaan_nobukti ?? '';
            $penerimaantruckingheader->statusformat = $request->statusformat ?? $format->id;
            $penerimaantruckingheader->statuscetak = $statusCetak->id;
            $penerimaantruckingheader->modifiedby = auth('api')->user()->name;

            if ($tanpaprosesnobukti == 0) {
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $penerimaantruckingheader->nobukti = $nobukti;
            } else {
                $penerimaantruckingheader->nobukti = $request->nobukti;
            }
            $penerimaantruckingheader->save();


            /* Store detail */

            $detaillog = [];
            if ($request->datadetail != '') {
                $counter = $request->datadetail;
            } else {
                $counter = $request->nominal;
            }
            for ($i = 0; $i < count($counter); $i++) {

                $datadetail = [
                    'penerimaantruckingheader_id' => $penerimaantruckingheader->id,
                    'nobukti' => $penerimaantruckingheader->nobukti,
                    'supir_id' => ($request->datadetail != '') ? $request->datadetail[$i]['supir_id']  :  $request->supir_id[$i] ?? '',
                    'pengeluarantruckingheader_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['pengeluarantruckingheader_nobukti'] : $request->pengeluarantruckingheader_nobukti[$i] ?? '',
                    'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan']  : $request->keterangan[$i],
                    'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal']  : $request->nominal[$i],
                    'modifiedby' => $penerimaantruckingheader->modifiedby,
                ];
                //STORE 
                $data = new StorePenerimaanTruckingDetailRequest($datadetail);

                $datadetails = app(PenerimaanTruckingDetailController::class)->store($data);
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
                $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpenerimaan',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                    ->whereRaw("bank.id = $request->bank_id")
                    ->first();
                $group = $querysubgrppenerimaan->grp;
                $subgroup = $querysubgrppenerimaan->subgrp;
                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();
                $penerimaanRequest = new Request();
                $penerimaanRequest['group'] = $querysubgrppenerimaan->grp;
                $penerimaanRequest['subgroup'] = $querysubgrppenerimaan->subgrp;
                $penerimaanRequest['table'] = 'penerimaanheader';
                $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];

                $penerimaantruckingheader->penerimaan_nobukti = $nobuktiPenerimaan;
                $penerimaantruckingheader->save();

                // LOGTRAIL HEADER
                $logTrail = [
                    'namatabel' => strtoupper($penerimaantruckingheader->getTable()),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN TRUCKING HEADER',
                    'idtrans' => $penerimaantruckingheader->id,
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaantruckingheader->toArray(),
                    'modifiedby' => $penerimaantruckingheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // LOGTRAIL DETAIL
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN TRUCKING DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];
                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);


                $penerimaanDetail = [];

                for ($i = 0; $i < count($counter); $i++) {

                    $detail = [];

                    $detail = [
                        'entriluar' => 1,
                        'nobukti' => $nobuktiPenerimaan,
                        'nowarkat' => '',
                        'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'coadebet' => $querysubgrppenerimaan->coa,
                        'coakredit' => $request->coa ?? '',
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan[$i],
                        "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                        'invoice_nobukti' => '',
                        'pelunasanpiutang_nobukti' => '',
                        'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $penerimaanDetail[] = $detail;
                }

                $penerimaanHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $nobuktiPenerimaan,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'pelanggan_id' => '',
                    'bank_id' => $request->bank_id,
                    'postingdari' => $request->postingdari ?? 'PENERIMAAN TRUCKING',
                    'diterimadari' => $request->diterimadari ?? 'PENERIMAAN ' . $fetchFormat->keterangan,
                    'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                    'statusformat' => $format->id,
                    'modifiedby' => auth('api')->user()->name,
                    'datadetail' => $penerimaanDetail
                ];
                $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
                app(PenerimaanHeaderController::class)->store($penerimaan);
            } else {

                // LOGTRAIL HEADER
                $logTrail = [
                    'namatabel' => strtoupper($penerimaantruckingheader->getTable()),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN TRUCKING HEADER',
                    'idtrans' => $penerimaantruckingheader->id,
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaantruckingheader->toArray(),
                    'modifiedby' => $penerimaantruckingheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // LOGTRAIL DETAIL
                $datalogtrail = [
                    'namatabel' => strtoupper('PENERIMAANTRUCKINGDETAIL'),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN TRUCKING DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
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


            $selected = $this->getPosition($penerimaantruckingheader, $penerimaantruckingheader->getTable());
            $penerimaantruckingheader->position = $selected->position;
            $penerimaantruckingheader->page = ceil($penerimaantruckingheader->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaantruckingheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {

        $data = PenerimaanTruckingHeader::findAll($id);
        $detail = PenerimaanTruckingDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePenerimaanTruckingHeaderRequest $request, PenerimaanTruckingHeader $penerimaantruckingheader)
    {
        DB::beginTransaction();

        try {

            $isUpdate = $request->isUpdate ?? 0;
            $from = $request->from;

            if ($isUpdate == 0) {


                $penerimaantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $penerimaantruckingheader->coa = $request->coa ?? '';
                $penerimaantruckingheader->modifiedby = auth('api')->user()->name;

                $penerimaantruckingheader->save();
            }

            if ($from == 'ebs') {
                $penerimaantruckingheader->bank_id = $request->bank_id;
                $penerimaantruckingheader->penerimaan_nobukti = $request->penerimaan_nobukti;
                
                $penerimaantruckingheader->save();
            }

            $logTrail = [
                'namatabel' => strtoupper($penerimaantruckingheader->getTable()),
                'postingdari' => $request->postingdari ?? 'EDIT PENERIMAAN TRUCKING HEADER',
                'idtrans' => $penerimaantruckingheader->id,
                'nobuktitrans' => $penerimaantruckingheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $penerimaantruckingheader->toArray(),
                'modifiedby' => $penerimaantruckingheader->modifiedby
            ];



            $validatedLogTrail = new StoreLogTrailRequest($logTrail);

            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            if ($from != 'ebs') {

                PenerimaanTruckingDetail::where('penerimaantruckingheader_id', $penerimaantruckingheader->id)->lockForUpdate()->delete();

                /* Store detail */
                $detaillog = [];
                if ($request->datadetail != '') {
                    $counter = $request->datadetail;
                } else {
                    $counter = $request->nominal;
                }
                for ($i = 0; $i < count($counter); $i++) {
                    $datadetail = [
                        'penerimaantruckingheader_id' => $penerimaantruckingheader->id,
                        'nobukti' => $penerimaantruckingheader->nobukti,
                        'supir_id' => ($request->datadetail != '') ? $request->datadetail[$i]['supir_id'] : $request->supir_id[$i] ?? '',
                        'pengeluarantruckingheader_nobukti' => ($request->datadetail != '') ? $request->datadetail[$i]['pengeluarantruckingheader_nobukti'] : $request->pengeluarantruckingheader_nobukti[$i] ?? '',
                        'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan[$i],
                        'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                        'modifiedby' => auth('api')->user()->name
                    ];
                    //STORE 
                    $data = new StorePenerimaanTruckingDetailRequest($datadetail);
                    $datadetails = app(PenerimaanTruckingDetailController::class)->store($data);

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
                    'postingdari' => $request->postingdari ?? 'EDIT PENERIMAAN TRUCKING DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                if ($isUpdate != 2) {
                    $bank = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $penerimaantruckingheader->bank_id)->first();
                    $penerimaanDetail = [];
                    for ($i = 0; $i < count($counter); $i++) {

                        $detail = [];

                        $detail = [
                            'isUpdate' => 1,
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                            'coadebet' => $bank->coa,
                            'coakredit' => $request->coa ?? '',
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan[$i],
                            "nominal" => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal[$i],
                            'invoice_nobukti' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)) ?? date('Y-m-d', strtotime($request->tglbukti)),
                            'modifiedby' => auth('api')->user()->name,
                        ];
                        $penerimaanDetail[] = $detail;
                    }

                    $penerimaanHeader = [
                        'isUpdate' => 1,
                        'datadetail' => $penerimaanDetail,
                        'postingdari' => $request->postingdari ?? 'EDIT PENERIMAAN TRUCKING',
                        'nowarkat' => '',
                        'bank_id' => $penerimaantruckingheader->bank_id,

                    ];
                    $get = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                        ->where('penerimaanheader.nobukti', $penerimaantruckingheader->penerimaan_nobukti)->first();
                    $newPenerimaan = new PenerimaanHeader();
                    $newPenerimaan = $newPenerimaan->findAll($get->id);
                    $penerimaan = new UpdatePenerimaanHeaderRequest($penerimaanHeader);
                    app(PenerimaanHeaderController::class)->update($penerimaan, $newPenerimaan);
                }
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($penerimaantruckingheader, $penerimaantruckingheader->getTable());
            $penerimaantruckingheader->position = $selected->position;
            $penerimaantruckingheader->page = ceil($penerimaantruckingheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaantruckingheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $gajiSupir = $request->gajisupir ?? 0;
        $getDetail = PenerimaanTruckingDetail::lockForUpdate()->where('penerimaantruckingheader_id', $id)->get();

        $request['postingdari'] = $request->postingdari ?? "DELETE PENERIMAAN TRUCKING";
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $penerimaanTrucking = $penerimaanTrucking->lockAndDestroy($id);

        if ($penerimaanTrucking) {
            $logTrail = [
                'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                'postingdari' => $request->postingdari ?? 'DELETE PENERIMAAN TRUCKING HEADER',
                'idtrans' => $penerimaanTrucking->id,
                'nobuktitrans' => $penerimaanTrucking->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanTrucking->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENERIMAAN TRUCKING DETAIL
            $logTrailPenerimaanTruckingDetail = [
                'namatabel' => 'PENERIMAANTRUCKINGDETAIL',
                'postingdari' => $request->postingdari ?? 'DELETE PENERIMAAN TRUCKING DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanTrucking->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPenerimaanTruckingDetail = new StoreLogTrailRequest($logTrailPenerimaanTruckingDetail);
            app(LogTrailController::class)->store($validatedLogTrailPenerimaanTruckingDetail);

            if ($gajiSupir == 0) {
                $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $penerimaanTrucking->penerimaan_nobukti)->first();
                app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaan->id);
            }

            DB::commit();

            $selected = $this->getPosition($penerimaanTrucking, $penerimaanTrucking->getTable(), true);
            $penerimaanTrucking->position = $selected->position;
            $penerimaanTrucking->id = $selected->id;
            $penerimaanTrucking->page = ceil($penerimaanTrucking->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanTrucking
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
            $penerimaanTrucking = PenerimaanTruckingHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanTrucking->statuscetak != $statusSudahCetak->id) {
                $penerimaanTrucking->statuscetak = $statusSudahCetak->id;
                $penerimaanTrucking->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanTrucking->userbukacetak = auth('api')->user()->name;
                $penerimaanTrucking->jumlahcetak = $penerimaanTrucking->jumlahcetak + 1;

                if ($penerimaanTrucking->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanTrucking->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN TRUCKING HEADER',
                        'idtrans' => $penerimaanTrucking->id,
                        'nobuktitrans' => $penerimaanTrucking->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanTrucking->toArray(),
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
        $penerimaanTrucking = PenerimaanTruckingHeader::find($id);
        $statusdatacetak = $penerimaanTrucking->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
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
        $penerimaan = new PenerimaanTruckingHeader();
        $nobukti = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader"))->where('id', $id)->first();
        $cekdata = $penerimaan->cekvalidasiaksi($nobukti->penerimaan_nobukti);
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaantruckingheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
