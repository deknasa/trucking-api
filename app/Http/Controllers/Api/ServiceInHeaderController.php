<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\StoreServiceInDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\ServiceInDetail;

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
            $servicein->keterangan = $request->keterangan;
            $servicein->statusformat =  $format->id;
            $servicein->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $servicein->nobukti = $nobukti;

            try {
                $servicein->save();
            } catch (\Exception $e) {
                dd($e->getMessage());
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

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

            // dd(count($request->mekanik_id));

            /* Store detail */
            $detaillog = [];
            // for ($i = 0; $i < count($request->mekanik_id); $i++) {
            $datadetail = [
                'servicein_id' => $servicein->id,
                'nobukti' => $servicein->nobukti,
                'mekanik_id' => $request->mekanik_id,
                'keterangan' => $request->keterangan_detail,
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

            $datadetaillog = [
                'id' => $iddetail,
                'servicein_id' => $servicein->id,
                'nobukti' => $servicein->nobukti,
                'mekanik_id' => $request->mekanik_id,
                'keterangan' => $request->keterangan_detail,
                'modifiedby' => $servicein->modifiedby,
                'created_at' => date('d-m-Y H:i:s', strtotime($servicein->created_at)),
                'updated_at' => date('d-m-Y H:i:s', strtotime($servicein->updated_at)),
            ];
            $detaillog[] = $datadetaillog;
            //}

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY SERVICE IN',
                'idtrans' =>  $iddetail->id,
                'nobuktitrans' => '',
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
            $selected = $this->getPosition($servicein, $servicein->getTable());
            $servicein->position = $selected->position;
            $servicein->page = ceil($servicein->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $servicein
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response($servicein->serviceindetail());
    }


    /**
     * @ClassName
     */
    public function show($id)
    {
        $data = ServiceInHeader::with(
            'serviceindetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }


    public function update(StoreServiceInHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $servicein = ServiceInHeader::findOrFail($id);

            $servicein->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $servicein->trado_id = $request->trado_id;
            $servicein->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $servicein->keterangan = $request->keterangan;
            $servicein->modifiedby = auth('api')->user()->name;

            if ($servicein->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($servicein->getTable()),
                    'postingdari' => 'ENTRY SERVICE IN',
                    'idtrans' => $servicein->id,
                    'nobuktitrans' => '',
                    'aksi' => 'ENTRY',
                    'datajson' => $servicein->toArray(),
                    'modifiedby' => $servicein->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                ServiceInDetail::where('servicein_id', $id)->delete();

                // $servicein->serviceindetail()->delete();


                /* Store detail */
                $detaillog = [];
               // for ($i = 0; $i < count($request->mekanik_id); $i++) {
                    $datadetail = [
                        'servicein_id' => $servicein->id,
                        'nobukti' => $servicein->nobukti,
                        'mekanik_id' => $request->mekanik_id,
                        'keterangan' => $request->keterangan_detail,
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

                    $datadetaillog = [
                        'id' => $iddetail,
                        'servicein_id' => $servicein->id,
                        'nobukti' => $servicein->nobukti,
                        'mekanik_id' => $request->mekanik_id,
                        'keterangan' => $request->keterangan_detail,
                        'modifiedby' => $servicein->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($servicein->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($servicein->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
               // }

                // $dataid = LogTrail::select('id')
                //     ->where('idtrans', '=', $servicein->id)
                //     ->where('namatabel', '=', $servicein->getTable())
                //     ->orderBy('id', 'DESC')
                //     ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT SERVICE IN',
                    'idtrans' =>  $iddetail->id,
                    'nobuktitrans' => '',
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => $request->modifiedby,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);

                $request->sortname = $request->sortname ?? 'id';
                $request->sortorder = $request->sortorder ?? 'asc';
            }
            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($servicein, $servicein->getTable());
            $servicein->position = $selected->position;
            $servicein->page = ceil($servicein->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $servicein
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy($id, $servicein, Request $request)
    {

        DB::beginTransaction();
        $servicein = new ServiceInHeader();

        try {
            // $delete = $servicein->delete();
            $delete = ServiceInDetail::where('Servicein_id', $id)->delete();
            $delete = ServiceInHeader::destroy($id);

            if ($delete) {
                $logTrail = [
                    'namatabel' => strtoupper($servicein->getTable()),
                    'postingdari' => 'DELETE SERVICE IN',
                    'idtrans' => $servicein->id,
                    'nobuktitrans' => $servicein->id,
                    'aksi' => 'DELETE',
                    'datajson' => $servicein->toArray(),
                    'modifiedby' => $servicein->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($servicein, $servicein->getTable(), true);
                $servicein->position = $selected->position;
                $servicein->id = $selected->id;
                $servicein->page = ceil($servicein->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil dihapus',
                    'data' => $servicein
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
}
