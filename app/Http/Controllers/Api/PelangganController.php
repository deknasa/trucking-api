<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Pelanggan;
use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class PelangganController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pelanggan = new Pelanggan();

        return response([
            'data' => $pelanggan->get(),
            'attributes' => [
                'totalRows' => $pelanggan->totalRows,
                'totalPages' => $pelanggan->totalPages
            ]
        ]);
    }


    public function show(Pelanggan $pelanggan)
    {
        return response([
            'status' => true,
            'data' => $pelanggan
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StorePelangganRequest $request)
    {
        DB::beginTransaction();

        try {
            $pelanggan = new Pelanggan();
            $pelanggan->kodepelanggan = $request->kodepelanggan;
            $pelanggan->namapelanggan = $request->namapelanggan;
            $pelanggan->telp = $request->telp;
            $pelanggan->alamat = $request->alamat;
            $pelanggan->alamat2 = $request->alamat2;
            $pelanggan->kota = $request->kota;
            $pelanggan->kodepos = $request->kodepos;
            $pelanggan->keterangan = $request->keterangan;
            $pelanggan->modifiedby = auth('api')->user()->name;
            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            if ($pelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelanggan->getTable()),
                    'postingdari' => 'ENTRY PELANGGAN',
                    'idtrans' => $pelanggan->id,
                    'nobuktitrans' => $pelanggan->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pelanggan->toArray(),
                    'modifiedby' => $pelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pelanggan, $pelanggan->getTable());
            $pelanggan->position = $selected->position;
            $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pelanggan->page = ceil($pelanggan->position / $request->limit);
            }

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pelanggan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

   

    /**
     * @ClassName 
     */
    public function update(UpdatePelangganRequest $request, Pelanggan $pelanggan)
    {
        DB::beginTransaction();

        try {
            $pelanggan->kodepelanggan = $request->kodepelanggan;
            $pelanggan->namapelanggan = $request->namapelanggan;
            $pelanggan->telp = $request->telp;
            $pelanggan->alamat = $request->alamat;
            $pelanggan->alamat2 = $request->alamat2;
            $pelanggan->kota = $request->kota;
            $pelanggan->kodepos = $request->kodepos;
            $pelanggan->keterangan = $request->keterangan;
            $pelanggan->modifiedby = auth('api')->user()->name;

            if ($pelanggan->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pelanggan->getTable()),
                    'postingdari' => 'EDIT PELANGGAN',
                    'idtrans' => $pelanggan->id,
                    'nobuktitrans' => $pelanggan->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pelanggan->toArray(),
                    'modifiedby' => $pelanggan->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();

                /* Set position and page */
                $selected = $this->getPosition($pelanggan, $pelanggan->getTable());
                $pelanggan->position = $selected->position;
                $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));

                return response([
                    'status' => true,
                    'message' => 'Berhasil diubah',
                    'data' => $pelanggan
                ]);
            } else {
                DB::rollBack();

                return response([
                    'status' => false,
                    'message' => 'Gagal diubah'
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
    public function destroy(Pelanggan $pelanggan, Request $request)
    {
        DB::beginTransaction();

        $delete = $pelanggan->lockForUpdate()->delete();

        if ($delete) {
            $logTrail = [
                'namatabel' => strtoupper($pelanggan->getTable()),
                'postingdari' => 'DELETE PARAMETER',
                'idtrans' => $pelanggan->id,
                'nobuktitrans' => $pelanggan->id,
                'aksi' => 'DELETE',
                'datajson' => $pelanggan->toArray(),
                'modifiedby' => $pelanggan->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pelanggan, $pelanggan->getTable(), true);
            $pelanggan->position = $selected->position;
            $pelanggan->id = $selected->id;
            $pelanggan->page = ceil($pelanggan->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pelanggan
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelanggan')->getColumns();

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
        $pelanggans = $decodedResponse['data'];

        $columns = [
            [
                'label' => 'No',
            ],
            [
                'label' => 'ID',
                'index' => 'id',
            ],
            [
                'label' => 'Kode Pelanggan',
                'index' => 'kodepelanggan',
            ],
            [
                'label' => 'Nama Pelanggan',
                'index' => 'namapelanggan',
            ],
            [
                'label' => 'Telp',
                'index' => 'telp',
            ],
            [
                'label' => 'Alamat',
                'index' => 'alamat',
            ],
            [
                'label' => 'Alamat2',
                'index' => 'alamat2',
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
                'label' => 'Keterangan',
                'index' => 'keterangan',
            ],
        ];

        $this->toExcel('Pelanggan', $pelanggans, $columns);
    }
}
