<?php

namespace App\Http\Controllers\Api;

use App\Models\BankPelanggan;
use App\Http\Requests\StoreBankPelangganRequest;
use App\Http\Requests\UpdateBankPelangganRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyBankPelangganRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Models\LogTrail;
use App\Models\Parameter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class BankPelangganController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $bankpelanggan = new BankPelanggan();

        return response([
            'data' => $bankpelanggan->get(),
            'attributes' => [
                'totalRows' => $bankpelanggan->totalRows,
                'totalPages' => $bankpelanggan->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $bankPelanggan = new BankPelanggan();
        $cekdata = $bankPelanggan->cekvalidasihapus($id);
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
        $bankPelanggan = new BankPelanggan();
        return response([
            'status' => true,
            'data' => $bankPelanggan->default()
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreBankPelangganRequest $request) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $bankpelanggan = (new BankPelanggan())->processStore($request->all());
            $bankpelanggan->position = $this->getPosition($bankpelanggan, $bankpelanggan->getTable())->position;
            $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $bankpelanggan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(BankPelanggan $bankpelanggan)
    {
        return response([
            'status' => true,
            'data' => $bankpelanggan
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateBankPelangganRequest $request, BankPelanggan $bankpelanggan) : JsonResponse
    {
        DB::beginTransaction();
        try {

            $bankpelanggan = (new BankPelanggan())->processUpdate($bankpelanggan, $request->all());
            $bankpelanggan->position = $this->getPosition($bankpelanggan, $bankpelanggan->getTable())->position;
            $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));

            DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $bankpelanggan
                ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyBankPelangganRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $bankpelanggan = (new BankPelanggan())->processDestroy($id);
            $selected = $this->getPosition($bankpelanggan, $bankpelanggan->getTable(), true);
            $bankpelanggan->position = $selected->position;
            $bankpelanggan->id = $selected->id;
            $bankpelanggan->page = ceil($bankpelanggan->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $bankpelanggan
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
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


    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('bankpelanggan')->getColumns();

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
            $bankpelanggans = $decodedResponse['data'];

            $judulLaporan = $bankpelanggans[0]['judulLaporan'];

            $i = 0;
            foreach ($bankpelanggans as $index => $params) {

                $statusaktif = $params['statusaktif'];

                $result = json_decode($statusaktif, true);

                $statusaktif = $result['MEMO'];

                $bankpelanggans[$i]['statusaktif'] = $statusaktif;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Kode Bank',
                    'index' => 'kodebank',
                ],
                [
                    'label' => 'Nama Bank',
                    'index' => 'namabank',
                ],
                [
                    'label' => 'Keterangan',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'Status AKtif',
                    'index' => 'statusaktif',
                ],
            ];

            $this->toExcel($judulLaporan, $bankpelanggans, $columns);
        }
    }
}
