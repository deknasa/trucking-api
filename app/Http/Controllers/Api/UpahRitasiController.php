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

        $upahritasi = new UpahRitasi();

        return response([
            'data' => $upahritasi->get(),
            'attributes' => [
                'totalRows' => $upahritasi->totalRows,
                'totalPages' => $upahritasi->totalPages
            ]
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
            $jarak = str_replace(',', '', str_replace('.', '', $request->jarak));

            $upahritasi = new UpahRitasi();
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = $jarak;
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
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $nominalsupir = str_replace(',', '', str_replace('.', '', $request->nominalsupir[$i]));
                    $nominalkenek = str_replace(',', '', str_replace('.', '', $request->nominalkenek[$i]));
                    $nominalkomisi = str_replace(',', '', str_replace('.', '', $request->nominalkomisi[$i]));
                    $nominaltol = str_replace(',', '', str_replace('.', '', $request->nominaltol[$i]));
                    $liter = str_replace(',', '', str_replace('.', '', $request->liter[$i]));

                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $nominalsupir,
                        'nominalkenek' => $nominalkenek,
                        'nominalkomisi' => $nominalkomisi,
                        'nominaltol' => $nominaltol,
                        'liter' => $liter,
                        'modifiedby' => $request->modifiedby,
                    ];

                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $nominalsupir,
                        'nominalkenek' => $nominalkenek,
                        'nominalkomisi' => $nominalkomisi,
                        'nominaltol' => $nominaltol,
                        'liter' => $liter,
                        'modifiedby' => $request->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($upahritasi->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($upahritasi->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
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
            // /* Set position and page */
            // $upahritasi->position = DB::table((new UpahRitasi())->getTable())->orderBy($request->sortname, $request->sortorder)
            //     ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $upahritasi->{$request->sortname})
            //     ->where('id', '<=', $upahritasi->id)
            //     ->count();

            // if (isset($request->limit)) {
            //     $upahritasi->page = ceil($upahritasi->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable());
            $upahritasi->position = $selected->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

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
            $jarak = str_replace(',', '', str_replace('.', '', $request->jarak));

            $upahritasi = UpahRitasi::findOrFail($id);
            $upahritasi->kotadari_id = $request->kotadari_id;
            $upahritasi->kotasampai_id = $request->kotasampai_id;
            $upahritasi->jarak = $jarak;
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
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $nominalsupir = str_replace(',', '', str_replace('.', '', $request->nominalsupir[$i]));
                    $nominalkenek = str_replace(',', '', str_replace('.', '', $request->nominalkenek[$i]));
                    $nominalkomisi = str_replace(',', '', str_replace('.', '', $request->nominalkomisi[$i]));
                    $nominaltol = str_replace(',', '', str_replace('.', '', $request->nominaltol[$i]));
                    $liter = str_replace(',', '', str_replace('.', '', $request->liter[$i]));

                    $datadetail = [
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $nominalsupir,
                        'nominalkenek' => $nominalkenek,
                        'nominalkomisi' => $nominalkomisi,
                        'nominaltol' => $nominaltol,
                        'liter' => $liter,
                        'modifiedby' => $request->modifiedby,
                    ];
                    $data = new StoreUpahRitasiRincianRequest($datadetail);
                    $datadetails = app(UpahRitasiRincianController::class)->store($data);

                    if ($datadetails['error']) {
                        return response($datadetails, 422);
                    } else {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    }

                    $datadetaillog = [
                        'id' => $iddetail,
                        'upahritasi_id' => $upahritasi->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $nominalsupir,
                        'nominalkenek' => $nominalkenek,
                        'nominalkomisi' => $nominalkomisi,
                        'nominaltol' => $nominaltol,
                        'liter' => $liter,
                        'modifiedby' => $request->modifiedby,
                        'created_at' => date('d-m-Y H:i:s', strtotime($upahritasi->created_at)),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($upahritasi->updated_at)),
                    ];
                    $detaillog[] = $datadetaillog;
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
            // $upahritasi->position = DB::table((new UpahRitasi())->getTable())->orderBy($request->sortname, $request->sortorder)
            //     ->where($request->sortname, $request->sortorder == 'desc' ? '>=' : '<=', $upahritasi->{$request->sortname})
            //     ->where('id', '<=', $upahritasi->id)
            //     ->count();

            // if (isset($request->limit)) {
            //     $upahritasi->page = ceil($upahritasi->position / $request->limit);
            // }

            /* Set position and page */
            $selected = $this->getPosition($upahritasi, $upahritasi->getTable());
            $upahritasi->position = $selected->position;
            $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));

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
    public function destroy($id, $upahritasi, Request $request)
    {
        DB::beginTransaction();

        try {
            $get = UpahRitasi::find($id);
            $delete = UpahRitasiRincian::where('upahritasi_id', $id)->delete();
            $delete = UpahRitasi::destroy($id);

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

                $selected = $this->getPosition($upahritasi, $upahritasi->getTable(), true);
                $upahritasi->position = $selected->position;
                $upahritasi->id = $selected->id;
                $upahritasi->page = ceil($upahritasi->position / ($request->limit ?? 10));
                
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
            'statusaktif' => Parameter::where('grp', 'STATUS AKTIF')->get(),
            'statusluarkota' => Parameter::where('grp', 'STATUS LUAR KOTA')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('upahritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
