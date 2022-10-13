<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOutHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\StoreServiceOutDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\ServiceInHeader;
use App\Models\ServiceOutDetail;

class ServiceOutHeaderController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
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
            $serviceout->keterangan = $request->keterangan;
            $serviceout->statusformat =  $format->id;
            $serviceout->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $serviceout->nobukti = $nobukti;
            try {
                $serviceout->save();
            } catch (\Exception $e) {
                dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

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

                $datadetaillog = [
                    'id' => $iddetail,
                    'serviceout_id' => $serviceout->id,
                    'nobukti' => $serviceout->nobukti,
                    'servicein_nobukti' => $request->servicein_nobukti[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $serviceout->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($serviceout->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($serviceout->updated_at)),
                ];
                $detaillog[] = $datadetaillog;
                // }

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY SERVICE OUT',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $serviceout->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $serviceout->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
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
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response($th->getMessage());
        }

        return response($serviceout->serviceoutdetail());
    }

  
    public function show($id)
    {

        $data = ServiceOutHeader::find($id);
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
    public function update(StoreServiceOutHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $serviceout = ServiceOutHeader::findOrFail($id);

            $serviceout->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $serviceout->trado_id = $request->trado_id;
            $serviceout->tglkeluar = date('Y-m-d', strtotime($request->tglkeluar));
            $serviceout->keterangan = $request->keterangan;
            $serviceout->modifiedby = auth('api')->user()->name;


            if ($serviceout->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($serviceout->getTable()),
                    'postingdari' => 'UPDATE SERVICE OUT HEADER',
                    'idtrans' => $serviceout->id,
                    'nobuktitrans' => $serviceout->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $serviceout->toArray(),
                    'modifiedby' => $serviceout->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                ServiceOutDetail::where('serviceout_id', $id)->delete();
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

                    $datadetaillog = [
                        'id' => $iddetail,
                        'serviceout_id' => $serviceout->id,
                        'nobukti' => $serviceout->nobukti,
                        'servicein_nobukti' => $request->servicein_nobukti[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $serviceout->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($serviceout->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($serviceout->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY SERVICE OUT',
                    'idtrans' =>  $iddetail,
                    'nobuktitrans' => $serviceout->nobukti,
                    'aksi' => 'ENTRY',
                    'datajson' => $detaillog,
                    'modifiedby' => $serviceout->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
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
    public function destroy($id,  Request $request)
    {

        DB::beginTransaction();
        $serviceout = new ServiceOutHeader();

        try {
            // $delete = $ServiceOut->delete();
            $delete = ServiceOutDetail::where('Serviceout_id', $id)->delete();
            $delete = ServiceOutHeader::destroy($id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($serviceout->getTable()),
                    'postingdari' => 'DELETE SERVICEOUT',
                    'idtrans' => $id,
                    'nobuktitrans' => '',
                    'aksi' => 'DELETE',
                    'datajson' => $serviceout->toArray(),
                    'modifiedby' => $serviceout->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($serviceout, $serviceout->getTable(), true);
                $serviceout->position = $selected->position;
                $serviceout->id = $selected->id;
                $serviceout->page = ceil($serviceout->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $serviceout
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
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
            'serviceout' => ServiceOutDetail::all(),
            'servicein' => ServiceInHeader::all()
        ];

        return response([
            'data' => $data
        ]);
    }
}
