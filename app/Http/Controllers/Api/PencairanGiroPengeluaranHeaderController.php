<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexPencairanGiroRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePencairanGiroPengeluaranDetailRequest;
use App\Models\PencairanGiroPengeluaranHeader;
use App\Http\Requests\StorePencairanGiroPengeluaranHeaderRequest;
use App\Http\Requests\UpdatePencairanGiroPengeluaranHeaderRequest;
use App\Http\Requests\UpdateTglJatuhTempoRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PencairanGiroPengeluaranDetail;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Rules\ApprovalBukaCetak;
use App\Rules\PencairanGiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencairanGiroPengeluaranHeaderController extends Controller
{
    /**
     * @ClassName 
     * PencairanGiroPengeluaranHeader
     * @Detail PencairanGiroPengeluaranDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(Request $request)
    {
        $pencairanGiro = new PencairanGiroPengeluaranHeader();

        $this->validate($request, [
            'periode' => ['required', new PencairanGiro()],
        ]);

        if ($request->periode) {
            $periode = explode("-", $request->periode);
            $request->merge([
                'year' => $periode[1],
                'month' => $periode[0]
            ]);
        }

        if ($request->periode) {
            return response([
                'data' => $pencairanGiro->get(),
                'attributes' => [
                    'totalRows' => $pencairanGiro->totalRows,
                    'totalPages' => $pencairanGiro->totalPages
                ]
            ]);
        }
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePencairanGiroPengeluaranHeaderRequest $request)
    {
        DB::BeginTransaction();
        try {
            $detail = json_decode($request->detail, true);
            $data = [
                'periode' => $request->periode,
                'status' => $request->status,
                'nobukti' => $detail['nobukti'],
            ];
            $pencairanGiro = (new PencairanGiroPengeluaranHeader())->processStore($data);
            // $pencairanGiro->position = $this->getPosition($pencairanGiro, $pencairanGiro->getTable())->position;
            // if ($request->limit==0) {
            //     $pencairanGiro->page = ceil($pencairanGiro->position / (10));
            // } else {
            //     $pencairanGiro->page = ceil($pencairanGiro->position / ($request->limit ?? 10));
            // }
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pencairanGiro
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
     * @Keterangan EDIT TGL JATUH TEMPO
     */
    public function updateTglJatuhTempo(UpdateTglJatuhTempoRequest $request)
    {
        DB::BeginTransaction();
        try {
            $detail = json_decode($request->detail, true);
            $data = [
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'periode' => $request->periode,
                'status' => $request->status,
                'nobukti' => $detail['nobukti'],
            ];
            $pencairanGiro = (new PencairanGiroPengeluaranHeader())->processUpdateTglJatuhtempo($data);
            // $pencairanGiro->position = $this->getPosition($pencairanGiro, $pencairanGiro->getTable())->position;
            // if ($request->limit==0) {
            //     $pencairanGiro->page = ceil($pencairanGiro->position / (10));
            // } else {
            //     $pencairanGiro->page = ceil($pencairanGiro->position / ($request->limit ?? 10));
            // }
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pencairanGiro
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
