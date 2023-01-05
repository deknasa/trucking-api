<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Controllers\Controller;
use App\Models\PengembalianKasBankHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranDetail;
use App\Models\JurnalUmumHeader;
use App\Models\JurnalUmumDetail;
use App\Http\Requests\StorePengembalianKasBankHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasBankHeaderRequest;

use App\Models\PengembalianKasBankDetail;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StorePengembalianKasBankDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;

class PengembalianKasBankHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengembalianKasBankHeader = new PengembalianKasBankHeader();

        return response([
            'data' => $pengembalianKasBankHeader->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasBankHeader->totalRows,
                'totalPages' => $pengembalianKasBankHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengembalianKasBankHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $group = 'PENGEMBALIAN KASBANK BUKTI';
            $subgroup = 'PENGEMBALIAN KASBANK BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'pengembaliankasbankheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader = new PengembalianKasBankHeader();

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $pengembalianKasBankHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti;
            $pengembalianKasBankHeader->keterangan = $request->keterangan ?? '';
            $pengembalianKasBankHeader->statusjenistransaksi = $request->statusjenistransaksi ?? 0;
            $pengembalianKasBankHeader->postingdari = $request->postingdari ?? 'ENTRY PENGEMBALIAN KAS BANK';
            $pengembalianKasBankHeader->statusapproval = $statusApproval->id ;
            $pengembalianKasBankHeader->dibayarke = $request->dibayarke ?? '';
            $pengembalianKasBankHeader->cabang_id = $request->cabang_id ?? 0;
            $pengembalianKasBankHeader->bank_id = $request->bank_id ?? 0;
            $pengembalianKasBankHeader->userapproval = $request->userapproval ?? '';
            $pengembalianKasBankHeader->tglapproval = $request->tglapproval ?? '';
            $pengembalianKasBankHeader->transferkeac = $request->transferkeac ?? '';
            $pengembalianKasBankHeader->transferkean = $request->transferkean ?? '';
            $pengembalianKasBankHeader->transferkebank = $request->transferkebank ?? '';
            $pengembalianKasBankHeader->statusformat = $format->id;
            $pengembalianKasBankHeader->modifiedby = auth('api')->user()->name;

            TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengembalianKasBankHeader->nobukti = $nobukti;

            if ($pengembalianKasBankHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                    'postingdari' => 'ENTRY PENGEMBALIAN KAS BANK HEADER',
                    'idtrans' => $pengembalianKasBankHeader->id,
                    'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengembalianKasBankHeader->toArray(),
                    'modifiedby' => $pengembalianKasBankHeader->modifiedby
                ];
            
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {

                        
                    $datadetail = [
                        'pengembaliankasbank_id' => $pengembalianKasBankHeader->id,
                        'nobukti' => $pengembalianKasBankHeader->nobukti,
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'nowarkat' => $request->nowarkat[$i],
                        'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' => $request->nominal_detail[$i],
                        'coadebet' => $request->coadebet[$i],
                        'coakredit' => $request->coakredit[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                        'modifiedby' => auth('api')->user()->name,
                    ];


                    $data = new StorePengembalianKasBankDetailRequest($datadetail);
                    $datadetails = app(PengembalianKasBankDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    $detaillog[] = $datadetail;
                }
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PENGEMBALIAN KAS BANK DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];
                
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                //SOTRE PENGELUARAN
                $pengeluaranHeader = [
                    'tanpaprosesnobukti' => 1,
                    "nobukti" => $pengembalianKasBankHeader->nobukti,
                    "tglbukti" => $pengembalianKasBankHeader->tglbukti,
                    "pelanggan_id" => 1,
                    // "pelanggan_id" => $pengeluaran->pelanggan_id,
                    "keterangan" => $pengembalianKasBankHeader->keterangan,
                    "statusjenistransaksi" => $pengembalianKasBankHeader->statusjenistransaksi,
                    "postingdari" => $pengembalianKasBankHeader->postingdari,
                    "statusapproval" => $pengembalianKasBankHeader->statusapproval,
                    "dibayarke" => $pengembalianKasBankHeader->dibayarke,
                    "cabang_id" => $pengembalianKasBankHeader->cabang_id,
                    "bank_id" => $pengembalianKasBankHeader->bank_id,
                    "userapproval" => $pengembalianKasBankHeader->userapproval,
                    "tglapproval" => $pengembalianKasBankHeader->tglapproval,
                    "transferkeac" => $pengembalianKasBankHeader->transferkeac,
                    "transferkean" => $pengembalianKasBankHeader->transferkean,
                    "transferkebank" => $pengembalianKasBankHeader->transferkebank,
                    "statusformat" => $pengembalianKasBankHeader->statusformat,
                    "modifiedby" => $pengembalianKasBankHeader->modifiedby,
                ];

                $pengeluaranDetail = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];
                    
                    $detail = [
                        'entriluar' => 1,
                        'nobukti' =>  $pengembalianKasBankHeader->nobukti,
                        'alatbayar_id' =>  $request->alatbayar_id[$i],
                        'nowarkat' =>  $request->nowarkat[$i],
                        'tgljatuhtempo' =>   date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' =>  $request->nominal_detail[$i],
                        'coadebet' =>  $request->coadebet[$i],
                        'coakredit' =>  $request->coakredit[$i],
                        'keterangan' =>  $request->keterangan_detail[$i],
                        'bulanbeban' =>   date('Y-m-d', strtotime($request->bulanbeban[$i])),
                        'modifiedby' =>  auth('api')->user()->name,
                    ];
    
                    $pengeluaranDetail[] = $detail;
                }
                $pengeluaran = $this->storePengeluaran($pengeluaranHeader, $pengeluaranDetail);

                // return response([$pengeluaran], 422);
                if (!$pengeluaran) {
                    return response(['Error'], 422);
                } 

                DB::commit();
                
                /* Set position and page */
                $selected = $this->getPosition($pengembalianKasBankHeader, $pengembalianKasBankHeader->getTable());
                $pengembalianKasBankHeader->position = $selected->position;
                $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / ($request->limit ?? 10));

                if (isset($request->limit)) {
                    $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / $request->limit);
                }

                return response([
                    'message' => 'Berhasil disimpan',
                    'data' => $pengembalianKasBankHeader
                ], 201);
            }
            return response($validatedLogTrail, 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        
    }

    /**
     * @ClassName 
     */
    public function show(PengembalianKasBankHeader $pengembalianKasBankHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengembalianKasBankHeader->findAll($id),
            'detail' => PengembalianKasBankDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $group = 'PENGEMBALIAN KASBANK BUKTI';
            $subgroup = 'PENGEMBALIAN KASBANK BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();
            $content = new Request();
            $content['group'] = $group ;
            $content['subgroup'] = $subgroup ;
            $content['table'] = 'pengembaliankasbankheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader = PengembalianKasBankHeader::lockForUpdate()->findOrFail($id);

            $pengembalianKasBankHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengembalianKasBankHeader->pengeluaran_nobukti = $request->pengeluaran_nobukti;
            $pengembalianKasBankHeader->keterangan = $request->keterangan ?? '';
            $pengembalianKasBankHeader->statusjenistransaksi = $request->statusjenistransaksi ?? 0;
            $pengembalianKasBankHeader->postingdari = $request->postingdari ?? 'EDIT PENGEMBALIAN KAS BANK';
            $pengembalianKasBankHeader->dibayarke = $request->dibayarke ?? '';
            $pengembalianKasBankHeader->cabang_id = $request->cabang_id ?? 0;
            $pengembalianKasBankHeader->bank_id = $request->bank_id ?? 0;
            $pengembalianKasBankHeader->userapproval = $request->userapproval ?? '';
            $pengembalianKasBankHeader->tglapproval = $request->tglapproval ?? '';
            $pengembalianKasBankHeader->transferkeac = $request->transferkeac ?? '';
            $pengembalianKasBankHeader->transferkean = $request->transferkean ?? '';
            $pengembalianKasBankHeader->transferkebank = $request->transferkebank ?? '';
            $pengembalianKasBankHeader->statusformat = $format->id;
            $pengembalianKasBankHeader->modifiedby = auth('api')->user()->name;


            if ($pengembalianKasBankHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                    'postingdari' => 'EDIT PENGEMBALIAN KAS BANK HEADER',
                    'idtrans' => $pengembalianKasBankHeader->id,
                    'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pengembalianKasBankHeader->toArray(),
                    'modifiedby' => $pengembalianKasBankHeader->modifiedby
                ];
            
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Delete existing detail */
                PengembalianKasBankDetail::where('nobukti',$pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
                PengeluaranDetail::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
                PengeluaranHeader::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
                JurnalUmumDetail::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
                JurnalUmumHeader::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();

                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {

                        
                    $datadetail = [
                        'pengembaliankasbank_id' => $pengembalianKasBankHeader->id,
                        'nobukti' => $pengembalianKasBankHeader->nobukti,
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'nowarkat' => $request->nowarkat[$i],
                        'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' => $request->nominal_detail[$i],
                        'coadebet' => $request->coadebet[$i],
                        'coakredit' => $request->coakredit[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                        'modifiedby' => auth('api')->user()->name,
                    ];


                    $data = new StorePengembalianKasBankDetailRequest($datadetail);
                    $datadetails = app(PengembalianKasBankDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }
                    $detaillog[] = $datadetail;
                }
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT PENGEMBALIAN KAS BANK DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];
                
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                $statusApp = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

                //SOTRE PENGELUARAN
                $pengeluaranHeader = [
                    'tanpaprosesnobukti' => 1,
                    "nobukti" => $pengembalianKasBankHeader->nobukti,
                    "tglbukti" => $pengembalianKasBankHeader->tglbukti,
                    "pelanggan_id" => 1,
                    // "pelanggan_id" => $pengeluaran->pelanggan_id,
                    "keterangan" => $pengembalianKasBankHeader->keterangan,
                    "statusjenistransaksi" => $pengembalianKasBankHeader->statusjenistransaksi,
                    "postingdari" => $pengembalianKasBankHeader->postingdari,
                    "statusapproval" => $pengembalianKasBankHeader->statusapproval,
                    "dibayarke" => $pengembalianKasBankHeader->dibayarke,
                    "cabang_id" => $pengembalianKasBankHeader->cabang_id,
                    "bank_id" => $pengembalianKasBankHeader->bank_id,
                    "userapproval" => $pengembalianKasBankHeader->userapproval,
                    "tglapproval" => $pengembalianKasBankHeader->tglapproval,
                    "transferkeac" => $pengembalianKasBankHeader->transferkeac,
                    "transferkean" => $pengembalianKasBankHeader->transferkean,
                    "transferkebank" => $pengembalianKasBankHeader->transferkebank,
                    "statusformat" => $pengembalianKasBankHeader->statusformat,
                    "modifiedby" => $pengembalianKasBankHeader->modifiedby,
                ];
                $nominal_total = 0;
                $pengeluaranDetail = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {
                    $detail = [];
                    
                    $detail = [
                        'entriluar' => 1,
                        'nobukti' =>  $pengembalianKasBankHeader->nobukti,
                        'alatbayar_id' =>  $request->alatbayar_id[$i],
                        'nowarkat' =>  $request->nowarkat[$i],
                        'tgljatuhtempo' =>   date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' =>  $request->nominal_detail[$i],
                        'coadebet' =>  $request->coadebet[$i],
                        'coakredit' =>  $request->coakredit[$i],
                        'keterangan' =>  $request->keterangan_detail[$i],
                        'bulanbeban' =>   date('Y-m-d', strtotime($request->bulanbeban[$i])),
                        'modifiedby' =>  auth('api')->user()->name,
                    ];
                    $nominal_total += $request->nominal_detail[$i];
                    $pengeluaranDetail[] = $detail;
                }
                $pengeluaran = $this->storePengeluaran($pengeluaranHeader, $pengeluaranDetail);

                if (!$pengeluaran) {
                    return response(['Error'], 422);
                } 
                
                DB::commit();
                
                /* Set position and page */
                $selected = $this->getPosition($pengembalianKasBankHeader, $pengembalianKasBankHeader->getTable());
                $pengembalianKasBankHeader->position = $selected->position;
                $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / ($request->limit ?? 10));

                if (isset($request->limit)) {
                    $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / $request->limit);
                }

                return response([
                    'message' => 'Berhasil disimpan',
                    'data' => $pengembalianKasBankHeader
                ], 201);
            }
            return response($validatedLogTrail, 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        $pengembalianKasBankHeader = PengembalianKasBankHeader::where('id', $id)->first();
        PengembalianKasBankDetail::where('nobukti',$pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
        PengeluaranDetail::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
        PengeluaranHeader::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
        JurnalUmumDetail::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
        JurnalUmumHeader::where('nobukti', $pengembalianKasBankHeader->nobukti)->lockForUpdate()->delete();
        
        $delete = $pengembalianKasBankHeader->lockForUpdate()->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                'postingdari' => 'DELETE Pengembalian Kas Bank',
                'idtrans' => $pengembalianKasBankHeader->id,
                'nobuktitrans' => $pengembalianKasBankHeader->id,
                'aksi' => 'DELETE',
                'datajson' => $pengembalianKasBankHeader->toArray(),
                'modifiedby' => $pengembalianKasBankHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($pengembalianKasBankHeader, $pengembalianKasBankHeader->getTable(), true);
            $pengembalianKasBankHeader->position = $selected->position;
            $pengembalianKasBankHeader->id = $selected->id;
            $pengembalianKasBankHeader->page = ceil($pengembalianKasBankHeader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengembalianKasBankHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function storePengeluaran($pengeluaranHeader,$pengeluaranDetail)
    {
        try {

            
            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            $header = app(PengeluaranHeaderController::class)->store($pengeluaran);
            $nobukti = $pengeluaranHeader['nobukti'];
            $fetchId = PengeluaranHeader::select('id')
                ->whereRaw("nobukti = '$nobukti'")
                ->first();
            $id = $fetchId->id;
            $details = [];
            foreach ($pengeluaranDetail as $value) {
                
                $value['pengeluaran_id'] = $id;
                $pengeluaranDetails = new StorePengeluaranDetailRequest($value);
                $detail = app(PengeluaranDetailController::class)->store($pengeluaranDetails);
                if ($detail['error']) {
                    return response($detail, 422);
                } else {
                    $iddetail = $detail['id'];
                    $tabeldetail = $detail['tabel'];
                }
                $details[] = $detail;

            }

            // return $details;
            return [
                'status' => true
            ];

        } catch (\Throwable $th) {
            throw $th;
            
        }
    }

    private function storeJurnal($header, $detail)
    {

        try {
            $jurnal = new StoreJurnalUmumHeaderRequest($header);
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


    public function approval($id)
    {
        DB::beginTransaction();
        $pengembalianKasBankHeader = PengembalianKasBankHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($pengembalianKasBankHeader->statusapproval == $statusApproval->id) {
                $pengembalianKasBankHeader->statusapproval = $statusNonApproval->id;
            } else {
                $pengembalianKasBankHeader->statusapproval = $statusApproval->id;
            }

            $pengembalianKasBankHeader->tglapproval = date('Y-m-d', time());
            $pengembalianKasBankHeader->userapproval = auth('api')->user()->name;

            if ($pengembalianKasBankHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengembalianKasBankHeader->getTable()),
                    'postingdari' => 'UN/APPROVE REKAP PENGELUARAN HEADER',
                    'idtrans' => $pengembalianKasBankHeader->id,
                    'nobuktitrans' => $pengembalianKasBankHeader->nobukti,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $pengembalianKasBankHeader->toArray(),
                    'modifiedby' => $pengembalianKasBankHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $pengembalianKasBankHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengembaliankasbankheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
