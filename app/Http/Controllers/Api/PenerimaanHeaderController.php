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

/**
     * @ClassName
     */
    public function store(StorePenerimaanHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $content = new Request();
            $bankid = $request->bank_id;
            $querysubgrppenerimaan = DB::table('bank')
            ->select(
                'parameter.grp',
                'parameter.subgrp',
                'bank.statusformatpenerimaan',
                'bank.coa',
                'bank.tipe'
            )
            ->join('parameter', 'bank.statusformatpenerimaan', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();

            $content['group'] = $querysubgrppenerimaan->grp;
            $content['subgroup'] = $querysubgrppenerimaan->subgrp;
            $content['table'] = 'penerimaanheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            // $content['nobukti'] = $querysubgrppenerimaan->formatbuktipenerimaan;
            
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();
            $penerimaanHeader = new PenerimaanHeader();
            $penerimaanHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanHeader->pelanggan_id = $request->pelanggan_id;
            $penerimaanHeader->keterangan = $request->keterangan ?? '';
            $penerimaanHeader->postingdari = $request->postingdari ?? 'PENERIMAAN';
            $penerimaanHeader->diterimadari = $request->diterimadari ?? '';
            $penerimaanHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanHeader->cabang_id = $request->cabang_id ?? 0;
            $penerimaanHeader->statuskas = $request->statuskas ?? 0;
            $penerimaanHeader->bank_id = $request->bank_id ?? '';
            $penerimaanHeader->noresi = $request->noresi ?? 0;
            $penerimaanHeader->statusapproval = $statusApproval->id ?? 0;
            $penerimaanHeader->statusberkas = $statusBerkas->id ?? 0;
            $penerimaanHeader->modifiedby = auth('api')->user()->name;
            $penerimaanHeader->statusformat = $querysubgrppenerimaan->statusformatpenerimaan;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $penerimaanHeader->nobukti = $nobukti;


            try {
                $penerimaanHeader->save();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }
            $logTrail = [
                'namatabel' => strtoupper($penerimaanHeader->getTable()),
                'postingdari' => 'ENTRY PENERIMAAN KAS/BANK',
                'idtrans' => $penerimaanHeader->id,
                'nobuktitrans' => $penerimaanHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $penerimaanHeader->toArray(),
                'modifiedby' => $penerimaanHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];

            for ($i = 0; $i < count($request->nominal_detail); $i++) {

               
                $datadetail = [
                    'penerimaan_id' => $penerimaanHeader->id,
                    'nobukti' => $penerimaanHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $querysubgrppenerimaan->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $penerimaanHeader->bank_id,
                    'pelanggan_id' => $penerimaanHeader->pelanggan_id,
                    'invoice_nobukti' => $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
                    'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i])),
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

                $datadetaillog = [
                    'penerimaan_id' => $penerimaanHeader->id,
                    'nobukti' => $penerimaanHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $querysubgrppenerimaan->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $penerimaanHeader->bank_id,
                    'pelanggan_id' => $penerimaanHeader->pelanggan_id,
                    'invoice_nobukti' => $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
                    'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaanHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaanHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;
                

                $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $penerimaanHeader->nobukti)
                ->where('namatabel', '=', $penerimaanHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PENERIMAAN DETAIL KAS/BANK',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $penerimaanHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];
                
                
                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }


            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerimaanHeader->save() && $penerimaanHeader->penerimaandetail) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $penerimaanHeader->nobukti,
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => 'ENTRY PENERIMAAN KAS',
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];
                $jurnaldetail = [];

                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];

                    $jurnalDetail = [
                        [
                            'nobukti' => $penerimaanHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($penerimaanHeader->tglbukti)),
                            'coa' =>  $request->coadebet[$i],
                            'nominal' => $request->nominal_detail[$i],
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $penerimaanHeader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($penerimaanHeader->tglbukti)),
                            'coa' =>  $querysubgrppenerimaan->coa,
                            'nominal' => -$request->nominal_detail[$i],
                            'keterangan' => $request->keterangan_detail[$i],
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
        } catch (QueryException $queryException) {
            if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                // Check if deadlock
                if ($queryException->errorInfo[1] === 1205) {
                    goto TOP;
                }
            }

            throw $queryException;
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

    public function combo(Request $request)
    {
        $data = [
            'penerimaan'    => PenerimaanHeader::all(),
            'cabang'        => Cabang::all(),
            'pelanggan'     => Pelanggan::all(),
            'bankpelanggan' => BankPelanggan::all(),
            'coa'           => AkunPusat::all(),
            'penerimaanpiutang' => PelunasanPiutangHeader::all(),
            'bank'          => Bank::all(),
            'statuskas'     => Parameter::where('grp', 'STATUS KAS')->get(),
            'statusapproval' => Parameter::where('grp', 'STATUS APPROVAL')->get(),
            'statusberkas'  => Parameter::where('grp', 'STATUS BERKAS')->get(),

        ];

        return response([
            'data' => $data
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
            $content = new Request();
            $bankid = $request->bank_id;
            $querysubgrppenerimaan = DB::table('bank')
            ->select(
                'parameter.grp',
                'parameter.subgrp',
                'bank.statusformatpenerimaan',
                'bank.coa',
                'bank.tipe'
            )
            ->join('parameter', 'bank.statusformatpenerimaan', 'parameter.id')
            ->whereRaw("bank.id = $bankid")
            ->first();

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();
            
            $penerimaanheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanheader->pelanggan_id = $request->pelanggan_id;
            $penerimaanheader->keterangan = $request->keterangan ?? '';
            $penerimaanheader->postingdari = $request->postingdari ?? 'PENERIMAAN';
            $penerimaanheader->diterimadari = $request->diterimadari ?? '';
            $penerimaanheader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanheader->cabang_id = $request->cabang_id ?? 0;
            $penerimaanheader->statuskas = $request->statuskas ?? 0;
            $penerimaanheader->bank_id = $request->bank_id ?? '';
            $penerimaanheader->noresi = $request->noresi ?? 0;
            $penerimaanheader->statusapproval = $statusApproval->id ?? 0;
            $penerimaanheader->statusberkas = $statusBerkas->id ?? 0;
            $penerimaanheader->modifiedby = auth('api')->user()->name;
            
            if ($penerimaanheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanheader->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN KAS',
                    'idtrans' => $penerimaanheader->id,
                    'nobuktitrans' => $penerimaanheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaanheader->toArray(),
                    'modifiedby' => $penerimaanheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            /* Delete existing detail */
            $penerimaanheader->penerimaanDetail()->lockForUpdate()->delete();
            JurnalUmumDetail::where('nobukti', $penerimaanheader->nobukti)->lockForUpdate()->delete();
            JurnalUmumHeader::where('nobukti', $penerimaanheader->nobukti)->lockForUpdate()->delete();

            /* Store detail */
            $detaillog = [];

            for ($i = 0; $i < count($request->nominal_detail); $i++) {

                $datadetail = [
                    'penerimaan_id' => $penerimaanheader->id,
                    'nobukti' => $penerimaanheader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $querysubgrppenerimaan->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $penerimaanheader->bank_id,
                    'pelanggan_id' => $penerimaanheader->pelanggan_id,
                    'invoice_nobukti' => $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
                    'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i])),
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

                $datadetaillog = [
                   'penerimaan_id' => $penerimaanheader->id,
                    'nobukti' => $penerimaanheader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $querysubgrppenerimaan->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $penerimaanheader->bank_id,
                    'pelanggan_id' => $penerimaanheader->pelanggan_id,
                    'invoice_nobukti' => $request->invoice_nobukti[$i] ?? '-',
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
                    'pelunasanpiutang_nobukti' => $request->pelunasanpiutang_nobukti[$i] ?? '-',
                    'bulanbeban' => date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaanheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaanheader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $penerimaanheader->nobukti)
                ->where('namatabel', '=', $penerimaanheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT PENERIMAAN DETAIL KAS/BANK',
                'idtrans' =>  $dataid->id,
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
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => 'ENTRY PENERIMAAN KAS',
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                    'statusformat' => "0",
                ];
                $jurnaldetail = [];

                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];

                    $jurnalDetail = [
                        [
                            'nobukti' => $penerimaanheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $request->coadebet[$i],
                            'nominal' => $request->nominal_detail[$i],
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $penerimaanheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $querysubgrppenerimaan->coa,
                            'nominal' => -$request->nominal_detail[$i],
                            'keterangan' => $request->keterangan_detail[$i],
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
    public function destroy(PenerimaanHeader $penerimaanheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $delete = PenerimaanDetail::where('penerimaan_id', $penerimaanheader->id)->lockForUpdate()->delete();
            $delete = JurnalUmumHeader::where('nobukti', $penerimaanheader->nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumDetail::where('nobukti', $penerimaanheader->nobukti)->lockForUpdate()->delete();

            $delete = PenerimaanHeader::destroy($penerimaanheader->id);

            $datalogtrail = [
                'namatabel' => strtoupper($penerimaanheader->getTable()),
                'postingdari' => 'DELETE PENERIMAAN KAS/BANK',
                'idtrans' => $penerimaanheader->id,
                'nobuktitrans' => $penerimaanheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $penerimaanheader->toArray(),
                'modifiedby' => $penerimaanheader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            if ($delete) {
                DB::commit();

                $selected = $this->getPosition($penerimaanheader, $penerimaanheader->getTable(), true);
                $penerimaanheader->position = $selected->position;
                $penerimaanheader->id = $selected->id;
                $penerimaanheader->page = ceil($penerimaanheader->position / ($request->limit ?? 10));
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $penerimaanheader
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



    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = PenerimaanHeader::find($id);
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($penerimaanHeader->statusapproval == $statusApproval->id) {
                $penerimaanHeader->statusapproval = $statusNonApproval->id;
            } else {
                $penerimaanHeader->statusapproval = $statusApproval->id;
            }

            $penerimaanHeader->tglapproval = date('Y-m-d', time());
            $penerimaanHeader->userapproval = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanHeader->getTable()),
                    'postingdari' => 'UN/APPROVE PENERIMAANHEADER',
                    'idtrans' => $penerimaanHeader->id,
                    'nobuktitrans' => $penerimaanHeader->id,
                    'aksi' => 'UN/APPROVE',
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
            // dd($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $jurnal = new StoreJurnalUmumDetailRequest($value);

                app(JurnalUmumDetailController::class)->store($jurnal);
            }

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
}
