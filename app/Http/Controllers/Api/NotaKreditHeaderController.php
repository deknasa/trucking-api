<?php

namespace App\Http\Controllers\Api;


use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\NotaKreditHeader;
use App\Models\NotaKreditDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalNotaKreditRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaKreditDetailRequest;
use App\Http\Requests\StoreNotaKreditHeaderRequest;
use App\Http\Requests\UpdateNotaKreditHeaderRequest;

class NotaKreditHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index(GetIndexRangeRequest $request)
    {
        $notaKreditHeader = new NotaKreditHeader();
        return response([
            'data' => $notaKreditHeader->get(),
            'attributes' => [
                'totalRows' => $notaKreditHeader->totalRows,
                'totalPages' => $notaKreditHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreNotaKreditHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $notaKreditHeader = new NotaKreditHeader();

            $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $notaKreditHeader->nobukti = $request->nobukti;
            $notaKreditHeader->pelunasanpiutang_nobukti = $request->pelunasanpiutang_nobukti;
            $notaKreditHeader->agen_id = $request->agen_id;
            $notaKreditHeader->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $notaKreditHeader->tgllunas = date('Y-m-d', strtotime($request->tgllunas));
            $notaKreditHeader->postingdari = $request->postingdari;
            $notaKreditHeader->statusformat = $request->statusformat;
            $notaKreditHeader->statusapproval = $statusApproval->id;
            $notaKreditHeader->statuscetak = $statusCetak->id;
            $notaKreditHeader->modifiedby = auth('api')->user()->name;

            $notaKreditHeader->save();

            $logTrail = [
                'namatabel' => strtoupper($notaKreditHeader->getTable()),
                'postingdari' => $request->postingdari,
                'idtrans' => $notaKreditHeader->id,
                'nobuktitrans' => $notaKreditHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $notaKreditHeader->toArray(),
                'modifiedby' => $notaKreditHeader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $detaillog = [];
            for ($i = 0; $i < count($request->datadetail); $i++) {
                $datadetail = [
                    "notakredit_id" => $notaKreditHeader->id,
                    "nobukti" =>  $notaKreditHeader->nobukti,
                    "tglterima" => $notaKreditHeader->tglbukti,
                    "invoice_nobukti" => $request->datadetail[$i]['invoice_nobukti'],
                    "nominal" => $request->datadetail[$i]['nominalpiutang'],
                    "nominalbayar" => $request->datadetail[$i]['nominal'],
                    "penyesuaian" => $request->datadetail[$i]['potongan'],
                    "keterangandetail" => $request->datadetail[$i]['keteranganpotongan'],
                    "coaadjust" => $request->datadetail[$i]['coapotongan'],
                    "modifiedby" => auth('api')->user()->name
                ];


                $data = new StoreNotaKreditDetailRequest($datadetail);
                $notaKreditDetail = app(NotaKreditDetailController::class)->store($data);

                if ($notaKreditDetail['error']) {
                    return response($notaKreditDetail, 422);
                } else {
                    $iddetail = $notaKreditDetail['id'];
                    $tabeldetail = $notaKreditDetail['tabel'];
                    $detaillog[] = $notaKreditDetail['data']->toArray();
                }
            }
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => $request->postingdari,
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $notaKreditHeader->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];
            $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            DB::commit();
            /* Set position and page */
            // $selected = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable());
            // $notaKreditHeader->position = $selected->position;
            // $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));

            // if (isset($request->limit)) {
            //     $notaKreditHeader->page = ceil($notaKreditHeader->position / $request->limit);
            // }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $notaKreditHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(NotaKreditHeader $notaKreditHeader, $id)
    {
        $data = $notaKreditHeader->findAll($id);
        // $detail = NotaKreditHeaderDetail::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            // 'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     */
    public function update(UpdateNotaKreditHeaderRequest $request, NotaKreditHeader $notakreditheader)
    {
        try {

            $notakreditheader->agen_id = $request->agen_id;
            $notakreditheader->modifiedby = auth('api')->user()->name;

            $notakreditheader->save();
            $logTrail = [
                'namatabel' => strtoupper($notakreditheader->getTable()),
                'postingdari' => $request->postingdari,
                'idtrans' => $notakreditheader->id,
                'nobuktitrans' => $notakreditheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $notakreditheader->toArray(),
                'modifiedby' => $notakreditheader->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            /* Store detail */
            $notaKreditDetail = NotaKreditDetail::where('notakredit_id', $notakreditheader->id)->lockForUpdate()->delete();

            $detaillog = [];
            for ($i = 0; $i < count($request->datadetail); $i++) {
                $datadetail = [
                    "notakredit_id" => $notakreditheader->id,
                    "nobukti" =>  $notakreditheader->nobukti,
                    "tglterima" => $notakreditheader->tglbukti,
                    "invoice_nobukti" => $request->datadetail[$i]['invoice_nobukti'],
                    "nominal" => $request->datadetail[$i]['nominalpiutang'],
                    "nominalbayar" => $request->datadetail[$i]['nominal'],
                    "penyesuaian" => $request->datadetail[$i]['potongan'],
                    "keterangandetail" => $request->datadetail[$i]['keteranganpotongan'],
                    "coaadjust" => $request->datadetail[$i]['coapotongan'],
                    "modifiedby" => auth('api')->user()->name
                ];


                $data = new StoreNotaKreditDetailRequest($datadetail);
                $notaKreditDetail = app(NotaKreditDetailController::class)->store($data);

                if ($notaKreditDetail['error']) {
                    return response($notaKreditDetail, 422);
                } else {
                    $iddetail = $notaKreditDetail['id'];
                    $tabeldetail = $notaKreditDetail['tabel'];
                    $detaillog[] = $notaKreditDetail['data']->toArray();
                }
            }
            $datalogtrail = [
                'namatabel' => strtoupper($tabeldetail),
                'postingdari' => 'EDIT NOTA KREDIT DETAIL',
                'idtrans' =>  $storedLogTrail['id'],
                'nobuktitrans' => $notakreditheader->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $detaillog,
                'modifiedby' => auth('api')->user()->name,
            ];
            $validatedLogTrail = new StoreLogTrailRequest($datalogtrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            DB::commit();


            /* Set position and page */
            // $selected = $this->getPosition($notakreditheader, $notakreditheader->getTable());
            // $notakreditheader->position = $selected->position;
            // $notakreditheader->page = ceil($notakreditheader->position / ($request->limit ?? 10));

            // if (isset($request->limit)) {
            //     $notakreditheader->page = ceil($notakreditheader->position / $request->limit);
            // }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $notakreditheader
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

        $getDetail = NotaKreditDetail::where('notakredit_id', $id)->get();

        $notaKreditHeader = new NotaKreditHeader();
        $notaKreditHeader = $notaKreditHeader->lockAndDestroy($id);

        if ($notaKreditHeader) {
            $logTrail = [
                'namatabel' => strtoupper($notaKreditHeader->getTable()),
                'postingdari' => $request['postingdari'] ?? 'DELETE NOTA KREDIT ',
                'idtrans' => $notaKreditHeader->id,
                'nobuktitrans' => $notaKreditHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $notaKreditHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE NOTA KREDIT DETAIL
            $logTrailNotaKreditDetail = [
                'namatabel' => 'NOTAKREDITDETAIL',
                'postingdari' => $request['postingdari'] ?? 'DELETE NOTA KREDIT DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $notaKreditHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailNotaKreditDetail = new StoreLogTrailRequest($logTrailNotaKreditDetail);
            app(LogTrailController::class)->store($validatedLogTrailNotaKreditDetail);

            DB::commit();

            if ($request->postingdari === null) {
                $selected = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable(), true);
                $notaKreditHeader->position = $selected->position;
                $notaKreditHeader->id = $selected->id;
                $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));
            }
            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $notaKreditHeader
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function getPelunasan($id)
    {
        $pelunasanPiutang = new PelunasanPiutangHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pelunasanPiutang->getPelunasanNotaKredit($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages
            ]
        ]);
    }
    public function getNotaKredit($id)
    {
        $notaKredit = new NotaKreditHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $notaKredit->getNotaKredit($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $notaKredit->totalRows,
                'totalPages' => $notaKredit->totalPages
            ]
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('notakreditheader')->getColumns();

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
    public function approval(ApprovalNotaKreditRequest $request)
    {
        DB::beginTransaction();

        try {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->kreditId); $i++) {
                $notaKredit = NotaKreditHeader::find($request->kreditId[$i]);
                if ($notaKredit->statusapproval == $statusApproval->id) {
                    $notaKredit->statusapproval = $statusNonApproval->id;
                    $aksi = $statusNonApproval->text;
                } else {
                    $notaKredit->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                }

                $notaKredit->tglapproval = date('Y-m-d', time());
                $notaKredit->userapproval = auth('api')->user()->name;

                if ($notaKredit->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notaKredit->getTable()),
                        'postingdari' => 'APPROVAL NOTA KREDIT',
                        'idtrans' => $notaKredit->id,
                        'nobuktitrans' => $notaKredit->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $notaKredit->toArray(),
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


    public function cekvalidasi($id)
    {
        $notaKredit = NotaKreditHeader::find($id);
        $status = $notaKredit->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $notaKredit->statuscetak;
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
            $notakredit = NotaKreditHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($notakredit->statuscetak != $statusSudahCetak->id) {
                $notakredit->statuscetak = $statusSudahCetak->id;
                $notakredit->tglbukacetak = date('Y-m-d H:i:s');
                $notakredit->userbukacetak = auth('api')->user()->name;
                $notakredit->jumlahcetak = $notakredit->jumlahcetak + 1;

                if ($notakredit->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notakredit->getTable()),
                        'postingdari' => 'PRINT NOTA KREDIT HEADER',
                        'idtrans' => $notakredit->id,
                        'nobuktitrans' => $notakredit->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $notakredit->toArray(),
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
}
