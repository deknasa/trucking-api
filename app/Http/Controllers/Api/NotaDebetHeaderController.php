<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalNotaDebetRequest;
use App\Http\Requests\GetIndexRangeRequest;
use Illuminate\Support\Facades\DB;
use App\Models\NotaDebetDetail;
use Illuminate\Http\Request;

use App\Models\NotaDebetHeader;
use App\Models\PelunasanPiutangHeader;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaDebetDetailRequest;
use App\Http\Requests\StoreNotaDebetHeaderRequest;
use App\Http\Requests\UpdateNotaDebetHeaderRequest;
use App\Models\Parameter;

class NotaDebetHeaderController extends Controller
{

      /**
     * @ClassName 
     * NotaDebetHeader
     * @Detail1 NotaDebetDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $notaDebetHeader = new NotaDebetHeader();
        return response([
            'data' => $notaDebetHeader->get(),
            'attributes' => [
                'totalRows' => $notaDebetHeader->totalRows,
                'totalPages' => $notaDebetHeader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StoreNotaDebetHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(NotaDebetHeader $notaDebetHeader, $id)
    {
        $data = $notaDebetHeader->findAll($id);
        // $detail = NotaDebetHeaderDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateNotaDebetHeaderRequest $request, NotaDebetHeader $notadebetheader)
    {
        DB::beginTransaction();
        try {
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
        try {
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getPelunasan($id)
    {
        $pelunasanPiutang = new PelunasanPiutangHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pelunasanPiutang->getPelunasanNotaDebet($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages
            ]
        ]);
    }
    public function getNotaDebet($id)
    {
        $notaDebet = new NotaDebetHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $notaDebet->getNotaDebet($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $notaDebet->totalRows,
                'totalPages' => $notaDebet->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     */
    public function approval(ApprovalNotaDebetRequest $request)
    {
        DB::beginTransaction();

        try {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->debetId); $i++) {
                $notaDebet = NotaDebetHeader::find($request->debetId[$i]);
                if ($notaDebet->statusapproval == $statusApproval->id) {
                    $notaDebet->statusapproval = $statusNonApproval->id;
                    $aksi = $statusNonApproval->text;
                } else {
                    $notaDebet->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                }

                $notaDebet->tglapproval = date('Y-m-d', time());
                $notaDebet->userapproval = auth('api')->user()->name;

                if ($notaDebet->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notaDebet->getTable()),
                        'postingdari' => 'APPROVAL NOTA DEBET',
                        'idtrans' => $notaDebet->id,
                        'nobuktitrans' => $notaDebet->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $notaDebet->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                }
            }
            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('notadebetheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function cekvalidasi($id)
    {
        $notaDebet = NotaDebetHeader::find($id);
        $status = $notaDebet->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $notaDebet->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $notadebet = NotaDebetHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($notadebet->statuscetak != $statusSudahCetak->id) {
                $notadebet->statuscetak = $statusSudahCetak->id;
                $notadebet->tglbukacetak = date('Y-m-d H:i:s');
                $notadebet->userbukacetak = auth('api')->user()->name;
                $notadebet->jumlahcetak = $notadebet->jumlahcetak + 1;

                if ($notadebet->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notadebet->getTable()),
                        'postingdari' => 'PRINT NOTA KREDIT HEADER',
                        'idtrans' => $notadebet->id,
                        'nobuktitrans' => $notadebet->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $notadebet->toArray(),
                        'modifiedby' => auth('api')->user()->name
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

    /**
     * @ClassName 
     */
    public function report()
    {
    }
}
