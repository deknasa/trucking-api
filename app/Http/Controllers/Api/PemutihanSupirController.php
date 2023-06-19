<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorepemutihansupirdetailRequest;
use App\Models\PemutihanSupir;
use App\Http\Requests\StorePemutihanSupirRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePemutihanSupirRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\PemutihanSupirDetail;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\Supir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PemutihanSupirController extends Controller
{
    /**
     * @ClassName
     */
    public function index(GetIndexRangeRequest $request)
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
    public function store(StorePemutihanSupirRequest $request) :JsonResponse
    {
        DB::beginTransaction();
        try {
            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $nominalPosting = ($request->posting_nominal) ? array_sum($request->posting_nominal) : 0;
            $nominalNonPosting = ($request->nonposting_nominal) ? array_sum($request->nonposting_nominal) : 0;

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'supir_id' => $request->supir_id ?? '',
                'supir' => $request->supir ?? '',
                'nonposting_nominal' => $request->nonposting_nominal ?? '',
                'nonposting_nobukti' => $request->nonposting_nobukti ?? '',
                'posting_nominal' => $request->posting_nominal ?? 0,
                'pengeluaransupir' => $nominalPosting + $nominalNonPosting,
                'penerimaansupir' => $request->penerimaansupir ?? 0,
                'bank_id' => $request->bank_id ?? '',
                'coa' => $coaPengembalian->coapostingkredit ?? '',
                'pengeluarantrucking_nobukti' => $request->posting_nobukti ?? 0,
                'posting_nobukti' => $request->posting_nobukti ?? 0,
                'nominal' => $request->posting_nominal ?? 0,
                'posting_keterangan' => $request->posting_keterangan ?? '',
                'postingId' => $request->postingId ?? '',
                'nonpostingId' => $request->nonpostingId ?? ''
            ];


            $pemutihanSupir = (new PemutihanSupir())->processStore($data);
            $pemutihanSupir->position = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable())->position;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            DB::commit();

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


    public function show($id)
    {
        $data = PemutihanSupir::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePemutihanSupirRequest $request, PemutihanSupir $pemutihansupir) :JsonResponse
    {
        DB::beginTransaction();
        try {

            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $nominalPosting = ($request->posting_nominal) ? array_sum($request->posting_nominal) : 0;
            $nominalNonPosting = ($request->nonposting_nominal) ? array_sum($request->nonposting_nominal) : 0;

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'supir_id' => $request->supir_id ?? '',
                'supir' => $request->supir ?? '',
                'nonposting_nominal' => $request->nonposting_nominal ?? '',
                'nonposting_nobukti' => $request->nonposting_nobukti ?? '',
                'posting_nominal' => $request->posting_nominal ?? 0,
                'pengeluaransupir' => $nominalPosting + $nominalNonPosting,
                'penerimaansupir' => $request->penerimaansupir ?? 0,
                'bank_id' => $request->bank_id ?? '',
                'coa' => $coaPengembalian->coapostingkredit ?? '',
                'pengeluarantrucking_nobukti' => $request->posting_nobukti ?? 0,
                'posting_nobukti' => $request->posting_nobukti ?? 0,
                'nominal' => $request->posting_nominal ?? 0,
                'posting_keterangan' => $request->posting_keterangan ?? '',
                'postingId' => $request->postingId ?? '',
                'nonpostingId' => $request->nonpostingId ?? ''
            ];

            $pemutihanSupir = (new PemutihanSupir())->processUpdate($pemutihansupir, $data);
            $pemutihanSupir->position = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable())->position;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $pemutihanSupir
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(Request $request, $id) :JsonResponse
    {
        DB::beginTransaction();

        try {
            $pemutihanSupir = (new PemutihanSupir())->processDestroy($id, 'DELETE PEMUTIHAN SUPIR');
            $selected = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable(), true);
            $pemutihanSupir->position = $selected->position;
            $pemutihanSupir->id = $selected->id;
            $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pemutihanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            $post = $data->getPosting($supirId);

            return response([
                'post' => $post,
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }
    public function getNonPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            $non = $data->getNonposting($supirId);
            return response([
                'non' => $non,
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getEditPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'post' => $data->getEditPost($id, $supirId),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getEditNonPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'non' => $data->getEditNonPost($id, $supirId),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }


    public function getDeletePost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'post' => $data->getDeletePost($id, $supirId),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getDeleteNonPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'non' => $data->getDeleteNonPost($id, $supirId),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function cekvalidasi($id)
    {
        $pemutihanSupir = new PemutihanSupir();
        $pemutihan = PemutihanSupir::from(DB::raw("pemutihansupirheader"))->where('id', $id)->first();
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pemutihansupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
