<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPenerimaanGiroHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanGiroDetailRequest;
use App\Models\PenerimaanGiroHeader;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePenerimaanGiroHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\ValidasiApprovalPenerimaanGiroRequest;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PenerimaanGiroDetail;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanGiroHeaderController extends Controller
{
    /**
     * @ClassName 
     * PenerimaanGiroHeader
     * @Detail1 PenerimaanGiroDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $penerimaanGiro = new PenerimaanGiroHeader();

        return response([
            'data' => $penerimaanGiro->get(),
            'attributes' => [
                'totalRows' => $penerimaanGiro->totalRows,
                'totalPages' => $penerimaanGiro->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function get(GetIndexRangeRequest $request): JsonResponse
    {
        // untuk lookup penerimaangiro
        $penerimaanGiro = new PenerimaanGiroHeader();

        return response()->json([
            'data' => $penerimaanGiro->getPenerimaan(),
            'attributes' => [
                'totalRows' => $penerimaanGiro->totalRows,
                'totalPages' => $penerimaanGiro->totalPages,
                'totalNominal' => $penerimaanGiro->totalNominal
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePenerimaanGiroHeaderRequest $request): JsonResponse
    {
        DB::BeginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan_detail' => $request->keterangan_detail,
                'bank_id' => $request->bank_id,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'jenisbiaya' => $request->jenisbiaya,
                'bulanbeban' => $request->bulanbeban,
            ];
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processStore($data);
            $penerimaanGiroHeader->position = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable())->position;
            $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $data = PenerimaanGiroHeader::findAll($id);
        $detail = PenerimaanGiroDetail::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePenerimaanGiroHeaderRequest $request, PenerimaanGiroHeader $penerimaangiroheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan_detail' => $request->keterangan_detail,
                'bank_id' => $request->bank_id,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'jenisbiaya' => $request->jenisbiaya,
                'bulanbeban' => $request->bulanbeban,
            ];
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processUpdate($penerimaangiroheader, $data);
            /* Set position and page */
            $penerimaanGiroHeader->position = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable())->position;
            $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroyPenerimaanGiroHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processDestroy($id, 'DELETE PENERIMAAN GIRO');
            $selected = $this->getPosition($penerimaanGiroHeader, $penerimaanGiroHeader->getTable(), true);
            $penerimaanGiroHeader->position = $selected->position;
            $penerimaanGiroHeader->id = $selected->id;
            $penerimaanGiroHeader->page = ceil($penerimaanGiroHeader->position / ($request->limit ?? 10));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanGiroHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function approval(ValidasiApprovalPenerimaanGiroRequest $request)
    {
        DB::beginTransaction();

        try {
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processApproval($request->all());

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanGiroHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function tarikPelunasan($id)
    {
        $penerimaan = new PenerimaanGiroHeader();
        // ($id!='') ? $tarik = $penerimaan->tarikPelunasan($id) : $tarik = $penerimaan->tarikPelunasan();
        return response([
            'data' => $penerimaan->tarikPelunasan($id),
        ]);
    }

    public function getPelunasan($id)
    {
        $get = new PenerimaanGiroHeader();
        return response([
            'data' => $get->getPelunasan($id),
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $piutang = PenerimaanGiroHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($piutang->statuscetak != $statusSudahCetak->id) {
                $piutang->statuscetak = $statusSudahCetak->id;
                $piutang->tglbukacetak = date('Y-m-d H:i:s');
                $piutang->userbukacetak = auth('api')->user()->name;

                if ($piutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($piutang->getTable()),
                        'postingdari' => 'PRINT PIUTANG HEADER',
                        'idtrans' => $piutang->id,
                        'nobuktitrans' => $piutang->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $piutang->toArray(),
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                    DB::commit();
                }
            }


            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = PenerimaanGiroHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }

    public function cekValidasiAksi($id)
    {
        $penerimaanGiro = new PenerimaanGiroHeader();
        $nobukti = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader"))->where('id', $id)->first();
        $cekdata = $penerimaanGiro->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
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

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
