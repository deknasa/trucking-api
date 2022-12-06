<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengembalianKasGantungDetailRequest;
use App\Models\KasGantungHeader;
use App\Models\Parameter;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanHeader;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;
use App\Models\KasGantungDetail;
use App\Models\PengembalianKasGantungHeader;
use App\Models\PengembalianKasGantungDetail;
use App\Models\Bank;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;

use App\Http\Requests\StorePenerimaanHeaderRequest;
// use App\Http\Controllers\ParameterController;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class PengembalianKasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
        return response([
            'data' => $pengembalianKasGantungHeader->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasGantungHeader->totalRows,
                'totalPages' => $pengembalianKasGantungHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StorePengembalianKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'PENGEMBALIAN KAS GANTUNG BUKTI';
            $subgroup = 'PENGEMBALIAN KAS GANTUNG BUKTI';


            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'PengembalianKasGantungHeader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusApproval = DB::table('parameter')
            ->where('grp', 'STATUS APPROVAL')
            ->where('text', 'NON APPROVAL')
            ->first();
 
            $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();

            $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->pelanggan_id = $request->pelanggan_id;
            $pengembalianKasGantungHeader->keterangan = $request->keterangan;
            $pengembalianKasGantungHeader->bank_id = $request->bank_id;
            $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            $pengembalianKasGantungHeader->penerimaan_nobukti = '';
            $pengembalianKasGantungHeader->coakasmasuk = $request->coa;
            $pengembalianKasGantungHeader->postingdari = "Pengembalian Kas Gantung";
            $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($request->tglkasmasuk));
            $pengembalianKasGantungHeader->statusformat = $format->id;
            $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;


            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengembalianKasGantungHeader->nobukti = $nobukti;

            try {
                $pengembalianKasGantungHeader->save();
            } catch (QueryException $queryException) {
                if (isset($queryException->errorInfo[1]) && is_array($queryException->errorInfo)) {
                    // Check if duplicated
                    if ($queryException->errorInfo[1] == 2601) {
                        goto TOP;
                    }
                }

                throw $queryException;
            }
            if ($pengembalianKasGantungHeader->save()) {
                
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                    'postingdari' => 'ENTRY PENGEMBALIAN KAS GANTUNG HEADER',
                    'idtrans' => $pengembalianKasGantungHeader->id,
                    'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengembalianKasGantungHeader->toArray(),
                    'modifiedby' => $pengembalianKasGantungHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                if ($request->kasgantungdetail_id) {
                    $penerimaanStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id', $pengembalianKasGantungHeader->id)->lockForUpdate()->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->kasgantungdetail_id); $i++) {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();

                        $datadetail = [
                            "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                            "nobukti" => $pengembalianKasGantungHeader->nobukti,
                            "nominal" => $kasgantung->nominal,
                            "coadetail" => $request->coadetail[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "kasgantung_nobukti" => $kasgantung->nobukti,
                        ];
                        $detaillog[] = $datadetail;
                        $data = new StorePengembalianKasGantungDetailRequest($datadetail);
                        $pengembalianKasGantungDetail = app(PengembalianKasGantungDetailController::class)->store($data);

                        if ($pengembalianKasGantungDetail['error']) {
                            return response($pengembalianKasGantungDetail, 422);
                        } else {
                            $iddetail = $pengembalianKasGantungDetail['id'];
                            $tabeldetail = $pengembalianKasGantungDetail['tabel'];
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PENGEMBALIAN KAS GANTUNG HEADER',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                    //INSERT TO PENERIMAAN

                    $bank = Bank::select('coa','statusformatpenerimaan','tipe')->where('id', $pengembalianKasGantungHeader->bank_id)->first();
                    $parameter = Parameter::where('id',$bank->statusformatpenerimaan)->first();
                                      
                    
                    if($bank->tipe == 'KAS'){
                        $statusKas = Parameter::where('grp','STATUS KAS')->where('text','KAS')->first();
                    }
                    if($bank->tipe == 'BANK'){
                        $statusKas = Parameter::where('grp','STATUS KAS')->where('text','BUKAN STATUS KAS')->first();
                    }

                    // return response($statusKas,422);
                    $group = $parameter->grp;
                    $subgroup = $parameter->subgrp;
                    $format = DB::table('parameter')
                        ->where('grp', $group )
                        ->where('subgrp', $subgroup)
                        ->first();
        
                    $penerimaanRequest = new Request();
                    $penerimaanRequest['group'] = $group;
                    $penerimaanRequest['subgroup'] = $subgroup;
                    $penerimaanRequest['table'] = 'penerimaanheader';
                    $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
        
                    $nobuktiPenerimaan= app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];
                        
                    $pengembalianKasGantungHeader->penerimaan_nobukti = $nobuktiPenerimaan;
                    $pengembalianKasGantungHeader->save();
                    $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

                    $penerimaanHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaan,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => '',
                        'bank_id' => $pengembalianKasGantungHeader->bank_id,
                        'keterangan' => $request->keterangan,
                        'postingdari' => 'PENGEMBALIAN KAS GANTUNG',
                        'diterimadari' => 'PENGEMBALIAN KAS GANTUNG',
                        'tgllunas' => date('Y-m-d', strtotime($request->tglkasmasuk)),
                        'cabang_id' => '',
                        'statusKas' => $statusKas->id,
                        'statusapproval' => $statusApproval->id, 
                        'userapproval' => '',
                        'tglapproval' => '',
                        'noresi' => '',
                        'statuserkas' => $statusBerkas->id,
                        'statusformat' => $format->id,
                        'modifiedby' => auth('api')->user()->name
                    ];
                            
                    $penerimaanDetail = [];
        
                    for ($i = 0; $i < count($request->kasgantungdetail_id); $i++) {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();
                        $detail = [];
                        
                        $detail = [
                            'entriluar' => 1,
                            'nobukti' => $nobuktiPenerimaan,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)),
                            'coadebet' =>$request->coa,
                            'coakredit' => $bank->coa,
                            'keterangan' => $request->keterangandetail[$i],
                            // 'penerimaan_id' => $penerimaanHeader->id,
                            "nominal" => $kasgantung->nominal,
                            'bank_id' => $penerimaanHeader['bank_id'],
                            'pelanggan_id' => $penerimaanHeader['pelanggan_id'],
                            'invoice_nobukti' => '',
                            'bankpelanggan_id' => 0,
                            'jenisbiaya' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)),
                            'modifiedby' => auth('api')->user()->name,
                        ];
                        $penerimaanDetail[] = $detail;
                    }

                    // return response($request->coa,422);
                    $penerimaan = $this->storePenerimaan($penerimaanHeader,$penerimaanDetail);
                    
                    if (!$penerimaan['status']) {
                        throw new \Throwable($penerimaan['message']);
                    }
                    

                    DB::commit();
                }
                /* Set position and page */
                $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable());
                $pengembalianKasGantungHeader->position = $selected->position;
                $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));

                if (isset($request->limit)) {
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / $request->limit);
                }

                return response([
                    'message' => 'Berhasil disimpan',
                    'data' => $pengembalianKasGantungHeader
                ], 201);
            }
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
            return response($th->getMessage());
        }
    }

    public function show(PengembalianKasGantungHeader $pengembalianKasGantungHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengembalianKasGantungHeader->find($id),
            'detail' => PengembalianKasGantungDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdatePengembalianKasGantungHeaderRequest $request, PengembalianKasGantungHeader $pengembalianKasGantungHeader, $id)
    {
        try {
            /* Store header */
            $statusApproval = DB::table('parameter')
            ->where('grp', 'STATUS APPROVAL')
            ->where('text', 'NON APPROVAL')
            ->first();
            $pengembalianKasGantungHeader = PengembalianKasGantungHeader::lockForUpdate()->findOrFail($id);
            DB::beginTransaction();

            $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasGantungHeader->pelanggan_id = $request->pelanggan_id;
            $pengembalianKasGantungHeader->keterangan = $request->keterangan;
            $pengembalianKasGantungHeader->bank_id = $request->bank_id;
            $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($request->tgldari));
            $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($request->tglsampai));
            // $pengembalianKasGantungHeader->penerimaan_nobukti = $request->penerimaan_nobukti;
            $pengembalianKasGantungHeader->coakasmasuk = $request->coa;
            $pengembalianKasGantungHeader->postingdari = $request->postingdari ?? '';

            $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;
            $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($request->tglkasmasuk));

            if ($pengembalianKasGantungHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                    'postingdari' => 'ENTRY PENGEMBALIAN KAS GANTUNG HEADER',
                    'idtrans' => $pengembalianKasGantungHeader->id,
                    'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengembalianKasGantungHeader->toArray(),
                    'modifiedby' => $pengembalianKasGantungHeader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                if ($request->kasgantungdetail_id) {

                    PenerimaanDetail::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
                    PenerimaanHeader::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
                    JurnalUmumDetail::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
                    JurnalUmumHeader::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
                    $penerimaanStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id', $pengembalianKasGantungHeader->id)->lockForUpdate()->delete();

                    $detaillog = [];
                    for ($i = 0; $i < count($request->kasgantungdetail_id); $i++) {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();

                        $datadetail = [
                            "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                            "nobukti" => $pengembalianKasGantungHeader->nobukti,
                            "nominal" => $kasgantung->nominal,
                            "coadetail" => $request->coadetail[$i],
                            "keterangandetail" => $request->keterangandetail[$i],
                            "kasgantung_nobukti" => $kasgantung->nobukti,
                        ];
                        $detaillog[] = $datadetail;
                        $data = new StorePengembalianKasGantungDetailRequest($datadetail);
                        $pengembalianKasGantungDetail = app(PengembalianKasGantungDetailController::class)->store($data);

                        if ($pengembalianKasGantungDetail['error']) {
                            return response($pengembalianKasGantungDetail, 422);
                        } else {
                            $iddetail = $pengembalianKasGantungDetail['id'];
                            $tabeldetail = $pengembalianKasGantungDetail['tabel'];
                        }

                        $datalogtrail = [
                            'namatabel' => $tabeldetail,
                            'postingdari' => 'ENTRY PENERIMAAN STOK HEADER',
                            'idtrans' =>  $iddetail,
                            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
                            'aksi' => 'ENTRY',
                            'datajson' => $detaillog,
                            'modifiedby' => auth('api')->user()->name,
                        ];
                        $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                        $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                         //INSERT TO PENERIMAAN

                    $bank = Bank::select('coa','statusformatpenerimaan','tipe')->where('id', $pengembalianKasGantungHeader->bank_id)->first();
                    $parameter = Parameter::where('id',$bank->statusformatpenerimaan)->first();
                                      
                    
                    if($bank->tipe == 'KAS'){
                        $statusKas = Parameter::where('grp','STATUS KAS')->where('text','KAS')->first();
                    }
                    if($bank->tipe == 'BANK'){
                        $statusKas = Parameter::where('grp','STATUS KAS')->where('text','BUKAN STATUS KAS')->first();
                    }

                    // return response($statusKas,422);
                    $group = $parameter->grp;
                    $subgroup = $parameter->subgrp;
                    $format = DB::table('parameter')
                        ->where('grp', $group )
                        ->where('subgrp', $subgroup)
                        ->first();
        
                    $penerimaanRequest = new Request();
                    $penerimaanRequest['group'] = $group;
                    $penerimaanRequest['subgroup'] = $subgroup;
                    $penerimaanRequest['table'] = 'penerimaanheader';
                    $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
        
                    $nobuktiPenerimaan= app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];
                        
                    $pengembalianKasGantungHeader->penerimaan_nobukti = $nobuktiPenerimaan;
                    $pengembalianKasGantungHeader->save();
                    $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

                    $penerimaanHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $nobuktiPenerimaan,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'pelanggan_id' => '',
                        'bank_id' => $pengembalianKasGantungHeader->bank_id,
                        'keterangan' => $request->keterangan,
                        'postingdari' => 'PENGEMBALIAN KAS GANTUNG',
                        'diterimadari' => 'PENGEMBALIAN KAS GANTUNG',
                        'tgllunas' => date('Y-m-d', strtotime($request->tglkasmasuk)),
                        'cabang_id' => '',
                        'statusKas' => $statusKas->id,
                        'statusapproval' => $statusApproval->id, 
                        'userapproval' => '',
                        'tglapproval' => '',
                        'noresi' => '',
                        'statuserkas' => $statusBerkas->id,
                        'statusformat' => $format->id,
                        'modifiedby' => auth('api')->user()->name
                    ];
                            
                    $penerimaanDetail = [];
        
                    for ($i = 0; $i < count($request->kasgantungdetail_id); $i++) {
                        $idKasgantungDetail = $request->kasgantungdetail_id[$i];
                        $kasgantung = KasGantungDetail::where('id', $idKasgantungDetail)->first();
                        $detail = [];
                        
                        $detail = [
                            'entriluar' => 1,
                            'nobukti' => $nobuktiPenerimaan,
                            'nowarkat' => '',
                            'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglkasmasuk)),
                            'coadebet' =>$request->coa,
                            'coakredit' => $bank->coa,
                            'keterangan' => $request->keterangandetail[$i],
                            // 'penerimaan_id' => $penerimaanHeader->id,
                            "nominal" => $kasgantung->nominal,
                            'bank_id' => $penerimaanHeader['bank_id'],
                            'pelanggan_id' => $penerimaanHeader['pelanggan_id'],
                            'invoice_nobukti' => '',
                            'bankpelanggan_id' => 0,
                            'jenisbiaya' => '',
                            'pelunasanpiutang_nobukti' => '',
                            'bulanbeban' => date('Y-m-d', strtotime($request->tglkasmasuk)),
                            'modifiedby' => auth('api')->user()->name,
                        ];
                        $penerimaanDetail[] = $detail;
                    }

                    // return response($request->coa,422);
                    $penerimaan = $this->storePenerimaan($penerimaanHeader,$penerimaanDetail);
                    
                    if (!$penerimaan['status']) {
                        throw new \Throwable($penerimaan['message']);
                    }


                        DB::commit();
                    }
                    /* Set position and page */
                    $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable());
                    $pengembalianKasGantungHeader->position = $selected->position;
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));

                    if (isset($request->limit)) {
                        $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / $request->limit);
                    }

                    return response([
                        'message' => 'Berhasil disimpan',
                        'data' => $pengembalianKasGantungHeader
                    ], 201);
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }

        return response([
            'message' => 'Berhasil disimpan',
            'data' => $id
        ], 422);
    }
    /**
     * @ClassName 
     */
    public function destroy(PengembalianKasGantungHeader $pengembalianKasGantungHeader, $id)
    {
        DB::beginTransaction();

        $pengembalianKasGantungHeader = PengembalianKasGantungHeader::where('id', $id)->first();
        PenerimaanDetail::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
        PenerimaanHeader::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
        JurnalUmumDetail::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
        JurnalUmumHeader::where('nobukti',$pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->delete();
        $penerimaanStokDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id', $pengembalianKasGantungHeader->id)->lockForUpdate()->delete();
        $delete = $pengembalianKasGantungHeader->lockForUpdate()->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
                'postingdari' => 'DELETE Pengembalian Kas Gantung',
                'idtrans' => $pengembalianKasGantungHeader->id,
                'nobuktitrans' => $pengembalianKasGantungHeader->id,
                'aksi' => 'DELETE',
                'datajson' => $pengembalianKasGantungHeader->toArray(),
                'modifiedby' => $pengembalianKasGantungHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable(), true);
            $pengembalianKasGantungHeader->position = $selected->position;
            $pengembalianKasGantungHeader->id = $selected->id;
            $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengembalianKasGantungHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function storePenerimaan($penerimaanHeader,$penerimaanDetail)
    {
        try {

            
            $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
            $header = app(PenerimaanHeaderController::class)->store($penerimaan);
           
            $nobukti = $penerimaanHeader['nobukti'];
            $fetchPenerimaan = PenerimaanHeader::whereRaw("nobukti = '$nobukti'")->first();
                
            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $fetchPenerimaan->nobukti,
                'tglbukti' => $fetchPenerimaan->tglbukti,
                'keterangan' => $fetchPenerimaan->keterangan,
                'postingdari' => "ENTRY PENGEMBALIAN KAS GANTUNG",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'statusformat' => 0,
                'modifiedby' => auth('api')->user()->name,
            ];
            $jurnal = new StoreJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->store($jurnal);
            $id = $fetchPenerimaan->id;

            foreach ($penerimaanDetail as $value) {
                
                $value['penerimaan_id'] = $id;
                $penerimaanDetail = new StorePenerimaanDetailRequest($value);
                $tes = app(PenerimaanDetailController::class)->store($penerimaanDetail);
                
                $fetchId = JurnalUmumHeader::select('id','tglbukti')
                ->where('nobukti','=',$nobukti)
                ->first();
    

                $getBaris = DB::table('jurnalumumdetail')->select('baris')->where('nobukti', $nobukti)->orderByDesc('baris')->first();

                if(is_null($getBaris)) {
                    $baris = 0;
                }else{
                    $baris = $getBaris->baris+1;
                }

                for ($x = 0; $x <= 1; $x++) {
                    if ($x == 1) {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $penerimaanDetail['coakredit'],
                            'nominal' => -$penerimaanDetail['nominal'],
                            'keterangan' => $penerimaanDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $penerimaanDetail['coadebet'],
                            'nominal' => $penerimaanDetail['nominal'],
                            'keterangan' => $penerimaanDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    }
                    $detail = new StoreJurnalUmumDetailRequest($datadetail);
                    $tes = app(JurnalUmumDetailController::class)->store($detail); 
                }
            }

            
            return [
                'status' => true
            ];

        } catch (\Throwable $th) {
            throw $th;
            
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('PengembalianKasGantungHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    //untuk create
    public function getKasGantung(Request $request)
    {

        
        $KasGantung = new KasGantungHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();

        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));
        return response([
            'data' => $KasGantung->getKasGantung($dari,$sampai),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $KasGantung->totalRows,
                'totalPages' => $KasGantung->totalPages
            ]
        ]);
    }
    public function getPengembalian($id)
    {
        $pengembalian = new PengembalianKasGantungHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pengembalian->getPengembalian($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pengembalian->totalRows,
                'totalPages' => $pengembalian->totalPages
            ]
        ]);
    }
}
