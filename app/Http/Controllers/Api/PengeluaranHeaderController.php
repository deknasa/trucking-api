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
use Exception;
use Illuminate\Database\QueryException;

class PengeluaranHeaderController extends Controller
{

    /**
     * @ClassName
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

            $tanpaprosesnobukti = $request->tanpaprosesnobukti ?? 0;


            if ($tanpaprosesnobukti == 0) {
                $content = new Request();
                $bankid = $request->bank_id;
                $querysubgrppengeluaran = DB::table('bank')
                    ->select(
                        'parameter.grp',
                        'parameter.subgrp',
                        'bank.statusformatpengeluaran',
                        'bank.coa',
                        'bank.tipe'
                    )
                    ->join('parameter', 'bank.statusformatpengeluaran', 'parameter.id')
                    ->whereRaw("bank.id = $bankid")
                    ->first();

                $content['group'] = $querysubgrppengeluaran->grp;
                $content['subgroup'] = $querysubgrppengeluaran->subgrp;
                $content['table'] = 'pengeluaranheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                if ($querysubgrppengeluaran->tipe == 'BANK') {
                    $request->validate([
                        'transferkeac' => 'required',
                        'transferkean' => 'required',
                        'transferkebank' => 'required',
                    ]);
                }
            }
            $pengeluaranHeader = new PengeluaranHeader();

            if ($tanpaprosesnobukti == 1) {
                $pengeluaranHeader->nobukti = $request->nobukti;
            }


            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $pengeluaranHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranHeader->pelanggan_id = $request->pelanggan_id;
            $pengeluaranHeader->keterangan = $request->keterangan ?? '';
            $pengeluaranHeader->statusjenistransaksi = $request->statusjenistransaksi ?? 0;
            $pengeluaranHeader->postingdari = $request->postingdari ?? 'ENTRY PENGELUARAN';
            $pengeluaranHeader->statusapproval = $statusApproval->id ?? $request->statusapproval;
            $pengeluaranHeader->statuscetak = $statusCetak->id ?? 0;
            $pengeluaranHeader->dibayarke = $request->dibayarke ?? '';
            $pengeluaranHeader->cabang_id = $request->cabang_id ?? 0;
            $pengeluaranHeader->bank_id = $request->bank_id ?? 0;
            $pengeluaranHeader->userapproval = $request->userapproval ?? '';
            $pengeluaranHeader->tglapproval = $request->tglapproval ?? '';
            $pengeluaranHeader->transferkeac = $request->transferkeac ?? '';
            $pengeluaranHeader->transferkean = $request->transferkean ?? '';
            $pengeluaranHeader->transferkebank = $request->transferkebank ?? '';
            $pengeluaranHeader->statusformat = $querysubgrppengeluaran->statusformatpengeluaran ?? $request->statusformat;
            $pengeluaranHeader->modifiedby = auth('api')->user()->name;

            if ($tanpaprosesnobukti == 0) {
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pengeluaranHeader->nobukti = $nobukti;
            }


            $pengeluaranHeader->save();

            if ($tanpaprosesnobukti == 1) {
                DB::commit();
            }
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                'postingdari' => 'ENTRY PENGELUARAN KAS HEADER',
                'idtrans' => $pengeluaranHeader->id,
                'nobuktitrans' => $pengeluaranHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengeluaranHeader->toArray(),
                'modifiedby' => $pengeluaranHeader->modifiedby
            ];


            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            /* Store detail */

            $parameterController = new ParameterController;
            $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');


            // if($tanpaprosesnobukti == 1) {
            //     $jurnalHeader = [
            //         'tanpaprosesnobukti' => 1,
            //         'nobukti' => $pengeluaranHeader->nobukti,
            //         'tglbukti' => $pengeluaranHeader->tglbukti,
            //         'keterangan' => $pengeluaranHeader->keterangan,
            //         'postingdari' => "ENTRY PENGELUARAN KAS DARI KAS GANTUNG",
            //         'statusapproval' => $statusApp->id,
            //         'userapproval' => "",
            //         'tglapproval' => "",
            //         'statusformat' => 0,
            //         'modifiedby' => auth('api')->user()->name,
            //     ];
            //     $jurnal = new StoreJurnalUmumHeaderRequest($jurnalHeader);
            //     app(JurnalUmumHeaderController::class)->store($jurnal);
            // }

