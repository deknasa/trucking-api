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
use App\Http\Requests\ApprovalKaryawanRequest;
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
     * @Keterangan TAMPILKAN DATA
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreKotaRequest $request)
    {

        DB::beginTransaction();

        try {
            $data = [
                'kodekota' => $request->kodekota,
                'keterangan' => $request->keterangan ?? '',
                'zona_id' => $request->zona_id,
                'statusaktif' => $request->statusaktif
            ];

            $kota = (new Kota())->processStore($data);
            $kota->position = $this->getPosition($kota, $kota->getTable())->position;
            if ($request->limit==0) {
                $kota->page = ceil($kota->position / (10));
            } else {
                $kota->page = ceil($kota->position / ($request->limit ?? 10));
            }

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
     * @Keterangan EDIT DATA
     */
    public function update(UpdateKotaRequest $request, Kota $kota)
    {

        DB::beginTransaction();

        try {
            $data = [
                'id' => $request->id,
                'kodekota' => $request->kodekota,
                'keterangan' => $request->keterangan ?? '',
                'zona_id' => $request->zona_id,
                'statusaktif' => $request->statusaktif
            ];

            $kota = (new Kota())->processUpdate($kota, $data);
            $kota->position = $this->getPosition($kota, $kota->getTable())->position;
            if ($request->limit==0) {
                $kota->page = ceil($kota->position / (10));
            } else {
                $kota->page = ceil($kota->position / ($request->limit ?? 10));
            }

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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyKotaRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $kota = (new Kota())->processDestroy($id);
            $selected = $this->getPosition($kota, $kota->getTable(), true);
            $kota->position = $selected->position;
            $kota->id = $selected->id;
            if ($request->limit==0) {
                $kota->page = ceil($kota->position / (10));
            } else {
                $kota->page = ceil($kota->position / ($request->limit ?? 10));
            }

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
    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }


    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
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
    /**
     * @ClassName 
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Kota())->processApprovalnonaktif($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
