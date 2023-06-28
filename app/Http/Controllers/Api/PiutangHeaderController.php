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
     * PiutangHeaderHeader
     * @Detail1 PiutangDetailController
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
            $piutangHeader->page = ceil($piutangHeader->position / ($request->limit ?? 10));

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
            $piutang->page = ceil($piutang->position / ($request->limit ?? 10));

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
     */
    public function destroy(DestroyPiutangHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $piutangHeader = (new PiutangHeader())->processDestroy($id, 'DELETE PIUTANG');
            $selected = $this->getPosition($piutangHeader, $piutangHeader->getTable(), true);
            $piutangHeader->position = $selected->position;
            $piutangHeader->id = $selected->id;
            $piutangHeader->page = ceil($piutangHeader->position / ($request->limit ?? 10));

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
            $piutang = PiutangHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(
                DB::raw("parameter with (readuncommitted)")
            )->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

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

    /**
     * @ClassName
     */
    public function report()
    {
    }

    /**
     * @ClassName
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
