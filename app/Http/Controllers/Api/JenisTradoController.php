<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisTrado;
use App\Http\Requests\StoreJenisTradoRequest;
use App\Http\Requests\UpdateJenisTradoRequest;
use App\Http\Requests\DestroyJenisTradoRequest;
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

class JenisTradoController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $jenistrado = new JenisTrado();

        return response([
            'data' => $jenistrado->get(),
            'attributes' => [
                'totalRows' => $jenistrado->totalRows,
                'totalPages' => $jenistrado->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $jenisTrado = new JenisTrado();
        $cekdata = $jenisTrado->cekvalidasihapus($id);
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

        $jenisTrado = new JenisTrado();
        return response([
            'status' => true,
            'data' => $jenisTrado->default(),
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreJenisTradoRequest $request)
    {
        DB::beginTransaction();

        try {
            $jenistrado = (new JenisTrado())->processStore($request->all());
            $jenistrado->position = $this->getPosition($jenistrado, $jenistrado->getTable())->position;
            $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $jenistrado
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(jenistrado $jenistrado)
    {
        return response([
            'status' => true,
            'data' => $jenistrado
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateJenisTradoRequest $request, JenisTrado $jenistrado)
    {
        DB::beginTransaction();
        try {
            $jenistrado = (new JenisTrado())->processUpdate($jenistrado, $request->all());
            $jenistrado->position = $this->getPosition($jenistrado, $jenistrado->getTable())->position;
            $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));
            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $jenistrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }


    /**
     * @ClassName 
     */
    public function destroy(DestroyJenisTradoRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $jenistrado = (new JenisTrado())->processDestroy($id);
            $selected = $this->getPosition($jenistrado, $jenistrado->getTable(), true);
            $jenistrado->position = $selected->position;
            $jenistrado->id = $selected->id;
            $jenistrado->page = ceil($jenistrado->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $jenistrado
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('jenistrado')->getColumns();

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
            $jenisTrados = $decodedResponse['data'];

            $judulLaporan = $jenisTrados[0]['judulLaporan'];

            $i = 0;
            foreach ($jenisTrados as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $jenisTrados[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Jenis Trado',
                    'index' => 'kodejenistrado',
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

            $this->toExcel($judulLaporan, $jenisTrados, $columns);
        }
    }
}
