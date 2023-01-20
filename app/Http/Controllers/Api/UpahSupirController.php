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

use App\Helpers\App;
use Illuminate\Support\Facades\Storage;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpahSupirController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $upahsupir = new UpahSupir();

        return response([
            'data' => $upahsupir->get(),
            'attributes' => [
                'totalRows' => $upahsupir->totalRows,
                'totalPages' => $upahsupir->totalPages
            ]
        ]);
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
            $upahsupir->parent_id = $request->parent_id ?? 0;
            $upahsupir->kotasampai_id = $request->kotasampai_id;
            $upahsupir->jarak = str_replace(',', '', str_replace('.', '', $request->jarak));
            $upahsupir->zona_id = ($request->zona_id == null) ? "" : $request->zona_id;
            $upahsupir->statusaktif = $request->statusaktif;
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            // $upahsupir->tglakhirberlaku = ($request->tglakhirberlaku == null) ? "" : date('Y-m-d', strtotime($request->tglakhirberlaku));
            $upahsupir->statusluarkota = $request->statusluarkota;

            $upahsupir->modifiedby = auth('api')->user()->name;
            $this->deleteFiles($upahsupir);
            if ($request->gambar) {
                $upahsupir->gambar = $this->storeFiles($request->gambar, 'upahsupir');
            } else {
                $upahsupir->gambar = '';
            }
            if ($upahsupir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahsupir->getTable()),
                    'postingdari' => 'ENTRY UPAH SUPIR',
                    'idtrans' => $upahsupir->id,
                    'nobuktitrans' => $upahsupir->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $upahsupir->toArray(),
                    'modifiedby' => $upahsupir->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {

                    $datadetail = [
                        'upahsupir_id' => $upahsupir->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i] ?? 0,
                        'nominalkomisi' => $request->nominalkomisi[$i] ?? 0,
                        'nominaltol' =>  $request->nominaltol[$i] ?? 0,
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => auth('api')->user()->name,
                    ];
                    $data = new StoreUpahSupirRincianRequest($datadetail);
                    $datadetails = app(UpahSupirRincianController::class)->store($data);
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
                    'postingdari' => 'ENTRY UPAH SUPIR RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $upahsupir->id,
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
            $selected = $this->getPosition($upahsupir, $upahsupir->getTable());
            $upahsupir->position = $selected->position;
            $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ], 201);
        } catch (\Throwable $th) {
            $this->deleteFiles($upahsupir);
            DB::rollBack();
            return response($th->getMessage());
        }

        return response($upahsupir->upahsupirRincian());
    }


    public function show($id)
    {

        $data = upahSupir::findAll($id);
        $detail = UpahSupirRincian::getAll($id);



        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateUpahSupirRequest $request, UpahSupir $upahsupir)
    {
        DB::beginTransaction();

        try {
            $upahsupir->kotadari_id = $request->kotadari_id;
            $upahsupir->parent_id = $request->parent_id ?? 0;
            $upahsupir->kotasampai_id = $request->kotasampai_id;
            $upahsupir->jarak = str_replace(',', '', str_replace('.', '', $request->jarak));
            $upahsupir->zona_id = ($request->zona_id == null) ? "" : $request->zona_id;
            $upahsupir->statusaktif = $request->statusaktif;
            $upahsupir->tglmulaiberlaku = date('Y-m-d', strtotime($request->tglmulaiberlaku));
            // $upahsupir->tglakhirberlaku = ($request->tglakhirberlaku == null) ? "" : date('Y-m-d', strtotime($request->tglakhirberlaku));
            $upahsupir->statusluarkota = $request->statusluarkota;

            $upahsupir->modifiedby = auth('api')->user()->name;

            $this->deleteFiles($upahsupir);
            if ($request->gambar) {
                $upahsupir->gambar = $this->storeFiles($request->gambar, 'upahsupir');
            } else {
                $upahsupir->gambar = '';
            }
            if ($upahsupir->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($upahsupir->getTable()),
                    'postingdari' => 'EDIT UPAH SUPIR',
                    'idtrans' => $upahsupir->id,
                    'nobuktitrans' => $upahsupir->id,
                    'aksi' => 'EDIT',
                    'datajson' => $upahsupir->toArray(),
                    'modifiedby' => $upahsupir->modifiedby
                ];
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                UpahSupirRincian::where('upahsupir_id', $upahsupir->id)->delete();
                /* Store detail */
                $detaillog = [];
                for ($i = 0; $i < count($request->nominalsupir); $i++) {
                    $datadetail = [
                        'upahsupir_id' => $upahsupir->id,
                        'container_id' => $request->container_id[$i],
                        'statuscontainer_id' => $request->statuscontainer_id[$i],
                        'nominalsupir' => $request->nominalsupir[$i],
                        'nominalkenek' => $request->nominalkenek[$i] ?? 0,
                        'nominalkomisi' => $request->nominalkomisi[$i] ?? 0,
                        'nominaltol' =>  $request->nominaltol[$i] ?? 0,
                        'liter' => $request->liter[$i] ?? 0,
                        'modifiedby' => $upahsupir->modifiedby,
                    ];

                    $data = new StoreUpahSupirRincianRequest($datadetail);
                    $datadetails = app(UpahSupirRincianController::class)->store($data);

                    if (!$datadetails['error']) {
                        $iddetail = $datadetails['id'];
                        $tabeldetail = $datadetails['tabel'];
                    } else {
                        return response($datadetails, 422);
                    }

                    $detaillog[] = $datadetails['detail']->toArray();
                }
                // return response($datadetails['error'], 422);
                $datalogtrail = [
                    'namatabel' => strtoupper($tabeldetail),
                    'postingdari' => 'EDIT UPAH SUPIR RINCIAN',
                    'idtrans' =>  $storedLogTrail['id'],
                    'nobuktitrans' => $upahsupir->id,
                    'aksi' => 'EDIT',
                    'datajson' => $detaillog,
                    'modifiedby' => auth('api')->user()->name,
                ];

                $data = new StoreLogTrailRequest($datalogtrail);
                app(LogTrailController::class)->store($data);
            }
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();


            /* Set position and page */
            $selected = $this->getPosition($upahsupir, $upahsupir->getTable());
            $upahsupir->position = $selected->position;
            $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ]);
        } catch (\Throwable $th) {
            $this->deleteFiles($upahsupir);
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

        $getDetail = UpahSupirRincian::lockForUpdate()->where('upahsupir_id', $id)->get();

        $upahSupir = new UpahSupir();
        $upahSupir = $upahSupir->lockAndDestroy($id);
        if ($upahSupir) {
            $logTrail = [
                'namatabel' => strtoupper($upahSupir->getTable()),
                'postingdari' => 'DELETE UPAH SUPIR',
                'idtrans' => $upahSupir->id,
                'nobuktitrans' => $upahSupir->id,
                'aksi' => 'DELETE',
                'datajson' => $upahSupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE UPAH SUPIR RINCIAN

            $logTrailUpahSupirRincian = [
                'namatabel' => 'UPAHSUPIRRINCIAN',
                'postingdari' => 'DELETE UPAH SUPIR RINCIAN',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $upahSupir->id,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailUpahSupirRincian = new StoreLogTrailRequest($logTrailUpahSupirRincian);
            app(LogTrailController::class)->store($validatedLogTrailUpahSupirRincian);
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($upahSupir, $upahSupir->getTable(), true);
            $upahSupir->position = $selected->position;
            $upahSupir->id = $selected->id;
            $upahSupir->page = ceil($upahSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $upahSupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
        throw $th;
    }

    public function default()
    {

        $UpahSupir = new UpahSupir();
        return response([
            'status' => true,
            'data' => $UpahSupir->default(),
        ]);
    }


    public function combo(Request $request)
    {
        $data = [
            'kota' => Kota::all(),
            'zona' => Zona::all(),
            'container' => Container::all(),
            'statuscontainer' => StatusContainer::all(),
            'statusaktif' => Parameter::where('grp', 'STATUS AKTIF')->get(),
            'statusluarkota' => Parameter::where('grp', 'UPAH SUPIR LUAR KOTA')->get(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('upahsupir')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    private function storeFiles(array $files, string $destinationFolder): string
    {
        $storedFiles = [];

        foreach ($files as $file) {
            $originalFileName = $file->hashName();
            $storedFile = Storage::putFileAs($destinationFolder, $file, 'ori-' . $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(UpahSupir $upahsupir)
    {
        $sizeTypes = ['ori', 'medium', 'small'];

        $relatedPhotoUpahSupir = [];
        $photoUpahSupir = json_decode($upahsupir->gambar, true);
        if ($photoUpahSupir) {
            foreach ($photoUpahSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoUpahSupir[] = "upahsupir/$sizeType-$path";
                }
            }
            Storage::delete($relatedPhotoUpahSupir);
        }
    }

    public function getImage(string $filename, string $type)
    {
        return response()->file(storage_path("app/upahsupir/$type-$filename"));
    }
}
