<?php

namespace App\Http\Controllers\Api;

use App\Models\Mandor;
use App\Http\Requests\StoreMandorRequest;
use App\Http\Requests\UpdateMandorRequest;
use App\Http\Requests\DestroyMandorRequest;
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

class MandorController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $mandor = new Mandor();

        return response([
            'data' => $mandor->get(),
            'attributes' => [
                'totalRows' => $mandor->totalRows,
                'totalPages' => $mandor->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $mandor = new Mandor();
        $cekdata = $mandor->cekvalidasihapus($id);
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

        $mandor = new Mandor();
        return response([
            'status' => true,
            'data' => $mandor->default(),
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreMandorRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $mandor = (new Mandor())->processStore($data);
            $mandor->position = $this->getPosition($mandor, $mandor->getTable())->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $mandor
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Mandor $mandor)
    {
        return response([
            'status' => true,
            'data' => $mandor
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateMandorRequest $request, Mandor $mandor)
    {
        DB::beginTransaction();
        try {
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif
            ];
            $mandor = (new Mandor())->processUpdate($mandor, $data);
            $mandor->position = $this->getPosition($mandor, $mandor->getTable())->position;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $mandor
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyMandorRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $mandor = (new Mandor())->processDestroy($id);
            $selected = $this->getPosition($mandor, $mandor->getTable(), true);
            $mandor->position = $selected->position;
            $mandor->id = $selected->id;
            $mandor->page = ceil($mandor->position / ($request->limit ?? 10));

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $mandor
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('mandor')->getColumns();

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
            $mandors = $decodedResponse['data'];

            $judulLaporan = $mandors[0]['judulLaporan'];


            $i = 0;
            foreach ($mandors as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $mandors[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Mandor',
                    'index' => 'namamandor',
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

            $this->toExcel($judulLaporan, $mandors, $columns);
        }
    }
}
