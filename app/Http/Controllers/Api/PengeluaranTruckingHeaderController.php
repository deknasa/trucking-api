<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranTruckingHeader;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranTruckingDetailRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;

class PengeluaranTruckingHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $pengeluarantruckingheader = new PengeluaranTruckingHeader();
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
    public function store(StorePengeluaranTruckingHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $idpengeluaran = $request->pengeluarantrucking_id;
            $fetchFormat =  DB::table('pengeluarantrucking')
                ->where('id', $idpengeluaran)
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
            $content['table'] = 'pengeluarantruckingheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $pengeluarantruckingheader = new PengeluaranTruckingHeader();
            $statusPosting = Parameter::where('grp', 'STATUS POSTING')->where('text', 'BUKAN POSTING')->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $pengeluarantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluarantruckingheader->pengeluarantrucking_id = $request->pengeluarantrucking_id;
            $pengeluarantruckingheader->keterangan = $request->keterangan;
            $pengeluarantruckingheader->bank_id = $request->bank_id;
            $pengeluarantruckingheader->statusposting = $statusPosting->id ?? 0;
            $pengeluarantruckingheader->coa = $request->coa;
            $pengeluarantruckingheader->pengeluaran_nobukti = $request->pengeluaran_nobukti;
            $pengeluarantruckingheader->statusformat = $format->id;
            $pengeluarantruckingheader->statuscetak = $statusCetak->id;
            $pengeluarantruckingheader->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengeluarantruckingheader->nobukti = $nobukti;

            $pengeluarantruckingheader->save();

            $logTrail = [
                'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                'postingdari' => 'ENTRY PENGELUARAN TRUCKING HEADER',
                'idtrans' => $pengeluarantruckingheader->id,
                'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pengeluarantruckingheader->toArray(),
                'modifiedby' => $pengeluarantruckingheader->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */

            $detaillog = [];

            for ($i = 0; $i < count($request->nominal); $i++) {

                $datadetail = [
                    'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                    'nobukti' => $pengeluarantruckingheader->nobukti,
                    'supir_id' => $request->supir_id[$i],
                    'penerimaantruckingheader_nobukti' => $request->penerimaantruckingheader_nobukti[$i] ?? '',
                    'nominal' => $request->nominal[$i],
                    'modifiedby' => $pengeluarantruckingheader->modifiedby,
                ];

                //STORE 
                $data = new StorePengeluaranTruckingDetailRequest($datadetail);

                $datadetails = app(PengeluaranTruckingDetailController::class)->store($data);
                // dd('tes');


                if ($datadetails['error']) {
                    return response($datadetails, 422);
                } else {
                    $iddetail = $datadetails['id'];
                    $tabeldetail = $datadetails['tabel'];
                }

                $detaillog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'ENTRY PENGELUARAN TRUCKING DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $request->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */


            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable());
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));


            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluarantruckingheader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }
    }


    public function show($id)
    {

        $data = PengeluaranTruckingHeader::findAll($id);
        $detail = PengeluaranTruckingDetail::getAll($id);

        // dd($details);
        // $datas = array_merge($data, $detail);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdatePengeluaranTruckingHeaderRequest $request, PengeluaranTruckingHeader $pengeluarantruckingheader)
    {
        DB::beginTransaction();

        try {

            $idpengeluaran = $request->pengeluarantrucking_id;
            $fetchFormat =  DB::table('pengeluarantrucking')
                ->where('id', $idpengeluaran)
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
            $content['table'] = 'pengeluarantruckingheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));


            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengeluarantruckingheader->nobukti = $nobukti;
            $pengeluarantruckingheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluarantruckingheader->pengeluarantrucking_id = $idpengeluaran;
            $pengeluarantruckingheader->keterangan = $request->keterangan;
            $pengeluarantruckingheader->bank_id = $request->bank_id;
            $pengeluarantruckingheader->coa = $request->coa;
            $pengeluarantruckingheader->pengeluaran_nobukti = $request->pengeluaran_nobukti;
            $pengeluarantruckingheader->statusformat =  $format->id;
            $pengeluarantruckingheader->modifiedby = auth('api')->user()->name;


            if ($pengeluarantruckingheader->save()) {

                $logTrail = [
                    'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN TRUCKING HEADER',
                    'idtrans' => $pengeluarantruckingheader->id,
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluarantruckingheader->toArray(),
                    'modifiedby' => $pengeluarantruckingheader->modifiedby
                ];


                $validatedLogTrail = new StoreLogTrailRequest($logTrail);

                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluarantruckingheader->id)->lockForUpdate()->delete();

                /* Store detail */

                $detaillog = [];

                for ($i = 0; $i < count($request->nominal); $i++) {
                    $datadetail = [
                        'pengeluarantruckingheader_id' => $pengeluarantruckingheader->id,
                        'nobukti' => $pengeluarantruckingheader->nobukti,
                        'supir_id' => $request->supir_id[$i],
                        'penerimaantruckingheader_nobukti' => $request->penerimaantruckingheader_nobukti[$i] ?? '',
                        'nominal' => $request->nominal[$i],
                        'modifiedby' => $pengeluarantruckingheader->modifiedby,
                    ];

                    //STORE 
                    $data = new StorePengeluaranTruckingDetailRequest($datadetail);
                    $datadetails = app(PengeluaranTruckingDetailController::class)->store($data);
                 
                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT PENGELUARAN TRUCKING DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'EDIT',
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
            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable());
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pengeluarantruckingheader
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
    public function destroy(PengeluaranTruckingHeader $pengeluarantruckingheader, Request $request)
    {
        DB::beginTransaction();
        try {

            $getDetail = PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluarantruckingheader->id)->get();
            $delete = PengeluaranTruckingDetail::where('pengeluarantruckingheader_id', $pengeluarantruckingheader->id)->lockForUpdate()->delete();
            $delete = PengeluaranTruckingHeader::destroy($pengeluarantruckingheader->id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluarantruckingheader->getTable()),
                    'postingdari' => 'DELETE PENGELUARAN TRUCKING HEADER',
                    'idtrans' => $pengeluarantruckingheader->id,
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $pengeluarantruckingheader->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                // DELETE PENGELUARAN TRUCKING DETAIL
                $logTrailPengeluaranTruckingDetail = [
                    'namatabel' => 'PENGELUARANTRUCKINGDETAIL',
                    'postingdari' => 'DELETE PENGELUARAN TRUCKING DETAIL',
                    'idtrans' => $storedLogTrail['id'],
                    'nobuktitrans' => $pengeluarantruckingheader->nobukti,
                    'aksi' => 'DELETE',
                    'datajson' => $getDetail->toArray(),
                    'modifiedby' => auth('api')->user()->name
                ];

                $validatedLogTrailPengeluaranTruckingDetail = new StoreLogTrailRequest($logTrailPengeluaranTruckingDetail);
                app(LogTrailController::class)->store($validatedLogTrailPengeluaranTruckingDetail);
                
            } 
            DB::commit();

            $selected = $this->getPosition($pengeluarantruckingheader, $pengeluarantruckingheader->getTable(), true);
            $pengeluarantruckingheader->position = $selected->position;
            $pengeluarantruckingheader->id = $selected->id;
            $pengeluarantruckingheader->page = ceil($pengeluarantruckingheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluarantruckingheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaran = PengeluaranTruckingHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaran->statuscetak != $statusSudahCetak->id) {
                $pengeluaran->statuscetak = $statusSudahCetak->id;
                $pengeluaran->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaran->userbukacetak = auth('api')->user()->name;
                $pengeluaran->jumlahcetak = $pengeluaran->jumlahcetak+1;

                if ($pengeluaran->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaran->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN TRUCKING HEADER',
                        'idtrans' => $pengeluaran->id,
                        'nobuktitrans' => $pengeluaran->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaran->toArray(),
                        'modifiedby' => auth('api')->user()->name,
                    ];
    
                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
    
                    DB::commit();
                }
            }


            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranTruckingHeader::find($id);
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluarantruckingheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
