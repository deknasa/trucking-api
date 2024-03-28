<?php

namespace App\Http\Controllers\Api;


use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\NotaKreditDetail;
use App\Models\NotaKreditHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalNotaKreditRequest;
use App\Http\Requests\StoreNotaKreditDetailRequest;
use App\Http\Requests\StoreNotaKreditHeaderRequest;
use App\Http\Requests\UpdateNotaKreditHeaderRequest;
use App\Http\Requests\DestroyNotaKreditHeaderRequest;

class NotaKreditHeaderController extends Controller
{
    /**
     * @ClassName 
     * NotaKreditHeader
     * @Detail NotaKreditDetailController
     * @Keterangan TAMPILKAN DATA
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

    public function default()
    {
        $notaDebet = new NotaKreditHeader();
        return response([
            'status' => true,
            'data' => $notaDebet->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreNotaKreditHeaderRequest $request)
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
                'keteranganpotongan' => $request->keterangan_detail,
                'potongan' => $request->nominal_detail
            ];
            $notaKreditHeader = (new NotaKreditHeader())->processStore($data);
            $notaKreditHeader->position = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable())->position;
            if ($request->limit == 0) {
                $notaKreditHeader->page = ceil($notaKreditHeader->position / (10));
            } else {
                $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));
            }
            $notaKreditHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaKreditHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
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
        $detail = (new NotaKreditDetail())->findAll($id);

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
    public function update(UpdateNotaKreditHeaderRequest $request, NotaKreditHeader $notakreditheader)
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
                'keteranganpotongan' => $request->keterangan_detail,
                'potongan' => $request->nominal_detail
            ];
            $notaKreditHeader = (new NotaKreditHeader())->processUpdate($notakreditheader, $data);
            $notaKreditHeader->position = $this->getPosition($notaKreditHeader, $notaKreditHeader->getTable())->position;
            if ($request->limit == 0) {
                $notaKreditHeader->page = ceil($notaKreditHeader->position / (10));
            } else {
                $notaKreditHeader->page = ceil($notaKreditHeader->position / ($request->limit ?? 10));
            }
            $notaKreditHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaKreditHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();
            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $notaKreditHeader
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
    public function destroy(DestroyNotaKreditHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $notaKredit = (new NotaKreditHeader())->processDestroy($id, 'DELETE NOTA KREDIT');
            $selected = $this->getPosition($notaKredit, $notaKredit->getTable(), true);
            $notaKredit->position = $selected->position;
            $notaKredit->id = $selected->id;
            if ($request->limit==0) {
                $notaKredit->page = ceil($notaKredit->position / (10));
            } else {
                $notaKredit->page = ceil($notaKredit->position / ($request->limit ?? 10));
            }
            $notaKredit->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaKredit->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $notaKredit
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
     * @Keterangan APPROVAL DATA
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
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($notaKredit == '') {
            $keterangan = $error->cekKeteranganError('DTA') ?? '';

            $keterror = $keterangan . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }
        $nobukti = $notaKredit->nobukti ?? '';

        $pengeluaran = $notaKredit->pengeluaran_nobukti ?? '';
        // dd($pengeluaran);
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';

        if ($idpengeluaran != 0) {
            $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
            $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut;
            } else {
                return $validasipengeluaran;
            }
        }

        lanjut:

        $status = $notaKredit->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $notaKredit->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $parameter = new Parameter();
        $user = auth('api')->user()->name;
        $useredit = $notaKredit->editing_by ?? '';

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($statusdatacetak == $statusCetak->id) {
             $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $notaKredit->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' )';
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {
            
            $waktu = (new Parameter())->cekBatasWaktuEdit('Nota Kredit Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($notaKredit->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('notakreditheader', $id, $aksi);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }            
            
        } else {

            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->updateEditingBy('notakreditheader', $id, $aksi);
            }

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
        $notaKreditHeader = new NotaKreditHeader();
        $cekdata = $notaKreditHeader->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            (new MyModel())->updateEditingBy('notakreditheader', $id, 'EDIT');

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
            $notaKreditHeader = NotaKreditHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($notaKreditHeader->statuscetak != $statusSudahCetak->id) {
                $notaKreditHeader->statuscetak = $statusSudahCetak->id;
                $notaKreditHeader->tglbukacetak = date('Y-m-d H:i:s');
                $notaKreditHeader->userbukacetak = auth('api')->user()->name;
                $notaKreditHeader->jumlahcetak = $notaKreditHeader->jumlahcetak + 1;
                if ($notaKreditHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notaKreditHeader->getTable()),
                        'postingdari' => 'PRINT NOTA KREDIT HEADER',
                        'idtrans' => $notaKreditHeader->id,
                        'nobuktitrans' => $notaKreditHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $notaKreditHeader->toArray(),
                        'modifiedby' => $notaKreditHeader->modifiedby
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
        $notakredit = new NotaKreditHeader();
        return response([
            'data' => $notakredit->getExport($id)
        ]);
    }
}
