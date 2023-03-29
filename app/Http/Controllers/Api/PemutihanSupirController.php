<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PemutihanSupir;
use App\Http\Requests\StorePemutihanSupirRequest;
use App\Http\Requests\UpdatePemutihanSupirRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PemutihanSupirController extends Controller
{
    /**
     * @ClassName
     */
    public function index()
    {
        $pemutihanSupir = new PemutihanSupir();
        return response([
            'data' => $pemutihanSupir->get(),
            'attributes' => [
                'totalRows' => $pemutihanSupir->totalRows,
                'totalPages' => $pemutihanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePemutihanSupirRequest $request)
    {
        DB::beginTransaction();
        try {

            $group = 'PEMUTIHAN SUPIR BUKTI';
            $subgroup = 'PEMUTIHAN SUPIR BUKTI';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pemutihansupir';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];

            $pemutihanSupir = new PemutihanSupir();
            $pemutihanSupir->nobukti = $nobukti;
            $pemutihanSupir->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pemutihanSupir->supir_id = $request->supir_id;
            $pemutihanSupir->pengeluaransupir = $request->pengeluaransupir;
            $pemutihanSupir->penerimaansupir = $request->penerimaansupir;
            $pemutihanSupir->statusformat = $format->id;
            $pemutihanSupir->modifiedby = auth('api')->user()->name;

            $pemutihanSupir->save();

            $logTrail = [
                'namatabel' => strtoupper($pemutihanSupir->getTable()),
                'postingdari' => $request->postingdari ?? 'ENTRY PEMUTIHAN SUPIR',
                'idtrans' => $pemutihanSupir->id,
                'nobuktitrans' => $pemutihanSupir->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pemutihanSupir->toArray(),
                'modifiedby' => $pemutihanSupir->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable());
            $pemutihanSupir->position = $selected->position;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pemutihanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show(PemutihanSupir $pemutihansupir)
    {
        return response([
            'data' => $pemutihansupir->load('supir'),
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePemutihanSupirRequest $request, PemutihanSupir $pemutihansupir)
    {
        DB::beginTransaction();
        try {
            $pemutihansupir->supir_id = $request->supir_id;
            $pemutihansupir->pengeluaransupir = $request->pengeluaransupir;
            $pemutihansupir->penerimaansupir = $request->penerimaansupir;
            $pemutihansupir->save();

            $logTrail = [
                'namatabel' => strtoupper($pemutihansupir->getTable()),
                'postingdari' => 'EDIT PEMUTIHAN SUPIR',
                'idtrans' => $pemutihansupir->id,
                'nobuktitrans' => $pemutihansupir->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pemutihansupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pemutihansupir, $pemutihansupir->getTable());
            $pemutihansupir->position = $selected->position;
            $pemutihansupir->page = ceil($pemutihansupir->position / ($request->limit ?? 10));



            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pemutihansupir
            ], 201);
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

        $pemutihanSupir = new PemutihanSupir();
        $pemutihanSupir = $pemutihanSupir->lockAndDestroy($id);
        if ($pemutihanSupir) {
            // DELETE PEMUTIHAN SUPIR
            $logTrail = [
                'namatabel' => strtoupper($pemutihanSupir->getTable()),
                'postingdari' => 'DELETE PEMUTIHAN SUPIR',
                'idtrans' => $pemutihanSupir->id,
                'nobuktitrans' => $pemutihanSupir->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pemutihanSupir->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();

            $selected = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable(), true);
            $pemutihanSupir->position = $selected->position;
            $pemutihanSupir->id = $selected->id;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pemutihanSupir
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function getPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        $post = $data->getPosting($supirId);
        
        return response([
            'post' => $post,
            'attributes' => [
                'totalRows' => $data->totalRows,
                'totalPages' => $data->totalPages,
            ]
        ]);
    }
    public function getNonpost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        $non = $data->getNonposting($supirId);
        return response([
            'non' => $non, 
            'attributesNon' => [
                'totalRows' => $data->totalRows,
                'totalPages' => $data->totalPages,
            ]
        ]);
    }

    public function cekvalidasi($id)
    {
        $pemutihanSupir = new PemutihanSupir();
        $pemutihan = PemutihanSupir::from(DB::raw("pemutihansupir"))->where('id', $id)->first();
        $now = date("Y-m-d");
        if ($pemutihan->tglbukti == $now) {

            $data = [
                'status' => false,
                'message' => '',
                'errors' => '',
                'kondisi' => true,
            ];

            return response($data);
        } else {

            $query = DB::table('error')
                ->select(
                    DB::raw("'PEMUTIHAN SUPIR '+ltrim(rtrim(keterangan)) as keterangan")
                )
                ->where('kodeerror', '=', 'ETS')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => false,
            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pemutihansupir')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
