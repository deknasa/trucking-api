<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Agen;
use App\Models\Bank;
use App\Models\Error;
use App\Models\Cabang;
use App\Models\MyModel;
use App\Models\LogTrail;


use App\Models\AkunPusat;
use App\Models\AlatBayar;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\SaldoPiutang;
use Illuminate\Http\Request;
use App\Models\PiutangHeader;
use App\Models\NotaDebetHeader;
use App\Models\JurnalUmumHeader;
use App\Models\NotaKreditHeader;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PenerimaanGiroHeader;
use App\Models\PelunasanPiutangDetail;
use App\Models\PelunasanPiutangHeader;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreNotaDebetHeaderRequest;
use App\Http\Requests\StoreNotaKreditHeaderRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdateNotaDebetDetailRequest;
use App\Http\Requests\UpdateNotaDebetHeaderRequest;
use App\Http\Requests\UpdateNotaKreditHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanGiroHeaderRequest;
use App\Http\Requests\UpdatePenerimaanGiroHeaderRequest;
use App\Http\Requests\StorePelunasanPiutangDetailRequest;
use App\Http\Requests\StorePelunasanPiutangHeaderRequest;
use App\Http\Requests\UpdatePelunasanPiutangHeaderRequest;
use App\Http\Requests\DestroyPelunasanPiutangHeaderRequest;



