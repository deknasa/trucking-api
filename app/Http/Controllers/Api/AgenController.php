<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agen;
use App\Http\Requests\StoreAgenRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAgenRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

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

        $totalRows = DB::table((new Agen)->getTable())->count();
        $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;

        /* Sorting */
        $query = DB::table((new Agen)->getTable())->orderBy($params['sortIndex'], $params['sortOrder']);

        if ($params['sortIndex'] == 'id') {
            $query = DB::table((new Agen)->getTable())->select(
                "agen.id",
                "agen.kodeagen",
                "agen.namaagen",
                "agen.keterangan",
                "parameter_statusaktif.text as statusaktif",
                "agen.namaperusahaan",
                "agen.alamat",
                "agen.notelp",
                "agen.nohp",
                "agen.contactperson",
                "agen.top",
                "parameter_statusapproval.text as statusapproval",
                "agen.userapproval",
                "agen.tglapproval",
                "parameter_statustas.text as statustas",
                "agen.jenisemkl",
                "agen.modifiedby",
                "agen.created_at",
                "agen.updated_at"
            )
                ->leftJoin('parameter as parameter_statusapproval', 'agen.statusapproval', '=', 'parameter_statusapproval.id')
                ->leftJoin('parameter as parameter_statusaktif', 'agen.statusaktif', '=', 'parameter_statusaktif.id')
                ->leftJoin('parameter as parameter_statustas', 'agen.statustas', '=', 'parameter_statustas.id')
                ->orderBy('agen.id', $params['sortOrder']);
        } else {
            if ($params['sortOrder'] == 'asc') {
                $query = DB::table((new Agen)->getTable())->select(
                    "agen.id",
                    "agen.kodeagen",
                    "agen.namaagen",
                    "agen.keterangan",
                    "parameter_statusaktif.text as statusaktif",
                    "agen.namaperusahaan",
                    "agen.alamat",
                    "agen.notelp",
                    "agen.nohp",
                    "agen.contactperson",
                    "agen.top",
                    "parameter_statusapproval.text as statusapproval",
                    "agen.userapproval",
                    "agen.tglapproval",
                    "parameter_statustas.text as statustas",
                    "agen.jenisemkl",
                    "agen.modifiedby",
                    "agen.created_at",
                    "agen.updated_at"
                )
                    ->leftJoin('parameter as parameter_statusapproval', 'agen.statusapproval', '=', 'parameter_statusapproval.id')
                    ->leftJoin('parameter as parameter_statusaktif', 'agen.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statustas', 'agen.statustas', '=', 'parameter_statustas.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('agen.id', $params['sortOrder']);
            } else {
                $query = DB::table((new Agen)->getTable())->select(
                    "agen.id",
                    "agen.kodeagen",
                    "agen.namaagen",
                    "agen.keterangan",
                    "parameter_statusaktif.text as statusaktif",
                    "agen.namaperusahaan",
                    "agen.alamat",
                    "agen.notelp",
                    "agen.nohp",
                    "agen.contactperson",
                    "agen.top",
                    "parameter_statusapproval.text as statusapproval",
                    "agen.userapproval",
                    "agen.tglapproval",
                    "parameter_statustas.text as statustas",
                    "agen.jenisemkl",
                    "agen.modifiedby",
                    "agen.created_at",
                    "agen.updated_at"
                )
                    ->leftJoin('parameter as parameter_statusapproval', 'agen.statusapproval', '=', 'parameter_statusapproval.id')
                    ->leftJoin('parameter as parameter_statusaktif', 'agen.statusaktif', '=', 'parameter_statusaktif.id')
                    ->leftJoin('parameter as parameter_statustas', 'agen.statustas', '=', 'parameter_statustas.id')
                    ->orderBy($params['sortIndex'], $params['sortOrder'])
                    ->orderBy('agen.id', 'asc');
            }
        }

        /* Searching */
        if (count($params['filters']) > 0 && @$params['filters']['rules'][0]['data'] != '') {
            switch ($params['filters']['groupOp']) {
                case "AND":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->where('parameter_statusaktif.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter_statusapproval.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->where('parameter_statustas.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where('agen.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusaktif') {
                            $query = $query->orWhere('parameter_statusaktif.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('parameter_statusapproval.text', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'statustas') {
                            $query = $query->orWhere('parameter_statustas.text', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere('agen.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $totalRows = count($query->get());
            $totalPages = $params['limit'] > 0 ? ceil($totalRows / $params['limit']) : 1;
        }

        /* Paging */
        $query = $query->skip($params['offset'])
            ->take($params['limit']);

        $agens = $query->get();

        /* Set attributes */
        $attributes = [
            'totalRows' => $totalRows ?? 0,
            'totalPages' => $totalPages ?? 0
        ];

        return response([
            'status' => true,
            'data' => $agens,
            'attributes' => $attributes,
            'params' => $params
        ]);
    }

          /**
     * @ClassName 
     */
    public function store(StoreAgenRequest $request)
    {
        DB::beginTransaction();

        try {
            $agen = new Agen();
            $agen->kodeagen = $request->kodeagen;
            $agen->namaagen = $request->namaagen;
            $agen->keterangan = $request->keterangan;
            $agen->statusaktif = $request->statusaktif;
            $agen->namaperusahaan = $request->namaperusahaan;
            $agen->alamat = $request->alamat;
            $agen->notelp = $request->notelp;
            $agen->nohp = $request->nohp;
            $agen->contactperson = $request->contactperson;
            $agen->top = $request->top;
            $agen->statusapproval = $request->statusapproval;
            $agen->userapproval = auth('api')->user()->name;
            $agen->tglapproval = date('Y-m-d', time());
            $agen->statustas = $request->statustas;
            $agen->jenisemkl = $request->jenisemkl;
            $agen->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            
            if ($agen->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($agen->getTable()),
                    'postingdari' => 'ENTRY PARAMETER',
                    'idtrans' => $agen->id,
                    'nobuktitrans' => $agen->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $agen->toArray(),
                    'modifiedby' => $agen->modifiedby
                ];
                
                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $del = 0;
            $data = $this->getid($agen->id, $request, $del);
            $agen->position = $data->row;

            if (isset($request->limit)) {
                $agen->page = ceil($agen->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $agen
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Agen $agen)
    {
        return response([
            'status' => true,
            'data' => $agen
        ]);
    }

              /**
     * @ClassName 
     */
    public function update(UpdateAgenRequest $request, Agen $agen)
    {
        try {
            $agen = Agen::findOrFail($agen->id);
            $agen->kodeagen = $request->kodeagen;
            $agen->namaagen = $request->namaagen;
            $agen->keterangan = $request->keterangan;
            $agen->statusaktif = $request->statusaktif;
            $agen->namaperusahaan = $request->namaperusahaan;
            $agen->alamat = $request->alamat;
            $agen->notelp = $request->notelp;
            $agen->nohp = $request->nohp;
            $agen->contactperson = $request->contactperson;
            $agen->top = $request->top;
            $agen->statusapproval = $request->statusapproval;
            $agen->userapproval = auth('api')->user()->name;
            $agen->tglapproval = date('Y-m-d', time());
            $agen->statustas = $request->statustas;
            $agen->jenisemkl = $request->jenisemkl;
            $agen->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($agen->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($agen->getTable()),
                    'postingdari' => 'EDIT PARAMETER',
                    'idtrans' => $agen->id,
                    'nobuktitrans' => $agen->id,
                    'aksi' => 'EDIT',
                    'datajson' => $agen->toArray(),
                    'modifiedby' => $agen->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                /* Set position and page */
                $agen->position = $this->getid($agen->id, $request, 0)->row;

                if (isset($request->limit)) {
                    $agen->page = ceil($agen->position / $request->limit);
                }

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $agen
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
    public function destroy(Agen $agen, Request $request)
    {
        $delete = Agen::destroy($agen->id);
        $del = 1;
        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($agen->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $agen->id,
                'nobuktitrans' => $agen->id,
                'aksi' => 'DELETE',
                'datajson' => $agen->toArray(),
                'modifiedby' => $agen->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $data = $this->getid($agen->id, $request, $del);
            $agen->position = $data->row;
            $agen->id = $data->id;
            if (isset($request->limit)) {
                $agen->page = ceil($agen->position / $request->limit);
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $agen
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function export()
    {
        $response = $this->index();
        $decodedResponse = json_decode($response->content(), true);
        $agens = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'Kode Agen',
                'index' => 'kodeagen',
            ],
            [
                'label' => 'Nama Agen',
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
                'label' => 'No Telp',
                'index' => 'notelp',
            ],
            [
                'label' => 'No Hp',
                'index' => 'nohp',
            ],
            [
                'label' => 'Contact Person',
                'index' => 'contactperson',
            ],
            [
                'label' => 'TOP',
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
            [
                'label' => 'Jenis Emkl',
                'index' => 'jenisemkl',
            ],
        ];

        $this->toExcel('Agen', $agens, $columns);
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
            $table->string('kodeagen', 300)->default('');
            $table->string('namaagen', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->string('statusaktif', 300)->default('');
            $table->string('namaperusahaan', 300)->default('');
            $table->string('alamat', 300)->default('');
            $table->string('notelp', 300)->default('');
            $table->string('nohp', 300)->default('');
            $table->string('contactperson', 300)->default('');
            $table->string('top', 300)->default('');
            $table->string('statusapproval', 300)->default('');
            $table->string('userapproval', 300)->default('');
            $table->string('tglapproval', 300)->default('');
            $table->string('statustas', 300)->default('');
            $table->string('jenisemkl', 300)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');

            $table->index('id_');
        });

        if ($params['sortname'] == 'id') {
            $query = Agen::orderBy('agen.id', $params['sortorder']);
        } else {
            if ($params['sortorder'] == 'asc') {
                $query = Agen::orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('agen.id', $params['sortorder']);
            } else {
                $query = Agen::orderBy($params['sortname'], $params['sortorder'])
                    ->orderBy('agen.id', 'asc');
            }
        }


        DB::table($temp)->insertUsing([
            'id_',
            'kodeagen',
            'namaagen',
            'keterangan',
            'statusaktif',
            'namaperusahaan',
            'alamat',
            'notelp',
            'nohp',
            'contactperson',
            'top',
            'statusapproval',
            'userapproval',
            'tglapproval',
            'statustas',
            'jenisemkl',
            'modifiedby',
            'created_at',
            'updated_at'
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
}
