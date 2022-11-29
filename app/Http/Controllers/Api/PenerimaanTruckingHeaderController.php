<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PenerimaanTruckingTruckingHeader;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;

class PenerimaanTruckingHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $penerimaantruckingheader = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaantruckingheader->get(),
            'attributes' => [
                'totalRows' => $penerimaantruckingheader->totalRows,
                'totalPages' => $penerimaantruckingheader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePenerimaanTruckingHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $idpenerimaan = $request->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();
            $statusformat = $fetchFormat->statusformat;

            $fetchGrp = Parameter::where('id', $statusformat)->first();

            $format = DB::table('parameter')
                ->where('grp', $fetchGrp->grp)
                ->where('subgrp', $fetchGrp->subgrp)
                ->first();

            $content = new Request();
            $content['group'] = $fetchGrp->grp;
            $content['subgroup'] = $fetchGrp->subgrp;
            $content['table'] = 'penerimaantruckingheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $penerimaantruckingheader = new PenerimaanTruckingHeader();
            $statusPosting = Parameter::where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();

            $penerimaantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaantruckingheader->penerimaantrucking_id = $idpenerimaan;
            $penerimaantruckingheader->keterangan = $request->keterangan;
            $penerimaantruckingheader->bank_id = $request->bank_id;
            $penerimaantruckingheader->coa = $request->coa;
            $penerimaantruckingheader->penerimaan_nobukti = $request->penerimaan_nobukti;
            $penerimaantruckingheader->statusformat =  $format->id;
            $penerimaantruckingheader->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $penerimaantruckingheader->nobukti = $nobukti;

            $penerimaantruckingheader->save();


            $logTrail = [
                'namatabel' => strtoupper($penerimaantruckingheader->getTable()),
                'postingdari' => 'ENTRY PENERIMAAN TRUCKING HEADER',
                'idtrans' => $penerimaantruckingheader->id,
                'nobuktitrans' => $penerimaantruckingheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $penerimaantruckingheader->toArray(),
                'modifiedby' => $penerimaantruckingheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */

            $detaillog = [];

            for ($i = 0; $i < count($request->nominal); $i++) {

                $datadetail = [
                    'penerimaantruckingheader_id' => $penerimaantruckingheader->id,
                    'nobukti' => $penerimaantruckingheader->nobukti,
                    'supir_id' => $request->supir_id[$i],
                    'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti[$i] ?? '',
                    'nominal' => $request->nominal[$i],
                    'modifiedby' => $penerimaantruckingheader->modifiedby,
                ];
                //STORE 
                $data = new StorePenerimaanTruckingDetailRequest($datadetail);

                $datadetails = app(PenerimaanTruckingDetailController::class)->store($data);
                // dd('tes');


                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }


                $datadetaillog = [
                    'id' => $iddetail,
                    'penerimaantruckingheader_id' => $penerimaantruckingheader->id,
                    'nobukti' => $penerimaantruckingheader->nobukti,
                    'supir_id' => $request->supir_id[$i],
                    'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti[$i] ?? '',
                    'nominal' => $request->nominal[$i],
                    'modifiedby' => $penerimaantruckingheader->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingheader->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingheader->updated_at)),

                ];

                $detaillog[] = $datadetaillog;


                $dataid = LogTrail::select('id')
                    ->where('idtrans', '=', $penerimaantruckingheader->id)
                    ->where('namatabel', '=', $penerimaantruckingheader->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY PENERIMAAN TRUCKING DETAIL',
                    'idtrans' =>  $dataid->id,
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
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


            $selected = $this->getPosition($penerimaantruckingheader, $penerimaantruckingheader->getTable());
            $penerimaantruckingheader->position = $selected->position;
            $penerimaantruckingheader->page = ceil($penerimaantruckingheader->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaantruckingheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    public function show($id)
    {

        $data = PenerimaanTruckingHeader::findAll($id);
        $detail = PenerimaanTruckingDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePenerimaanTruckingHeaderRequest $request, PenerimaanTruckingHeader $penerimaantruckingheader)
    {
        DB::beginTransaction();

        try {

            $idpenerimaan = $request->penerimaantrucking_id;
            $fetchFormat =  DB::table('penerimaantrucking')
                ->where('id', $idpenerimaan)
                ->first();

            $statusformat = $fetchFormat->statusformat;

            $fetchGrp = Parameter::where('id', $statusformat)->first();

            $format = DB::table('parameter')
                ->where('grp', $fetchGrp->grp)
                ->where('subgrp', $fetchGrp->subgrp)
                ->first();

            $penerimaantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $penerimaantruckingheader->penerimaantrucking_id = $idpenerimaan;
            $penerimaantruckingheader->keterangan = $request->keterangan;
            $penerimaantruckingheader->bank_id = $request->bank_id;
            $penerimaantruckingheader->coa = $request->coa;
            $penerimaantruckingheader->penerimaan_nobukti = $request->penerimaan_nobukti;
            $penerimaantruckingheader->statusformat =  $format->id;
            $penerimaantruckingheader->modifiedby = auth('api')->user()->name;


            if ($penerimaantruckingheader->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($penerimaantruckingheader->getTable()),
                    'postingdari' => 'EDIT PENERIMAAN TRUCKING HEADER',
                    'idtrans' => $penerimaantruckingheader->id,
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $penerimaantruckingheader->toArray(),
                    'modifiedby' => $penerimaantruckingheader->modifiedby
                ];



                $validatedLogTrail = new StoreLogTrailRequest($logTrail);

                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


                PenerimaanTruckingDetail::where('penerimaantruckingheader_id', $penerimaantruckingheader->id)->lockForUpdate()->delete();

                /* Store detail */

                $detaillog = [];

                for ($i = 0; $i < count($request->nominal); $i++) {


                    $datadetail = [
                        'penerimaantruckingheader_id' => $penerimaantruckingheader->id,
                        'nobukti' => $penerimaantruckingheader->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti[$i] ?? '',
                        'nominal' => $request->nominal[$i],
                        'modifiedby' => $penerimaantruckingheader->modifiedby,
                    ];


                    //STORE 
                    $data = new StorePenerimaanTruckingDetailRequest($datadetail);

                    $datadetails = app(PenerimaanTruckingDetailController::class)->store($data);
                    // dd('here');

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }


                    $datadetaillog = [
                        'id' => $iddetail,
                        'penerimaantruckingheader_id' => $penerimaantruckingheader->id,
                        'nobukti' => $penerimaantruckingheader->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti[$i] ?? '',
                        'nominal' => $request->nominal[$i],
                        'modifiedby' => $penerimaantruckingheader->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingheader->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($penerimaantruckingheader->updated_at)),

                    ];

                    $detaillog[] = $datadetaillog;



                    $datalogtrail = [
                        'namatabel' => $tabeldetail,
                        'postingdari' => 'EDIT PENERIMAAN TRUCKING DETAIL',
                        'idtrans' =>  $iddetail,
                        'nobuktitrans' => $penerimaantruckingheader->nobukti,
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
            $selected = $this->getPosition($penerimaantruckingheader, $penerimaantruckingheader->getTable());
            $penerimaantruckingheader->position = $selected->position;
            $penerimaantruckingheader->page = ceil($penerimaantruckingheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $penerimaantruckingheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName
     */
    public function destroy(PenerimaanTruckingHeader $penerimaantruckingheader, Request $request)
    {
        DB::beginTransaction();
        try {

            $delete = PenerimaanTruckingDetail::where('penerimaantruckingheader_id', $penerimaantruckingheader->id)->lockForUpdate()->delete();
            $delete = PenerimaanTruckingHeader::destroy($penerimaantruckingheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaantruckingheader->getTable()),
                    'postingdari' => 'DELETE PENERIMAAN TRUCKING HEADER',
                    'idtrans' => $penerimaantruckingheader->id,
                    'nobuktitrans' => $penerimaantruckingheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $penerimaantruckingheader->toArray(),
                    'modifiedby' => $penerimaantruckingheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                $selected = $this->getPosition($penerimaantruckingheader, $penerimaantruckingheader->getTable(), true);
                $penerimaantruckingheader->position = $selected->position;
                $penerimaantruckingheader->id = $selected->id;
                $penerimaantruckingheader->page = ceil($penerimaantruckingheader->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $penerimaantruckingheader
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaantruckingheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
