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
use App\Http\Requests\DestroyUpahSupirRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class UpahSupirController extends Controller
{
    private $upahsupir;
    /**
     * @ClassName 
     * UpahSupir
     * @Detail1 UpahSupirRincianController
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


    public function listpivot(GetUpahSupirRangeRequest $request)
    {
        $dari = date('Y-m-d', strtotime($request->dari));
        $sampai = date('Y-m-d', strtotime($request->sampai));

        $upahsupirrincian = new UpahSupirRincian();

        $cekData = DB::table("upahsupir")->from(DB::raw("upahsupir with (readuncommitted)"))
            ->whereBetween('tglmulaiberlaku', [$dari, $sampai])
            ->first();

        if ($cekData != null) {
            return response([
                'status' => true,
                'data' => $upahsupirrincian->listpivot($dari, $sampai),
            ]);
        } else {
            return response([
                'errors' => [
                    "export" => "tidak ada data"
                ],
                'message' => "The given data was invalid.",
            ], 422);
        }
    }
    
    /**
     * @ClassName 
     */
    public function store(StoreUpahSupirRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kotadari_id' => $request->kotadari_id,
                'parent_id' => $request->parent_id ?? 0,
                'tarif_id' => $request->tarif_id ?? 0,
                'kotasampai_id' => $request->kotasampai_id,
                'penyesuaian' => $request->penyesuaian,
                'jarak' => $request->jarak,
                'zona_id' => ($request->zona_id == null) ? 0 : $request->zona_id ?? 0,
                'statusaktif' => $request->statusaktif,

                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'statussimpankandang' => $request->statussimpankandang,
                'statusluarkota' => $request->statusluarkota,
                'keterangan' => $request->keterangan,
                'gambar' => $request->gambar ?? [],

                'container_id' => $request->container_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'nominalsupir' => $request->nominalsupir,
                'nominalkenek' => $request->nominalkenek ?? 0,
                'nominalkomisi' => $request->nominalkomisi ?? 0,
                'nominaltol' =>  $request->nominaltol ?? 0,
                'liter' => $request->liter ?? 0,

            ];
            $upahsupir = (new UpahSupir())->processStore($data);
            $upahsupir->position = $this->getPosition($upahsupir, $upahsupir->getTable())->position;
            $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));
            $this->upahsupir = $upahsupir;
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
    public function update(UpdateUpahSupirRequest $request, UpahSupir $upahsupir): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kotadari_id' => $request->kotadari_id,
                'parent_id' => $request->parent_id ?? 0,
                'tarif_id' => $request->tarif_id ?? 0,
                'kotasampai_id' => $request->kotasampai_id,
                'penyesuaian' => $request->penyesuaian,
                'jarak' => $request->jarak,
                'zona_id' => ($request->zona_id == null) ? 0 : $request->zona_id ?? 0,
                'statusaktif' => $request->statusaktif,

                'tglmulaiberlaku' => date('Y-m-d', strtotime($request->tglmulaiberlaku)),

                'statussimpankandang' => $request->statussimpankandang,
                'statusluarkota' => $request->statusluarkota,
                'keterangan' => $request->keterangan,
                'gambar' => $request->gambar ?? [],

                'container_id' => $request->container_id,
                'statuscontainer_id' => $request->statuscontainer_id,
                'nominalsupir' => $request->nominalsupir,
                'nominalkenek' => $request->nominalkenek ?? 0,
                'nominalkomisi' => $request->nominalkomisi ?? 0,
                'nominaltol' =>  $request->nominaltol ?? 0,
                'liter' => $request->liter ?? 0,

            ];
            $upahsupir = (new UpahSupir())->processUpdate($upahsupir, $data);
            $upahsupir->position = $this->getPosition($upahsupir, $upahsupir->getTable())->position;
            $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $upahsupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(DestroyUpahSupirRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $upahsupir = (new upahsupir())->processDestroy($id);
            $selected = $this->getPosition($upahsupir, $upahsupir->getTable(), true);
            $upahsupir->position = $selected->position;
            $upahsupir->id = $selected->id;
            $upahsupir->page = ceil($upahsupir->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $upahsupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
            $storedFile = Storage::putFileAs($destinationFolder, $file, $originalFileName);
            $resizedFiles = App::imageResize(storage_path("app/$destinationFolder/"), storage_path("app/$storedFile"), $originalFileName);

            $storedFiles[] = $originalFileName;
        }

        return json_encode($storedFiles);
    }

    private function deleteFiles(UpahSupir $upahsupir)
    {
        $sizeTypes = ['', 'medium_', 'small_'];
        dd('here');;
        $relatedPhotoUpahSupir = [];
        $photoUpahSupir = json_decode($upahsupir->gambar, true);
        if ($photoUpahSupir) {
            foreach ($photoUpahSupir as $path) {
                foreach ($sizeTypes as $sizeType) {
                    $relatedPhotoUpahSupir[] = "upahsupir/$sizeType$path";
                }
            }
            Storage::delete($relatedPhotoUpahSupir);
        }
    }

    public function getImage(string $filename, string $type)
    {
        if (Storage::exists("upahsupir/$type" . '_' . "$filename")) {
            return response()->file(storage_path("app/upahsupir/$type" . '_' . "$filename"));
        } else {
            if (Storage::exists("upahsupir/$filename")) {
                return response()->file(storage_path("app/upahsupir/$filename"));
            } else {
                return response('no-image');
            }
        }
    }
    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $upahSupirs = $decodedResponse['data'];

        // dd($upahSupirs);


        $i = 0;
        foreach ($tarifs as $index => $params) {

            $statusaktif = $params['statusaktif'];
            $statusSistemTon = $params['statussistemton'];
            $statusPenyesuaianHarga = $params['statuspenyesuaianharga'];

            $result = json_decode($statusaktif, true);
            $resultSistemTon = json_decode($statusSistemTon, true);
            $resultPenyesuaianHarga = json_decode($statusPenyesuaianHarga, true);

            $statusaktif = $result['MEMO'];
            $statusSistemTon = $resultSistemTon['MEMO'];
            $statusPenyesuaianHarga = $resultPenyesuaianHarga['MEMO'];


            $tarifs[$i]['statusaktif'] = $statusaktif;
            $tarifs[$i]['statussistemton'] = $statusSistemTon;
            $tarifs[$i]['statuspenyesuaianharga'] = $statusPenyesuaianHarga;


            $i++;
        }

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Parent',
                'index' => 'parent_id',
            ],
            [
                'label' => 'Upah Supir',
                'index' => 'upahsupir_id',
            ],
            [
                'label' => 'Tujuan',
                'index' => 'tujuan',
            ],
            [
                'label' => 'Status Aktif',
                'index' => 'statusaktif',
            ],
            [
                'label' => 'Status Sistem Ton',
                'index' => 'statussistemton',
            ],
            [
                'label' => 'Kota',
                'index' => 'kota_id',
            ],
            [
                'label' => 'Zona',
                'index' => 'zona_id',
            ],
            [
                'label' => 'Tgl Mulai Berlaku',
                'index' => 'tglmulaiberlaku',
            ],
            [
                'label' => 'Status Penyesuaian Harga',
                'index' => 'statuspenyesuaianharga',
            ],
            [
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
        ];

        $this->toExcel('Tarif', $tarifs, $columns);
    }

    public function cekValidasi($id)
    {
        $upahSupir = new UpahSupir();
        $cekdata = $upahSupir->cekValidasi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        }
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
