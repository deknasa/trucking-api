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
use App\Models\PengeluaranHeaderController;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;



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

    

    public function show($id)
    {
        $data = PenerimaanHeader::with(
            'penerimaandetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'penerimaan'    => PenerimaanHeader::all(),
            'cabang'        => Cabang::all(),
            'pelanggan'     => Pelanggan::all(),
            'bankpelanggan' => BankPelanggan::all(),
            'bank'          => Bank::all(),
            'coa'           => AkunPusat::all(),
            'penerimaanpiutang' => PelunasanPiutangHeader::all(),

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
    public function create()
    {
        //
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
            $content['group'] = 'PENERIMAAN KAS';
            $content['subgroup'] = 'NOMOR PENERIMAAN KAS';
            $content['table'] = 'penerimaanheader';

            $penerimaanHeader = new PenerimaanHeader();
            $penerimaanHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanHeader->pelanggan_id = $request->pelanggan_id;
            $penerimaanHeader->keterangan = $request->keterangan ?? '';
            $penerimaanHeader->postingdari = $request->postingdari ?? 'PENERIMAAN';
            $penerimaanHeader->diterimadari = $request->diterimadari ?? '';
            $penerimaanHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanHeader->cabang_id = $request->cabang_id ?? 0;
            $penerimaanHeader->statuskas = $request->statuskas ?? 0;
            $penerimaanHeader->noresi = $request->noresi ?? 0;
            // $penerimaanHeader->statusberkas = $request->statusberkas ?? 0;
            $penerimaanHeader->modifiedby = auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $penerimaanHeader->nobukti = $nobukti;
            
            try {
                $penerimaanHeader->save();
            } catch (\Exception $e) {
                //   dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }
            
            $logTrail = [
                'namatabel' => strtoupper($penerimaanHeader->getTable()),
                'postingdari' => 'ENTRY PENERIMAAN',
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
            
            $total = 0;
            $coaDebet = Parameter::where('grp', 'COA')->where('subgrp', 'PENERIMAAN KAS DEBET')->first();
            $coaKredit = Parameter::where('grp', 'COA')->where('subgrp', 'PENERIMAAN KAS KREDIT')->first();

            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'penerimaan_id' => $penerimaanHeader->id,
                    'nobukti' => $penerimaanHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $nominal,
                    'coadebet' => $coaDebet->text ?? '',
                    'coakredit' => $coaKredit->text ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $request->bank_id[$i],
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
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
                    'nominal' => $nominal,
                    'coadebet' => $coaDebet->text ?? '',
                    'coakredit' => $coaKredit->text ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $request->bank_id[$i],
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaanHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaanHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }
            
            $dataid = LogTrail::select('id')
            ->where('nobuktitrans', '=', $penerimaanHeader->nobukti)
            ->where('namatabel', '=', $penerimaanHeader->getTable())
            ->orderBy('id', 'DESC')
            ->first();
        //    dd($dataid);

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY PENERIMAAN',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => '',
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            
            if ($penerimaanHeader->save() && $penerimaanHeader->penerimaandetail()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'nobukti' => $penerimaanHeader->nobukti,
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => "ENTRY PENERIMAAN KAS",
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                ];

                $jurnalDetail = [
                    [
                        'nobukti' => $penerimaanHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => $coaDebet->text,
                        'nominal' => $total,
                        'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                    ],
                    [
                        'nobukti' => $penerimaanHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => $coaKredit->text ?? '',
                        'nominal' => -$total,
                        'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                    ]
                ];

                $jurnal = $this->storeJurnal($jurnalHeader, $jurnalDetail);

                // if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                //     goto ATAS;
                // }

                if (!$jurnal['status']) {
                    throw new \Throwable($jurnal['message']);
                }

                DB::commit();

                /* Set position and page */
                $penerimaanHeader->position = PenerimaanHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $penerimaanHeader->{$request->sortname})
                    ->where('id', '<=', $penerimaanHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $penerimaanHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($penerimaanHeader->penerimaandetail);
    }


    /**
     * @ClassName
     */
    public function edit(PenerimaanHeader $penerimaanHeader)
    {
        //
    }

    /**
     * @ClassName
     */
    public function update(StorePenerimaanHeaderRequest $request, PenerimaanHeader $penerimaanHeader, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $penerimaanHeader = PenerimaanHeader::findOrFail($id);
            $penerimaanHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaanHeader->pelanggan_id = $request->pelanggan_id;
            $penerimaanHeader->keterangan = $request->keterangan ?? '';
            $penerimaanHeader->postingdari = $request->postingdari ?? 'PENERIMAAN';
            $penerimaanHeader->diterimadari = $request->diterimadari ?? '';
            $penerimaanHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $penerimaanHeader->cabang_id = $request->cabang_id ?? 0;
            $penerimaanHeader->statuskas = $request->statuskas ?? 0;
            $penerimaanHeader->noresi = $request->noresi ?? 0;
            // $penerimaanHeader->statusberkas = $request->statusberkas ?? 0;
            $penerimaanHeader->modifiedby = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanHeader->getTable()),
                    'postingdari' => 'ENTRY PENERIMAAN',
                    'idtrans' => $penerimaanHeader->id,
                    'nobuktitrans' => $penerimaanHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaanHeader->toArray(),
                    'modifiedby' => $penerimaanHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

             /* Delete existing detail */
             $penerimaanHeader->penerimaanDetail()->delete();
             JurnalUmumDetail::where('nobukti',$penerimaanHeader->nobukti)->delete();
             JurnalUmumHeader::where('nobukti',$penerimaanHeader->nobukti)->delete();

            /* Store detail */
            $detaillog = [];

            $total = 0;
            $coaDebet = Parameter::where('grp', 'COA')->where('subgrp', 'PENERIMAAN KAS DEBET')->first();
            $coaKredit = Parameter::where('grp', 'COA')->where('subgrp', 'PENERIMAAN KAS KREDIT')->first();
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'penerimaan_id' => $penerimaanHeader->id,
                    'nobukti' => $penerimaanHeader->nobukti,
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $nominal,
                    'coadebet' => $coaDebet->text ?? '',
                    'coakredit' => $coaKredit->text ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $request->bank_id[$i],
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
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
                    'nominal' => $nominal,
                    'coadebet' => $coaDebet->text ?? '',
                    'coakredit' => $coaKredit->text ?? '',
                    'keterangan' => $request->keterangan_detail[$i],
                    'bank_id' => $request->bank_id[$i],
                    'bankpelanggan_id' => $request->bankpelanggan_id[$i],
                    'jenisbiaya' => $request->jenisbiaya[$i],
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaanHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaanHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $penerimaanHeader->nobukti)
                ->where('namatabel', '=', $penerimaanHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY PENERIMAAN',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $penerimaanHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($penerimaanHeader->save() && $penerimaanHeader->penerimaandetail()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'nobukti' => $penerimaanHeader->nobukti,
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => "ENTRY PENERIMAAN KAS",
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                ];

                $jurnalDetail = [
                    [
                        'nobukti' => $penerimaanHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => $coaDebet->text,
                        'nominal' => $total,
                        'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                    ],
                    [
                        'nobukti' => $penerimaanHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => $coaKredit->text ?? '',
                        'nominal' => -$total,
                        'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                    ]
                ];

                $jurnal = $this->storeJurnal($jurnalHeader, $jurnalDetail);


                // if (!$jurnal['status'] AND @$jurnal['errorCode'] == 2601) {
                //     goto ATAS;
                // }

                if (!$jurnal['status']) {
                    throw new \Throwable($jurnal['message']);
                }

                DB::commit();

                /* Set position and page */
                $penerimaanHeader->position = PenerimaanHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $penerimaanHeader->{$request->sortname})
                    ->where('id', '<=', $penerimaanHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $penerimaanHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($penerimaanHeader->penerimaandetail);
    }

    /**
     * @ClassName
     */
    public function destroy($id,JurnalUmumHeader $jurnalumumheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = PenerimaanHeader::find($id);
            // $get = JurnalUmumDetail::find($id);
            // $get = JurnalUmumHeader::find($id);

            $delete = PenerimaanDetail::where('penerimaan_id', $id)->delete();
            $delete = JurnalUmumDetail::where('nobukti',$get->nobuktikaskeluar)->delete();
            $delete = JurnalUmumHeader::where('nobukti',$get->nobuktikaskeluar)->delete();

            $delete = PenerimaanHeader::destroy($id);
            // $delete = JurnalUmumHeader::destroy($id);
            // $delete = JurnalUmumDetail::destroy($id);


            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE PENERIMAAN',
                'idtrans' => $id,
                'nobuktitrans' => '',
                'aksi' => 'HAPUS',
                'datajson' => '',
                'modifiedby' => $get->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            if ($delete) {
                DB::commit();
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus'
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

    private function storeJurnal($header, $detail)
    {
        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);
            
            foreach ($detail as $key => $value) {
                $value['jurnalumum_id'] = $jurnals['id'];

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
}
