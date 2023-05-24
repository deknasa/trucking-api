<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupirRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\Request;
use App\Models\Parameter;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

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

    public function cekValidasi($id) {
        $supplier= new Supplier();
        $cekdata=$supplier->cekvalidasihapus($id);
        if ($cekdata['kondisi']==true) {
            $query = DB::table('error')
            ->select(
                DB::raw("ltrim(rtrim(keterangan))+' (".$cekdata['keterangan'].")' as keterangan")
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
    public function store(StoreSupplierRequest $request)
    {
        DB::beginTransaction();

        try {
            $supplier = new Supplier();
            $supplier->namasupplier = $request->namasupplier;
            $supplier->namakontak = $request->namakontak;
            $supplier->alamat = $request->alamat;
            $supplier->kota = $request->kota;
            $supplier->kodepos = $request->kodepos;
            $supplier->notelp1 = $request->notelp1;
            $supplier->notelp2 = $request->notelp2 ?? '';
            $supplier->email = $request->email;
            $supplier->statusaktif = $request->statusaktif;
            $supplier->web = $request->web;
            $supplier->namapemilik = $request->namapemilik;
            $supplier->jenisusaha = $request->jenisusaha;
            // $supplier->top = $request->top;
            $supplier->bank = $request->bank;
            $supplier->coa = $request->coa;
            $supplier->rekeningbank = $request->rekeningbank;
            $supplier->namarekening = $request->namarekening;
            $supplier->jabatan = $request->jabatan;
            $supplier->statusdaftarharga = $request->statusdaftarharga;
            $supplier->kategoriusaha = $request->kategoriusaha;
            $supplier->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($supplier->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($supplier->getTable()),
                    'postingdari' => 'ENTRY SUPPLIER',
                    'idtrans' => $supplier->id,
                    'nobuktitrans' => $supplier->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $supplier->toArray(),
                    'modifiedby' => $supplier->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($supplier, $supplier->getTable());
            $supplier->position = $selected->position;
            $supplier->page = ceil($supplier->position / ($request->limit ?? 10));

            return response([
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
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        DB::beginTransaction();

        try {
            $supplier->namasupplier = $request->namasupplier;
            $supplier->namakontak = $request->namakontak;
            $supplier->alamat = $request->alamat;
            $supplier->kota = $request->kota;
            $supplier->kodepos = $request->kodepos;
            $supplier->notelp1 = $request->notelp1;
            $supplier->notelp2 = $request->notelp2 ?? '';
            $supplier->email = $request->email;
            $supplier->statusaktif = $request->statusaktif;
            $supplier->web = $request->web;
            $supplier->namapemilik = $request->namapemilik;
            $supplier->jenisusaha = $request->jenisusaha;
            // $supplier->top = $request->top;
            $supplier->bank = $request->bank;
            $supplier->coa = $request->coa;
            $supplier->rekeningbank = $request->rekeningbank;
            $supplier->namarekening = $request->namarekening;
            $supplier->jabatan = $request->jabatan;
            $supplier->statusdaftarharga = $request->statusdaftarharga;
            $supplier->kategoriusaha = $request->kategoriusaha;
            $supplier->modifiedby = auth('api')->user()->name;

            if ($supplier->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($supplier->getTable()),
                    'postingdari' => 'EDIT SUPPLIER',
                    'idtrans' => $supplier->id,
                    'nobuktitrans' => $supplier->id,
                    'aksi' => 'EDIT',
                    'datajson' => $supplier->toArray(),
                    'modifiedby' => $supplier->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);
            }
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($supplier, $supplier->getTable());
            $supplier->position = $selected->position;
            $supplier->page = ceil($supplier->position / ($request->limit ?? 10));

            return response([
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $supplier = new Supplier();
        $supplier = $supplier->lockAndDestroy($id);

        if ($supplier) {
            $logTrail = [
                'namatabel' => strtoupper($supplier->getTable()),
                'postingdari' => 'DELETE SUPPLIER',
                'idtrans' => $supplier->id,
                'nobuktitrans' => $supplier->id,
                'aksi' => 'DELETE',
                'datajson' => $supplier->toArray(),
                'modifiedby' => $supplier->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($supplier, $supplier->getTable(), true);
            $supplier->position = $selected->position;
            $supplier->id = $selected->id;
            $supplier->page = ceil($supplier->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $supplier
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $parameters = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
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

        $this->toExcel('Parameter', $parameters, $columns);
    }
}
