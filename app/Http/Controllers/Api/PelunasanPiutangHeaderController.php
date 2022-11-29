<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangHeader;
use App\Models\PelunasanPiutangDetail;
use App\Models\PiutangHeader;


use App\Http\Requests\StorePelunasanPiutangHeaderRequest;
use App\Http\Requests\UpdatePelunasanPiutangHeaderRequest;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\LogTrail;
use App\Models\Agen;
use App\Models\AkunPusat;
use App\Models\Cabang;
use App\Models\Bank;
use App\Models\Pelanggan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class PelunasanPiutangHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluarantruckingheader = new PelunasanPiutangHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePelunasanPiutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            if ($request->piutang_id != '') {

                $group = 'PELUNASAN PIUTANG BUKTI';
                $subgroup = 'PELUNASAN PIUTANG BUKTI';


                $format = DB::table('parameter')
                    ->where('grp', $group)
                    ->where('subgrp', $subgroup)
                    ->first();

                $content = new Request();
                $content['group'] = $group;
                $content['subgroup'] = $subgroup;
                $content['table'] = 'pelunasanpiutangheader';
                $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                $pelunasanpiutangheader = new PelunasanPiutangHeader();

                $pelunasanpiutangheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
                $pelunasanpiutangheader->keterangan = $request->keterangan;
                $pelunasanpiutangheader->bank_id = $request->bank_id;
                $pelunasanpiutangheader->agen_id = $request->agen_id;
                $pelunasanpiutangheader->cabang_id = $request->cabang_id;
                $pelunasanpiutangheader->statusformat = $format->id;
                $pelunasanpiutangheader->modifiedby = auth('api')->user()->name;

                TOP:
                $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
                $pelunasanpiutangheader->nobukti = $nobukti;


                try {
                    $pelunasanpiutangheader->save();
                    DB::commit();
                } catch (\Exception $e) {
                    $errorCode = @$e->errorInfo[1];
                    if ($errorCode == 2601) {
                        goto TOP;
                    }
                }

                $logTrail = [
                    'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                    'postingdari' => 'ENTRY PELUNASAN PIUTANG HEADER',
                    'idtrans' => $pelunasanpiutangheader->id,
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $pelunasanpiutangheader->toArray(),
                    'modifiedby' => $pelunasanpiutangheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                /* Store detail */

                $detaillog = [];


                for ($i = 0; $i < count($request->piutang_id); $i++) {
                    $idpiutang = $request->piutang_id[$i];
                    $piutang = PiutangHeader::where('id', $idpiutang)->first();


                    //get coa penyesuaian
                    if ($request->penyesuaianppd[$i] > 0) {
                        $getCoaPenyesuaian = AkunPusat::where('id', '143')->first();
                    }

                    //get coa nominal lebih bayar                
                    if ($request->nominallebihbayarppd[$i] > 0) {
                        $getNominalLebih = AkunPusat::where('id', '138')->first();
                    }

                    $datadetail = [
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'pelanggan_id' => $request->pelanggan_id,
                        'agen_id' => $request->agendetail_id,
                        'nominal' => $request->bayarppd[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'cicilan' => '',
                        'tglcair' => $piutang->tglbukti,
                        'keterangan' => $request->keterangandetailppd[$i] ?? '',
                        'tgljt' => $piutang->tglbukti,
                        'penyesuaian' => $request->penyesuaianppd[$i] ?? '',
                        'coapenyesuaian' => $getCoaPenyesuaian->coa ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti,
                        'keteranganpenyesuaian' => $request->keteranganpenyesuaianppd[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayarppd[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                    ];

                    //STORE 
                    $data = new StorePelunasanPiutangDetailRequest($datadetail);

                    $datadetails = app(PelunasanPiutangDetailController::class)->store($data);
                    // dd('tes');


                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }


                    $datadetaillog = [
                        'id' => $iddetail,
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'pelanggan_id' => $request->pelanggan_id,
                        'agen_id' => $request->agendetail_id,
                        'nominal' => $request->bayarppd[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'cicilan' => '',
                        'tglcair' => $piutang->tglbukti,
                        'keterangan' => $request->keterangandetailppd[$i] ?? '',
                        'tgljt' => $piutang->tglbukti,
                        'penyesuaian' => $request->penyesuaianppd[$i] ?? '',
                        'coapenyesuaian' => $getCoaPenyesuaian->coa ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti,
                        'keteranganpenyesuaian' => $keteranganpenyesuaianppd[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayarppd[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->updated_at)),

                    ];

                    $detaillog[] = $datadetaillog;


                    $dataid = LogTrail::select('id')
                        ->where('idtrans', '=', $pelunasanpiutangheader->id)
                        ->where('namatabel', '=', $pelunasanpiutangheader->getTable())
                        ->orderBy('id', 'DESC')
                        ->first();

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'ENTRY PELUNASAN PIUTANG DETAIL',
                        'idtrans' =>  $dataid->id,
                        'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }


                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
                DB::commit();

                /* Set position and page */


                $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable());
                $pelunasanpiutangheader->position = $selected->position;
                $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));


                return response([
                    'status' => true,
                    'message' => 'Berhasil disimpan',
                    'data' => $pelunasanpiutangheader
                ], 201);
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'piutang' => "PIUTANG $query->keterangan"
                    ],
                    'message' => "PIUTANG $query->keterangan",
                ], 422);
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


    public function show($id)
    {
        // $data = PelunasanPiutangHeader::with(
        //     'pelunasanpiutangdetail',
        // )->find($id);

        $data = PelunasanPiutangHeader::findAll($id);
        $detail = PelunasanPiutangDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePelunasanPiutangHeaderRequest $request, PelunasanPiutangHeader $pelunasanpiutangheader)
    {
        DB::beginTransaction();

        try {

            $pelunasanpiutangheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pelunasanpiutangheader->keterangan = $request->keterangan;
            $pelunasanpiutangheader->bank_id = $request->bank_id;
            $pelunasanpiutangheader->agen_id = $request->agen_id;
            $pelunasanpiutangheader->cabang_id = $request->cabang_id;
            $pelunasanpiutangheader->modifiedby = auth('api')->user()->name;

            if ($pelunasanpiutangheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                    'postingdari' => 'EDIT PELUNASAN PIUTANG HEADER',
                    'idtrans' => $pelunasanpiutangheader->id,
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pelunasanpiutangheader->toArray(),
                    'modifiedby' => $pelunasanpiutangheader->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                PelunasanPiutangDetail::where('pelunasanpiutang_id', $pelunasanpiutangheader->id)->lockForUpdate()->delete();

                /* Store detail */


                $detaillog = [];
                for ($i = 0; $i < count($request->piutang_id); $i++) {
                    $idpiutang = $request->piutang_id[$i];
                    $piutang = PiutangHeader::where('id', $idpiutang)->first();

                    //get coa penyesuaian
                    if ($request->penyesuaianppd[$i] > 0) {
                        $getCoaPenyesuaian = AkunPusat::where('id', '143')->first();
                    }

                    //get coa nominal lebih bayar
                    if ($request->nominallebihbayarppd[$i] > 0) {
                        $getNominalLebih = AkunPusat::where('id', '138')->first();
                    }

                    $datadetail = [
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'pelanggan_id' => $request->pelanggan_id,
                        'agen_id' => $request->agendetail_id,
                        'nominal' => $request->bayarppd[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'cicilan' => '',
                        'tglcair' => $piutang->tglbukti,
                        'keterangan' => $request->keterangandetailppd[$i] ?? '',
                        'tgljt' => $piutang->tglbukti,
                        'penyesuaian' => $request->penyesuaianppd[$i] ?? '',
                        'coapenyesuaian' => $getCoaPenyesuaian->coa ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti,
                        'keteranganpenyesuaian' => $request->keteranganpenyesuaianppd[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayarppd[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                    ];

                    //STORE

                    $data = new StorePelunasanPiutangDetailRequest($datadetail);
                    $datadetails = app(PelunasanPiutangDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'pelunasanpiutang_id' => $pelunasanpiutangheader->id,
                        'nobukti' => $pelunasanpiutangheader->nobukti,
                        'pelanggan_id' => $request->pelanggan_id,
                        'agen_id' => $request->agendetail_id,
                        'nominal' => $request->bayarppd[$i],
                        'piutang_nobukti' => $piutang->nobukti,
                        'cicilan' => '',
                        'tglcair' => $piutang->tglbukti,
                        'keterangan' => $request->keterangandetailppd[$i] ?? '',
                        'tgljt' => $piutang->tglbukti,
                        'penyesuaian' => $request->penyesuaianppd[$i] ?? '',
                        'coapenyesuaian' => $getCoaPenyesuaian->coa ?? '',
                        'invoice_nobukti' => $piutang->invoice_nobukti,
                        'keteranganpenyesuaian' => $request->keteranganpenyesuaianppd[$i] ?? '',
                        'nominallebihbayar' => $request->nominallebihbayarppd[$i] ?? '',
                        'coalebihbayar' => $getNominalLebih->coa ?? '',
                        'modifiedby' => $pelunasanpiutangheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($pelunasanpiutangheader->updated_at)),

                    ];

                    $detaillog[] = $datadetaillog;

                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PELUNASAN PIUTANG DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                        'aksi' => 'EDIT',
                        'datajson' => $detaillog,
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);

                    app(LogTrailController::class)->store($data);
                }
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';


            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable());
            $pelunasanpiutangheader->position = $selected->position;
            $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));



            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelunasanpiutangheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName
     */
    public function destroy(PelunasanPiutangHeader $pelunasanpiutangheader, Request $request)
    {
        DB::beginTransaction();
        try {

            $delete = PelunasanPiutangDetail::where('pelunasanpiutang_id', $pelunasanpiutangheader->id)->lockForUpdate()->delete();
            $delete = PelunasanPiutangHeader::destroy($pelunasanpiutangheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
                    'postingdari' => 'DELETE PELUNASAN PIUTANG HEADER',
                    'idtrans' => $pelunasanpiutangheader->id,
                    'nobuktitrans' => $pelunasanpiutangheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $pelunasanpiutangheader->toArray(),
                    'modifiedby' => $pelunasanpiutangheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable(), true);
                $pelunasanpiutangheader->position = $selected->position;
                $pelunasanpiutangheader->id = $selected->id;
                $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $pelunasanpiutangheader
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

    public function getpiutang($id)
    {
        $piutang = new PiutangHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $piutang->getPiutang($id),
            'id' => $id,
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }


    public function getPelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getPelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function getDeletePelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getDeletePelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelunasanpiutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
