<?php

namespace App\Http\Controllers\Api;

use App\Models\Merk;
use App\Http\Requests\StoreMerkRequest;
use App\Http\Requests\UpdateMerkRequest;
use App\Http\Requests\DestroyMerkRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class MerkController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $merk = new Merk();

        return response([
            'data' => $merk->get(),
            'attributes' => [
                'totalRows' => $merk->totalRows,
                'totalPages' => $merk->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $merk = new Merk();
        $cekdata = $merk->cekvalidasihapus($id);
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
        $merk = new Merk();
        return response([
            'status' => true,
            'data' => $merk->default()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreMerkRequest $request) : JsonResponse
    {
        DB::beginTransaction();

        try {
            $merk = (new Merk())->processStore($request->all());
            $merk->position = $this->getPosition($merk, $merk->getTable())->position;
            $merk->page = ceil($merk->position / ($request->limit ?? 10));

            DB::commit();   

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $merk
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Merk $merk)
    {
        return response([
            'status' => true,
            'data' => $merk
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateMerkRequest $request, Merk $merk) : JsonResponse
    {
        DB::beginTransaction();
        try {

            $merk = (new Merk())->processUpdate($merk, $request->all());
            $merk->position = $this->getPosition($merk, $merk->getTable())->position;
            $merk->page = ceil($merk->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $merk
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyMerkRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $merk = (new Merk())->processDestroy($id);
            $selected = $this->getPosition($merk, $merk->getTable(), true);
            $merk->position = $selected->position;
            $merk->id = $selected->id;
            $merk->page = ceil($merk->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $merk
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('merk')->getColumns();

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
        ];

        return response([
            'data' => $data
        ]);
    }
    public function export(RangeExportReportRequest $request)
    {

        if (request()->cekExport) {
            return response([
                'status' => true,
            ]);
        } else {

            $response = $this->index();
            $decodedResponse = json_decode($response->content(), true);
            $merks = $decodedResponse['data'];

            $judulLaporan = $merks[0]['judulLaporan'];

            $i = 0;
            foreach ($merks as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $merks[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Merk',
                    'index' => 'kodemerk',
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

            $this->toExcel($judulLaporan, $merks, $columns);
        }
    }
}