            if ($tanpaprosesnobukti == 0) {
                $detaillog = [];
                for ($i = 0; $i < count($request->nominal_detail); $i++) {


                    // $coadebet = DB::table('akunpusat')
                    //     ->select(
                    //         'akunpusat.coa'
                    //     )
                    //     ->where('id', '=', $request->coadebet[$i])
                    //     ->first();

                    $datadetail = [
                        'pengeluaran_id' => $pengeluaranHeader->id,
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'nowarkat' => $request->nowarkat[$i],
                        'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' => $request->nominal_detail[$i],
                        'coadebet' => $request->coadebet[$i],
                        'coakredit' => $querysubgrppengeluaran->coa,
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
                        'alatbayar_id' => $request->alatbayar_id[$i],
                        'nowarkat' => $request->nowarkat[$i],
                        'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                        'nominal' => $request->nominal_detail[$i],
                        'coadebet' =>  $request->coadebet[$i],
                        'coakredit' => $querysubgrppengeluaran->coa,
                        'keterangan' => $request->keterangan_detail[$i],
                        'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                        'modifiedby' => auth('api')->user()->name,
                        'created_at' => date('d-m-Y H:i:s', strtotime($pengeluaranHeader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluaranHeader->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }

                $dataid = LogTrail::select('id')
                    ->where('nobuktitrans', '=', $pengeluaranHeader->nobukti)
                    ->where('namatabel', '=', $pengeluaranHeader->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PENGELUARAN KAS DETAIL',
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

                    // $coadebet = DB::table('akunpusat')
                    //     ->select(
                    //         'akunpusat.coa'
                    //     )
                    //     ->where('id', '=', $request->coadebet[0])
                    //     ->first();


                    $jurnalHeader = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pengeluaranHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                        'keterangan' => $request->keterangan,
                        'postingdari' => "ENTRY PENGELUARAN KAS",
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
                                'nobukti' => $pengeluaranHeader->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                                'coa' =>  $request->coadebet[$i],
                                'nominal' => $request->nominal_detail[$i],
                                'keterangan' => $request->keterangan_detail[$i],
                                'modifiedby' => auth('api')->user()->name,
                                'baris' => $i,
                            ],
                            [
                                'nobukti' => $pengeluaranHeader->nobukti,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                                'coa' =>  $querysubgrppengeluaran->coa,
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
                    $selected = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable());
                    $pengeluaranHeader->position = $selected->position;
                    $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
                }
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
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

    /**
     * @ClassName
     */
    public function update(UpdatePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranheader)
    {
        DB::beginTransaction();

        try {
            /* Store header */
            $bankid = $request->bank_id;
            $querysubgrppengeluaran = DB::table('bank')
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                    'bank.statusformatpengeluaran',
                    'bank.coa',
                    'bank.tipe'
                )
                ->join('parameter', 'bank.statusformatpengeluaran', 'parameter.id')
                ->whereRaw("bank.id = $bankid")
                ->first();

            if ($querysubgrppengeluaran->tipe == 'BANK') {
                $request->validate([
                    'transferkeac' => 'required',
                    'transferkean' => 'required',
                    'transferkebank' => 'required',
                ]);
            }
            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


            $pengeluaranheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranheader->pelanggan_id = $request->pelanggan_id;
            $pengeluaranheader->keterangan = $request->keterangan ?? '';
            $pengeluaranheader->statusjenistransaksi = $request->statusjenistransaksi ?? 0;
            $pengeluaranheader->postingdari = $request->postingdari ?? 'ENTRY PENGELUARAN';
            $pengeluaranheader->statusapproval = $statusApproval->id ?? 0;
            $pengeluaranheader->statuscetak = $statusCetak->id ?? 0;
            $pengeluaranheader->dibayarke = $request->dibayarke ?? '';
            $pengeluaranheader->cabang_id = $request->cabang_id ?? 0;
            $pengeluaranheader->bank_id = $request->bank_id ?? 0;
            $pengeluaranheader->transferkeac = $request->transferkeac ?? '';
            $pengeluaranheader->transferkean = $request->transferkean ?? '';
            $pengeluaranheader->transferkebank = $request->transferkebank ?? '';
            $pengeluaranheader->modifiedby = auth('api')->user()->name;

            if ($pengeluaranheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranheader->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN KAS',
                    'idtrans' => $pengeluaranheader->id,
                    'nobuktitrans' => $pengeluaranheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranheader->toArray(),
                    'modifiedby' => $pengeluaranheader->modifiedby
                ];


                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            /* Delete existing detail */
            PengeluaranDetail::where('nobukti', $pengeluaranheader->nobukti)->lockForUpdate()->delete();
            JurnalUmumDetail::where('nobukti', $pengeluaranheader->nobukti)->lockForUpdate()->delete();
            JurnalUmumHeader::where('nobukti', $pengeluaranheader->nobukti)->lockForUpdate()->delete();

            /* Store detail */
            $detaillog = [];

            for ($i = 0; $i < count($request->nominal_detail); $i++) {


                $datadetail = [
                    'pengeluaran_id' => $pengeluaranheader->id,
                    'nobukti' => $pengeluaranheader->nobukti,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $querysubgrppengeluaran->coa,
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
                    'pengeluaran_id' => $pengeluaranheader->id,
                    'nobukti' => $pengeluaranheader->nobukti,
                    'alatbayar_id' => $request->alatbayar_id[$i],
                    'nowarkat' => $request->nowarkat[$i],
                    'tgljatuhtempo' =>  date('Y-m-d', strtotime($request->tgljatuhtempo[$i])),
                    'nominal' => $request->nominal_detail[$i],
                    'coadebet' => $request->coadebet[$i],
                    'coakredit' => $querysubgrppengeluaran->coa,
                    'keterangan' => $request->keterangan_detail[$i],
                    'bulanbeban' =>  date('Y-m-d', strtotime($request->bulanbeban[$i])),
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($pengeluaranheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($pengeluaranheader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $pengeluaranheader->nobukti)
                ->where('namatabel', '=', $pengeluaranheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'EDIT PENGELUARAN DETAIL',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $pengeluaranheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pengeluaranheader->save() && $pengeluaranheader->pengeluarandetail()) {
                $parameterController = new ParameterController;
                $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

                $jurnalHeader = [
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $pengeluaranheader->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                    'keterangan' => $request->keterangan,
                    'postingdari' => "ENTRY PENGELUARAN KAS",
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
                            'nobukti' => $pengeluaranheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $request->coadebet[$i],
                            'nominal' => $request->nominal_detail[$i],
                            'keterangan' => $request->keterangan_detail[$i],
                            'modifiedby' => auth('api')->user()->name,
                            'baris' => $i,
                        ],
                        [
                            'nobukti' => $pengeluaranheader->nobukti,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'coa' =>  $querysubgrppengeluaran->coa,
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
                $selected = $this->getPosition($pengeluaranheader, $pengeluaranheader->getTable());
                $pengeluaranheader->position = $selected->position;
                $pengeluaranheader->page = ceil($pengeluaranheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $pengeluaranheader
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
    public function destroy(PengeluaranHeader $pengeluaranheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $delete = PengeluaranDetail::where('pengeluaran_id', $pengeluaranheader->id)->lockForUpdate()->delete();
            $delete = JurnalUmumDetail::where('nobukti', $pengeluaranheader->nobukti)->lockForUpdate()->delete();
            $delete = JurnalUmumHeader::where('nobukti', $pengeluaranheader->nobukti)->lockForUpdate()->delete();

            $delete = PengeluaranHeader::destroy($pengeluaranheader->id);


            $datalogtrail = [
                'namatabel' => strtoupper($pengeluaranheader->getTable()),
                'postingdari' => 'DELETE PENGELUARAN KAS/BANK',
                'idtrans' => $pengeluaranheader->id,
                'nobuktitrans' => $pengeluaranheader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranheader->toArray(),
                'modifiedby' => $pengeluaranheader->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            if ($delete) {
                DB::commit();
                $selected = $this->getPosition($pengeluaranheader, $pengeluaranheader->getTable(), true);
                $pengeluaranheader->position = $selected->position;
                $pengeluaranheader->id = $selected->id;
                $pengeluaranheader->page = ceil($pengeluaranheader->position / ($request->limit ?? 10));
                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $pengeluaranheader
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
        $data = PengeluaranHeader::findAll($id);
        $detail = PengeluaranDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
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
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }


    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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
}
