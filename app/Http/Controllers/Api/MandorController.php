<?php

namespace App\Http\Controllers\Api;

use App\Models\Mandor;
use App\Http\Requests\StoreMandorRequest;
use App\Http\Requests\UpdateMandorRequest;
use App\Http\Requests\DestroyMandorRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalKaryawanRequest;
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
     * @Keterangan TAMPILKAN DATA
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

    public function cekValidasi(Request $request, $id)
    {
        $mandor = new Mandor();
        $server = '';
        $cekdata = $mandor->cekvalidasihapus($id);

        $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();

        if ($cekdata['kondisi'] == true) {
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $server = ' tas';
            }
            goto selesai;
        }

        $data['tas_id'] = $id;
        if ($cekStatusPostingTnl->text == 'POSTING TNL') {
            $data = [
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
            ];
            $cektnl = $this->CekValidasiToTnl("mandor/" . $id . "/cekValidasi", $data);
            return response($cektnl['data']);
        }
        selesai:

        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . "$server)' as keterangan")
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
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreMandorRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'tas_id' => $request->tas_id,
                'user_id' => $request->user_id
            ];
            $mandor = (new Mandor())->processStore($data);
            $mandor->position = $this->getPosition($mandor, $mandor->getTable())->position;
            if ($request->limit == 0) {
                $mandor->page = ceil($mandor->position / (10));
            } else {
                $mandor->page = ceil($mandor->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $mandor->id;

            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('mandor', 'add', $data);
            }

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
            'data' => $mandor->findAll($mandor->id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateMandorRequest $request, Mandor $mandor)
    {
        DB::beginTransaction();
        try {
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'user_id' => $request->user_id
            ];
            $mandor = (new Mandor())->processUpdate($mandor, $data);
            $mandor->position = $this->getPosition($mandor, $mandor->getTable())->position;
            if ($request->limit == 0) {
                $mandor->page = ceil($mandor->position / (10));
            } else {
                $mandor->page = ceil($mandor->position / ($request->limit ?? 10));
            }

            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            $data['tas_id'] = $mandor->id;
            
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('mandor', 'edit', $data);
            }
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
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyMandorRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $mandor = (new Mandor())->processDestroy($id);
            $selected = $this->getPosition($mandor, $mandor->getTable(), true);
            $mandor->position = $selected->position;
            $mandor->id = $selected->id;
            if ($request->limit == 0) {
                $mandor->page = ceil($mandor->position / (10));
            } else {
                $mandor->page = ceil($mandor->position / ($request->limit ?? 10));
            }
            $cekStatusPostingTnl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING TNL')->where('default', 'YA')->first();
            
            $data = [
                'namamandor' => $request->namamandor,
                'keterangan' => $request->keterangan ?? '',
                'statusaktif' => $request->statusaktif,
                "accessTokenTnl" => $request->accessTokenTnl ?? '',
                'user_id' => $request->user_id,
            ];
            $data['tas_id'] = $id;
            if ($cekStatusPostingTnl->text == 'POSTING TNL') {
                $this->saveToTnl('mandor', 'delete', $data);
            }
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
            (new Mandor())->processApprovalnonaktif($data);

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
