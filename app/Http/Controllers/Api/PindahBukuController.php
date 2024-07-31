<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Bank;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\PindahBuku;
use Illuminate\Http\Request;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalValidasiApprovalRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePindahBukuRequest;
use App\Http\Requests\UpdatePindahBukuRequest;
use App\Http\Requests\DestroyPindahBukuRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Models\Locking;

class PindahBukuController extends Controller
{

    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $pindahBuku = new PindahBuku();

        return response([
            'data' => $pindahBuku->get(),
            'attributes' => [
                'totalRows' => $pindahBuku->totalRows,
                'totalPages' => $pindahBuku->totalPages,
            ]
        ]);
    }


    public function default()
    {
        $pindahBuku = new PindahBuku();
        return response([
            'status' => true,
            'data' => $pindahBuku->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePindahBukuRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'bankdari_id' => $request->bankdari_id,
                'bankke_id' => $request->bankke_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ];
            $pindahBuku = (new PindahBuku())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $pindahBuku->position = $this->getPosition($pindahBuku, $pindahBuku->getTable())->position;
                if ($request->limit == 0) {
                    $pindahBuku->page = ceil($pindahBuku->position / (10));
                } else {
                    $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));
                }
                $pindahBuku->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $pindahBuku->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pindahBuku
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $pindahBuku = new PindahBuku();
        return response([
            'data' => $pindahBuku->findAll($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePindahBukuRequest $request, PindahBuku $pindahbuku)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'bankdari_id' => $request->bankdari_id,
                'bankke_id' => $request->bankke_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ];
            $pindahBuku = (new PindahBuku())->processUpdate($pindahbuku, $data);
            $pindahBuku->position = $this->getPosition($pindahBuku, $pindahBuku->getTable())->position;
            if ($request->limit == 0) {
                $pindahBuku->page = ceil($pindahBuku->position / (10));
            } else {
                $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));
            }
            $pindahBuku->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pindahBuku->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $pindahBuku
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
    public function destroy(DestroyPindahBukuRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pindahBuku = (new PindahBuku())->processDestroy($id, 'DELETE PINDAH BUKU');
            $selected = $this->getPosition($pindahBuku, $pindahBuku->getTable(), true);
            $pindahBuku->position = $selected->position;
            $pindahBuku->id = $selected->id;
            if ($request->limit == 0) {
                $pindahBuku->page = ceil($pindahBuku->position / (10));
            } else {
                $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));
            }
            $pindahBuku->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pindahBuku->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pindahBuku
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

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalValidasiApprovalRequest $request)
    {
        // dd('a');

        DB::beginTransaction();

        try {
            $data = [
                'pindahId' => $request->pindahId
            ];
            $pindahBuku = (new PindahBuku())->approvalData($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
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
        $pindahBuku = new PindahBuku();
        return response([
            'data' => $pindahBuku->getExport($id)
        ]);
    }
    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pindahBuku = PindahBuku::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pindahBuku->statuscetak != $statusSudahCetak->id) {
                $pindahBuku->statuscetak = $statusSudahCetak->id;
                // $pindahBuku->tglbukacetak = date('Y-m-d H:i:s');
                // $pindahBuku->userbukacetak = auth('api')->user()->name;
                $pindahBuku->jumlahcetak = $pindahBuku->jumlahcetak + 1;
                if ($pindahBuku->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pindahBuku->getTable()),
                        'postingdari' => 'PRINT PINDAH BUKU',
                        'idtrans' => $pindahBuku->id,
                        'nobuktitrans' => $pindahBuku->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pindahBuku->toArray(),
                        'modifiedby' => $pindahBuku->modifiedby
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

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $pengeluaran = PindahBuku::find($id);
        if (!isset($pengeluaran)) {
            $keteranganerror = $error->cekKeteranganError('DTA') ?? '';
            $keterror = 'No Bukti <b>' . request()->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        }

        $nobukti = $pengeluaran->nobukti;
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $status = $pengeluaran->statusapproval ?? 0;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        $tgltutup = (new Parameter())->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pindahbuku', $id);
        $useredit = $getEditing->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $cekPencairan = DB::table("pencairangiropengeluaranheader")->from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))
            ->where('pengeluaran_nobukti', $nobukti)
            ->first();
        if ($status == $statusApproval->id) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($cekPencairan != '') {
            $keteranganerror = $error->cekKeteranganError('SCG') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pencairan giro <b>' . $cekPencairan->nobukti . '</b> <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('Pindah Buku BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                (new MyModel())->createLockEditing($id, 'pindahbuku',$useredit);
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
            (new MyModel())->createLockEditing($id, 'pindahbuku',$useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
