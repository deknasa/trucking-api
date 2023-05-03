<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\Parameter;
use App\Models\Error;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\StoreServiceInDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateServiceInHeaderRequest;
use App\Models\LogTrail;
use App\Models\ServiceInDetail;
use Illuminate\Database\QueryException;

class ServiceInHeaderController extends Controller
{

    /**
     * @ClassName index
     */
    public function index()
    {
        $servicein = new ServiceInHeader();
        return response([
            'data' => $servicein->get(),
            'attributes' => [
                'totalRows' => $servicein->totalRows,
                'totalPages' => $servicein->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StoreServiceInHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $group = 'SERVICE IN BUKTI';
            $subgroup = 'SERVICE IN BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'serviceinheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $servicein = new ServiceInHeader();
            $servicein->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $servicein->trado_id = $request->trado_id;
            $servicein->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $servicein->statusformat =  $format->id;
            $servicein->modifiedby = auth('api')->user()->name;

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $servicein->nobukti = $nobukti;


            $servicein->save();

            $logTrail = [
                'namatabel' => strtoupper($servicein->getTable()),
                'postingdari' => 'ENTRY SERVICE IN HEADER',
                'idtrans' => $servicein->id,
                'nobuktitrans' => $servicein->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $servicein->toArray(),
                'modifiedby' => $servicein->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];

            for ($i = 0; $i < count($request->keterangan_detail); $i++) {
                $datadetail = [
                    'servicein_id' => $servicein->id,
                    'nobukti' => $servicein->nobukti,
                    'karyawan_id' => $request->karyawan_id[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $servicein->modifiedby,
                ];

                $data = new StoreServiceInDetailRequest($datadetail);
                $datadetails = app(ServiceInDetailController::class)->store($data);

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
                'postingdari' => 'ENTRY SERVICE IN DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $servicein->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => $servicein->modifiedby,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($servicein, $servicein->getTable());
            $servicein->position = $selected->position;
            $servicein->page = ceil($servicein->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $servicein
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



    public function show($id)
    {

        $data = ServiceInHeader::findAll($id);
        $detail = ServiceInDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdateServiceInHeaderRequest $request, ServiceInHeader $serviceinheader)
    {
        DB::beginTransaction();

        try {
            $serviceinheader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $serviceinheader->trado_id = $request->trado_id;
            $serviceinheader->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $serviceinheader->modifiedby = auth('api')->user()->name;

            if ($serviceinheader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($serviceinheader->getTable()),
                    'postingdari' => 'EDIT SERVICE IN HEADER',
                    'idtrans' => $serviceinheader->id,
                    'nobuktitrans' => $serviceinheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $serviceinheader->toArray(),
                    'modifiedby' => $serviceinheader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                ServiceInDetail::where('servicein_id', $serviceinheader->id)->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->keterangan_detail); $i++) {
                    $datadetail = [
                        'servicein_id' => $serviceinheader->id,
                        'nobukti' => $serviceinheader->nobukti,
                        'karyawan_id' => $request->karyawan_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $serviceinheader->modifiedby,
                    ];

                    $data = new StoreServiceInDetailRequest($datadetail);
                    $datadetails = app(ServiceInDetailController::class)->store($data);

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
                    'postingdari' => 'EDIT SERVICE IN DETAIL',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $serviceinheader->nobukti,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $serviceinheader->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($serviceinheader, $serviceinheader->getTable());
            $serviceinheader->position = $selected->position;
            $serviceinheader->page = ceil($serviceinheader->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $serviceinheader
            ]);
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

        $getDetail = ServiceInDetail::lockForUpdate()->where('servicein_id', $id)->get();

        $serviceIn = new ServiceInHeader();
        $serviceIn = $serviceIn->lockAndDestroy($id);

        if ($serviceIn) {
            $logTrail = [
                'namatabel' => strtoupper($serviceIn->getTable()),
                'postingdari' => 'DELETE SERVICE IN HEADER',
                'idtrans' => $serviceIn->id,
                'nobuktitrans' => $serviceIn->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $serviceIn->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE SERVICE IN DETAIL
            $logTrailServiceIn = [
                'namatabel' => 'SERVICEINDETAIL',
                'postingdari' => 'DELETE SERVICE IN DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $serviceIn->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailServiceinDetail = new StoreLogTrailRequest($logTrailServiceIn);
            app(LogTrailController::class)->store($validatedLogTrailServiceinDetail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($serviceIn, $serviceIn->getTable(), true);
            $serviceIn->position = $selected->position;
            $serviceIn->id = $selected->id;
            $serviceIn->page = ceil($serviceIn->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $serviceIn
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
        $pengeluaran = ServiceInHeader::findOrFail($id);
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

    public function combo()
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceinheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
