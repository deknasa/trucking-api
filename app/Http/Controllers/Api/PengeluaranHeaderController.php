<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranDetail;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\Cabang;
use App\Models\Bank;
use App\Models\AlatBayar;
use App\Models\AkunPusat;
use App\Models\LogTrail;

use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;

use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;


class PengeluaranHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pengeluaran = new PengeluaranHeader();

        return response([
            'data' => $pengeluaran->get(),
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePengeluaranHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $content = new Request();
            $content['group'] = 'PENGELUARAN KAS';
            $content['subgroup'] = 'NOMOR PENGELUARAN KAS';
            $content['table'] = 'pengeluaranheader';

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

            $pengeluaranHeader = new PengeluaranHeader();
            $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranHeader->pelanggan_id = $request->pelanggan_id;
            $pengeluaranHeader->keterangan = $request->keterangan ?? '';
            $pengeluaranHeader->statusjenistransaksi = $request->statusjenistransaksi ?? 0;
            $pengeluaranHeader->postingdari = $request->postingdari ?? 'PENGELUARAN';
            $pengeluaranHeader->statusapproval = $statusApproval->id ?? 0;
            $pengeluaranHeader->dibayarke = $request->dibayarke ?? '';
            $pengeluaranHeader->cabang_id = $request->cabang_id ?? 0;
            $pengeluaranHeader->bank_id = $request->bank_id ?? 0;
            $pengeluaranHeader->transferkeac = $request->transferkeac ?? '';
            $pengeluaranHeader->transferkean = $request->transferkean ?? '';
            $pengeluaranHeader->transferkebank = $request->transferkebank ?? '';
            $pengeluaranHeader->modifiedby = auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengeluaranHeader->nobukti = $nobukti;

            try {
                $pengeluaranHeader->save();
            } catch (\Exception $e) {
                //dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                'postingdari' => 'ENTRY PENGELUARAN KAS',
                'idtrans' => $pengeluaranHeader->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengeluaranHeader->toArray(),
                'modifiedby' => $pengeluaranHeader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];

            $total = 0;

            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'pengeluaran_id' => $pengeluaranHeader->id,
                    'nobukti' => $pengeluaranHeader->nobukti,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $nominal,
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $pengeluaranHeader->bank_id,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                ];
                $data = new StorePengeluaranDetailRequest($datadetail);
                $datadetails = app(PengeluaranDetailController::class)->store($data);


                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'pengeluaran_id' => $pengeluaranHeader->id,
                    'nobukti' => $pengeluaranHeader->nobukti,
                    //   'alatbayar_id' => $request->namaalatbayar[$i],
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $nominal,
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $pengeluaranHeader->bank_id,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($pengeluaranHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluaranHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $pengeluaranHeader->nobukti)
                ->where('namatabel', '=', $pengeluaranHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();
            //    dd($dataid);

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY PENGELUARAN',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pengeluaranHeader->save() && $pengeluaranHeader->pengeluarandetail()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'nobukti' => $pengeluaranHeader->nobukti,
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => "ENTRY PENGELUARAN KAS",
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                ];


                $jurnalDetail = [
                    [
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => $request->coadebet,
                        'nominal' => $total,
                        'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                    ],
                    [
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' =>  $pengeluaranHeader->bank_id,
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
                $pengeluaranHeader->position = PengeluaranHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $pengeluaranHeader->{$request->sortname})
                    ->where('id', '<=', $pengeluaranHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $pengeluaranHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($pengeluaranHeader->pengeluarandetail);
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

    public function update(StorePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranHeader, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $pengeluaranHeader = PengeluaranHeader::findOrFail($id);
            $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranHeader->pelanggan_id = $request->pelanggan_id;
            $pengeluaranHeader->keterangan = $request->keterangan ?? '';
            $pengeluaranHeader->statusjenistransaksi = $request->statusjenistransaksi ?? 0;
            $pengeluaranHeader->postingdari = $request->postingdari ?? 'PENGELUARAN';
            $pengeluaranHeader->statusapproval = $request->statusapproval ?? 0;
            $pengeluaranHeader->dibayarke = $request->dibayarke ?? '';
            $pengeluaranHeader->cabang_id = $request->cabang_id ?? 0;
            $pengeluaranHeader->bank_id = $request->bank_id ?? 0;
            $pengeluaranHeader->transferkeac = $request->transferkeac ?? '';
            $pengeluaranHeader->transferkean = $request->transferkean ?? '';
            $pengeluaranHeader->transferkebank = $request->transferkebank ?? '';
            $pengeluaranHeader->modifiedby = auth('api')->user()->name;

            if ($pengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN',
                    'idtrans' => $pengeluaranHeader->id,
                    'nobuktitrans' => $pengeluaranHeader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranHeader->toArray(),
                    'modifiedby' => $pengeluaranHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            /* Delete existing detail */
            $pengeluaranHeader->pengeluaranDetail()->delete();
            JurnalUmumDetail::where('nobukti', $pengeluaranHeader->nobukti)->delete();
            JurnalUmumHeader::where('nobukti', $pengeluaranHeader->nobukti)->delete();

            /* Store detail */
            $detaillog = [];

            $total = 0;
            $coaDebet = Parameter::where('grp', 'COA')->where('subgrp', 'PENGELUARAN KAS DEBET')->first();
            $coaKredit = Parameter::where('grp', 'COA')->where('subgrp', 'PENGELUARAN KAS KREDIT')->first();
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'pengeluaran_id' => $pengeluaranHeader->id,
                    'nobukti' => $pengeluaranHeader->nobukti,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $nominal,
                    'coadebet' => $coaDebet->text ?? '',
                    'coakredit' => $coaKredit->text ?? '',
                    // 'coadebet' => $request->coadebet[$i],
                    // 'coakredit' => $request->coakredit[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                ];
                $data = new StorePengeluaranDetailRequest($datadetail);
                $datadetails = app(PengeluaranDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }


                $datadetaillog = [
                    'pengeluaran_id' => $pengeluaranHeader->id,
                    'nobukti' => $pengeluaranHeader->nobukti,
                    //   'alatbayar_id' => $request->namaalatbayar[$i],
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $nominal,
                    'coadebet' => $coaDebet->text ?? '',
                    'coakredit' => $coaKredit->text ?? '',
                    // 'coadebet' => $request->coadebet[$i],
                    // 'coakredit' => $request->coakredit[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($pengeluaranHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluaranHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $pengeluaranHeader->nobukti)
                ->where('namatabel', '=', $pengeluaranHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT PENGELUARAN',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pengeluaranHeader->save() && $pengeluaranHeader->pengeluarandetail()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'nobukti' => $pengeluaranHeader->nobukti,
                    'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => "EDIT PENGELUARAN KAS",
                    'statusapproval' => $statusApp->id,
                    'userapproval' => "",
                    'tglapproval' => "",
                    'modifiedby' => auth('api')->user()->name,
                ];

                $jurnalDetail = [
                    [
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'tgl' => date('Y-m-d', strtotime($request->tglbukti)),
                        'coa' => $coaDebet->text ?? '',
                        'nominal' => $total,
                        'keterangan' => $request->keterangan,
                        'modifiedby' => auth('api')->user()->name,
                    ],
                    [
                        'nobukti' => $pengeluaranHeader->nobukti,
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
                $pengeluaranHeader->position = PengeluaranHeader::orderBy($request->sortname, $request->sortorder)
                    ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $pengeluaranHeader->{$request->sortname})
                    ->where('id', '<=', $pengeluaranHeader->id)
                    ->count();

                if (isset($request->limit)) {
                    $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $pengeluaranHeader
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($pengeluaranHeader->pengeluarandetail);
    }

    /**
     * @ClassName
     */
    public function destroy($id, JurnalUmumHeader $jurnalumumheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = PengeluaranHeader::find($id);
            // $get = JurnalUmumDetail::find($id);
            // $get = JurnalUmumHeader::find($id);

            $delete = PengeluaranDetail::where('pengeluaran_id', $id)->delete();
            $delete = JurnalUmumDetail::where('nobukti', $get->nobuktikaskeluar)->delete();
            $delete = JurnalUmumHeader::where('nobukti', $get->nobuktikaskeluar)->delete();

            $delete = PengeluaranHeader::destroy($id);
            // $delete = JurnalUmumHeader::destroy($id);
            // $delete = JurnalUmumDetail::destroy($id);


            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE SERVICE IN',
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

    public function show($id)
    {
        $data = PengeluaranHeader::with(
            'pengeluarandetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'pelanggan'     => Pelanggan::all(),
            'cabang'        => Cabang::all(),
            'bank'          => Bank::all(),
            'pengeluaran'   => PengeluaranHeader::all(),
            'pengeluaran'   => PengeluaranHeader::all(),
            'alatbayar'     => AlatBayar::all(),
            'akunpusat'     => AkunPusat::all(),
            'statusjenistransaksi' => Parameter::where('grp', 'JENIS TRANSAKSI')->get(),
            'statuskas'     => Parameter::where('grp', 'STATUS KAS')->get(),

            'statusapproval' => Parameter::where('grp', 'STATUS APPROVAL')->get(),
            'statusberkas'  => Parameter::where('grp', 'STATUS BERKAS')->get(),

        ];

        return response([
            'data' => $data
        ]);
    }

    public function approval($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranHeader = PengeluaranHeader::find($id);
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($pengeluaranHeader->statusapproval == $statusApproval->id) {
                $pengeluaranHeader->statusapproval = $statusNonApproval->id;
            } else {
                $pengeluaranHeader->statusapproval = $statusApproval->id;
            }

            $pengeluaranHeader->tglapproval = date('Y-m-d', time());
            $pengeluaranHeader->userapproval = auth('api')->user()->name;

            if ($pengeluaranHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                    'postingdari' => 'UN/APPROVE pengeluaranheader',
                    'idtrans' => $pengeluaranHeader->id,
                    'nobuktitrans' => $pengeluaranHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $pengeluaranHeader->toArray(),
                    'modifiedby' => $pengeluaranHeader->modifiedby
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
