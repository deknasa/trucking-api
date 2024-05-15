<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPiutangHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\PiutangHeader;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;


use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Models\InvoiceHeader;
use App\Models\PiutangDetail;

use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Models\Agen;
use App\Models\Parameter;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PiutangHeaderController extends Controller
{
    /**
     * @ClassName 
     * PiutangHeader
     * @Detail PiutangDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $piutang = new PiutangHeader();

        return response([
            'data' => $piutang->get(),
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePiutangHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail
            ];
            $piutangHeader = (new PiutangHeader())->processStore($data);
            $piutangHeader->position = $this->getPosition($piutangHeader, $piutangHeader->getTable())->position;
            if ($request->limit == 0) {
                $piutangHeader->page = ceil($piutangHeader->position / (10));
            } else {
                $piutangHeader->page = ceil($piutangHeader->position / ($request->limit ?? 10));
            }
            $piutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $piutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $piutangHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(PiutangHeader $piutangHeader)
    {
        return response([
            'data' => $piutangHeader->load('piutangDetails', 'agen'),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePiutangHeaderRequest $request, PiutangHeader $piutangHeader)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen_id' => $request->agen_id,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal_detail' => $request->nominal_detail
            ];
            $piutang = (new PiutangHeader())->processUpdate($piutangHeader, $data);
            $piutang->position = $this->getPosition($piutang, $piutang->getTable())->position;
            if ($request->limit == 0) {
                $piutang->page = ceil($piutang->position / (10));
            } else {
                $piutang->page = ceil($piutang->position / ($request->limit ?? 10));
            }
            $piutang->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $piutang->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $piutang
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyPiutangHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $piutangHeader = (new PiutangHeader())->processDestroy($id, 'DELETE PIUTANG');
            $selected = $this->getPosition($piutangHeader, $piutangHeader->getTable(), true);
            $piutangHeader->position = $selected->position;
            $piutangHeader->id = $selected->id;
            if ($request->limit == 0) {
                $piutangHeader->page = ceil($piutangHeader->position / (10));
            } else {
                $piutangHeader->page = ceil($piutangHeader->position / ($request->limit ?? 10));
            }
            $piutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $piutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $piutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();

        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];
            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $detail = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($detail);

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => $header['postingdari'],
                'idtrans' =>  $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $piutangHeader = PiutangHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($piutangHeader->statuscetak != $statusSudahCetak->id) {
                $piutangHeader->statuscetak = $statusSudahCetak->id;
                $piutangHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $piutangHeader->userbukacetak = auth('api')->user()->name;
                $piutangHeader->jumlahcetak = $piutangHeader->jumlahcetak + 1;
                if ($piutangHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($piutangHeader->getTable()),
                        'postingdari' => 'PRINT PIUTANG HEADER',
                        'idtrans' => $piutangHeader->id,
                        'nobuktitrans' => $piutangHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $piutangHeader->toArray(),
                        'modifiedby' => $piutangHeader->modifiedby
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


    public function cekValidasiAksi($id)
    {
        $piutangHeader = new PiutangHeader();
        $nobukti = PiutangHeader::from(DB::raw("piutangheader"))->where('id', $id)->first();
        $cekdata = $piutangHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = PiutangHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();

            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
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
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }
        /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }

    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $piutang = new PiutangHeader();
        return response([
            'data' => $piutang->getExport($id)
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('piutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
}
