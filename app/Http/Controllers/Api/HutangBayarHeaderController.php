<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreHutangBayarHeaderRequest;
use App\Http\Requests\StoreHutangBayarDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdateHutangBayarHeaderRequest;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\AkunPusat;
use App\Models\Supplier;
use App\Models\HutangBayarHeader;
use App\Models\HutangBayarDetail;
use App\Models\HutangDetail;
use App\Models\Parameter;
use App\Models\HutangHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\LogTrail;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class HutangBayarHeaderController extends Controller
{
    /**
     * @ClassName index
     */
    public function index()
    {
        $hutangbayarheader = new HutangBayarHeader();
        return response([
            'data' => $hutangbayarheader->get(),
            'attributes' => [
                'totalRows' => $hutangbayarheader->totalRows,
                'totalPages' => $hutangbayarheader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName store
     */
    public function store(StoreHutangBayarHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $group = 'PEMBAYARAN HUTANG BUKTI';
            $subgroup = 'PEMBAYARAN HUTANG BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'hutangbayarheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $hutangbayarheader = new HutangBayarHeader();
            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->keterangan = $request->keterangan;
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id;
            $hutangbayarheader->coa = $request->coa;
            $hutangbayarheader->pengeluaran_nobukti = '';
            $hutangbayarheader->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $hutangbayarheader->userapproval = '';
            $hutangbayarheader->tglapproval = '';
            $hutangbayarheader->statusformat = $format->id;
            $hutangbayarheader->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $hutangbayarheader->nobukti = $nobukti;

            try {
                $hutangbayarheader->save();
            } catch (\Exception $e) {
                dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($hutangbayarheader->getTable()),
                'postingdari' => 'ENTRY HUTANG BAYAR HEADER',
                'idtrans' => $hutangbayarheader->id,
                'nobuktitrans' => $hutangbayarheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $hutangbayarheader->toArray(),
                'modifiedby' => $hutangbayarheader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            /* Store detail */
            $detaillog = [];

            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::where('id', $request->hutang_id[$i])->first();

                $datadetail = [
                    'hutangbayar_id' => $hutangbayarheader->id,
                    'nobukti' => $hutangbayarheader->nobukti,
                    'hutang_nobukti' => $hutang->nobukti,
                    'nominal' => $request->bayar[$i],
                    'cicilan' => '',
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'tglcair' => $request->tglcair[$i],
                    'userid' => '',
                    'coa_id' => '',
                    'potongan' => $request->potongan[$i],
                    'keterangan' => $request->keterangandetail[$i],
                    'modifiedby' => $hutangbayarheader->modifiedby,
                ];

                $data = new StoreHutangBayarDetailRequest($datadetail);
                $datadetails = app(HutangBayarDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'id' => $iddetail,
                    'hutangbayar_id' => $hutangbayarheader->id,
                    'nobukti' => $hutangbayarheader->nobukti,
                    'hutang_nobukti' => $hutang->nobukti,
                    'nominal' => $request->bayar[$i],
                    'cicilan' => '',
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'tglcair' => $request->tglcair[$i],
                    'userid' => '',
                    'coa_id' => '',
                    'potongan' => $request->potongan[$i],
                    'keterangan' => $request->keterangandetail[$i],
                    'modifiedby' => $hutangbayarheader->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($hutangbayarheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($hutangbayarheader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY HUTANG BAYAR DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $hutangbayarheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            //INSERT TO PENGELUARAN
            $bank = Bank::select('coa','statusformatpengeluaran','tipe')->where('id', $hutangbayarheader->bank_id)->first();
            $parameter = Parameter::where('id',$bank->statusformatpengeluaran)->first();
           
            
            if($bank->tipe == 'KAS'){
                $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','KAS')->first();
            }
            if($bank->tipe == 'BANK'){
                $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','BANK')->first();
            }
            $group = $parameter->grp;
            $subgroup = $parameter->subgrp;
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $pengeluaranRequest = new Request();
            $pengeluaranRequest['group'] = $group;
            $pengeluaranRequest['subgroup'] = $subgroup;
            $pengeluaranRequest['table'] = 'pengeluaranheader';
            $pengeluaranRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobuktiPengeluaran= app(Controller::class)->getRunningNumber($pengeluaranRequest)->original['data'];
                
            $hutangbayarheader->pengeluaran_nobukti = $nobuktiPengeluaran;
            $hutangbayarheader->save();

            $pengeluaranHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $nobuktiPengeluaran,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pelanggan_id' => '',
                'keterangan' => $request->keterangan,
                'statusjenistransaksi' => $jenisTransaksi->id,
                'postingdari' => 'ENTRY HUTANG BAYAR',
                'statusapproval' => $statusApproval->id, 
                'dibayarke' => '',
                'cabang_id' => '',
                'bank_id' => $hutangbayarheader->bank_id,
                'userapproval' => '',
                'tglapproval' => '',
                'transferkeac' => '',
                'transferkean' => '',
                'transferkebank' => '',
                'statusformat' => $format->id,
                'modifiedby' => auth('api')->user()->name
            ];

            $pengeluaranDetail = [];
            $coaDebet = Parameter::where('grp','COA PEMBAYARAN HUTANG DEBET')->first();

            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::where('id', $request->hutang_id[$i])->first();
                $hutangDetail = HutangDetail::where('nobukti',$hutang->nobukti)->first();
                $detail = [];
                
                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $nobuktiPengeluaran,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => '',
                    'tgljatuhtempo' => $hutangDetail->tgljatuhtempo,
                    'nominal' => $request->bayar[$i] - $request->potongan[$i],
                    'coadebet' => $coaDebet->text,
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangandetail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $pengeluaranDetail[] = $detail;
            }
            

            $pengeluaran = $this->storePengeluaran($pengeluaranHeader,$pengeluaranDetail);
            
            
            // if (!$pengeluaran['status'] AND @$pengeluaran['errorCode'] == 2601) {
            //     goto ATAS;
            // }
            if (!$pengeluaran['status']) {
                throw new \Throwable($pengeluaran['message']);
            }
            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable());
            $hutangbayarheader->position = $selected->position;
            $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangbayarheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    public function show($id)
    {

        $data = HutangBayarHeader::findAll($id);
        $detail = HutangBayarDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName update
     */
    public function update(UpdateHutangBayarHeaderRequest $request,HutangBayarHeader $hutangbayarheader)
    {
        DB::beginTransaction();

        try {
            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->keterangan = $request->keterangan ?? '';
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id;
            $hutangbayarheader->coa = $request->coa;
            $hutangbayarheader->modifiedby = auth('api')->user()->name;

            if ($hutangbayarheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangbayarheader->getTable()),
                    'postingdari' => 'EDIT HUTANG BAYAR HEADER',
                    'idtrans' => $hutangbayarheader->id,
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $hutangbayarheader->toArray(),
                    'modifiedby' => $hutangbayarheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                PengeluaranDetail::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
                PengeluaranHeader::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
                JurnalUmumDetail::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
                JurnalUmumHeader::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
                HutangBayarDetail::where('hutangbayar_id', $hutangbayarheader->id)->lockForUpdate()->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->hutang_id); $i++) {
                    $hutang = HutangHeader::where('id', $request->hutang_id[$i])->first();

                    $datadetail = [
                        'hutangbayar_id' => $hutangbayarheader->id,
                        'nobukti' => $hutangbayarheader->nobukti,
                        'hutang_nobukti' => $hutang->nobukti,
                        'nominal' => $request->bayar[$i],
                        'cicilan' => '',
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'tglcair' => $request->tglcair[$i],
                        'userid' => '',
                        'coa_id' => '',
                        'potongan' => $request->potongan[$i],
                        'keterangan' => $request->keterangandetail[$i],
                        'modifiedby' => $hutangbayarheader->modifiedby,
                    ];
                    $data = new StoreHutangBayarDetailRequest($datadetail);
                    $datadetails = app(HutangBayarDetailController::class)->store($data);
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'hutangbayar_id' => $hutangbayarheader->id,
                        'nobukti' => $hutangbayarheader->nobukti,
                        'hutang_nobukti' => $hutang->nobukti,
                        'nominal' => $request->bayar[$i],
                        'cicilan' => '',
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'tglcair' => $request->tglcair[$i],
                        'userid' => '',
                        'coa_id' => '',
                        'potongan' => $request->potongan[$i],
                        'keterangan' => $request->keterangandetail[$i],
                        'modifiedby' => $hutangbayarheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($hutangbayarheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($hutangbayarheader->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY HUTANG BAYAR DETAIL',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $hutangbayarheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            //INSERT TO PENGELUARAN
            $bank = Bank::select('coa','statusformatpengeluaran','tipe')->where('id', $hutangbayarheader->bank_id)->first();
            $parameter = Parameter::where('id',$bank->statusformatpengeluaran)->first();
        
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            
            if($bank->tipe == 'KAS'){
                $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','KAS')->first();
            }
            if($bank->tipe == 'BANK'){
                $jenisTransaksi = Parameter::where('grp','JENIS TRANSAKSI')->where('text','BANK')->first();
            }
            $group = $parameter->grp;
            $subgroup = $parameter->subgrp;
            $format = DB::table('parameter')
                ->where('grp', $group )
                ->where('subgrp', $subgroup)
                ->first();

            $pengeluaranRequest = new Request();
            $pengeluaranRequest['group'] = $group;
            $pengeluaranRequest['subgroup'] = $subgroup;
            $pengeluaranRequest['table'] = 'pengeluaranheader';
            $pengeluaranRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobuktiPengeluaran= app(Controller::class)->getRunningNumber($pengeluaranRequest)->original['data'];
                
            $hutangbayarheader->pengeluaran_nobukti = $nobuktiPengeluaran;
            $hutangbayarheader->save();

            $pengeluaranHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $nobuktiPengeluaran,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pelanggan_id' => '',
                'keterangan' => $request->keterangan,
                'statusjenistransaksi' => $jenisTransaksi->id,
                'postingdari' => 'ENTRY HUTANG BAYAR',
                'statusapproval' => $statusApproval->id, 
                'dibayarke' => '',
                'cabang_id' => '',
                'bank_id' => $hutangbayarheader->bank_id,
                'userapproval' => '',
                'tglapproval' => '',
                'transferkeac' => '',
                'transferkean' => '',
                'transferkebank' => '',
                'statusformat' => $format->id,
                'modifiedby' => auth('api')->user()->name
            ];

            $pengeluaranDetail = [];
            $coaDebet = Parameter::where('grp','COA PEMBAYARAN HUTANG DEBET')->first();
            for ($i = 0; $i < count($request->hutang_id); $i++) {
                $hutang = HutangHeader::where('id', $request->hutang_id[$i])->first();
                $hutangDetail = HutangDetail::where('nobukti',$hutang->nobukti)->first();
                $detail = [];
                
                $detail = [
                    'entriluar' => 1,
                    'nobukti' => $nobuktiPengeluaran,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => '',
                    'tgljatuhtempo' => $hutangDetail->tgljatuhtempo,
                    'nominal' => $request->bayar[$i] - $request->potongan[$i],
                    'coadebet' => $coaDebet->text,
                    'coakredit' => $bank->coa,
                    'keterangan' => $request->keterangandetail[$i],
                    'bulanbeban' => '',
                    'modifiedby' =>  auth('api')->user()->name
                ];
                $pengeluaranDetail[] = $detail;
            }
            

            $pengeluaran = $this->storePengeluaran($pengeluaranHeader,$pengeluaranDetail);
            
            
            // if (!$pengeluaran['status'] AND @$pengeluaran['errorCode'] == 2601) {
            //     goto ATAS;
            // }
            if (!$pengeluaran['status']) {
                throw new \Throwable($pengeluaran['message']);
            }

            DB::commit();
            /* Set position and page */
            $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable());
            $hutangbayarheader->position = $selected->position;
            $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $hutangbayarheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName destroy
     */
    public function destroy(HutangBayarHeader $hutangbayarheader, Request $request)
    {
        DB::beginTransaction();
        try {

            $delete = PengeluaranDetail::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = PengeluaranHeader::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumDetail::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumHeader::where('nobukti',$hutangbayarheader->pengeluaran_nobukti)->lockForUpdate()->delete();
            $delete = HutangBayarDetail::where('hutangbayar_id', $hutangbayarheader->id)->lockForUpdate()->delete();
            $delete = HutangBayarHeader::destroy($hutangbayarheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangbayarheader->getTable()),
                    'postingdari' => 'DELETE PEMBAYARAN HUTANG HEADER',
                    'idtrans' => $hutangbayarheader->id,
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $hutangbayarheader->toArray(),
                    'modifiedby' => $hutangbayarheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable(), true);
                $hutangbayarheader->position = $selected->position;
                $hutangbayarheader->id = $selected->id;
                $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $hutangbayarheader
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

    public function combo(Request $request)
    {
        $data = [
            'supplier' => Supplier::all(),
            'bank' => Bank::all(),
            'coa' => AkunPusat::all(),
            'alatbayar' => AlatBayar::all(),
            'hutangbayar' => HutangBayarHeader::all(),
            'pengeluaran' => PengeluaranHeader::all(),
            'hutangheader' => HutangHeader::all(),

        ];

        return response([
            'data' => $data
        ]);
    }

    public function getHutang($id)
    {
        $hutang = new HutangHeader();
        return response([
            'data' => $hutang->getHutang($id),
            'id' => $id,
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    public function getPembayaran($id, $supplierId)
    {
        $hutangBayar = new HutangBayarHeader();
        return response([
            'data' => $hutangBayar->getPembayaran($id, $supplierId),
            'attributes' => [
                'totalRows' => $hutangBayar->totalRows,
                'totalPages' => $hutangBayar->totalPages
            ]
        ]);
    }

    public function storePengeluaran($pengeluaranHeader,$pengeluaranDetail)
    {
        try {

            
            $pengeluaran = new StorePengeluaranHeaderRequest($pengeluaranHeader);
            $header = app(PengeluaranHeaderController::class)->store($pengeluaran);
           
            $nobukti = $pengeluaranHeader['nobukti'];
            $fetchPengeluaran = PengeluaranHeader::whereRaw("nobukti = '$nobukti'")->first();
                
            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');
            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $fetchPengeluaran->nobukti,
                'tglbukti' => $fetchPengeluaran->tglbukti,
                'keterangan' => $fetchPengeluaran->keterangan,
                'postingdari' => "ENTRY PENGELUARAN DARI HUTANG BAYAR",
                'statusapproval' => $statusApp->id,
                'userapproval' => "",
                'tglapproval' => "",
                'statusformat' => 0,
                'modifiedby' => auth('api')->user()->name,
            ];
            $jurnal = new StoreJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->store($jurnal);
            $id = $fetchPengeluaran->id;

            foreach ($pengeluaranDetail as $value) {
                
                $value['pengeluaran_id'] = $id;
                $pengeluaranDetail = new StorePengeluaranDetailRequest($value);
                $tes = app(PengeluaranDetailController::class)->store($pengeluaranDetail);
                
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
                            'coa' =>  $pengeluaranDetail['coakredit'],
                            'nominal' => -$pengeluaranDetail['nominal'],
                            'keterangan' => $pengeluaranDetail['keterangan'],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $baris,
                        ];
                    } else {
                        $datadetail = [
                            'jurnalumum_id' => $fetchId->id,
                            'nobukti' => $nobukti,
                            'tglbukti' => $fetchId->tglbukti,
                            'coa' =>  $pengeluaranDetail['coadebet'],
                            'nominal' => $pengeluaranDetail['nominal'],
                            'keterangan' => $pengeluaranDetail['keterangan'],
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

    public function comboapproval(Request $request)
    {
        
        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, 10000);
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->default(0);
                $table->string('parameter', 50)->default(0);
                $table->string('param', 50)->default(0);
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }
}
