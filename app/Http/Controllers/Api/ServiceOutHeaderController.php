<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOutHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\StoreServiceOutDetailRequest;
use App\Http\Requests\UpdateServiceOutHeaderRequest;
use Database\Factories\MekanikFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\Parameter;
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

            $content = new Request();
            $content['group'] = 'SERVICEOUT';
            $content['subgroup'] = 'SERVICEOUT';
            $content['table'] = 'serviceoutheader';

            $serviceout = new ServiceOutHeader();
            $serviceout->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $serviceout->trado_id = $request->trado_id;
            $serviceout->tglkeluar = date('Y-m-d', strtotime($request->tglkeluar));
            $serviceout->keterangan = $request->keterangan;
            $serviceout->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $serviceout->nobukti = $nobukti;

            try {
                $serviceout->save();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

            $logTrail = [
                'namatabel' => strtoupper($serviceout->getTable()),
                'postingdari' => 'ENTRY SERVICE OUT',
                'idtrans' => $serviceout->id,
                'nobuktitrans' => '',
                'aksi' => 'ENTRY',
                'datajson' => $serviceout->toArray(),
                'modifiedby' => $serviceout->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->servicein_nobukti); $i++) {
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
                    'servicein_id' => $serviceout->id,
                    'nobukti' => $serviceout->nobukti,
                    'servicein_nobukti' => $request->servicein_nobukti[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $serviceout->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($serviceout->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($serviceout->updated_at)),
                ];
                $detaillog[] = $datadetaillog;
            }

            $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $serviceout->id)
                ->where('namatabel', '=', $serviceout->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY SERVICE OUT',
                'idtrans' =>  $dataid->id,
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
            $serviceout->position = DB::table((new ServiceOutHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $serviceout->{$request->sortname})
                ->where('id', '<=', $serviceout->id)
                ->count();

            if (isset($request->limit)) {
                $serviceout->page = ceil($serviceout->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $serviceout
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function show($id)
    {
        $data = ServiceOutHeader::with(
            'serviceoutdetail',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
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
            $serviceout->tglkeluar = date('Y-m-d', strtotime($request->tglmasuk));
            $serviceout->keterangan = $request->keterangan;
            $serviceout->modifiedby = auth('api')->user()->name;

            if ($serviceout->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($serviceout->getTable()),
                    'postingdari' => 'ENTRY SERVICE OUT',
                    'idtrans' => $serviceout->id,
                    'nobuktitrans' => '',
                    'aksi' => 'ENTRY',
                    'datajson' => $serviceout->toArray(),
                    'modifiedby' => $serviceout->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                $serviceout->serviceoutdetail()->delete();


                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->servicein_nobukti); $i++) {
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
                        'servicein_id' => $serviceout->id,
                        'nobukti' => $serviceout->nobukti,
                        'servicein_nobukti' => $request->servicein_nobukti[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $serviceout->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($serviceout->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($serviceout->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }

                $dataid = LogTrail::select('id')
                    ->where('idtrans', '=', $serviceout->id)
                    ->where('namatabel', '=', $serviceout->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT SERVICE OUT',
                    'idtrans' =>  $dataid->id,
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
            $serviceout->position = DB::table((new ServiceOutHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $serviceout->{$request->sortname})
                ->where('id', '<=', $serviceout->id)
                ->count();

            if (isset($request->limit)) {
                $serviceout->page = ceil($serviceout->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $serviceout
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    /**
     * @ClassName
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = ServiceOutHeader::find($id);
            $delete = ServiceOutDetail::where('serviceout_id', $id)->delete();
            $delete = ServiceOutHeader::destroy($id);

            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE SERVICE OUT',
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
