<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\DestroySupplierRequest;
use App\Http\Requests\RangeExportReportRequest;
use App\Http\Requests\UpdateSupirRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\Request;
use App\Models\Parameter;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $supplier = new Supplier();

        $rows = $supplier->get();

        $baseUrl = asset('');

        return response([
            'data' => $supplier->get(),
            'attributes' => [
                'totalRows' => $supplier->totalRows,
                'totalPages' => $supplier->totalPages
            ]
        ]);
    }

    public function cekValidasi($id)
    {
        $supplier = new Supplier();
        $cekdata = $supplier->cekvalidasihapus($id);
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
        $supplier = new Supplier();
        return response([
            'status' => true,
            'data' => $supplier->default()
        ]);
    }

    public function show($id)
    {

        $data = Supplier::find($id);
        // $detail = ServiceInDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);

        // return response([
        //     'status' => true,
        //     'data' => $supplier
        // ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreSupplierRequest $request) : JsonResponse
    {
        DB::beginTransaction();

        try {
            $supplier = (new Supplier())->processStore($request->all());
            $supplier->position = $this->getPosition($supplier, $supplier->getTable())->position;
            $supplier->page = ceil($supplier->position / ($request->limit ?? 10));

            DB::commit();   

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $supplier
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName 
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier) : JsonResponse
    {
        DB::beginTransaction();

        try {
            $supplier = (new Supplier())->processUpdate($supplier, $request->all());
            $supplier->position = $this->getPosition($supplier, $supplier->getTable())->position;
            $supplier->page = ceil($supplier->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil diubah',
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroySupplierRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $supplier = (new Supplier())->processDestroy($id);
            $selected = $this->getPosition($supplier, $supplier->getTable(), true);
            $supplier->position = $selected->position;
            $supplier->id = $selected->id;
            $supplier->page = ceil($supplier->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('supplier')->getColumns();

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
            $suppliers = $decodedResponse['data'];

            $judulLaporan = $suppliers[0]['judulLaporan'];

            $i = 0;
            foreach ($suppliers as $index => $params) {

                $statusaktif = $params['statusaktif'];
                $statusDaftarHarga = $params['statusdaftarharga'];

                $result = json_decode($statusaktif, true);
                $resultDaftarHarga = json_decode($statusDaftarHarga, true);

                $statusaktif = $result['MEMO'];
                $statusDaftarHarga = $resultDaftarHarga['MEMO'];

                $suppliers[$i]['statusaktif'] = $statusaktif;
                $suppliers[$i]['statusdaftarharga'] = $statusDaftarHarga;
                $i++;
            }

            $columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'Nama Supplier',
                    'index' => 'namasupplier',
                ],
                [
                    'label' => 'Nama Kontak',
                    'index' => 'namakontak',
                ],
                [
                    'label' => 'Alamat',
                    'index' => 'alamat',
                ],
                [
                    'label' => 'Kota',
                    'index' => 'kota',
                ],
                [
                    'label' => 'Kode Pos',
                    'index' => 'kodepos',
                ],
                [
                    'label' => 'No Telp 1',
                    'index' => 'notelp1',
                ],
                [
                    'label' => 'No Telp 2',
                    'index' => 'notelp2',
                ],
                [
                    'label' => 'Email',
                    'index' => 'email',
                ],
                [
                    'label' => 'Status Aktif',
                    'index' => 'statusaktif',
                ],
                [
                    'label' => 'Web',
                    'index' => 'web',
                ],
                [
                    'label' => 'Nama Pemilik',
                    'index' => 'namapemilik',
                ],
                [
                    'label' => 'Jenis Usaha',
                    'index' => 'jenisusaha',
                ],
                // [
                //     'label' => 'TOP',
                //     'index' => 'top',
                // ],
                [
                    'label' => 'Bank',
                    'index' => 'bank',
                ],
                [
                    'label' => 'Rekening Bank',
                    'index' => 'rekeningbank',
                ],
                [
                    'label' => 'Nama Rekening',
                    'index' => 'namarekening',
                ],
                [
                    'label' => 'Jabatan',
                    'index' => 'jabatan',
                ],
                [
                    'label' => 'Status Daftar Harga',
                    'index' => 'statusdaftarharga',
                ],
                [
                    'label' => 'Kategori Usaha',
                    'index' => 'kategoriusaha',
                ],

            ];

            $this->toExcel($judulLaporan, $suppliers, $columns);
        }

    }
}
