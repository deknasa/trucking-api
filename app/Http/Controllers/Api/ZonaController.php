<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Models\Zona;
use App\Models\Error;
use App\Models\MyModel;

use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreZonaRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\UpdateZonaRequest;
use App\Http\Requests\DestroyZonaRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalKaryawanRequest;
use App\Http\Requests\RangeExportReportRequest;

class ZonaController extends Controller
{

    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $zona = new Zona();

        return response([
            'data' => $zona->get(),
            'attributes' => [
                'totalRows' => $zona->totalRows,
                'totalPages' => $zona->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $zona = new Zona();
        $dataMaster = $zona->where('id',$id)->first();
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $user = auth('api')->user()->name;
        $useredit = $dataMaster->editing_by ?? '';
        $aksi = request()->aksi ?? '';
        
        $cekdata = $zona->cekvalidasihapus($id);
        if ($cekdata['kondisi'] == true && $aksi != 'EDIT') {
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
        } else  if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('BATAS WAKTU EDIT MASTER');
            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($dataMaster->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('zona', $id, $aksi);
                }
                
                $data = [
                    'status' => false,
                    'message' => '',
                    'errors' => '',
                    'kondisi' => false,
                    'editblok' => false,
                ];
                
                // return response($data);
            } else {
                
                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'Data <b>' . $dataMaster->kodezona . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                
                $data = [
                    'status' => true,
                    'message' => ["keterangan"=>$keterror],
                    'errors' => '',
                    'kondisi' => true,
                    'editblok' => true,
                ];
                
                return response($data);
            }
        } else {
            (new MyModel())->updateEditingBy('zona', $id, $aksi);

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
        $zona = new Zona();
        return response([
            'status' => true,
            'data' => $zona->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreZonaRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'zona' => $request->zona,
                'statusaktif' => $request->statusaktif,
                'keterangan' => $request->keterangan ?? ''
            ];
            $zona = (new Zona())->processStore($data);
            $zona->position = $this->getPosition($zona, $zona->getTable())->position;
            if ($request->limit == 0) {
                $zona->page = ceil($zona->position / (10));
            } else {
                $zona->page = ceil($zona->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $zona
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Zona $zona)
    {
        return response([
            'status' => true,
            'data' => $zona
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateZonaRequest $request, Zona $zona)
    {
        DB::beginTransaction();
        try {
            $data = [
                'zona' => $request->zona,
                'statusaktif' => $request->statusaktif,
                'keterangan' => $request->keterangan ?? ''
            ];
            $zona = (new Zona())->processUpdate($zona, $data);
            $zona->position = $this->getPosition($zona, $zona->getTable())->position;
            if ($request->limit == 0) {
                $zona->page = ceil($zona->position / (10));
            } else {
                $zona->page = ceil($zona->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $zona
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
    public function destroy(DestroyZonaRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $zona = (new Zona())->processDestroy($id);
            $selected = $this->getPosition($zona, $zona->getTable(), true);
            $zona->position = $selected->position;
            $zona->id = $selected->id;
            if ($request->limit == 0) {
                $zona->page = ceil($zona->position / (10));
            } else {
                $zona->page = ceil($zona->position / ($request->limit ?? 10));
            }

            DB::commit();

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $zona
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('zona')->getColumns();

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


    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export(
        RangeExportReportRequest $request
    ) {
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
            $zonas = $decodedResponse['data'];

            $judulLaporan = $zonas[0]['judulLaporan'];

            $i = 0;
            foreach ($zonas as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];


                $zonas[$i]['statusaktif'] = $statusaktif;


                $i++;
            }
            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Zona',
                    'index' => 'zona',
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

            $this->toExcel($judulLaporan, $zonas, $columns);
        }
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
     * @Keterangan APRROVAL NON AKTIF
     */
    public function approvalnonaktif(ApprovalKaryawanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'Id' => $request->Id,
            ];
            (new Zona())->processApprovalnonaktif($data);

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
