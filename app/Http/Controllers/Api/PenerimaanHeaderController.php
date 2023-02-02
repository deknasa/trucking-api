<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanHeader;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\BankPelanggan;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PelunasanPiutangHeader;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\Error;
use Exception;
use Illuminate\Database\QueryException;
use PhpParser\Builder\Param;

class PenerimaanHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $penerimaan = new PenerimaanHeader();
        return response([
            'data' => $penerimaan->get(),
            'attributes' => [
                'totalRows' => $penerimaan->totalRows,
                'totalPages' => $penerimaan->totalPages
            ]
        ]);
    }


    public function default()
    {


        $penerimaanheader = new PenerimaanHeader();
        return response([
            'status' => true,
            'data' => $penerimaanheader->default(),
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePenerimaanHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;

            $content = new Request();
            $bankid = $request->bank_id;
            $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpenerimaan',
                    'bank.coa',
                    'bank.tipe'
                )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->whereRaw("bank.id = $bankid")
                ->first();

            $content['group'] = $querysubgrppenerimaan->grp;
            $content['subgroup'] = $querysubgrppenerimaan->subgrp;
            $content['table'] = 'penerimaanheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            // $content['nobukti'] = $querysubgrppenerimaan->formatbuktipenerimaan;

            // if ($tanpaprosesnobukti == 1) {
            //     $pengeluaranHeader->nobukti = $request->nobukti;
            // }

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();
            $statuscetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


            $penerimaanHeader = new PenerimaanHeader();
            $penerimaanHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanHeader->pelanggan_id = $request->pelanggan_id ?? '';
            $penerimaanHeader->agen_id = $request->agen_id ?? '';
            $penerimaanHeader->postingdari = $request->postingdari ?? 'ENTRY PENERIMAAN KAS/BANK';
            $penerimaanHeader->diterimadari = $request->diterimadari ?? '';
            $penerimaanHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanHeader->bank_id = $request->bank_id ?? '';
            $penerimaanHeader->statusapproval = $statusApproval->id;
            $penerimaanHeader->statusberkas = $statusBerkas->id;
            $penerimaanHeader->statuscetak = $statuscetak->id;
            $penerimaanHeader->modifiedby = auth('api')->user()->name;
            $penerimaanHeader->statusformat = $request->statusformat ?? $querysubgrppenerimaan->formatpenerimaan;
            if ($tanpaprosesnobukti == 0) {
                $penerimaanHeader->nobukti = $nobukti;
            } else {
                $penerimaanHeader->nobukti = $request->nobukti;
            }
            $penerimaanHeader->save();

            $logTrail = [
                'namatabel' => strtoupper($penerimaanHeader->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN KAS/BANK',
                'idtrans' => $penerimaanHeader->id,
                'nobuktitrans' => $penerimaanHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $penerimaanHeader->toArray(),
                'modifiedby' => $penerimaanHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            // if ($tanpaprosesnobukti == 0) {
            /* Store detail */
            $detaillog = [];
            if ($request->datadetail != '') {


                $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JURNAL PENERIMAAN DARI PELUNASAN')->where('subgrp', 'KREDIT')->first();
                $memo = json_decode($getCoaKredit->memo, true);
                for ($i = 0; $i < count($request->datadetail); $i++) {
                    $datadetail = [
                        'penerimaan_id' => $penerimaanHeader->id,
                        'nobukti' => $penerimaanHeader->nobukti,
                        'nowarkat' => $request->nowarkat,
                        'tgljatuhtempo' => $penerimaanHeader->tglbukti,
                        'nominal' => $request->datadetail[$i]['nominal'],
                        'coadebet' => $request->coadebet ?? $request->datadetail[$i]['coadebet'],
                        'coakredit' => $request->datadetail[$i]['coakredit'] ?? $memo['JURNAL'],
                        'keterangan' => $request->datadetail[$i]['keterangan'],
                        'bank_id' => $penerimaanHeader->bank_id,
                        'invoice_nobukti' => $request->datadetail[$i]['invoice_nobukti'],
                        'bankpelanggan_id' => '',
                        'jenisbiaya' => '',
                        'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti ?? '',
                        'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StorePenerimaanDetailRequest($datadetail);
                    $datadetails = app(PenerimaanDetailController::class)->store($data);

                    if ($datadetails['error']) {

                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
            } else {
                for ($i = 0; $i < count($request->nominal_detail); $i++) {

                    $datadetail = [
                        'penerimaan_id' => $penerimaanHeader->id,
                        'nobukti' => $penerimaanHeader->nobukti,
                        'nowarkat' => $request->nowarkat[$i] ?? '',
                        'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' => $request->nominal_detail[$i],
                        'coadebet' => $querysubgrppenerimaan->coa,
                        'coakredit' => $request->coakredit[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'bank_id' => $penerimaanHeader->bank_id,
                        'invoice_nobukti' => $request->invoice_nobukti[$i] ?? '-',
                        'bankpelanggan_id' => $request->bankpelanggan_id[$i] ?? '',
                        'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti[$i] ?? '-',
                        'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')),
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StorePenerimaanDetailRequest($datadetail);

                    $datadetails = app(PenerimaanDetailController::class)->store($data);

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
                'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];
            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerimaanHeader->save()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanHeader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'postingdari' => $request->postingdari ?? 'ENTRY PENERIMAAN KAS/BANK',
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];
                $jurnaldetail = [];

                if ($request->datadetail != '') {
                    $counter = $request->datadetail;
                } else {
                    $counter = $request->nominal_detail;
                }
                for ($i = 0; $i < count($counter); $i++) {
                    $detail = [];
                    $jurnalDetail = [
                        [
                            'nobukti' => $penerimaanHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($penerimaanHeader->tglbukti)),
                            'coa' =>  $querysubgrppenerimaan->coa,
                            'nominal' => ($request->datadetail != '') ? $request->datadetail[$i]['nominal'] : $request->nominal_detail[$i],
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $penerimaanHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($penerimaanHeader->tglbukti)),
                            'coa' =>  $request->coakredit[$i] ?? '-',
                            'nominal' => ($request->datadetail != '') ? '-' . $request->datadetail[$i]['nominal'] : '-' . $request->nominal_detail[$i],
                            'keterangan' => ($request->datadetail != '') ? $request->datadetail[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];


                    $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                }
                $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);


                if (!$jurnal['status']) {
                    throw new Exception($jurnal['message']);
                }
                DB::commit();
                $tanpagetposition = $request->tanpagetposition ?? 0;
                if ($tanpagetposition) {
                    return response([
                        'status' => true,
                        'message' => 'Berhasil disimpan',
                        'data' => $penerimaanHeader
                    ], 201);
                }
                /* Set position and page */

                $selected = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable());
                $penerimaanHeader->position = $selected->position;

                $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
            }

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable());
            $penerimaanHeader->position = $selected->position;
            $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        // $data = PenerimaanHeader::with(
        //     'penerimaandetail',
        // )->find($id);
        $data = PenerimaanHeader::findAll($id);
        $detail = PenerimaanDetail::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePenerimaanHeaderRequest $request, PenerimaanHeader $penerimaanheader)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $isUpdate = $request->isUpdate ?? 0;
            if ($isUpdate == 0) {

                $content = new Request();
                $bankid = $request->bank_id;
                $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpenerimaan',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
                $statusBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

                $penerimaanheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $penerimaanheader->pelanggan_id = $request->pelanggan_id;
                $penerimaanheader->diterimadari = $request->diterimadari ?? '';
                $penerimaanheader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
                $penerimaanheader->bank_id = $request->bank_id ?? '';
                $penerimaanheader->statusapproval = $statusApproval->id ?? 0;
                $penerimaanheader->statusberkas = $statusBerkas->id ?? 0;
                $penerimaanheader->modifiedby = auth('api')->user()->name;
                $penerimaanheader->save();
            } else {
                if ($request->from != 'prosesuangjalansupir') {

                    $penerimaanheader->agen_id = $request->agen_id;
                    $penerimaanheader->save();
                }
            }


            $logTrail = [
                'namatabel' => strtoupper($penerimaanheader->getTable()),
                'postingdari' => $request['postingdari'] ?? 'EDIT PENERIMAAN HEADER',
                'idtrans' => $penerimaanheader->id,
                'nobuktitrans' => $penerimaanheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $penerimaanheader->toArray(),
                'modifiedby' => $penerimaanheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Delete existing detail */
            $penerimaanheader->penerimaanDetail()->delete();
            JurnalUmumHeader::where('nobukti', $penerimaanheader->nobukti)->delete();

            /* Store detail */
            $detaillog = [];

            if ($request->datadetail != '') {
                $counter = $request->datadetail;

                if($request->from != 'prosesuangjalansupir'){
                    $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                        ->where('grp', 'JURNAL PENERIMAAN DARI PELUNASAN')->where('subgrp', 'KREDIT')->first();
                    $memo = json_decode($getCoaKredit->memo, true);

                }
            } else {
                $counter = $request->nominal_detail;
            }

            for ($i = 0; $i < count($counter); $i++) {

                $datadetail = [
                    'penerimaan_id' => $penerimaanheader->id,
                    'nobukti' => $penerimaanheader->nobukti,
                    'nowarkat' => ($isUpdate != 0) ? $request->nowarkat : $request->nowarkat[$i],
                    'tgljatuhtempo' => ($isUpdate != 0) ? $penerimaanheader->tglbukti : date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => ($isUpdate != 0) ? $counter[$i]['nominal'] : $request->nominal_detail[$i],
                    'coadebet' => ($isUpdate != 0) ? $counter[$i]['coadebet'] ?? $request->coadebet : $querysubgrppenerimaan->coa,
                    'coakredit' => ($isUpdate != 0) ? $counter[$i]['coakredit'] ?? $memo['JURNAL'] : $request->coakredit[$i],
                    'keterangan' => ($isUpdate != 0) ? $counter[$i]['keterangan'] : $request->keterangan_detail[$i],
                    'bank_id' => ($isUpdate != 0) ? $request->bank_id : $penerimaanheader->bank_id,
                    'invoice_nobukti' => ($isUpdate != 0) ? $counter[$i]['invoice_nobukti'] : $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => ($isUpdate != 0) ? '' : $request->bankpelanggan_id[$i] ?? '',
                    'jenisbiaya' => ($isUpdate != 0) ? '' : $request->jenisbiaya[$i] ?? '',
                    'pelunasanpiutang_nobukti' => ($isUpdate != 0) ? $request->pelunasanpiutang_nobukti : $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => ($isUpdate != 0) ? '' : date('Y-m-d', strtotime($request->bulanbeban[$i] ?? '1900/1/1')) ?? '',
                    'modifiedby' => auth('api')->user()->name,
                ];


                $data = new StorePenerimaanDetailRequest($datadetail);
                $datadetails = app(PenerimaanDetailController::class)->store($data);

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
                'postingdari' => $request['postingdari'] ?? 'EDIT PENERIMAAN DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerimaanheader->save() && $penerimaanheader->penerimaandetail()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanheader->nobukti,
                    'tglbukti' => ($isUpdate != 0) ? $penerimaanheader->tglbukti : date('Y-m-d', strtotime($request->tglbukti)),
                    'postingdari' => $request->postingdari ?? 'EDIT PENERIMAAN KAS/BANK',
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];
                $jurnaldetail = [];

                for ($i = 0; $i < count($counter); $i++) {
                    $detail = [];
                    $jurnalDetail = [
                        [
                            'nobukti' => $penerimaanheader->nobukti,
                            'tglbukti' => ($isUpdate != 0) ? $penerimaanheader->tglbukti : date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' => ($isUpdate != 0) ? $counter[$i]['coadebet'] ?? $request->coadebet : $querysubgrppenerimaan->coa,
                            'nominal' => ($isUpdate != 0) ? $counter[$i]['nominal'] : $request->nominal_detail[$i],
                            'keterangan' => ($isUpdate != 0) ? $counter[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $penerimaanheader->nobukti,
                            'tglbukti' => ($isUpdate != 0) ? $penerimaanheader->tglbukti : date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' => ($isUpdate != 0) ? $counter[$i]['coakredit'] ?? '-' : $request->coakredit[$i] ?? '-',
                            'nominal' => ($isUpdate != 0) ? '-' . $counter[$i]['nominal'] : -$request->nominal_detail[$i],
                            'keterangan' => ($isUpdate != 0) ? $counter[$i]['keterangan'] : $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ]
                    ];


                    $jurnaldetail = array_merge($jurnaldetail, $jurnalDetail);
                }
                $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);


                // if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                //     goto ATAS;
                // }

                if (!$jurnal['status']) {
                    throw new Exception($jurnal['message']);
                }


                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($penerimaanheader, $penerimaanheader->getTable());
                $penerimaanheader->position = $selected->position;
                $penerimaanheader->page = ceil($penerimaanheader->position / ($request->limit ?? 10));


                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $penerimaanheader
                ]);
            }
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

        $getDetail = PenerimaanDetail::lockForUpdate()->where('penerimaan_id', $id)->get();
        $penerimaanHeader = new PenerimaanHeader();
        $penerimaanHeader = $penerimaanHeader->lockAndDestroy($id);

        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $penerimaanHeader->nobukti)->first();
        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $penerimaanHeader->nobukti)->get();

        JurnalUmumHeader::where('nobukti', $penerimaanHeader->nobukti)->delete();

        if ($penerimaanHeader) {
            $datalogtrail = [
                'namatabel' => strtoupper($penerimaanHeader->getTable()),
                'postingdari' => $request['postingdari'] ?? 'DELETE PENERIMAAN HEADER',
                'idtrans' => $penerimaanHeader->id,
                'nobuktitrans' => $penerimaanHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($data);

            // DELETE PENERIMAAN DETAIL
            $logTrailPenerimaanDetail = [
                'namatabel' => 'PENERIMAANDETAIL',
                'postingdari' => $request['postingdari'] ?? 'DELETE PENERIMAAN DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $penerimaanHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPenerimaanDetail = new StoreLogTrailRequest($logTrailPenerimaanDetail);
            app(LogTrailController::class)->store($validatedLogTrailPenerimaanDetail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => $request['postingdari'] ?? 'DELETE JURNAL UMUM HEADER DARI PENERIMAAN KAS/BANK',
                'idtrans' => $getJurnalHeader->id,
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
            $storedLogTrailJurnal = app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);


            // DELETE JURNAL DETAIL

            $logTrailJurnalDetail = [
                'namatabel' => 'JURNALUMUMDETAIL',
                'postingdari' => $request['postingdari'] ?? 'DELETE JURNAL UMUM DETAIL DARI PENERIMAAN KAS/BANK',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);
            DB::commit();

            $selected = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable(), true);
            $penerimaanHeader->position = $selected->position;
            $penerimaanHeader->id = $selected->id;
            $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanHeader
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
            $penerimaanHeader = PenerimaanHeader::find($id);
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($penerimaanHeader->statusapproval == $statusApproval->id) {
                $penerimaanHeader->statusapproval = $statusNonApproval->id;
                $aksi = $statusNonApproval->text;
            } else {
                $penerimaanHeader->statusapproval = $statusApproval->id;
                $aksi = $statusApproval->text;
            }

            $penerimaanHeader->tglapproval = date('Y-m-d', time());
            $penerimaanHeader->userapproval = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanHeader->getTable()),
                    'postingdari' => 'APPROVED KAS/BANK',
                    'idtrans' => $penerimaanHeader->id,
                    'nobuktitrans' => $penerimaanHeader->nobukti,
                    'aksi' => $aksi,
                    'datajson' => $penerimaanHeader->toArray(),
                    'modifiedby' => auth('api')->user()->name
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

    public function bukaCetak($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = PenerimaanHeader::find($id);
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanHeader->statuscetak == $statusCetak->id) {
                $penerimaanHeader->statuscetak = $statusBelumCetak->id;
            } else {
                $penerimaanHeader->statuscetak = $statusCetak->id;
            }

            $penerimaanHeader->tglbukacetak = date('Y-m-d', time());
            $penerimaanHeader->userbukacetak = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanHeader->getTable()),
                    'postingdari' => 'BUKA/BELUM CETAK PENERIMAANHEADER',
                    'idtrans' => $penerimaanHeader->id,
                    'nobuktitrans' => $penerimaanHeader->id,
                    'aksi' => 'BUKA/BELUM CETAK',
                    'datajson' => $penerimaanHeader->toArray(),
                    'modifiedby' => $penerimaanHeader->modifiedby
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



    public function tarikPelunasan($id)
    {
        $penerimaan = new PenerimaanHeader();
        // ($id!='') ? $tarik = $penerimaan->tarikPelunasan($id) : $tarik = $penerimaan->tarikPelunasan();
        return response([
            'data' => $penerimaan->tarikPelunasan($id),
        ]);
    }
    public function getPelunasan($id, $table)
    {
        $get = new PenerimaanHeader();
        return response([
            'data' => $get->getPelunasan($id, $table),
        ]);
    }

    private function storeJurnal($header, $detail)
    {

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];
            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($jurnal);

                $detailLog[] = $datadetails['detail']->toArray();
            }
            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => $header['postingdari'] ?? 'ENTRY PENERIMAAN KAS/BANK',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);


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

    public function cekvalidasi($id)
    {
        $pengeluaran = PenerimaanHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaan = PenerimaanHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaan->statuscetak != $statusSudahCetak->id) {
                $penerimaan->statuscetak = $statusSudahCetak->id;
                $penerimaan->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaan->userbukacetak = auth('api')->user()->name;
                $penerimaan->jumlahcetak = $penerimaan->jumlahcetak + 1;

                if ($penerimaan->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaan->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN HEADER',
                        'idtrans' => $penerimaan->id,
                        'nobuktitrans' => $penerimaan->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaan->toArray(),
                        'modifiedby' => auth('api')->user()->name
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
}
