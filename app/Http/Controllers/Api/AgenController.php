<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotDeletableModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyAgenRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\Agen;
use App\Http\Requests\StoreAgenRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAgenRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class AgenController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $agen = new Agen();

        return response([
            'data' => $agen->get(),
            'attributes' => [
                'totalRows' => $agen->totalRows,
                'totalPages' => $agen->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $agen = new Agen();
        $cekdata = $agen->cekvalidasihapus($id);
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

        $agen = new Agen();
        return response([
            'status' => true,
            'data' => $agen->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAgenRequest $request): JsonResponse
    {
        $data = [
            'id' => $request->id,
            'kodeagen' => $request->kodeagen,
            'namaagen' => $request->namaagen,
            'keterangan' => $request->keterangan,
            'statusaktif' => $request->statusaktif,
            'namaperusahaan' => $request->namaperusahaan,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'contactperson' => $request->contactperson,
            'top' => $request->top,
            'statustas' => $request->statustas,
        ];
        DB::beginTransaction();

        try {
            $agen = (new Agen())->processStore($data);
            $agen->position = $this->getPosition($agen, $agen->getTable())->position;
            $agen->page = ceil($agen->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $agen
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $agen = Agen::from(DB::raw("agen with (readuncommitted)"))->where('agen.id', $id)->first();
        return response([
            'status' => true,
            'data' => $agen
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAgenRequest $request, Agen $agen): JsonResponse
    {

        $data = [
            'id' => $request->id,
            'kodeagen' => $request->kodeagen,
            'namaagen' => $request->namaagen,
            'keterangan' => $request->keterangan,
            'statusaktif' => $request->statusaktif,
            'namaperusahaan' => $request->namaperusahaan,
            'alamat' => $request->alamat,
            'notelp' => $request->notelp,
            'contactperson' => $request->contactperson,
            'top' => $request->top,
            'statustas' => $request->statustas,
        ];
        DB::beginTransaction();

        try {
            $agen = (new Agen())->processUpdate($agen, $data);
            $agen->position = $this->getPosition($agen, $agen->getTable())->position;
            $agen->page = ceil($agen->position / ($request->limit ?? 10));


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $agen
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function destroy(DestroyAgenRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $agen = (new Agen())->processDestroy($id);
            $selected = $this->getPosition($agen, $agen->getTable(), true);
            $agen->position = $selected->position;
            $agen->id = $selected->id;
            $agen->page = ceil($agen->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $agen
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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
            $agens = $decodedResponse['data'];

            $judulLaporan = $agens[0]['judulLaporan'];

            $i = 0;
            foreach ($agens as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusApproval = $params['statusapproval'];
                $statusTas = $params['statustas'];

                $result = json_decode($statusaktif, true);
                $resultApproval = json_decode($statusApproval, true);
                $resultTas = json_decode($statusTas, true);

                $statusaktif = $result['MEMO'];
                $statusApproval = $resultApproval['MEMO'];
                $statusTas = $resultTas['MEMO'];

                $agens[$i]['statusaktif'] = $statusaktif;
                $agens[$i]['statusapproval'] = $statusApproval;
                $agens[$i]['statustas'] = $statusTas;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Agen (EMKL)',
                    'index' => 'kodeagen',
                ],
                [
                    'label' => 'Nama Agen (EMKL)',
                    'index' => 'namaagen',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Nama Perusahaan',
                    'index' => 'namaperusahaan',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'No Telp/HP',
                    'index' => 'notelp',
                ],
                [
                    'label' => 'Nama Kontak',
                    'index' => 'contactperson',
                ],
                [
                    'label' => 'Status Pembayaran (TOP)',
                    'index' => 'top',
                ],
                [
                    'label' => 'Status Approval',
                    'index' => 'statusapproval',
                ],
                [
                    'label' => 'User approval',
                    'index' => 'userapproval',
                ],
                [
                    'label' => 'Tgl Approval',
                    'index' => 'tglapproval',
                ],
                [
                    'label' => 'Status Tas',
                    'index' => 'statustas',
                ],
                // [
                //     'label' => 'Jenis Emkl',
                //     'index' => 'jenisemkl',
                // ],
            ];

            $this->toExcel($judulLaporan, $agens, $columns);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('agen')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     */
    public function approval(Agen $agen)
    {
        DB::beginTransaction();

        try {
            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($agen->statusapproval == $statusApproval->id) {
                $agen->statusapproval = $statusNonApproval->id;
            } else {
                $agen->statusapproval = $statusApproval->id;
            }

            $agen->tglapproval = date('Y-m-d', time());
            $agen->userapproval = auth('api')->user()->name;

            if ($agen->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($agen->getTable()),
                    'postingdari' => 'UN/APPROVE AGEN',
                    'idtrans' => $agen->id,
                    'nobuktitrans' => $agen->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $agen->toArray(),
                    'modifiedby' => $agen->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $agen
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
