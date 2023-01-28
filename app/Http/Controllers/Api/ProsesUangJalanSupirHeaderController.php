<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Models\ProsesUangJalanSupirHeader;
use App\Http\Requests\StoreProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirHeaderRequest;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\PenerimaanTrucking;
use App\Models\PengeluaranTrucking;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesUangJalanSupirHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->get(),
            'attributes' => [
                'totalRows' => $prosesUangJalanSupir->totalRows,
                'totalPages' => $prosesUangJalanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreProsesUangJalanSupirHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'PROSES UANG JALAN BUKTI';
            $subgroup = 'PROSES UANG JALAN BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'prosesuangjalansupirheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $prosesUangJalan = new ProsesUangJalanSupirHeader();
            $prosesUangJalan->nobukti = $nobukti;
            $prosesUangJalan->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $prosesUangJalan->absensisupir_nobukti = $request->absensisupir;
            $prosesUangJalan->trado_id = $request->trado_id;
            $prosesUangJalan->supir_id = $request->supir_id;
            $prosesUangJalan->nominaluangjalan = '10000';
            $prosesUangJalan->statusapproval = $statusApproval->id;
            $prosesUangJalan->statusformat = $format->id;
            $prosesUangJalan->modifiedby = auth('api')->user()->name;

            $prosesUangJalan->save();

            $namaSupir = Supir::from(DB::raw("supir with (readuncommitted)"))->select('namasupir')->where('id',$request->supir_id)->first();
            
            $logTrail = [
                'namatabel' => strtoupper($prosesUangJalan->getTable()),
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR HEADER',
                'idtrans' => $prosesUangJalan->id,
                'nobuktitrans' => $prosesUangJalan->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $prosesUangJalan->toArray(),
                'modifiedby' => $prosesUangJalan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $statusBerkas = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

            $statusTransfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
            $statusAdjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
            $statusPengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
            $statusDeposit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();

            //INSERT PENGELUARAN DARI LIST TRANSFER            
            $detaillogTransfer = [];
            for ($i = 0; $i < count($request->nilaitransfer); $i++) {
                $content = new Request();
                $bankid = $request->bank_idtransfer[$i];
                $querysubgrppengeluaran = Bank::from(DB::raw("bank with (readuncommitted)"))
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.formatpengeluaran',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $content['group'] = $querysubgrppengeluaran->grp;
                $content['subgroup'] = $querysubgrppengeluaran->subgrp;
                $content['table'] = 'pengeluaranheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $nobuktiPengeluaran = app(Controller::class)->getRunningNumber($content)->original['data'];

                $datadetail = [
                    'prosesuangjalansupir_id' => $prosesUangJalan->id,
                    'nobukti' => $prosesUangJalan->nobukti,
                    'penerimaantrucking_bank_id' => '',
                    'penerimaantrucking_tglbukti' => '',
                    'penerimaantrucking_nobukti' => '',
                    'pengeluarantrucking_bank_id' => $bankid,
                    'pengeluarantrucking_tglbukti' => date('Y-m-d', strtotime($request->tgltransfer[$i])),
                    'pengeluarantrucking_nobukti' => $nobuktiPengeluaran,
                    'pengembaliankasgantung_bank_id' => '',
                    'pengembaliankasgantung_tglbukti' => '',
                    'pengembaliankasgantung_nobukti' => '',
                    'statusprosesuangjalan' => $statusTransfer->id,
                    'nominal' => $request->nilaitransfer[$i],
                    'keterangan' => $request->keterangantransfer[$i],
                    'modifiedby' => $prosesUangJalan->modifiedby,

                ];

                //STORE PROSES UANG JALAN DETAIL
                $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
                $datadetails = app(ProsesUangJalanSupirDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $getCoaDebet = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', 'JURNAL PENGELUARAN PROSES UANG JALAN')->where('subgrp', 'DEBET')->first();
                $memo = json_decode($getCoaDebet->memo, true);

                $alatbayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('bank_id', $bankid)->first();
                $detailPengeluaran[] = [
                    'nobukti' => $nobuktiPengeluaran,
                    'nowarkat' => '',
                    'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgltransfer[$i])),
                    'nominal' => $request->nilaitransfer[$i],
                    'coadebet' =>  $memo['JURNAL'],
                    'coakredit' => $querysubgrppengeluaran->coa,
                    'keterangan' => $request->keterangantransfer[$i]
                ];

                $pengeluaranHeader = [
                    'tanpaprosesnobukti' => '1',
                    'nobukti' =>  $nobuktiPengeluaran,
                    'tglbukti' => date('Y-m-d', strtotime($request->tgltransfer[$i])),
                    'pelanggan_id' => '',
                    'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                    'statusapproval' => $statusApproval->id,
                    'dibayarke' => $namaSupir->namasupir,
                    'alatbayar_id' => $alatbayar->id,
                    'bank_id' => $bankid,
                    'transferkeac' => '',
                    'transferkean' => '',
                    'transferkebank' => '',
                    'statusformat' => $querysubgrppengeluaran->formatpengeluaran,
                    'statuscetak' => $statusCetak->id,
                    'modifiedby' => auth('api')->user()->name,
                    'datadetail' => $detailPengeluaran

                ];

                $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
                app(PengeluaranHeaderController::class)->store($pengeluaran);

                // PENGELUARAN TRUCKING HEADER
                $fetchFormatBLS =  DB::table('pengeluarantrucking')
                    ->where('kodepengeluaran', 'BLS')
                    ->first();
                $statusformatBLS = $fetchFormatBLS->format;

                $fetchGrpBLS = Parameter::where('id', $statusformatBLS)->first();

                $formatBLS = DB::table('parameter')
                    ->where('grp', $fetchGrpBLS->grp)
                    ->where('subgrp', $fetchGrpBLS->subgrp)
                    ->first();

                $contentBLS = new Request();
                $contentBLS['group'] = $fetchGrpBLS->grp;
                $contentBLS['subgroup'] = $fetchGrpBLS->subgrp;
                $contentBLS['table'] = 'pengeluarantruckingheader';
                $contentBLS['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
                $nobuktiPengeluaranTrucking = app(Controller::class)->getRunningNumber($contentBLS)->original['data'];

                $pengeluaranTruckingDetail[] = [
                    'supir_id' => $request->supir_id,
                    'penerimaantruckingheader_nobukti' => '',
                    'nominal' => $request->nilaitransfer[$i],
                ];
                $pengeluaranTruckingHeader = [
                    'tanpaprosesnobukti' => 0,
                    'nobukti' => $nobuktiPengeluaranTrucking,
                    'tglbukti' => date('Y-m-d', strtotime($request->tgltransfer[$i])),
                    'pengeluarantrucking_id' => $fetchFormatBLS->id,
                    'bank_id' => $bankid,
                    'coa' => $fetchFormatBLS->coa,
                    'pengeluaran_nobukti' => $nobuktiPengeluaran,
                    'statusformat' => $formatBLS->id,
                    'postingdari' => 'ENTRY PROSES UANG JALAN',
                    'datadetail' => $pengeluaranTruckingDetail
                ];

                $pengeluaranTrucking = new StorePengeluaranTruckingHeaderRequest($pengeluaranTruckingHeader);
                app(PengeluaranTruckingHeaderController::class)->store($pengeluaranTrucking);

                $detaillogTransfer[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $prosesUangJalan->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillogTransfer,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            // END PENGELUARAN DARI LIST TRANSFER 

            // INSERT PENERIMAAN DARI ADJUST TRANSFER / PENGEMBALIAN KAS GANTUNG

            $getCoaKredit = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JURNAL PENERIMAAN PROSES UANG JALAN')->where('subgrp', 'KREDIT')->first();
            $memoKredit = json_decode($getCoaKredit->memo, true);

            $contentAdjust = new Request();
            $bankidAdjust = $request->bank_idadjust;
            $queryPenerimaanAdjust = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpenerimaan',
                    'bank.coa',
                    'bank.tipe'
                )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->whereRaw("bank.id = $bankidAdjust")
                ->first();

            $contentAdjust['group'] = $queryPenerimaanAdjust->grp;
            $contentAdjust['subgroup'] = $queryPenerimaanAdjust->subgrp;
            $contentAdjust['table'] = 'penerimaanheader';
            $contentAdjust['tgl'] = date('Y-m-d', strtotime($request->tgladjust));

            $nobuktiPenerimaanAdjust = app(Controller::class)->getRunningNumber($contentAdjust)->original['data'];

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalan->id,
                'nobukti' => $prosesUangJalan->nobukti,
                'penerimaantrucking_bank_id' => $bankidAdjust,
                'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($request->tgladjust)),
                'penerimaantrucking_nobukti' => $nobuktiPenerimaanAdjust,
                'pengeluarantrucking_bank_id' => '',
                'pengeluarantrucking_tglbukti' => '',
                'pengeluarantrucking_nobukti' => '',
                'pengembaliankasgantung_bank_id' => '',
                'pengembaliankasgantung_tglbukti' => '',
                'pengembaliankasgantung_nobukti' => '',
                'statusprosesuangjalan' => $statusAdjust->id,
                'nominal' => $request->nilaiadjust,
                'keterangan' => $request->keteranganadjust,
                'modifiedby' => $prosesUangJalan->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
            $datadetailsAdjust = app(ProsesUangJalanSupirDetailController::class)->store($data);

            if ($datadetailsAdjust['error']) {
                return response($datadetailsAdjust, 422);
            } else {
                $iddetail = $datadetailsAdjust['id'];
                $tabeldetail = $datadetailsAdjust['tabel'];
            }
            $detaillogAdjust = $datadetailsAdjust['detail']->toArray();

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $prosesUangJalan->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillogAdjust,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);
            $detailPenerimaanAdjust[] = [
                'nowarkat' => '',
                'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgladjust)),
                'nominal' => $request->nilaiadjust,
                'coakredit' =>  $memoKredit['JURNAL'],
                'coadebet' => $queryPenerimaanAdjust->coa,
                'keterangan' => $request->keteranganadjust,
                'invoice_nobukti' => '',
                'pelunasanpiutang_nobukti' => ''
            ];
            $penerimaanAdjustHeader = [
                'tanpaprosesnobukti' => '1',
                'nobukti' => $nobuktiPenerimaanAdjust,
                'tglbukti' => date('Y-m-d', strtotime($request->tgladjust)),
                'pelanggan_id' => '',
                'agen_id' => '',
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                'diterimadari' => $namaSupir->namasupir,
                'tgllunas' => date('Y-m-d', strtotime($request->tgladjust)),
                'bank_id' => $bankidAdjust,
                'statusapproval' => $statusApproval->id,
                'statusberkas' => $statusBerkas->id,
                'statuscetak' => $statusCetak->id,
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $queryPenerimaanAdjust->formatpenerimaan,
                'datadetail' => $detailPenerimaanAdjust

            ];

            $penerimaanAdjust = new StorePenerimaanHeaderRequest($penerimaanAdjustHeader);
            app(PenerimaanHeaderController::class)->store($penerimaanAdjust);

            // END PENERIMAAN DARI ADJUST TRANSFER / PENGEMBALIAN KAS GANTUNG

            // INSERT PENERIMAAN DARI DEPOSITO
            $content = new Request();
            $bankidDeposit = $request->bank_iddeposit;
            $querysubgrppenerimaan = Bank::from(DB::raw("bank with (readuncommitted)"))
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.formatpenerimaan',
                    'bank.coa',
                    'bank.tipe'
                )
                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                ->whereRaw("bank.id = $bankidDeposit")
                ->first();

            $content['group'] = $querysubgrppenerimaan->grp;
            $content['subgroup'] = $querysubgrppenerimaan->subgrp;
            $content['table'] = 'penerimaanheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tgldeposit));

            $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($content)->original['data'];

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalan->id,
                'nobukti' => $prosesUangJalan->nobukti,
                'penerimaantrucking_bank_id' => $bankidDeposit,
                'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($request->tgldeposit)),
                'penerimaantrucking_nobukti' => $nobuktiPenerimaan,
                'pengeluarantrucking_bank_id' => '',
                'pengeluarantrucking_tglbukti' => '',
                'pengeluarantrucking_nobukti' => '',
                'pengembaliankasgantung_bank_id' => '',
                'pengembaliankasgantung_tglbukti' => '',
                'pengembaliankasgantung_nobukti' => '',
                'statusprosesuangjalan' => $statusDeposit->id,
                'nominal' => $request->nilaideposit,
                'keterangan' => $request->keterangandeposit,
                'modifiedby' => $prosesUangJalan->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $data = new StoreProsesUangJalanSupirDetailRequest($datadetail);
            $datadetailsDeposit = app(ProsesUangJalanSupirDetailController::class)->store($data);

            if ($datadetailsDeposit['error']) {
                return response($datadetailsDeposit, 422);
            } else {
                $iddetail = $datadetailsDeposit['id'];
                $tabeldetail = $datadetailsDeposit['tabel'];
            }
            $detaillogDeposit = $datadetailsDeposit['detail']->toArray();

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $prosesUangJalan->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillogDeposit,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $getCoaKreditDPO = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', 'JURNAL PENERIMAAN DEPOSITO')->where('subgrp', 'KREDIT')->first();
            $memoKreditDPO = json_decode($getCoaKreditDPO->memo, true);

            $detailPenerimaanDeposit[] = [
                'nowarkat' => '',
                'tgljatuhtempo' => date('Y-m-d', strtotime($request->tgldeposit)),
                'nominal' => $request->nilaideposit,
                'coakredit' =>  $memoKreditDPO['JURNAL'],
                'coadebet' => $querysubgrppenerimaan->coa,
                'keterangan' => $request->keterangandeposit,
                'invoice_nobukti' => '',
                'pelunasanpiutang_nobukti' => ''
            ];
            $penerimaanHeader = [
                'tanpaprosesnobukti' => '1',
                'nobukti' => $nobuktiPenerimaan,
                'tglbukti' => date('Y-m-d', strtotime($request->tgldeposit)),
                'pelanggan_id' => '',
                'agen_id' => '',
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                'diterimadari' => $namaSupir->namasupir,
                'tgllunas' => date('Y-m-d', strtotime($request->tgldeposit)),
                'bank_id' => $bankidDeposit,
                'statusapproval' => $statusApproval->id,
                'statusberkas' => $statusBerkas->id,
                'statuscetak' => $statusCetak->id,
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $querysubgrppenerimaan->formatpenerimaan,
                'datadetail' => $detailPenerimaanDeposit

            ];
            $penerimaanDeposit = new StorePenerimaanHeaderRequest($penerimaanHeader);
            app(PenerimaanHeaderController::class)->store($penerimaanDeposit);
            // END PENERIMAAN KAS BANK DEPOSITO

            // INSERT PENERIMAAN TRUCKING DEPOSITO
            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();
            $statusformatDPO = $fetchFormatDPO->format;
            $fetchGrpDPO = Parameter::where('id', $statusformatDPO)->first();
            $formatDPO = DB::table('parameter')
                ->where('grp', $fetchGrpDPO->grp)
                ->where('subgrp', $fetchGrpDPO->subgrp)
                ->first();

            $contentDPO = new Request();
            $contentDPO['group'] = $fetchGrpDPO->grp;
            $contentDPO['subgroup'] = $fetchGrpDPO->subgrp;
            $contentDPO['table'] = 'penerimaantruckingheader';
            $contentDPO['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobuktiPenerimaanTruckingDPO = app(Controller::class)->getRunningNumber($contentDPO)->original['data'];

            $penerimaanTruckingDetailDPO[] = [
                'supir_id' => $request->supir_id,
                'pengeluarantruckingheader_nobukti' => '',
                'nominal' => $request->nilaideposit
            ];
            $penerimaanTruckingHeaderDPO = [
                'tanpaprosesnobukti' => '1',
                'nobukti' => $nobuktiPenerimaanTruckingDPO,
                'tglbukti' => date('Y-m-d', strtotime($request->tgldeposit)),
                'penerimaantrucking_id' => $fetchFormatDPO->id,
                'bank_id' => $bankidDeposit,
                'coa' => $fetchFormatDPO->coa,
                'penerimaan_nobukti' => $nobuktiPenerimaan,
                'statusformat' => $formatDPO->id,
                'postingdari' => 'ENTRY PROSES UANG JALAN',
                'datadetail' => $penerimaanTruckingDetailDPO
            ];
            $penerimaanTruckingDeposit = new StorePenerimaanTruckingHeaderRequest($penerimaanTruckingHeaderDPO);
            app(PenerimaanTruckingHeaderController::class)->store($penerimaanTruckingDeposit);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */


            $selected = $this->getPosition($prosesUangJalan, $prosesUangJalan->getTable());
            $prosesUangJalan->position = $selected->position;
            $prosesUangJalan->page = ceil($prosesUangJalan->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $prosesUangJalan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(ProsesUangJalanSupirHeader $prosesUangJalanSupirHeader)
    {
        //
    }

    /**
     * @ClassName
     */
    public function update(UpdateProsesUangJalanSupirHeaderRequest $request, ProsesUangJalanSupirHeader $prosesUangJalanSupirHeader)
    {
        //
    }

    /**
     * @ClassName
     */
    public function destroy(ProsesUangJalanSupirHeader $prosesUangJalanSupirHeader)
    {
        //
    }
}
