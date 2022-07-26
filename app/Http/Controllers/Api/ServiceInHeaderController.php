<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInHeader;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\StoreServiceInDetailRequest;
use App\Http\Requests\UpdateServiceInHeaderRequest;
use Database\Factories\MekanikFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\Parameter;
use App\Models\ServiceInDetail;

class ServiceInHeaderController extends Controller
{
    /**
     * @ClassName
     */
    public function index(Request $request)
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

            $content = new Request();
            $content['group'] = 'SERVICEIN';
            $content['subgroup'] = 'SERVICEIN';
            $content['table'] = 'serviceinheader';

            $servicein = new ServiceInHeader();
            $servicein->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $servicein->trado_id = $request->trado_id;
            $servicein->tglmasuk = date('Y-m-d', strtotime($request->tglmasuk));
            $servicein->keterangan = $request->keterangan;
            $servicein->modifiedby = auth('api')->user()->name;

            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $servicein->nobukti = $nobukti;


            try {
                $servicein->save();
            } catch (\Exception $e) {
                $errorCode = @$e->errorInfo[1];
                if ($errorCode == 2601) {
                    goto TOP;
                }
            }

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

            // dd(count($request->mekanik_id));

            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->mekanik_id); $i++) {
                $datadetail = [
                    'servicein_id' => $servicein->id,
                    'nobukti' => $servicein->nobukti,
                    'mekanik_id' => $request->mekanik_id[$i],
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

                $datadetaillog = [
                    'id' => $iddetail,
                    'servicein_id' => $servicein->id,
                    'nobukti' => $servicein->nobukti,
                    'mekanik_id' => $request->mekanik_id[$i],
                    'keterangan' => $request->keterangan_detail[$i],
                    'modifiedby' => $servicein->modifiedby,
                    'created_at' => date('d-m-Y H:i:s', strtotime($servicein->created_at)),
                    'updated_at' => date('d-m-Y H:i:s', strtotime($servicein->updated_at)),
                ];
                $detaillog[] = $datadetaillog;
            }

            $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $servicein->id)
                ->where('namatabel', '=', $servicein->getTable())
                ->orderBy('id', 'DESC')
                ->first();

            $datalogtrail = [
                'namatabel' => $tabeldetail,
                'postingdari' => 'ENTRY SERVICE IN',
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
            $servicein->position = DB::table((new ServiceInHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $servicein->{$request->sortname})
                ->where('id', '<=', $servicein->id)
                ->count();

            if (isset($request->limit)) {
                $servicein->page = ceil($servicein->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $servicein
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

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

    /**
     * @ClassName
     */
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

                $servicein->serviceindetail()->delete();


                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->mekanik_id); $i++) {
                    $datadetail = [
                        'servicein_id' => $servicein->id,
                        'nobukti' => $servicein->nobukti,
                        'mekanik_id' => $request->mekanik_id[$i],
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

                    $datadetaillog = [
                        'id' => $iddetail,
                        'servicein_id' => $servicein->id,
                        'nobukti' => $servicein->nobukti,
                        'mekanik_id' => $request->mekanik_id[$i],
                        'keterangan' => $request->keterangan_detail[$i],
                        'modifiedby' => $servicein->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($servicein->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($servicein->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
                }

                $dataid = LogTrail::select('id')
                    ->where('idtrans', '=', $servicein->id)
                    ->where('namatabel', '=', $servicein->getTable())
                    ->orderBy('id', 'DESC')
                    ->first();

                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT SERVICE IN',
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
            $servicein->position = DB::table((new ServiceInHeader())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $servicein->{$request->sortname})
                ->where('id', '<=', $servicein->id)
                ->count();

            if (isset($request->limit)) {
                $servicein->page = ceil($servicein->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $servicein
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
            $get = ServiceInHeader::find($id);
            $delete = ServiceInDetail::where('servicein_id', $id)->delete();
            $delete = ServiceInHeader::destroy($id);

            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE SERVICE IN',
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
        ];

        return response([
            'data' => $data
        ]);
    }
}