class PelunasanPiutangHeaderController extends Controller
{
    /**
     * @ClassName 
     * PelunasanPiutangHeader
     * @Detail PelunasanPiutangDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pengeluarantruckingheader = new PelunasanPiutangHeader();
        return response([
            'data' => $pengeluarantruckingheader->get(),
            'attributes' => [
                'totalRows' => $pengeluarantruckingheader->totalRows,
                'totalPages' => $pengeluarantruckingheader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pelunasan = new PelunasanPiutangHeader();
        return response([
            'status' => true,
            'data' => $pelunasan->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePelunasanPiutangHeaderRequest $request): JsonResponse
    {

        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'agen_id' => $request->agen_id,
                'agen' => $request->agen,
                'notadebet_nobukti' => $request->notadebet_nobukti,
                'statuspelunasan' => $request->statuspelunasan,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'piutang_id' => $request->piutang_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'nominallebihbayar' => $request->nominallebihbayar,
                'bayar' => $request->bayar,
                'keterangan' => $request->keterangan,
                'potongan' => $request->potongan,
                'statusnotakredit' => $request->statusnotakredit,
                'keteranganpotongan' => $request->keteranganpotongan,
                'nominallebihbayar' => $request->nominallebihbayar,
                'statusnotadebet' => $request->statusnotadebet
            ];
            $pelunasanPiutangHeader = (new PelunasanPiutangHeader())->processStore($data);
            $pelunasanPiutangHeader->position = $this->getPosition($pelunasanPiutangHeader, $pelunasanPiutangHeader->getTable())->position;
            if ($request->limit == 0) {
                $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / (10));
            } else {
                $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / ($request->limit ?? 10));
            }
            $pelunasanPiutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pelunasanPiutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pelunasanPiutangHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        // $data = PelunasanPiutangHeader::with(
        //     'pelunasanpiutangdetail',
        // )->find($id);

        $data = PelunasanPiutangHeader::findAll($id);

        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePelunasanPiutangHeaderRequest $request, PelunasanPiutangHeader $pelunasanpiutangheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'agen_id' => $request->agen_id,
                'notadebet_nobukti' => $request->notadebet_nobukti,
                'statuspelunasan' => $request->statuspelunasan,
                'agen' => $request->agen,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'piutang_id' => $request->piutang_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'nominallebihbayar' => $request->nominallebihbayar,
                'bayar' => $request->bayar,
                'keterangan' => $request->keterangan,
                'potongan' => $request->potongan,
                'statusnotakredit' => $request->statusnotakredit,
                'keteranganpotongan' => $request->keteranganpotongan,
                'nominallebihbayar' => $request->nominallebihbayar,
                'statusnotadebet' => $request->statusnotadebet
            ];
            $pelunasanPiutangHeader = (new PelunasanPiutangHeader())->processUpdate($pelunasanpiutangheader, $data);
            $pelunasanPiutangHeader->position = $this->getPosition($pelunasanPiutangHeader, $pelunasanPiutangHeader->getTable())->position;
            if ($request->limit == 0) {
                $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / (10));
            } else {
                $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / ($request->limit ?? 10));
            }
            $pelunasanPiutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pelunasanPiutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $pelunasanPiutangHeader
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
    public function destroy(DestroyPelunasanPiutangHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pelunasanPiutangHeader = (new PelunasanPiutangHeader())->processDestroy($id, 'DELETE PELUNASAN PIUTANG');
            $selected = $this->getPosition($pelunasanPiutangHeader, $pelunasanPiutangHeader->getTable(), true);
            $pelunasanPiutangHeader->position = $selected->position;
            $pelunasanPiutangHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / (10));
            } else {
                $pelunasanPiutangHeader->page = ceil($pelunasanPiutangHeader->position / ($request->limit ?? 10));
            }
            $pelunasanPiutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pelunasanPiutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pelunasanPiutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
        // $getDetail = PelunasanPiutangDetail::where('pelunasanpiutang_id', $id)->get();

        // $request['postingdari'] = "DELETE PELUNASAN PIUTANG";
        // $pelunasanpiutangheader = new PelunasanPiutangHeader();
        // $pelunasanpiutangheader = $pelunasanpiutangheader->lockAndDestroy($id);

        // $newRequestPenerimaan = new DestroyPenerimaanHeaderRequest();
        // $newRequestPenerimaan->postingdari = "DELETE PELUNASAN PIUTANG HEADER";
        // if ($pelunasanpiutangheader) {
        //     $logTrail = [
        //         'namatabel' => strtoupper($pelunasanpiutangheader->getTable()),
        //         'postingdari' => 'DELETE PELUNASAN PIUTANG HEADER',
        //         'idtrans' => $pelunasanpiutangheader->id,
        //         'nobuktitrans' => $pelunasanpiutangheader->nobukti,
        //         'aksi' => 'DELETE',
        //         'datajson' => $pelunasanpiutangheader->toArray(),
        //         'modifiedby' => auth('api')->user()->name
        //     ];

        //     $validatedLogTrail = new StoreLogTrailRequest($logTrail);
        //     $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

        //     // DELETE PELUNASAN PIUTANG DETAIL

        //     $logTrailPiutangDetail = [
        //         'namatabel' => 'PELUNASANPIUTANGDETAIL',
        //         'postingdari' => 'DELETE PELUNASAN PIUTANG DETAIL',
        //         'idtrans' => $storedLogTrail['id'],
        //         'nobuktitrans' => $pelunasanpiutangheader->nobukti,
        //         'aksi' => 'DELETE',
        //         'datajson' => $getDetail->toArray(),
        //         'modifiedby' => auth('api')->user()->name
        //     ];

        //     $validatedLogTrailPiutangDetail = new StoreLogTrailRequest($logTrailPiutangDetail);
        //     app(LogTrailController::class)->store($validatedLogTrailPiutangDetail);

        //     if ($pelunasanpiutangheader->penerimaan_nobukti != '-') {
        //         $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->penerimaan_nobukti)->first();
        //         app(PenerimaanHeaderController::class)->destroy($newRequestPenerimaan, $getPenerimaan->id);
        //     }
        //     if ($pelunasanpiutangheader->penerimaangiro_nobukti != '-') {
        //         $getGiro = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->penerimaangiro_nobukti)->first();
        //         app(PenerimaanGiroHeaderController::class)->destroy($request, $getGiro->id);
        //     }

        //     if ($pelunasanpiutangheader->notakredit_nobukti != '-') {
        //         $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->notakredit_nobukti)->first();
        //         app(NotaKreditHeaderController::class)->destroy($request, $getNotaKredit->id);
        //     }

        //     if ($pelunasanpiutangheader->notadebet_nobukti != '-') {
        //         $getNotaDebet = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))->where('nobukti', $pelunasanpiutangheader->notadebet_nobukti)->first();
        //         app(NotaDebetHeaderController::class)->destroy($request, $getNotaDebet->id);
        //     }

        //     DB::commit();

        //     $selected = $this->getPosition($pelunasanpiutangheader, $pelunasanpiutangheader->getTable(), true);
        //     $pelunasanpiutangheader->position = $selected->position;
        //     $pelunasanpiutangheader->id = $selected->id;
        //     $pelunasanpiutangheader->page = ceil($pelunasanpiutangheader->position / ($request->limit ?? 10));

        //     return response([
        //         'status' => true,
        //         'message' => 'Berhasil dihapus',
        //         'data' => $pelunasanpiutangheader
        //     ]);
        // } else {
        //     DB::rollBack();

        //     return response([
        //         'status' => false,
        //         'message' => 'Gagal dihapus'
        //     ]);
        // }
    }

    public function getpiutang($id)
    {
        $piutang = new PiutangHeader();
        return response([
            'data' => $piutang->getPiutang($id),
            'id' => $id,
            'attributes' => [
                'totalRows' => $piutang->totalRows,
                'totalPages' => $piutang->totalPages
            ]
        ]);
    }


    public function getPelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getPelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function getDeletePelunasanPiutang($id, $agenId)
    {
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getDeletePelunasanPiutang($id, $agenId),
            'attributes' => [
                'totalRows' => $pelunasanpiutang->totalRows,
                'totalPages' => $pelunasanpiutang->totalPages
            ]
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pelunasanpiutangheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
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
        $pelunasanpiutang = new PelunasanPiutangHeader();
        return response([
            'data' => $pelunasanpiutang->getExport($id)
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pelunasanpiutang = PelunasanPiutangHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pelunasanpiutang->statuscetak != $statusSudahCetak->id) {
                $pelunasanpiutang->statuscetak = $statusSudahCetak->id;
                $pelunasanpiutang->tglbukacetak = date('Y-m-d H:i:s');
                $pelunasanpiutang->userbukacetak = auth('api')->user()->name;
                $pelunasanpiutang->jumlahcetak = $pelunasanpiutang->jumlahcetak + 1;
                if ($pelunasanpiutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pelunasanpiutang->getTable()),
                        'postingdari' => 'PRINT PELUNASAN PIUTANG HEADER',
                        'idtrans' => $pelunasanpiutang->id,
                        'nobuktitrans' => $pelunasanpiutang->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pelunasanpiutang->toArray(),
                        'modifiedby' => $pelunasanpiutang->modifiedby
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
        // dd('test');
        $pengeluaran = PelunasanPiutangHeader::find($id);


        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $pengeluarannobukti = $pengeluaran->pengeluaran_nobukti ?? '';
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluarannobukti)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';
        if ($idpengeluaran != 0) {
            $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
            $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut1;
            } else {
                return $validasipengeluaran;
            }
        }

        lanjut1:
        $penerimaan = $pengeluaran->penerimaan_nobukti ?? '';

        $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $penerimaan)
            ->first()->id ?? 0;
        if ($idpenerimaan != 0) {
            $validasipenerimaan = app(PenerimaanHeaderController::class)->cekvalidasi($idpenerimaan);
            $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut;
            } else {
                return $validasipenerimaan;
            }
        }



        lanjut:
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $nobukti = $pengeluaran->nobukti;
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $useredit = $pengeluaran->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' =>  $keterror,
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
        } else if ($useredit != '' && $useredit != $user) {
            $waktu = (new Parameter())->cekBatasWaktuEdit('Pelunasan Piutang Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($pengeluaran->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('pelunasanpiutangheader', $id, $aksi);
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
                (new MyModel())->updateEditingBy('pelunasanpiutangheader', $id, $aksi);
            }

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function cekvalidasiAksi($id)
    {
        $cekdata = (new PelunasanPiutangHeader())->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            (new MyModel())->updateEditingBy('pelunasanpiutangheader', $id, 'EDIT');

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
