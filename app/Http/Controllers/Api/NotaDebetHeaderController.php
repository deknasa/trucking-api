<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalNotaDebetRequest;
use App\Http\Requests\DestroyNotaDebetHeaderRequest;
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
     * @Detail NotaDebetDetailController
     * @Keterangan TAMPILKAN DATA
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

    public function default()
    {
        $notaDebet = new NotaDebetHeader();
        return response([
            'status' => true,
            'data' => $notaDebet->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreNotaDebetHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen' => $request->agen,
                'agen_id' => $request->agen_id,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'pelunasanpiutang_nobukti' => '',
                'tanpaprosesnobukti' => 0,
                'keterangan_detail' => $request->keterangan_detail,
                'nominallebihbayar' => $request->nominal_detail
            ];
            $notaDebetHeader = (new NotaDebetHeader())->processStore($data);
            $notaDebetHeader->position = $this->getPosition($notaDebetHeader, $notaDebetHeader->getTable())->position;
            if ($request->limit == 0) {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / (10));
            } else {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / ($request->limit ?? 10));
            }
            $notaDebetHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaDebetHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $notaDebetHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(NotaDebetHeader $notaDebetHeader, $id)
    {
        $data = $notaDebetHeader->findAll($id);
        $detail = (new NotaDebetDetail())->findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateNotaDebetHeaderRequest $request, NotaDebetHeader $notadebetheader)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen' => $request->agen,
                'agen_id' => $request->agen_id,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'pelunasanpiutang_nobukti' => '',
                'tanpaprosesnobukti' => 0,
                'keterangan_detail' => $request->keterangan_detail,
                'nominallebihbayar' => $request->nominal_detail
            ];
            $notaDebetHeader = (new NotaDebetHeader())->processUpdate($notadebetheader, $data);
            $notaDebetHeader->position = $this->getPosition($notaDebetHeader, $notaDebetHeader->getTable())->position;
            if ($request->limit == 0) {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / (10));
            } else {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / ($request->limit ?? 10));
            }
            $notaDebetHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaDebetHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();
            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $notaDebetHeader
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
    public function destroy(DestroyNotaDebetHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $notaDebet = (new NotaDebetHeader())->processDestroy($id, 'DELETE NOTA DEBET');
            $selected = $this->getPosition($notaDebet, $notaDebet->getTable(), true);
            $notaDebet->position = $selected->position;
            $notaDebet->id = $selected->id;
            if ($request->limit==0) {
                $notaDebet->page = ceil($notaDebet->position / (10));
            } else {
                $notaDebet->page = ceil($notaDebet->position / ($request->limit ?? 10));
            }
            $notaDebet->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaDebet->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $notaDebet
            ]);
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
     * @Keterangan APPROVAL DATA
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
        $aksi = request()->aksi ?? '';

        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
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

    
    public function cekValidasiAksi($id)
    {
        $notaDebetHeader = new NotaDebetHeader();
        $cekdata = $notaDebetHeader->cekvalidasiaksi($id);
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
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $notaDebetHeader = NotaDebetHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($notaDebetHeader->statuscetak != $statusSudahCetak->id) {
                $notaDebetHeader->statuscetak = $statusSudahCetak->id;
                $notaDebetHeader->tglbukacetak = date('Y-m-d H:i:s');
                $notaDebetHeader->userbukacetak = auth('api')->user()->name;
                $notaDebetHeader->jumlahcetak = $notaDebetHeader->jumlahcetak + 1;
                if ($notaDebetHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notaDebetHeader->getTable()),
                        'postingdari' => 'PRINT NOTA DEBET HEADER',
                        'idtrans' => $notaDebetHeader->id,
                        'nobuktitrans' => $notaDebetHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $notaDebetHeader->toArray(),
                        'modifiedby' => $notaDebetHeader->modifiedby
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $notadebet = new NotaDebetHeader();
        return response([
            'data' => $notadebet->getExport($id)
        ]);
    }
}
