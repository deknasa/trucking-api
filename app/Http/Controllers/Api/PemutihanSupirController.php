<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorepemutihansupirdetailRequest;
use App\Models\PemutihanSupir;
use App\Http\Requests\StorePemutihanSupirRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePemutihanSupirRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\PemutihanSupirDetail;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PemutihanSupirController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pemutihanSupir = new PemutihanSupir();
        return response([
            'data' => $pemutihanSupir->get(),
            'attributes' => [
                'totalRows' => $pemutihanSupir->totalRows,
                'totalPages' => $pemutihanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePemutihanSupirRequest $request)
    {
        DB::beginTransaction();
        try {
            $group = 'PEMUTIHAN SUPIR BUKTI';
            $subgroup = 'PEMUTIHAN SUPIR BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pemutihansupirheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $pemutihanSupir = new PemutihanSupir();

            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $nominalPosting = ($request->posting_nominal) ? array_sum($request->posting_nominal) : 0;
            $nominalNonPosting = ($request->nonposting_nominal) ? array_sum($request->nonposting_nominal) : 0;
            $pemutihanSupir->nobukti = $nobukti;
            $pemutihanSupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pemutihanSupir->supir_id = $request->supir_id;
            $pemutihanSupir->pengeluaransupir = $nominalPosting + $nominalNonPosting;
            $pemutihanSupir->penerimaansupir = $request->penerimaansupir ?? 0;
            $pemutihanSupir->bank_id = $request->bank_id;
            $pemutihanSupir->coa = $coaPengembalian->coapostingkredit;
            $pemutihanSupir->statusformat = $format->id;
            $pemutihanSupir->modifiedby = auth('api')->user()->name;

            //GET NO BUKTI PENERIMAAN
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
            $formatPenerimaan = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();
            $penerimaanRequest = new Request();
            $penerimaanRequest['group'] = $querysubgrppenerimaan->grp;
            $penerimaanRequest['subgroup'] = $querysubgrppenerimaan->subgrp;
            $penerimaanRequest['table'] = 'penerimaanheader';
            $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];

            $pemutihanSupir->penerimaan_nobukti = $nobuktiPenerimaan;
            $pemutihanSupir->save();

            $logTrail = [
                'namatabel' => strtoupper($pemutihanSupir->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY PEMUTIHAN SUPIR',
                'idtrans' => $pemutihanSupir->id,
                'nobuktitrans' => $pemutihanSupir->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pemutihanSupir->toArray(),
                'modifiedby' => $pemutihanSupir->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $detaillog = [];
            $penerimaanDetail = [];
            $posting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
            if ($request->postingId) {

                for ($i = 0; $i < count($request->postingId); $i++) {
                    $datadetail = [
                        'pemutihansupir_id' => $pemutihanSupir->id,
                        'nobukti' => $pemutihanSupir->nobukti,
                        'pengeluarantrucking_nobukti' => $request->posting_nobukti[$i],
                        'nominal' => $request->posting_nominal[$i],
                        'statusposting' => $posting->id,
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $penerimaanDetail[] = [
                        'nobukti' => $nobuktiPenerimaan,
                        'nowarkat' => '',
                        'tgljatuhtempo' => date('Y-m-d', strtotime($request->tglbukti)),
                        "nominal" => $request->posting_nominal[$i],
                        'coadebet' => $querysubgrppenerimaan->coa,
                        'coakredit' => $coaPengembalian->coapostingkredit,
                        'keterangan' => $request->posting_keterangan[$i],
                        'invoice_nobukti' => '',
                        'pelunasanpiutang_nobukti' => '',
                        'bulanbeban' => date('Y-m-d', strtotime($request->tglbukti)),
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    // STORE 
                    $data = new StorepemutihansupirdetailRequest($datadetail);

                    $datadetails = app(PemutihanSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
            }

            if ($request->nonpostingId) {

                $nonPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
                for ($i = 0; $i < count($request->nonpostingId); $i++) {
                    $datadetail = [
                        'pemutihansupir_id' => $pemutihanSupir->id,
                        'nobukti' => $pemutihanSupir->nobukti,
                        'pengeluarantrucking_nobukti' => $request->nonposting_nobukti[$i],
                        'nominal' => $request->nonposting_nominal[$i],
                        'statusposting' => $nonPosting->id,
                        'modifiedby' => auth('api')->user()->name
                    ];

                    // STORE 
                    $data = new StorepemutihansupirdetailRequest($datadetail);

                    $datadetails = app(PemutihanSupirDetailController::class)->store($data);

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
                'postingdari' => 'ENTRY PEMUTIHAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pemutihanSupir->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $pemutihanSupir->modifiedby,
            ];
            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $supir = Supir::from(DB::raw("supir with (readuncommitted)"))->where('id', $request->supir_id)->first();

            $penerimaanHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $nobuktiPenerimaan,
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'pelanggan_id' => '',
                'bank_id' => $request->bank_id,
                'postingdari' => 'ENTRY PEMUTIHAN SUPIR',
                'diterimadari' => "PEMUTIHAN SUPIR $supir->namasupir",
                'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                'statusformat' => $formatPenerimaan->id,
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $penerimaanDetail
            ];

            $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
            app(PenerimaanHeaderController::class)->store($penerimaan);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable());
            $pemutihanSupir->position = $selected->position;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pemutihanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $data = PemutihanSupir::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePemutihanSupirRequest $request, PemutihanSupir $pemutihansupir)
    {
        DB::beginTransaction();
        try {
            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $nominalPosting = ($request->posting_nominal) ? array_sum($request->posting_nominal) : 0;
            $nominalNonPosting = ($request->nonposting_nominal) ? array_sum($request->nonposting_nominal) : 0;

            $pemutihansupir->pengeluaransupir = $nominalPosting + $nominalNonPosting;
            $pemutihansupir->penerimaansupir = $request->penerimaansupir ?? 0;
            $pemutihansupir->coa = $coaPengembalian->coapostingkredit;
            $pemutihansupir->modifiedby = auth('api')->user()->name;
            $pemutihansupir->save();

            $logTrail = [
                'namatabel' => strtoupper($pemutihansupir->getTable()),
                'postingdari' => 'EDIT PEMUTIHAN SUPIR',
                'idtrans' => $pemutihansupir->id,
                'nobuktitrans' => $pemutihansupir->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pemutihansupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            PemutihanSupirDetail::where('pemutihansupir_id', $pemutihansupir->id)->delete();
            $coadebet = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $pemutihansupir->bank_id)->first();
            $detaillog = [];
            $penerimaanDetail = [];
            $posting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
            if ($request->postingId) {

                for ($i = 0; $i < count($request->postingId); $i++) {
                    $datadetail = [
                        'pemutihansupir_id' => $pemutihansupir->id,
                        'nobukti' => $pemutihansupir->nobukti,
                        'pengeluarantrucking_nobukti' => $request->posting_nobukti[$i],
                        'nominal' => $request->posting_nominal[$i],
                        'statusposting' => $posting->id,
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $penerimaanDetail[] = [
                        'nobukti' => $pemutihansupir->penerimaan_nobukti,
                        'nowarkat' => '',
                        'tgljatuhtempo' => date('Y-m-d', strtotime($pemutihansupir->tglbukti)),
                        "nominal" => $request->posting_nominal[$i],
                        'coadebet' => $coadebet->coa,
                        'coakredit' => $coaPengembalian->coapostingkredit,
                        'keterangan' => $request->posting_keterangan[$i],
                        'invoice_nobukti' => '',
                        'pelunasanpiutang_nobukti' => '',
                        'bulanbeban' => date('Y-m-d', strtotime($pemutihansupir->tglbukti)),
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    // STORE 
                    $data = new StorepemutihansupirdetailRequest($datadetail);

                    $datadetails = app(PemutihanSupirDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
            }

            if ($request->nonpostingId) {

                $nonPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
                for ($i = 0; $i < count($request->nonpostingId); $i++) {
                    $datadetail = [
                        'pemutihansupir_id' => $pemutihansupir->id,
                        'nobukti' => $pemutihansupir->nobukti,
                        'pengeluarantrucking_nobukti' => $request->nonposting_nobukti[$i],
                        'nominal' => $request->nonposting_nominal[$i],
                        'statusposting' => $nonPosting->id,
                        'modifiedby' => auth('api')->user()->name
                    ];

                    // STORE 
                    $data = new StorepemutihansupirdetailRequest($datadetail);

                    $datadetails = app(PemutihanSupirDetailController::class)->store($data);

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
                'postingdari' => 'EDIT PEMUTIHAN SUPIR DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pemutihansupir->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => $pemutihansupir->modifiedby,
            ];
            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $penerimaanHeader = [
                'isUpdate' => 1,
                'postingdari' => 'EDIT PEMUTIHAN SUPIR',
                'datadetail' => $penerimaanDetail,
                'nowarkat' => '',
                'bank_id' => $pemutihansupir->bank_id

            ];
            $get = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $pemutihansupir->penerimaan_nobukti)->first();
            $newPenerimaan = new PenerimaanHeader();
            $newPenerimaan = $newPenerimaan->findAll($get->id);

            $penerimaanUpdate = new UpdatePenerimaanHeaderRequest($penerimaanHeader);
            app(PenerimaanHeaderController::class)->update($penerimaanUpdate, $newPenerimaan);


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pemutihansupir, $pemutihansupir->getTable());
            $pemutihansupir->position = $selected->position;
            $pemutihansupir->page = ceil($pemutihansupir->position / ($request->limit ?? 10));



            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pemutihansupir
            ], 201);
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

        $getDetail = PemutihanSupirDetail::lockForUpdate()->where('pemutihansupir_id', $id)->get();
        $request['postingdari'] =  'DELETE PEMUTIHAN SUPIR';
        $pemutihanSupir = new PemutihanSupir();
        $pemutihanSupir = $pemutihanSupir->lockAndDestroy($id);
        if ($pemutihanSupir) {
            // DELETE PEMUTIHAN SUPIR
            $logTrail = [
                'namatabel' => strtoupper($pemutihanSupir->getTable()),
                'postingdari' => 'DELETE PEMUTIHAN SUPIR',
                'idtrans' => $pemutihanSupir->id,
                'nobuktitrans' => $pemutihanSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pemutihanSupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            
            // DELETE PEMUTIHAN SUPIR DETAIL
            $logTrailPemutihanSupirDetail = [
                'namatabel' => 'PEMUTIHANSUPIRDETAIL',
                'postingdari' => 'DELETE PEMUTIHAN SUPIR DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pemutihanSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPemutihanSupirDetail = new StoreLogTrailRequest($logTrailPemutihanSupirDetail);
            app(LogTrailController::class)->store($validatedLogTrailPemutihanSupirDetail);

            $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $pemutihanSupir->penerimaan_nobukti)->first();
            app(PenerimaanHeaderController::class)->destroy($request, $getPenerimaan->id);

            DB::commit();

            $selected = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable(), true);
            $pemutihanSupir->position = $selected->position;
            $pemutihanSupir->id = $selected->id;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pemutihanSupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            $post = $data->getPosting($supirId);

            return response([
                'post' => $post,
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }
    public function getNonPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            $non = $data->getNonposting($supirId);
            return response([
                'non' => $non,
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getEditPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'post' => $data->getEditPost($id, $supirId),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getEditNonPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'non' => $data->getEditNonPost($id, $supirId),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }


    public function getDeletePost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'post' => $data->getDeletePost($id, $supirId),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getDeleteNonPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'non' => $data->getDeleteNonPost($id, $supirId),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function cekvalidasi($id)
    {
        $pemutihanSupir = new PemutihanSupir();
        $pemutihan = PemutihanSupir::from(DB::raw("pemutihansupirheader"))->where('id', $id)->first();
        $now = date("Y-m-d");
        if ($pemutihan->tglbukti == $now) {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => true,
            ];

            return response($data);
        } else {

            $query = DB::table('error')
                ->select(
                    DB::raw("'PEMUTIHAN SUPIR '+ltrim(rtrim(keterangan)) as keterangan")
                )
                ->where('kodeerror', '=', 'ETS')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => false,
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pemutihansupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
