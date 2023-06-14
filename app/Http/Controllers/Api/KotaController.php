<?php

namespace App\Http\Controllers\Api;

use App\Models\Kota;
use App\Http\Requests\StoreKotaRequest;
use App\Http\Requests\UpdateKotaRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\DestroyKotaRequest;
use App\Models\Parameter;
use App\Models\Zona;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use Carbon\Carbon;
use Hamcrest\Type\IsDouble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class KotaController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $kota = new Kota();

        return response([
            'data' => $kota->get(),
            'attributes' => [
                'totalRows' => $kota->totalRows,
                'totalPages' => $kota->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $kota = new Kota();
        $cekdata = $kota->cekvalidasihapus($id);
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
        $kota = new Kota();
        return response([
            'status' => true,
            'data' => $kota->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreKotaRequest $request)
    {

        DB::beginTransaction();

        try {
            $kota = (new Kota())->processStore($request->all());
            $kota->position = $this->getPosition($kota, $kota->getTable())->position;
            $kota->page = ceil($kota->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $kota
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = Kota::findAll($id);
        // dd($data);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateKotaRequest $request, Kota $kota)
    {

        DB::beginTransaction();

        try {

            $kota = (new Kota())->processUpdate($kota, $request->all());
            $kota->position = $this->getPosition($kota, $kota->getTable())->position;
            $kota->page = ceil($kota->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $kota
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyKotaRequest $request, $id)
    {
        DB::beginTransaction();
        try{
            $kota = (new Kota())->processDestroy($id);
            $selected = $this->getPosition($kota, $kota->getTable(), true);
            $kota->position = $selected->position;
            $kota->id = $selected->id;
            $kota->page = ceil($kota->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $kota
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kota')->getColumns();

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
            'zona' => Zona::all(),
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
            $kotas = $decodedResponse['data'];

            $judulLaporan = $kotas[0]['judulLaporan'];

            $i = 0;
            foreach ($kotas as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $kotas[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Kota',
                    'index' => 'kodekota',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Zona',
                    'index' => 'zona_id',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $kotas, $columns);
        }
    }
}
