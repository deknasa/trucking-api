<?php

namespace App\Http\Controllers;

use App\Models\HutangHeader;
use App\Models\HutangDetail;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use PhpParser\Builder\Param;

class HutangDetailController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $hutang = new HutangHeader();

        return response([
            'data' => $hutang->get(),
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    public function show($id)
    {
        $data = HutangHeader::with(
            'hutangdetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'bank'          => Bank::all(),
            'coa'           => AkunPusat::all(),
            'parameter'     => Parameter::all(),
       
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
    public function update(StoreHutangHeaderRequest $request, HutangHeader $hutangHeader, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $hutangHeader = HutangHeader::findOrFail($id);
            $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->postingdari = $request->postingdari ?? 'ENTRU HUTANG';
            $hutangHeader->diterimadari = $request->diterimadari ?? '';
            $hutangHeader->bank_id = $request->bank_id ?? '';
            $hutangHeader->coa = $request->coa ?? '';
            $hutangHeader->modifiedby = auth('api')->user()->name;

            if ($hutangHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($hutangHeader->getTable()),
                    'postingdari' => 'EDIT HUTANG',
                    'idtrans' => $hutangHeader->id,
                    'nobuktitrans' => $hutangHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $hutangHeader->toArray(),
                    'modifiedby' => $hutangHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            /* Delete existing detail */
            $hutangHeader->hutangDetail()->delete();
            JurnalUmumDetail::where('nobukti', $hutangHeader->nobukti)->delete();
            JurnalUmumHeader::where('nobukti', $hutangHeader->nobukti)->delete();

            /* Store detail */
            $detaillog = [];

            $total = 0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supir_id' => $hutangHeader->supir,
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreHutangDetailRequest($datadetail);
                $datadetails = app(HutangDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails
                    ['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supir_id' => $hutangHeader->supir_id[$i],
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $hutangHeader->nobukti)
                ->where('namatabel', '=', $hutangHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT HUTANG',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $hutangHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();

                /* Set position and page */
        $hutangHeader->position = DB::table((new HutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
        ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $hutangHeader->{$request->sortname})
        ->where('id', '<=', $hutangHeader->id)
        ->count();

    if (isset($request->limit)) {
        $hutangHeader->page = ceil($hutangHeader->position / $request->limit);
    }

    return response([
        'status' => true,
        'message' => 'Berhasil disimpan',
        'data' => $hutangHeader
    ]);
} catch (\Throwable $th) {
    DB::rollBack();
    throw $th;
}

    return response($hutangHeader->hutangdetail());
}

    /**
     * @ClassName
     */
    public function destroy($id, JurnalUmumHeader $jurnalumumheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = HutangHeader::find($id);
            // $get = JurnalUmumDetail::find($id);
            // $get = JurnalUmumHeader::find($id);

            $delete = HutangDetail::where('hutang_id', $id)->delete();
            // $delete = JurnalUmumHeader::where('nobukti', $get->nobukti)->delete();
            // $delete = JurnalUmumDetail::where('nobukti', $get->nobukti)->delete();

            $delete = HutangHeader::destroy($id);
            // $delete = JurnalUmumHeader::destroy($id);
            // $delete = JurnalUmumDetail::destroy($id);


            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE HUTANG',
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
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function store(StoreHutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $content = new Request();
            $bankid = $request->bank_id;
            $querysubgrphutang = DB::table('bank')
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                )
                // ->join('parameter', 'bank.kodepenerimaan', 'parameter.id')
                ->where('bank.id', '=', $bankid)
                ->first();

            $content['group'] = $querysubgrphutang->grp;
            $content['subgroup'] = $querysubgrphutang->subgrp;
            $content['table'] = 'hutangheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
             $content['format'] = '';

            $hutangHeader = new HutangHeader();
            $hutangHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $hutangHeader->keterangan = $request->keterangan ?? '';
            $hutangHeader->postingdari = $request->postingdari ?? 'HUTANG';
            $hutangHeader->diterimadari = $request->diterimadari ?? '';
            $hutangHeader->bank_id = $request->bank_id ?? '';
            $hutangHeader->coa = $request->coa ?? '';
            $hutangHeader->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $hutangHeader->nobukti = $nobukti;

            try {
                $hutangHeader->save();
            } catch (\Exception $e) {
                dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($hutangHeader->getTable()),
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' => $hutangHeader->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $hutangHeader->toArray(),
                'modifiedby' => $hutangHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];

            $total = 0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supir_id' => $hutangHeader->supir,
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                ];
                $data = new StoreHutangDetailRequest($datadetail);
                $datadetails = app(HutangDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'hutang_id' => $hutangHeader->id,
                    'nobukti' => $hutangHeader->nobukti,
                    'supir_id' => $hutangHeader->supir_id[$i],
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($hutangHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $hutangHeader->nobukti)
                ->where('namatabel', '=', $hutangHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $hutangHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];
            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

                DB::commit();

                /* Set position and page */
            $hutangHeader->position = DB::table((new HutangHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
            ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $hutangHeader->{$request->sortname})
            ->where('id', '<=', $hutangHeader->id)
            ->count();

        if (isset($request->limit)) {
            $hutangHeader->page = ceil($hutangHeader->position / $request->limit);
        }

        return response([
            'status' => true,
            'message' => 'Berhasil disimpan',
            'data' => $hutangHeader
        ]);
    } catch (\Throwable $th) {
        DB::rollBack();
        throw $th;
    }
    
        return response($hutangHeader->hutangdetail());
    }

}

