<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahSupir;
use App\Models\UpahSupirRincian;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Http\Requests\StoreUpahSupirRequest;
use App\Http\Requests\UpdateUpahSupirRequest;
use App\Http\Requests\StoreUpahSupirRincianRequest;
use App\Http\Requests\UpdateUpahSupirRincianRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpahSupirController extends Controller
{
 /**
     * @ClassName 
     */
    public function index(Request $request)
    {
        $params = [
            'offset' => $request->offset ?? 0,
            'limit' => $request->limit ?? 10,
            'search' => $request->search ?? [],
            'sortIndex' => $request->sortIndex ?? 'id',
            'sortOrder' => $request->sortOrder ?? 'asc',
            'withRelations' => $request->withRelations ?? false,
        ];

        $totalRows = UpahSupir::count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = UpahSupir::orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = UpahSupir::select(
                'upahsupir.id',
                'kotadari.keterangan as kotadari_id',
                'kotasampai.keterangan as kotasampai_id',
                'upahsupir.jarak',
                'zona.zona as zona_id',
                'parameter.text as statusaktif',
                'upahsupir.tglmulaiberlaku',
                'param.text as statusluarkota',
                'upahsupir.modifiedby',
                'upahsupir.created_at',
                'upahsupir.updated_at'
            )
            ->join('kota as kotadari', 'kotadari.id', '=', 'upahsupir.kotadari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'upahsupir.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahsupir.zona_id')
            ->leftJoin('parameter', 'upahsupir.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahsupir.statusluarkota', '=', 'param.id')
            ->orderBy('upahsupir.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kotadari_id' or $params['sortIndex'] == 'kotasampai_id') {
            $query = UpahSupir::select(
                    'upahsupir.id',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahsupir.jarak',
                    'zona.zona as zona_id',
                    'parameter.text as statusaktif',
                    'upahsupir.tglmulaiberlaku',
                    'param.text as statusluarkota',
                    'upahsupir.modifiedby',
                    'upahsupir.created_at',
                    'upahsupir.updated_at'
                )
            ->join('kota as kotadari', 'kota.id', '=', 'upahsupir.kotadari_id')
            ->join('kota as kotasampai', 'kota.id', '=', 'upahsupir.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahsupir.zona_id')
            ->leftJoin('parameter', 'upahsupir.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahsupir.statusluarkota', '=', 'param.id')
            ->orderBy($params['sortIndex'], $params['sortOrder'])
            ->orderBy('upahsupir.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = UpahSupir::select(
                    'upahsupir.id',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahsupir.jarak',
                    'zona.zona as zona_id',
                    'parameter.text as statusaktif',
                    'upahsupir.tglmulaiberlaku',
                    'param.text as statusluarkota',
                    'upahsupir.modifiedby',
                    'upahsupir.created_at',
                    'upahsupir.updated_at'
                )
                ->join('kota as kotadari', 'kota.id', '=', 'upahsupir.kotadari_id')
                ->join('kota as kotasampai', 'kota.id', '=', 'upahsupir.kotasampai_id')
                ->join('zona', 'zona.id', '=', 'upahsupir.zona_id')
                ->leftJoin('parameter', 'upahsupir.statusaktif', '=', 'parameter.id')
                ->leftJoin('parameter as param', 'upahsupir.statusluarkota', '=', 'param.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('upahsupir.id', $params['sortOrder']);
            } else {
                $query = UpahSupir::select(
                    'upahsupir.id',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahsupir.jarak',
                    'zona.zona as zona_id',
                    'parameter.text as statusaktif',
                    'upahsupir.tglmulaiberlaku',
                    'param.text as statusluarkota',
                    'upahsupir.modifiedby',
                    'upahsupir.created_at',
                    'upahsupir.updated_at'
                )
                ->join('kota as kotadari', 'kota.id', '=', 'upahsupir.kotadari_id')
                ->join('kota as kotasampai', 'kota.id', '=', 'upahsupir.kotasampai_id')
                ->join('zona', 'zona.id', '=', 'upahsupir.zona_id')
                ->leftJoin('parameter', 'upahsupir.statusaktif', '=', 'parameter.id')
                ->leftJoin('parameter as param', 'upahsupir.statusluarkota', '=', 'param.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('upahsupir.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['search']) > 0 && @$params['search']['rules'][0]['data'] != '') {
            switch ($params['search']['groupOp']) {
                case "AND":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['search']['rules'] as $index => $search) {
                        $query = $query->orWhere($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $upahsupir = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $upahsupir,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

    public function create()
    {
        //
    }
 /**
     * @ClassName 
     */
    public function store(StoreUpahSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $upahsupir = new UpahSupir();
            $upahsupir->kotadari_id = $request->kotadari_id;
            $upahsupir->kotasampai_id = $request->kotasampai_id;
            $upahsupir->jarak = $request->jarak;
            $upahsupir->zona_id = $request->zona_id;
            $upahsupir->statusaktif = $request->statusaktif;
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $upahsupir->statusluarkota = $request->statusluarkota;
            $upahsupir->modifiedby = $request->modifiedby;

            if ($upahsupir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahsupir->getTable()),
                    'postingdari' => 'ENTRY UPAH SUPIR',
                    'idtrans' => $upahsupir->id,
                    'nobuktitrans' => '',
                    'aksi' => 'ENTRY',
                    'datajson' => $upahsupir->toArray(),
                    'modifiedby' => $upahsupir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                $detaillog=[];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahsupir_id' => $upahsupir->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreUpahSupirRincianRequest($datadetail);
                    $datadetails = app(UpahSupirRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail=$datadetails['id'];
                        $tabeldetail=$datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahsupir_id' => $upahsupir->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                        'created_at' => date('d-m-Y H:i:s',strtotime($upahsupir->created_at)),
                        'updated_at' => date('d-m-Y H:i:s',strtotime($upahsupir->updated_at)),
                    ];
                    $detaillog[]=$datadetaillog;
                }

                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $upahsupir->id)
                ->where('namatabel', '=', $upahsupir->getTable())
                ->orderBy('id', 'DESC')
                ->first(); 
                
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY UPAH SUPIR',
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
        }
            /* Set position and page */
            $upahsupir->position = UpahSupir::orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $upahsupir->{$request->sortname})
                ->where('id', '<=', $upahsupir->id)
                ->count();

            if (isset($request->limit)) {
                $upahsupir->page = ceil($upahsupir->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        // return response($upahsupir->upahsupirRincian);
    }


    public function show($id)
    {
        $data = UpahSupir::with(
            'upahsupirRincian',
            // 'absensiSupirDetail.trado',
            // 'absensiSupirDetail.supir',
            // 'absensiSupirDetail.absenTrado',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }


    public function edit(UpahSupir $upahSupir)
    {
        //
    }
 /**
     * @ClassName 
     */
    public function update(StoreUpahSupirRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $upahsupir = UpahSupir::findOrFail($id);
            $upahsupir->kotadari_id = $request->kotadari_id;
            $upahsupir->kotasampai_id = $request->kotasampai_id;
            $upahsupir->jarak = $request->jarak;
            $upahsupir->zona_id = $request->zona_id;
            $upahsupir->statusaktif = $request->statusaktif;
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $upahsupir->statusluarkota = $request->statusluarkota;
            $upahsupir->modifiedby = $request->modifiedby;

            if ($upahsupir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahsupir->getTable()),
                    'postingdari' => 'EDIT UPAH SUPIR',
                    'idtrans' => $upahsupir->id,
                    'nobuktitrans' => '',
                    'aksi' => 'EDIT',
                    'datajson' => $upahsupir->toArray(),
                    'modifiedby' => $upahsupir->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                $upahsupir->upahsupirRincian()->delete();

                /* Store detail */
                $detaillog=[];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahsupir_id' => $upahsupir->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                        ];
                    $data = new StoreUpahSupirRincianRequest($datadetail);
                    $datadetails = app(UpahSupirRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail=$datadetails['id'];
                        $tabeldetail=$datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahsupir_id' => $upahsupir->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                        'created_at' => date('d-m-Y H:i:s',strtotime($upahsupir->created_at)),
                        'updated_at' => date('d-m-Y H:i:s',strtotime($upahsupir->updated_at)),
                        ];
                    $detaillog[]=$datadetaillog;
                }

                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $upahsupir->id)
                ->where('namatabel', '=', $upahsupir->getTable())
                ->orderBy('id', 'DESC')
                ->first(); 
                
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT UPAH SUPIR',
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
            
            DB::commit();
        }
            /* Set position and page */
            $upahsupir->position = UpahSupir::orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $upahsupir->{$request->sortname})
                ->where('id', '<=', $upahsupir->id)
                ->count();

            if (isset($request->limit)) {
                $upahsupir->page = ceil($upahsupir->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($upahsupir->kasgantungDetail);
    }

 /**
     * @ClassName 
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = UpahSupir::find($id);
            $delete = UpahSupirRincian::where('upahsupir_id',$id)->delete();
            $delete = UpahSupir::destroy($id);
            
            $datalogtrail = [
                'namatabel' => $get->getTable(),
                'postingdari' => 'DELETE UPAH SUPIR',
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
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'container' => Container::all(),
            'statuscontainer' => StatusContainer::all(),
            'statusaktif' => Parameter::where('grp','STATUS AKTIF')->get(),
            'statusluarkota' => Parameter::where('grp','STATUS LUAR KOTA')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }
}
