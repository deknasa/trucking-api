<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Requests\DestroyKategoriRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\SubKelompok;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $kategori = new Kategori();

        return response([
            'data' => $kategori->get(),
            'attributes' => [
                'totalRows' => $kategori->totalRows,
                'totalPages' => $kategori->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $kategori = new Kategori();
        $cekdata = $kategori->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
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
    public function default()
    {
        $kategori = new Kategori();
        return response([
            'status' => true,
            'data' => $kategori->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreKategoriRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'kodekategori' => $request->kodekategori ?? '',
                'keterangan' => $request->keterangan ?? '',
                'subkelompok_id' => $request->subkelompok_id,
                'statusaktif' => $request->statusaktif
            ];
            $kategori = (new Kategori())->processStore($data);
            $kategori->position = $this->getPosition($kategori, $kategori->getTable())->position;
            if ($request->limit==0) {
                $kategori->page = ceil($kategori->position / (10));
            } else {
                $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kategori
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $kategori = new Kategori();
        return response([
            'status' => true,
            'data' => $kategori->find($id)
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateKategoriRequest $request, Kategori $kategori): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodekategori' => $request->kodekategori ?? '',
                'keterangan' => $request->keterangan ?? '',
                'subkelompok_id' => $request->subkelompok_id,
                'statusaktif' => $request->statusaktif
            ];
            $kategori = (new Kategori())->processUpdate($kategori, $data);
            $kategori->position = $this->getPosition($kategori, $kategori->getTable())->position;
            if ($request->limit==0) {
                $kategori->page = ceil($kategori->position / (10));
            } else {
                $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyKategoriRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $kategori = (new Kategori())->processDestroy($id);
            $selected = $this->getPosition($kategori, $kategori->getTable(), true);
            $kategori->position = $selected->position;
            $kategori->id = $selected->id;
            if ($request->limit==0) {
                $kategori->page = ceil($kategori->position / (10));
            } else {
                $kategori->page = ceil($kategori->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $kategori
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kategori')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'subkelompok' => SubKelompok::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     */
    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {

            if (request()->offset == "-1" && request()->limit == '1') {
                
                return response([
                    'errors' => [
                        "export" => app(ErrorController::class)->geterror('DTA')->keterangan
                    ],
                    'status' => false,
                    'message' => "The given data was invalid."
                ], 422);
            } else {
                return response([
                    'status' => true,
                ]);
            }
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $kategoris = $decodedResponse['data'];

            $judulLaporan = $kategoris[0]['judulLaporan'];

            $i = 0;
            foreach ($kategoris as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $kategoris[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode kategori',
                    'index' => 'kodekategori',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $kategoris, $columns);
        }
    }
}
