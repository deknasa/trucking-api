<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalProsesUangJalanSupirRequest;
use App\Http\Requests\DestroyPenerimaanTruckingHeaderRequest;
use App\Http\Requests\DestroyPengeluaranTruckingHeaderRequest;
use App\Http\Requests\DestroyPengembalianKasGantungHeaderRequest;
use App\Http\Requests\DestroyProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranTruckingHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\StoreProsesUangJalanSupirDetailRequest;
use App\Models\ProsesUangJalanSupirHeader;
use App\Http\Requests\StoreProsesUangJalanSupirHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranTruckingHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdateProsesUangJalanSupirHeaderRequest;
use App\Models\AbsensiSupirHeader;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\Error;
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranTrucking;
use App\Models\PengeluaranTruckingDetail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PengembalianKasGantungHeader;
use App\Models\ProsesUangJalanSupirDetail;
use App\Models\Supir;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProsesUangJalanSupirHeaderController extends Controller
{
    /**
     * @ClassName 
     * ProsesUangJalanSupirHeader
     * @Detail ProsesUangJalanSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->get(),
            'attributes' => [
                'totalRows' => $prosesUangJalanSupir->totalRows,
                'totalPages' => $prosesUangJalanSupir->totalPages
            ]
        ]);
    }

    public function default()
    {
        
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalanSupir->default(),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreProsesUangJalanSupirHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'absensisupir' => $request->absensisupir,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
                'tgltransfer' => $request->tgltransfer,
                'keterangantransfer' => $request->keterangantransfer,
                'nilaitransfer' => $request->nilaitransfer,
                'bank_idtransfer' => $request->bank_idtransfer,
                'banktransfer' => $request->banktransfer,
                'nobukti_kasbank' => $request->nobukti_kasbank,
                'tgladjust' => $request->tgladjust,
                'nilaiadjust' => $request->nilaiadjust,
                'keteranganadjust' => $request->keteranganadjust,
                'bank_idadjust' => $request->bank_idadjust,
                'bankadjust' => $request->bankadjust,
                'penerimaan_nobukti' => $request->penerimaan_nobukti,
                'nobuktideposit' => $request->nobuktideposit,
                'tgldeposit' => $request->tgldeposit,
                'nilaideposit' => $request->nilaideposit,
                'keterangandeposit' => $request->keterangandeposit,
                'bank_iddeposit' => $request->bank_iddeposit,
                'bankdeposit' => $request->bankdeposit,
                'penerimaandeposit_nobukti' => $request->penerimaandeposit_nobukti,
                'bank_idpengembalian' => $request->bank_idpengembalian,
                'nobuktipengeluaran' => $request->nobuktipengeluaran,
                'pjt_id' => $request->pjt_id,
                'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti,
                'keteranganpinjaman' => $request->keteranganpinjaman,
                'sisa' => $request->sisa,
                'nombayar' => $request->nombayar
            ];

            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $prosesUangJalanSupir->position = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable())->position;
                if ($request->limit == 0) {
                    $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
                } else {
                    $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
                }
                $prosesUangJalanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $prosesUangJalanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $prosesUangJalanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $data = ProsesUangJalanSupirHeader::findAll($id);
        $detail = new ProsesUangJalanSupirDetail();

        return response([
            'status' => true,
            'data' => $data,
            'detail' => [
                'transfer' => $detail->findTransfer($id),
                'adjust' => $detail->adjustTransfer($id),
                'deposito' => $detail->deposito($id),
                'pengembalian' => $detail->pengembalian($id),
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateProsesUangJalanSupirHeaderRequest $request, ProsesUangJalanSupirHeader $prosesuangjalansupirheader): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = [
                'tglbukti' => $request->tglbukti,
                'absensisupir' => $request->absensisupir,
                'supir_id' => $request->supir_id,
                'supir' => $request->supir,
                'trado_id' => $request->trado_id,
                'tgltransfer' => $request->tgltransfer,
                'keterangantransfer' => $request->keterangantransfer,
                'nilaitransfer' => $request->nilaitransfer,
                'bank_idtransfer' => $request->bank_idtransfer,
                'banktransfer' => $request->banktransfer,
                'nobukti_kasbank' => $request->nobukti_kasbank,
                'tgladjust' => $request->tgladjust,
                'nilaiadjust' => $request->nilaiadjust,
                'keteranganadjust' => $request->keteranganadjust,
                'bank_idadjust' => $request->bank_idadjust,
                'bankadjust' => $request->bankadjust,
                'penerimaan_nobukti' => $request->penerimaan_nobukti,
                'nobuktideposit' => $request->nobuktideposit,
                'tgldeposit' => $request->tgldeposit,
                'nilaideposit' => $request->nilaideposit,
                'keterangandeposit' => $request->keterangandeposit,
                'bank_iddeposit' => $request->bank_iddeposit,
                'bankdeposit' => $request->bankdeposit,
                'penerimaandeposit_nobukti' => $request->penerimaandeposit_nobukti,
                'bank_idpengembalian' => $request->bank_idpengembalian,
                'nobuktipengeluaran' => $request->nobuktipengeluaran,
                'pjt_id' => $request->pjt_id,
                'pengeluarantruckingheader_nobukti' => $request->pengeluarantruckingheader_nobukti,
                'keteranganpinjaman' => $request->keteranganpinjaman,
                'sisa' => $request->sisa,
                'nombayar' => $request->nombayar
            ];

            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processUpdate($prosesuangjalansupirheader, $data);
            $prosesUangJalanSupir->position = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable())->position;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            $prosesUangJalanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $prosesUangJalanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $prosesUangJalanSupir
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
    public function destroy(DestroyProsesUangJalanSupirHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $prosesUangJalanSupir = (new ProsesUangJalanSupirHeader())->processDestroy($id, 'DELETE PROSES UANG JALAN SUPIR');
            $selected = $this->getPosition($prosesUangJalanSupir, $prosesUangJalanSupir->getTable(), true);
            $prosesUangJalanSupir->position = $selected->position;
            $prosesUangJalanSupir->id = $selected->id;
            if ($request->limit == 0) {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / (10));
            } else {
                $prosesUangJalanSupir->page = ceil($prosesUangJalanSupir->position / ($request->limit ?? 10));
            }
            $prosesUangJalanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $prosesUangJalanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $prosesUangJalanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPinjaman($supirId)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPinjaman($supirId)
        ]);
    }
    public function getPengembalian($id)
    {
        $prosesUangJalan = new ProsesUangJalanSupirHeader();
        return response([
            'status' => true,
            'data' => $prosesUangJalan->getPengembalian($id)
        ]);
    }

    public function cekvalidasi($id)
    {
        $prosesUangJalan = ProsesUangJalanSupirHeader::find($id);
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($prosesUangJalan == '') {
            $keterangan = $error->cekKeteranganError('DTA') ?? '';

            $keterror = $keterangan . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'DTA',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $getDetail = DB::table("prosesuangjalansupirdetail")->from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->where('prosesuangjalansupir_id', $id)
            ->get();

        foreach ($getDetail as $row => $val) {
            if ($val->penerimaantrucking_nobukti != '') {
                $cekPenerimaan = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $val->penerimaantrucking_nobukti)
                    ->first();

                if ($cekPenerimaan != '') {
                    $penerimaan = $cekPenerimaan->penerimaan_nobukti ?? '';
                    // dd($penerimaan);
                    $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
                        ->select(
                            'a.id'
                        )
                        ->where('a.nobukti', $penerimaan)
                        ->first()->id ?? 0;
                    if ($idpenerimaan != 0) {
                        $validasipenerimaan = app(PenerimaanHeaderController::class)->cekvalidasi($idpenerimaan);
                        $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
                        if ($msg == true) {
                            return $validasipenerimaan;
                        }
                    }
                }
            }


            if ($val->pengeluarantrucking_nobukti != '') {
                $cekPengeluaran = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $val->pengeluarantrucking_nobukti)
                    ->first();

                if ($cekPengeluaran != '') {
                    $pengeluaran = $cekPengeluaran->pengeluaran_nobukti ?? '';
                    // dd($pengeluaran);
                    $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
                        ->select(
                            'a.id'
                        )
                        ->where('a.nobukti', $pengeluaran)
                        ->first()->id ?? 0;
                    if ($idpengeluaran != 0) {
                        $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
                        $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
                        if ($msg == true) {
                            return $validasipengeluaran;
                        }
                    }
                }
            }
        }

        $nobukti = $prosesUangJalan->nobukti ?? '';
        $status = $prosesUangJalan->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $prosesUangJalan->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $parameter = new Parameter();

        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('prosesuangjalansupirheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $aksi = request()->aksi;
        if ($status == $statusApproval->id && ($aksi == 'EDIT' || $aksi == 'DELETE')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $prosesUangJalan->tglbukti) {
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

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'prosesuangjalansupirheader',$useredit);  
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
            
        }else {

            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->createLockEditing($id, 'prosesuangjalansupirheader',$useredit);  
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
        $prosesUangJalan = DB::table("prosesuangjalansupirheader")->from(DB::raw("prosesuangjalansupirheader"))->where('id', $id)->first();

        $cekdata = (new ProsesUangJalanSupirHeader())->cekvalidasiaksi($prosesUangJalan->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('prosesuangjalansupirheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'prosesuangjalansupirheader',$useredit);  

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
            $prosesUangJalanSupir = ProsesUangJalanSupirHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($prosesUangJalanSupir->statuscetak != $statusSudahCetak->id) {
                $prosesUangJalanSupir->statuscetak = $statusSudahCetak->id;
                // $prosesUangJalanSupir->tglbukacetak = date('Y-m-d H:i:s');
                // $prosesUangJalanSupir->userbukacetak = auth('api')->user()->name;
                $prosesUangJalanSupir->jumlahcetak = $prosesUangJalanSupir->jumlahcetak + 1;
                if ($prosesUangJalanSupir->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
                        'postingdari' => 'PRINT PROSES UANG JALAN SUPIR HEADER',
                        'idtrans' => $prosesUangJalanSupir->id,
                        'nobuktitrans' => $prosesUangJalanSupir->id,
                        'aksi' => 'PRINT',
                        'datajson' => $prosesUangJalanSupir->toArray(),
                        'modifiedby' => $prosesUangJalanSupir->modifiedby
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();
        return response([
            'data' => $prosesUangJalanSupir->getExport($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalProsesUangJalanSupirRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'prosesId' => $request->prosesId
            ];
            $prosesUangJalan = (new ProsesUangJalanSupirHeader())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
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
    
}
