<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTruckingHeader;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\PenerimaanHeader;
use App\Models\BankPelanggan;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PenerimaanTruckingDetail;
use App\Models\StorePenerimaanTruckingDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Models\PenerimaanTrucking;
use App\Models\Supir;
use PhpParser\Builder\Param;

class PenerimaanTruckingHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $penerimaantrucking = new PenerimaanTruckingHeader();

        return response([
            'data' => $penerimaantrucking->get(),
            'attributes' => [
                'totalRows' => $penerimaantrucking->totalRows,
                'totalPages' => $penerimaantrucking->totalPages
            ]
        ]);
    }

    public function show($id)
    {
        $data = PenerimaanTruckingHeader::with(
            'penerimaantruckingdetail',
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
            'penerimaantrucking'    => PenerimaanTrucking::all(),
            'bank'          => Bank::all(),
            'coa'           => AkunPusat::all(),
            'supir'         => Supir::all(),

            'cabang'        => Cabang::all(),
            'pelanggan'     => Pelanggan::all(),
            'bankpelanggan' => BankPelanggan::all(),
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
    public function update(StorePenerimaanTruckingHeaderRequest $request, PenerimaanTruckingHeader $penerimaanHeader, $id)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $penerimaantruckingHeader = PenerimaanTruckingHeader::findOrFail($id);
            $penerimaantruckingHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaantruckingHeader->keterangan = $request->keterangan ?? '';
            $penerimaantruckingHeader->postingdari = $request->postingdari ?? 'PENERIMAAN TRUCKING';
            $penerimaantruckingHeader->diterimadari = $request->diterimadari ?? '';
            $penerimaantruckingHeader->bank_id = $request->bank_id ?? '';
            $penerimaantruckingHeader->coa = $request->coa ?? '';
            $penerimaantruckingHeader->modifiedby = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaantruckingHeader->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN TRUCKING',
                    'idtrans' => $penerimaantruckingHeader->id,
                    'nobuktitrans' => $penerimaantruckingHeader->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $penerimaantruckingHeader->toArray(),
                    'modifiedby' => $penerimaantruckingHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            }

            /* Delete existing detail */
            $penerimaanHeader->penerimaanDetail()->delete();
            JurnalUmumDetail::where('nobukti', $penerimaanHeader->nobukti)->delete();
            JurnalUmumHeader::where('nobukti', $penerimaanHeader->nobukti)->delete();

            /* Store detail */
            $detaillog = [];

            $total = 0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'penerimaan_id' => $penerimaantruckingHeader->id,
                    'nobukti' => $penerimaantruckingHeader->nobukti,
                    'supir_id' => $penerimaantruckingHeader->supir,
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StorePenerimaanDetailRequest($datadetail);
                $datadetails = app(PenerimaanDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails
                    ['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'penerimaan_id' => $penerimaantruckingHeader->id,
                    'nobukti' => $penerimaantruckingHeader->nobukti,
                    'supir_id' => $penerimaantruckingHeader->supir_id[$i],
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingHeader->updated_at)),
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
                    'postingdari' => 'EDIT PENERIMAAN TRUCKING',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $penerimaantruckingHeader->nobukti,
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
        $penerimaantruckingHeader->position = DB::table((new PenerimaanTruckingHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
        ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $penerimaantruckingHeader->{$request->sortname})
        ->where('id', '<=', $penerimaantruckingHeader->id)
        ->count();

    if (isset($request->limit)) {
        $penerimaantruckingHeader->page = ceil($penerimaantruckingHeader->position / $request->limit);
    }

    return response([
        'status' => true,
        'message' => 'Berhasil disimpan',
        'data' => $penerimaantruckingHeader
    ]);
} catch (\Throwable $th) {
    DB::rollBack();
    throw $th;
}

    return response($penerimaantruckingHeader->penerimaantruckingdetail());
}

    /**
     * @ClassName
     */
    public function destroy($id, JurnalUmumHeader $jurnalumumheader, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = PenerimaanTruckingHeader::find($id);
            // $get = JurnalUmumDetail::find($id);
            // $get = JurnalUmumHeader::find($id);

            $delete = PenerimaanTruckingDetail::where('penerimaantrucking_id', $id)->delete();
            // $delete = JurnalUmumHeader::where('nobukti', $get->nobukti)->delete();
            // $delete = JurnalUmumDetail::where('nobukti', $get->nobukti)->delete();

            $delete = PenerimaanTruckingHeader::destroy($id);
            // $delete = JurnalUmumHeader::destroy($id);
            // $delete = JurnalUmumDetail::destroy($id);


            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE PENERIMAAN TRUCKING',
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



    // public function approval($id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $penerimaanHeader = PenerimaanTruckingHeader::find($id);
    //         $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
    //         $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

    //         if ($penerimaanHeader->statusapproval == $statusApproval->id) {
    //             $penerimaanHeader->statusapproval = $statusNonApproval->id;
    //         } else {
    //             $penerimaanHeader->statusapproval = $statusApproval->id;
    //         }

    //         $penerimaanHeader->tglapproval = date('Y-m-d', time());
    //         $penerimaanHeader->userapproval = auth('api')->user()->name;

    //         if ($penerimaanHeader->save()) {
    //             $logTrail = [
    //                 'namatabel' => strtoupper($penerimaanHeader->getTable()),
    //                 'postingdari' => 'UN/APPROVE PENERIMAANHEADER',
    //                 'idtrans' => $penerimaanHeader->id,
    //                 'nobuktitrans' => $penerimaanHeader->id,
    //                 'aksi' => 'UN/APPROVE',
    //                 'datajson' => $penerimaanHeader->toArray(),
    //                 'modifiedby' => $penerimaanHeader->modifiedby
    //             ];

    //             $validatedLogTrail = new StoreLogTrailRequest($logTrail);
    //             $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

    //             DB::commit();
    //         }

    //         return response([
    //             'message' => 'Berhasil'
    //         ]);
    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }

    /**
     * @ClassName
     */
    public function store(StorePenerimaanTruckingHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            /* Store header */

            $content = new Request();
            $bankid = $request->bank_id;
            $querysubgrppenerimaantrucking = DB::table('bank')
                ->select(
                    'parameter.grp',
                    'parameter.subgrp',
                )
                ->join('parameter', 'bank.kodepenerimaan', 'parameter.id')
                ->where('bank.id', '=', $bankid)
                ->first();

            $content['group'] = $querysubgrppenerimaantrucking->grp;
            $content['subgroup'] = $querysubgrppenerimaantrucking->subgrp;
            $content['table'] = 'penerimaantruckingheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
             $content['format'] = '';

            $penerimaantruckingHeader = new PenerimaanTruckingHeader();
            $penerimaantruckingHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaantruckingHeader->keterangan = $request->keterangan ?? '';
            $penerimaantruckingHeader->postingdari = $request->postingdari ?? 'PENERIMAAN TRUCKING';
            $penerimaantruckingHeader->diterimadari = $request->diterimadari ?? '';
            $penerimaantruckingHeader->bank_id = $request->bank_id ?? '';
            $penerimaantruckingHeader->coa = $request->coa ?? '';
            $penerimaantruckingHeader->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $penerimaantruckingHeader->nobukti = $nobukti;

            try {
                $penerimaantruckingHeader->save();
            } catch (\Exception $e) {
                dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($penerimaantruckingHeader->getTable()),
                'postingdari' => 'ENTRY PENERIMAAN TRUCKING',
                'idtrans' => $penerimaantruckingHeader->id,
                'nobuktitrans' => $penerimaantruckingHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $penerimaantruckingHeader->toArray(),
                'modifiedby' => $penerimaantruckingHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];

            $total = 0;
            for ($i = 0; $i < count($request->nominal); $i++) {
                $nominal = str_replace(',', '', str_replace('.', '', $request->nominal[$i]));
                $datadetail = [
                    'penerimaan_id' => $penerimaantruckingHeader->id,
                    'nobukti' => $penerimaantruckingHeader->nobukti,
                    'supir_id' => $penerimaantruckingHeader->supir,
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                ];
                $data = new StorePenerimaanTruckingDetailRequest($datadetail);
                $datadetails = app(PenerimaanTruckingDetailController::class)->store($data);

                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $datadetaillog = [
                    'penerimaan_id' => $penerimaantruckingHeader->id,
                    'nobukti' => $penerimaantruckingHeader->nobukti,
                    'supir_id' => $penerimaantruckingHeader->supir_id[$i],
                    'nominal' => $nominal,
                    'modifiedby' => auth('api')->user()->name,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingHeader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingHeader->updated_at)),
                ];
                $detaillog[] = $datadetaillog;

                $total += $nominal;
            }

            $dataid = LogTrail::select('id')
                ->where('nobuktitrans', '=', $penerimaantruckingHeader->nobukti)
                ->where('namatabel', '=', $penerimaantruckingHeader->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY PENERIMAAN TRUCKING',
                'idtrans' =>  $dataid->id,
                'nobuktitrans' => $penerimaantruckingHeader->nobukti,
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
            $penerimaantruckingHeader->position = DB::table((new PenerimaanTruckingHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
            ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $penerimaantruckingHeader->{$request->sortname})
            ->where('id', '<=', $penerimaantruckingHeader->id)
            ->count();

        if (isset($request->limit)) {
            $penerimaantruckingHeader->page = ceil($penerimaantruckingHeader->position / $request->limit);
        }

        return response([
            'status' => true,
            'message' => 'Berhasil disimpan',
            'data' => $penerimaantruckingHeader
        ]);
    } catch (\Throwable $th) {
        DB::rollBack();
        throw $th;
    }
    
        return response($penerimaantruckingHeader->penerimaantruckingdetail());
    }

}
