<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupirRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierController extends Controller
{

    /**
     * @ClassName 
     */
    public function index()
    {
        $params = [
            'offset' => request()->offset ?? ((request()->page - 1) * request()->limit),
            'limit' => request()->limit ?? 10,
            'filters' => json_decode(request()->filters, true) ?? [],
            'sortIndex' => request()->sortIndex ?? 'id',
            'sortOrder' => request()->sortOrder ?? 'asc',
        ];

        $table = (new Supplier())->getTable();

        $totalRows = DB::table($table)->count();
        $totalPages = ceil($totalRows / $params['limit']);

        /* Select Columns */
        $query = DB::table($table)
            ->select(
                "$table.*",
                "parameter_statusaktif.text as statusaktif",
                "parameter_statusdaftarharga.text as statusdaftarharga",
            )
            ->leftJoin('parameter as parameter_statusaktif', "$table.statusaktif", '=', 'parameter_statusaktif.id')
            ->leftJoin('parameter as parameter_statusdaftarharga', "$table.statusdaftarharga", '=', 'parameter_statusdaftarharga.id');

        /* Sorting */
        $query->orderBy($table . '.' . $params['sortIndex'], $params['sortOrder']);

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query->where('parameter_statusaktif.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusdaftarharga') {
                            $query->where('parameter_statusdaftarharga.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query->where("$table." . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query->orWhere('parameter_statusaktif.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusdaftarharga') {
                            $query->orWhere('parameter_statusdaftarharga.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query->orWhere("$table." . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $totalRows = $query->count();
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $suppliers = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $suppliers,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }


    public function show(Supplier $supplier)
    {
        return response([
            'status' => true,
            'data' => $supplier
        ]);
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
            $supplier->notelp2 = $request->notelp2;
            $supplier->email = $request->email;
            $supplier->statusaktif = $request->statusaktif;
            $supplier->web = $request->web;
            $supplier->namapemilik = $request->namapemilik;
            $supplier->jenisusaha = $request->jenisusaha;
            $supplier->top = $request->top;
            $supplier->bank = $request->bank;
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
            $del = 0;
            $data = $this->getid($supplier->id, $request, $del);
            $supplier->position = $data->row ?? 0;

            if (isset($request->limit)) {
                $supplier->page = ceil($supplier->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
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
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        try {
            $supplier->namasupplier = $request->namasupplier;
            $supplier->namakontak = $request->namakontak;
            $supplier->alamat = $request->alamat;
            $supplier->kota = $request->kota;
            $supplier->kodepos = $request->kodepos;
            $supplier->notelp1 = $request->notelp1;
            $supplier->notelp2 = $request->notelp2;
            $supplier->email = $request->email;
            $supplier->statusaktif = $request->statusaktif;
            $supplier->web = $request->web;
            $supplier->namapemilik = $request->namapemilik;
            $supplier->jenisusaha = $request->jenisusaha;
            $supplier->top = $request->top;
            $supplier->bank = $request->bank;
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

                /* Set position and page */
                $supplier->position = $this->getid($supplier->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $supplier->page = ceil($supplier->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $supplier
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(Supplier $supplier, Request $request)
    {
        $delete = $supplier->delete();
        $del = 1;
        if ($delete) {
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

            $data = $this->getid($supplier->id, $request, $del);
            $supplier->position = $data->row  ?? 0;
            $supplier->id = $data->id  ?? 0;
            if (isset($request->limit)) {
                $supplier->page = ceil($supplier->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $supplier
            ]);
        } else {
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

    public function getid($id, $request, $del)
    {

        $params = [
            'indexRow' => $request->indexRow ?? 1,
            'limit' => $request->limit ?? 100,
            'page' => $request->page ?? 1,
            'sortname' => $request->sortname ?? 'id',
            'sortorder' => $request->sortorder ?? 'asc',
        ];
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->id();
            $table->bigInteger('id_')->default('0');
            $table->string('namasupplier', 300)->default('');
            $table->string('namakontak', 300)->default('');
            $table->string('alamat', 300)->default('');
            $table->string('kota', 300)->default('');
            $table->string('kodepos', 300)->default('');
            $table->string('notelp1', 300)->default('');
            $table->string('notelp2', 300)->default('');
            $table->string('email', 300)->default('');
            $table->string('web', 300)->default('');
            $table->string('namapemilik', 300)->default('');
            $table->string('jenisusaha', 300)->default('');
            $table->string('top', 300)->default('');
            $table->string('bank', 300)->default('');
            $table->string('rekeningbank', 300)->default('');
            $table->string('jabatan', 300)->default('');
            $table->string('statusdaftarharga', 300)->default('');
            $table->string('kategoriusaha', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Supplier::select(
                'supplier.id as id_',
                'supplier.namasupplier',
                'supplier.namakontak',
                'supplier.alamat',
                'supplier.kota',
                'supplier.kodepos',
                'supplier.notelp1',
                'supplier.notelp2',
                'supplier.email',
                'supplier.web',
                'supplier.namapemilik',
                'supplier.jenisusaha',
                'supplier.top',
                'supplier.bank',
                'supplier.rekeningbank',
                'supplier.jabatan',
                'supplier.statusdaftarharga',
                'supplier.kategoriusaha',
                'supplier.modifiedby',
                'supplier.created_at',
                'supplier.updated_at',
            )
                ->orderBy('supplier.id', $params['sortorder']);
        } else if ($params['sortname'] == 'grp' or $params['sortname'] == 'subgrp') {
            $query = Supplier::select(
                'supplier.id as id_',
                'supplier.namasupplier',
                'supplier.namakontak',
                'supplier.alamat',
                'supplier.kota',
                'supplier.kodepos',
                'supplier.notelp1',
                'supplier.notelp2',
                'supplier.email',
                'supplier.web',
                'supplier.namapemilik',
                'supplier.jenisusaha',
                'supplier.top',
                'supplier.bank',
                'supplier.rekeningbank',
                'supplier.jabatan',
                'supplier.statusdaftarharga',
                'supplier.kategoriusaha',
                'supplier.modifiedby',
                'supplier.created_at',
                'supplier.updated_at',
            )
                ->orderBy($params['sortname'], $params['sortorder'])
                ->orderBy('supplier.text', $params['sortorder'])
                ->orderBy('supplier.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Supplier::select(
                    'supplier.id as id_',
                    'supplier.namasupplier',
                    'supplier.namakontak',
                    'supplier.alamat',
                    'supplier.kota',
                    'supplier.kodepos',
                    'supplier.notelp1',
                    'supplier.notelp2',
                    'supplier.email',
                    'supplier.web',
                    'supplier.namapemilik',
                    'supplier.jenisusaha',
                    'supplier.top',
                    'supplier.bank',
                    'supplier.rekeningbank',
                    'supplier.jabatan',
                    'supplier.statusdaftarharga',
                    'supplier.kategoriusaha',
                    'supplier.modifiedby',
                    'supplier.created_at',
                    'supplier.updated_at',
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('supplier.id', $params['sortorder']);
            } else {
                $query = Supplier::select(
                    'supplier.id as id_',
                    'supplier.namasupplier',
                    'supplier.namakontak',
                    'supplier.alamat',
                    'supplier.kota',
                    'supplier.kodepos',
                    'supplier.notelp1',
                    'supplier.notelp2',
                    'supplier.email',
                    'supplier.web',
                    'supplier.namapemilik',
                    'supplier.jenisusaha',
                    'supplier.top',
                    'supplier.bank',
                    'supplier.rekeningbank',
                    'supplier.jabatan',
                    'supplier.statusdaftarharga',
                    'supplier.kategoriusaha',
                    'supplier.modifiedby',
                    'supplier.created_at',
                    'supplier.updated_at',
                )
                    ->orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('supplier.id', 'asc');
            }
        }

        DB::table($temp)->insertUsing([
            'id_',
            'namasupplier',
            'namakontak',
            'alamat',
            'kota',
            'kodepos',
            'notelp1',
            'notelp2',
            'email',
            'web',
            'namapemilik',
            'jenisusaha',
            'top',
            'bank',
            'rekeningbank',
            'jabatan',
            'statusdaftarharga',
            'kategoriusaha',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $query);


        if ($del == 1) {
            if ($params['page'] == 1) {
                $baris = $params['indexRow'] + 1;
            } else {
                $hal = $params['page'] - 1;
                $bar = $hal * $params['limit'];
                $baris = $params['indexRow'] + $bar + 1;
            }


            if (DB::table($temp)
                ->where('id', '=', $baris)->exists()
            ) {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', $baris)
                    ->orderBy('id');
            } else {
                $querydata = DB::table($temp)
                    ->select('id as row', 'id_ as id')
                    ->where('id', '=', ($baris - 1))
                    ->orderBy('id');
            }
        } else {
            $querydata = DB::table($temp)
                ->select('id as row')
                ->where('id_', '=',  $id)
                ->orderBy('id');
        }


        $data = $querydata->first();
        return $data;
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
            [
                'label' => 'TOP',
                'index' => 'top',
            ],
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
