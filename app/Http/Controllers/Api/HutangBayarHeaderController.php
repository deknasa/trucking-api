<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreHutangBayarHeaderRequest;
use App\Http\Requests\StoreHutangBayarDetailRequest;

use App\Http\Requests\StoreLogTrailRequest;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\AkunPusat;
use App\Models\Supplier;
use App\Models\HutangBayarHeader;
use App\Models\HutangBayarDetail;
use App\Models\Parameter;
use App\Models\HutangHeader;
use App\Models\LogTrail;
use App\Models\PengeluaranHeader;

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

            $hutangbayarheader = new HutangBayarHeader();

            $nobuktiHutang = $request->hutang_nobukti;
            $HutangHeader =  HutangHeader::where('nobukti', $nobuktiHutang)->first();

            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->keterangan = $request->keterangan;
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id;
            $hutangbayarheader->coa = $request->akunpusat;
            $hutangbayarheader->statusformat =  $format->id;
            $hutangbayarheader->modifiedby = auth('api')->user()->name;
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $hutangbayarheader->nobukti = $nobukti;

            try {
                $hutangbayarheader->save();

                DB::commit();
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

            //   for ($i = 0; $i < count($request->nominal); $i++) {

            $datadetail = [
                'hutangbayar_id' => $hutangbayarheader->id,
                'nobukti' => $hutangbayarheader->nobukti,
                'nominal' => str_replace(',', '', $request->nominal),
                'hutang_nobukti' => $request->hutang_nobukti,
                'cicilan' => str_replace(',', '', $request->cicilan),
                'alatbayar_id' => $request->alatbayar_id,
                'potongan' => str_replace(',', '', $request->potongan),
                'keterangan' => $request->keterangan_detail,
                'modifiedby' => $hutangbayarheader->modifiedby,
            ];
            //STORE 
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
                'nominal' => str_replace(',', '', $request->nominal),
                'hutang_nobukti' => $request->hutang_nobukti,
                'cicilan' => str_replace(',', '', $request->cicilan),
                'alatbayar_id' => $request->alatbayar_id,
                'potongan' => str_replace(',', '', $request->potongan),
                'keterangan' => $request->keterangan_detail,
                'modifiedby' => $hutangbayarheader->modifiedby,
                'created_at' => date('d-m-Y H:i:s', strtotime($hutangbayarheader->created_at)),
                'updated_at' => date('d-m-Y H:i:s', strtotime($hutangbayarheader->updated_at)),

            ];

            $detaillog[] = $datadetaillog;


            $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $hutangbayarheader->id)
                ->where('namatabel', '=', $hutangbayarheader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY HUTANG BAYAR DETAIL',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $hutangbayarheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];
            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);
            //   }


            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
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


    public function show($id)
    {

        $data = HutangBayarHeader::find($id);
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
    public function update(StoreHutangBayarHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $hutangbayarheader = HutangBayarHeader::findOrFail($id);

            $hutangbayarheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangbayarheader->keterangan = $request->keterangan;
            $hutangbayarheader->bank_id = $request->bank_id;
            $hutangbayarheader->supplier_id = $request->supplier_id;
            $hutangbayarheader->coa = $request->akunpusat;
            $hutangbayarheader->modifiedby = auth('api')->user()->name;


            if ($hutangbayarheader->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($hutangbayarheader->getTable()),
                    'postingdari' => 'EDIT HUTANG BAYAR HEADER',
                    'idtrans' => $hutangbayarheader->id,
                    'nobuktitrans' => $hutangbayarheader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $hutangbayarheader->toArray(),
                    'modifiedby' => $hutangbayarheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                HutangBayarDetail::where('hutangbayar_id', $id)->delete();

                /* Store detail */
                $detaillog = [];

                //    for($i = 0; $i < count($request->nominal); $i++){
                $datadetail = [
                    'hutangbayar_id' => $hutangbayarheader->id,
                    'nobukti' => $hutangbayarheader->nobukti,
                    'nominal' => str_replace(',', '', $request->nominal),
                    'hutang_nobukti' => $request->hutang_nobukti,
                    'cicilan' => str_replace(',', '', $request->cicilan),
                    'alatbayar_id' => $request->alatbayar_id,
                    'potongan' => str_replace(',', '', $request->potongan),
                    'keterangan' => $request->keterangan_detail,
                    'modifiedby' => $hutangbayarheader->modifiedby,
                ];


                //STORE 
                $data = new StoreHutangBayarDetailRequest($datadetail);
                $datadetails = app(HutangBayarDetailController::class)->store($data);
                // dd('here');

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
                    'nominal' => str_replace(',', '', $request->nominal),
                    'hutang_nobukti' => $request->hutang_nobukti,
                    'cicilan' => str_replace(',', '', $request->cicilan),
                    'alatbayar_id' => $request->alatbayar_id,
                    'potongan' => str_replace(',', '', $request->potongan),
                    'keterangan' => $request->keterangan_detail,
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
                    'modifiedby' => $request->modifiedby,
                ];
                $data = new StoreLogTrailRequest($datalogtrail);

                app(LogTrailController::class)->store($data);
                //}
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($hutangbayarheader, $hutangbayarheader->getTable());
            $hutangbayarheader->position = $selected->position;
            $hutangbayarheader->page = ceil($hutangbayarheader->position / ($request->limit ?? 10));



            // if (isset($request->limit)) {
            //     $penerimaantruckingheader->page = ceil($penerimaantruckingheader->position / $request->limit);
            // }

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
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();
        $hutangbayarheader = new HutangBayarHeader();
        try {

            $delete = HutangBayarDetail::where('hutangbayar_id', $id)->delete();
            $delete = HutangBayarHeader::destroy($id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangbayarheader->getTable()),
                    'postingdari' => 'DELETE PEMBAYARAN HUTANG HEADER',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
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
}
