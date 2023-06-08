<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOutHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\Parameter;
use App\Models\Error;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\StoreServiceOutDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateServiceOutHeaderRequest;
use App\Models\LogTrail;
use App\Models\ServiceInHeader;
use App\Models\ServiceOutDetail;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;

class ServiceOutHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index(GetIndexRangeRequest $request)
    {
        $serviceout = new ServiceOutHeader();

        return response([
            'data' => $serviceout->get(),
            'attributes' => [
                'totalRows' => $serviceout->totalRows,
                'totalPages' => $serviceout->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StoreServiceOutHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'SERVICE OUT BUKTI';
            $subgroup = 'SERVICE OUT BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'serviceoutheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $serviceout = new ServiceOutHeader();
            $serviceout->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $serviceout->trado_id = $request->trado_id;
            $serviceout->tglkeluar = date('Y-m-d', strtotime($request->tglkeluar));
            $serviceout->statusformat =  $format->id;
            $serviceout->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $serviceout->nobukti = $nobukti;

            $serviceout->save();

            $logTrail = [
                'namatabel' => strtoupper($serviceout->getTable()),
                'postingdari' => 'ENTRY SERVICE OUT HEADER',
                'idtrans' => $serviceout->id,
                'nobuktitrans' => $serviceout->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $serviceout->toArray(),
                'modifiedby' => $serviceout->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->keterangan_detail); $i++) {
                $datadetail = [
                    'serviceout_id' => $serviceout->id,
                    'nobukti' => $serviceout->nobukti,
                    'servicein_nobukti' => $request->servicein_nobukti[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $serviceout->modifiedby,
                ];
                $data = new StoreServiceOutDetailRequest($datadetail);
                $datadetails = app(ServiceOutDetailController::class)->store($data);

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
                'postingdari' => 'ENTRY SERVICE OUT',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $serviceout->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $serviceout->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($serviceout, $serviceout->getTable());
            $serviceout->position = $selected->position;
            $serviceout->page = ceil($serviceout->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $serviceout
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }

        return response($serviceout->serviceoutdetail());
    }


    public function show($id)
    {

        $data = ServiceOutHeader::findAll($id);
        $detail = ServiceOutDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */
    public function update(UpdateServiceOutHeaderRequest $request, ServiceOutHeader $serviceoutheader)
    {
        DB::beginTransaction();

        try {
            $serviceoutheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $serviceoutheader->trado_id = $request->trado_id;
            $serviceoutheader->tglkeluar = date('Y-m-d', strtotime($request->tglkeluar));
            $serviceoutheader->modifiedby = auth('api')->user()->name;


            if ($serviceoutheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($serviceoutheader->getTable()),
                    'postingdari' => 'EDIT SERVICE OUT HEADER',
                    'idtrans' => $serviceoutheader->id,
                    'nobuktitrans' => $serviceoutheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $serviceoutheader->toArray(),
                    'modifiedby' => $serviceoutheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                ServiceOutDetail::where('serviceout_id', $serviceoutheader->id)->lockForUpdate()->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->keterangan_detail); $i++) {
                    $datadetail = [
                        'serviceout_id' => $serviceoutheader->id,
                        'nobukti' => $serviceoutheader->nobukti,
                        'servicein_nobukti' => $request->servicein_nobukti[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $serviceoutheader->modifiedby,
                    ];

                    $data = new StoreServiceOutDetailRequest($datadetail);
                    $datadetails = app(ServiceOutDetailController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT SERVICE OUT DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $serviceoutheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $serviceoutheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($serviceoutheader, $serviceoutheader->getTable());
            $serviceoutheader->position = $selected->position;
            $serviceoutheader->page = ceil($serviceoutheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $serviceoutheader
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
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        $getDetail = ServiceOutDetail::lockForUpdate()->where('serviceout_id', $id)->get();

        $serviceOut = new ServiceOutHeader();
        $serviceOut = $serviceOut->lockAndDestroy($id);
        if ($serviceOut) {
            $logTrail = [
                'namatabel' => strtoupper($serviceOut->getTable()),
                'postingdari' => 'DELETE SERVICE OUT HEADER',
                'idtrans' => $serviceOut->id,
                'nobuktitrans' => $serviceOut->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $serviceOut->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE SERVICE OUT DETAIL

            $logTrailServiceOut = [
                'namatabel' => 'SERVICEOUTDETAIL',
                'postingdari' => 'DELETE SERVICE OUT DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $serviceOut->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailServiceOutDetail = new StoreLogTrailRequest($logTrailServiceOut);
            app(LogTrailController::class)->store($validatedLogTrailServiceOutDetail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($serviceOut, $serviceOut->getTable(), true);
            $serviceOut->position = $selected->position;
            $serviceOut->id = $selected->id;
            $serviceOut->page = ceil($serviceOut->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $serviceOut
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceOutHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
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
    public function combo(Request $request)
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
            'serviceout' => ServiceOutDetail::all(),
            'servicein' => ServiceInHeader::all()
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceoutheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
