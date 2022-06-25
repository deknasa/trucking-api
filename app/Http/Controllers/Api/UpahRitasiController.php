<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;
use App\Models\Kota;
use App\Models\Zona;
use App\Models\Container;
use App\Models\StatusContainer;
use App\Http\Requests\StoreUpahRitasiRequest;
use App\Http\Requests\UpdateUpahRitasiRequest;
use App\Http\Requests\StoreUpahRitasiRincianRequest;
use App\Http\Requests\UpdateUpahRitasiRincianRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpahRitasiController extends Controller
{
 /**
     * @ClassName 
     */
    public function index()
    {

        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
            'withRelations' => request()->withRelations ?? false,
        ];

        $totalRows = DB::table((new UpahRitasi())->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new UpahRitasi())->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new UpahRitasi())->getTable())->select(
                'upahritasi.id',
                'kotadari.keterangan as kotadari_id',
                'kotasampai.keterangan as kotasampai_id',
                'upahritasi.jarak',
                'zona.zona as zona_id',
                'parameter.text as statusaktif',
                'upahritasi.tglmulaiberlaku',
                'param.text as statusluarkota',
                'upahritasi.modifiedby',
                'upahritasi.created_at',
                'upahritasi.updated_at'
            )
            ->join('kota as kotadari', 'kotadari.id', '=', 'upahritasi.kotadari_id')
            ->join('kota as kotasampai', 'kotasampai.id', '=', 'upahritasi.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
            ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id')
            ->orderBy('upahritasi.id', $params['sortOrder']);
        } else if ($params['sortIndex'] == 'kotadari_id' or $params['sortIndex'] == 'kotasampai_id') {
            $query = DB::table((new UpahRitasi())->getTable())->select(
                    'upahritasi.id',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahritasi.jarak',
                    'zona.zona as zona_id',
                    'parameter.text as statusaktif',
                    'upahritasi.tglmulaiberlaku',
                    'param.text as statusluarkota',
                    'upahritasi.modifiedby',
                    'upahritasi.created_at',
                    'upahritasi.updated_at'
                )
            ->join('kota as kotadari', 'kota.id', '=', 'upahritasi.kotadari_id')
            ->join('kota as kotasampai', 'kota.id', '=', 'upahritasi.kotasampai_id')
            ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
            ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
            ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id')
            ->orderBy($params['sortIndex'], $params['sortOrder'])
            ->orderBy('upahritasi.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new UpahRitasi())->getTable())->select(
                    'upahritasi.id',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahritasi.jarak',
                    'zona.zona as zona_id',
                    'parameter.text as statusaktif',
                    'upahritasi.tglmulaiberlaku',
                    'param.text as statusluarkota',
                    'upahritasi.modifiedby',
                    'upahritasi.created_at',
                    'upahritasi.updated_at'
                )
                ->join('kota as kotadari', 'kota.id', '=', 'upahritasi.kotadari_id')
                ->join('kota as kotasampai', 'kota.id', '=', 'upahritasi.kotasampai_id')
                ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
                ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
                ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('upahritasi.id', $params['sortOrder']);
            } else {
                $query = DB::table((new UpahRitasi())->getTable())->select(
                    'upahritasi.id',
                    'kotadari.keterangan as kotadari_id',
                    'kotasampai.keterangan as kotasampai_id',
                    'upahritasi.jarak',
                    'zona.zona as zona_id',
                    'parameter.text as statusaktif',
                    'upahritasi.tglmulaiberlaku',
                    'param.text as statusluarkota',
                    'upahritasi.modifiedby',
                    'upahritasi.created_at',
                    'upahritasi.updated_at'
                )
                ->join('kota as kotadari', 'kota.id', '=', 'upahritasi.kotadari_id')
                ->join('kota as kotasampai', 'kota.id', '=', 'upahritasi.kotasampai_id')
                ->join('zona', 'zona.id', '=', 'upahritasi.zona_id')
                ->leftJoin('parameter', 'upahritasi.statusaktif', '=', 'parameter.id')
                ->leftJoin('parameter as param', 'upahritasi.statusluarkota', '=', 'param.id')
                ->orderBy($params['sortIndex'], $params['sortOrder'])
                ->orderBy('upahritasi.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $search) {
                        $query = $query->where($search['field'], 'LIKE', "%$search[data]%");
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $search) {
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

        $upahritasi = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $upahritasi,
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
    public function store(StoreUpahRitasiRequest $request)
    {
        DB::beginTransaction();

        try {
            $upahritasi = new UpahRitasi();
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = $request->jarak;
            $upahritasi->zona_id = $request->zona_id;
            $upahritasi->statusaktif = $request->statusaktif;
            $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $upahritasi->statusluarkota = $request->statusluarkota;
            $upahritasi->modifiedby = auth('api')->user()->name;

            if ($upahritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'ENTRY UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => '',
                    'aksi' => 'ENTRY',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => $upahritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                /* Store detail */
                $detaillog=[];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail=$datadetails['id'];
                        $tabeldetail=$datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                        'created_at' => date('d-m-Y H:i:s',strtotime($upahritasi->created_at)),
                        'updated_at' => date('d-m-Y H:i:s',strtotime($upahritasi->updated_at)),
                    ];
                    $detaillog[]=$datadetaillog;
                }

                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $upahritasi->id)
                ->where('namatabel', '=', $upahritasi->getTable())
                ->orderBy('id', 'DESC')
                ->first(); 
                
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'ENTRY UPAH RITASI',
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
            $upahritasi->position = DB::table((new UpahRitasi())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $upahritasi->{$request->sortname})
                ->where('id', '<=', $upahritasi->id)
                ->count();

            if (isset($request->limit)) {
                $upahritasi->page = ceil($upahritasi->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        // return response($upahritasi->upahritasiRincian);
    }


    public function show($id)
    {
        $data = UpahRitasi::with(
            'upahritasiRincian',
            // 'absensiSupirDetail.trado',
            // 'absensiSupirDetail.supir',
            // 'absensiSupirDetail.absenTrado',
        )->find($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }


    public function edit(UpahRitasi $upahritasi)
    {
        //
    }
 /**
     * @ClassName 
     */
    public function update(StoreUpahRitasiRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $upahritasi = DB::table((new UpahRitasi())->getTable())->findOrFail($id);
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = $request->jarak;
            $upahritasi->zona_id = $request->zona_id;
            $upahritasi->statusaktif = $request->statusaktif;
            $upahritasi->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            $upahritasi->statusluarkota = $request->statusluarkota;
            $upahritasi->modifiedby = auth('api')->user()->name;

            if ($upahritasi->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahritasi->getTable()),
                    'postingdari' => 'EDIT UPAH RITASI',
                    'idtrans' => $upahritasi->id,
                    'nobuktitrans' => '',
                    'aksi' => 'EDIT',
                    'datajson' => $upahritasi->toArray(),
                    'modifiedby' => $upahritasi->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                
                $upahritasi->upahritasiRincian()->delete();

                /* Store detail */
                $detaillog=[];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                        ];
                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail=$datadetails['id'];
                        $tabeldetail=$datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i],
                        'nominalkomisi' => $request->nominalkomisi[$i],
                        'nominaltol' => $request->nominaltol[$i],
                        'liter' => $request->liter[$i],
                        'modifiedby' => $request->modifiedby,
                        'created_at' => date('d-m-Y H:i:s',strtotime($upahritasi->created_at)),
                        'updated_at' => date('d-m-Y H:i:s',strtotime($upahritasi->updated_at)),
                        ];
                    $detaillog[]=$datadetaillog;
                }

                $dataid = LogTrail::select('id')
                ->where('idtrans', '=', $upahritasi->id)
                ->where('namatabel', '=', $upahritasi->getTable())
                ->orderBy('id', 'DESC')
                ->first(); 
                
                $datalogtrail = [
                    'namatabel' => $tabeldetail,
                    'postingdari' => 'EDIT UPAH RITASI',
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
            $upahritasi->position = DB::table((new UpahRitasi())->getTable())->orderBy($request->sortname, $request->sortorder)
                ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $upahritasi->{$request->sortname})
                ->where('id', '<=', $upahritasi->id)
                ->count();

            if (isset($request->limit)) {
                $upahritasi->page = ceil($upahritasi->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahritasi
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($upahritasi->kasgantungDetail);
    }

 /**
     * @ClassName 
     */
    public function destroy($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = DB::table((new UpahRitasi())->getTable())->find($id);
            $delete = UpahRitasiRincian::where('upahritasi_id',$id)->delete();
            $delete = DB::table((new UpahRitasi())->getTable())->destroy($id);
            
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
