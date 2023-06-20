<?php

namespace App\Http\Controllers\Api;

use App\Models\AlatBayar;
use App\Models\Bank;
use App\Http\Requests\StoreAlatBayarRequest;
use App\Http\Requests\UpdateAlatBayarRequest;
use App\Http\Requests\DestroyAlatBayarRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AlatBayarController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $alatbayar = new AlatBayar();

        return response([
            'data' => $alatbayar->get(),
            'attributes' => [
                'totalRows' => $alatbayar->totalRows,
                'totalPages' => $alatbayar->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $alatBayar = new AlatBayar();
        $cekdata = $alatBayar->cekvalidasihapus($id);
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
        $alatBayar = new AlatBayar();
        return response([
            'status' => true,
            'data' => $alatBayar->default()
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StoreAlatBayarRequest $request): JsonResponse
    {
        DB::beginTransaction();
        // dd($request->all());
        try {
            $data = [
                'kodealatbayar' => $request->kodealatbayar,
                'namaalatbayar' => $request->namaalatbayar,
                'keterangan' => $request->keterangan ?? '',
                'statuslangsungcair' => $request->statuslangsungcair,
                'statusdefault' => $request->statusdefault,
                'bank_id' => $request->bank_id,
                'coa' => $request->coa ?? '',
                'statusaktif' => $request->statusaktif,
            ];

            $alatbayar = (new AlatBayar())->processStore($data);
            $alatbayar->position = $this->getPosition($alatbayar, $alatbayar->getTable())->position;
            $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $alatbayar
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $data = AlatBayar::find($id);
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateAlatBayarRequest $request, AlatBayar $alatbayar): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'kodealatbayar' => $request->kodealatbayar,
                'namaalatbayar' => $request->namaalatbayar,
                'keterangan' => $request->keterangan ?? '',
                'statuslangsungcair' => $request->statuslangsungcair,
                'statusdefault' => $request->statusdefault,
                'bank_id' => $request->bank_id,
                'coa' => $request->coa ?? '',
                'statusaktif' => $request->statusaktif,
            ];
            $alatbayar = (new AlatBayar())->processUpdate($alatbayar, $data);
            $alatbayar->position = $this->getPosition($alatbayar, $alatbayar->getTable())->position;
            $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $alatbayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyAlatBayarRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $alatbayar = (new AlatBayar())->processDestroy($id);
            $selected = $this->getPosition($alatbayar, $alatbayar->getTable(), true);
            $alatbayar->position = $selected->position;
            $alatbayar->id = $selected->id;
            $alatbayar->page = ceil($alatbayar->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $alatbayar
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'langsungcair' => Parameter::where(['grp' => 'status langsung cair'])->get(),
            'statusdefault' => Parameter::where(['grp' => 'status default'])->get(),
            'statusaktif' => Parameter::where(['grp' => 'status aktif'])->get(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('alatbayar')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

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
            $alatbayars = $decodedResponse['data'];

            $judulLaporan = $alatbayars[0]['judulLaporan'];

            $i = 0;
            foreach ($alatbayars as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusLangsungCair = $params['statuslangsungcair'];
                $statusDefault = $params['statusdefault'];

                $result = json_decode($statusaktif, true);
                $resultLangsungCair = json_decode($statusLangsungCair, true);
                $resultDefault = json_decode($statusDefault, true);

                $statusaktif = $result['MEMO'];
                $statusLangsungCair = $resultLangsungCair['MEMO'];
                $statusDefault = $resultDefault['MEMO'];


                $alatbayars[$i]['statusaktif'] = $statusaktif;
                $alatbayars[$i]['statuslangsungcair'] = $statusLangsungCair;
                $alatbayars[$i]['statusdefault'] = $statusDefault;


                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Alat Bayar',
                    'index' => 'kodealatbayar',
                ],
                [
                    'label' => 'Nama Alat Bayar',
                    'index' => 'namaalatbayar',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status Langsung Cair',
                    'index' => 'statuslangsungcair',
                ],
                [
                    'label' => 'Status Default',
                    'index' => 'statusdefault',
                ],
                [
                    'label' => 'Bank',
                    'index' => 'bank',
                ],
                [
                    'label' => 'Status AKtif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $alatbayars, $columns);
        }
    }
}
