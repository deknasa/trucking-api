<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NotDeletableModel;
use App\Http\Controllers\Controller;
use App\Models\Agen;
use App\Http\Requests\StoreAgenRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\UpdateAgenRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

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

    public function cekValidasi($id) {
        $agen= new Agen();
        $cekdata=$agen->cekvalidasihapus($id);
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

        $agen = new Agen();
        return response([
            'status' => true,
            'data' => $agen->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreAgenRequest $request)
    {
        DB::beginTransaction();

        try {
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

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
            $agen->statusapproval = $statusNonApproval->id;
            $agen->statustas = $request->statustas;
            // $agen->jenisemkl = $request->jenisemkl;
            $agen->tglapproval = '';
            $agen->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($agen->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($agen->getTable()),
                    'postingdari' => 'ENTRY AGEN',
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
            $selected = $this->getPosition($agen, $agen->getTable());
            $agen->position = $selected->position;
            $agen->page = ceil($agen->position / ($request->limit ?? 10));

            return response([
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
    public function update(UpdateAgenRequest $request, Agen $agen)
    {
        DB::beginTransaction();

        try {
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
            $agen->statustas = $request->statustas;
            // $agen->jenisemkl = $request->jenisemkl;
            $agen->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($agen->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($agen->getTable()),
                    'postingdari' => 'EDIT AGEN',
                    'idtrans' => $agen->id,
                    'nobuktitrans' => $agen->id,
                    'aksi' => 'EDIT',
                    'datajson' => $agen->toArray(),
                    'modifiedby' => $agen->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);


                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($agen, $agen->getTable());
                $agen->position = $selected->position;
                $agen->page = ceil($agen->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $agen
                ]);
            }
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

        $agen = new Agen();
        $agen = $agen->lockAndDestroy($id);
        if ($agen) {
            $logTrail = [
                'namatabel' => strtoupper($agen->getTable()),
                'postingdari' => 'DELETE AGEN',
                'idtrans' => $agen->id,
                'nobuktitrans' => $agen->id,
                'aksi' => 'DELETE',
                'datajson' => $agen->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($agen, $agen->getTable(), true);
            $agen->position = $selected->position;
            $agen->id = $selected->id;
            $agen->page = ceil($agen->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $agen
            ]);
        } else {
            DB::rollBack();

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
