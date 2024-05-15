<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPemutihanSupirRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorepemutihansupirdetailRequest;
use App\Models\PemutihanSupir;
use App\Http\Requests\StorePemutihanSupirRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePemutihanSupirRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Models\Bank;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\PemutihanSupirDetail;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\Supir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PemutihanSupirController extends Controller
{
    /**
     * @ClassName 
     * PemutihanSupir
     * @Detail PemutihanSupirDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pemutihanSupir = new PemutihanSupir();
        return response([
            'data' => $pemutihanSupir->get(),
            'attributes' => [
                'totalRows' => $pemutihanSupir->totalRows,
                'totalPages' => $pemutihanSupir->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePemutihanSupirRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $nominalPosting = ($request->posting_nominal) ? array_sum($request->posting_nominal) : 0;
            $nominalNonPosting = ($request->nonposting_nominal) ? array_sum($request->nonposting_nominal) : 0;

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'supir_id' => $request->supir_id ?? '',
                'supir' => $request->supir ?? '',
                'nonposting_nominal' => $request->nonposting_nominal ?? '',
                'nonposting_nobukti' => $request->nonposting_nobukti ?? '',
                'posting_nominal' => $request->posting_nominal ?? 0,
                'pengeluaransupir' => $nominalPosting + $nominalNonPosting,
                'penerimaansupir' => $request->penerimaansupir ?? 0,
                'bank_id' => $request->bank_id ?? '',
                'coa' => $coaPengembalian->coapostingkredit ?? '',
                'pengeluarantrucking_nobukti' => $request->posting_nobukti ?? 0,
                'posting_nobukti' => $request->posting_nobukti ?? 0,
                'nominal' => $request->posting_nominal ?? 0,
                'posting_keterangan' => $request->posting_keterangan ?? '',
                'nonposting_keterangan' => $request->nonposting_keterangan ?? '',
                'postingId' => $request->postingId ?? '',
                'nonpostingId' => $request->nonpostingId ?? ''
            ];


            $pemutihanSupir = (new PemutihanSupir())->processStore($data);
            $pemutihanSupir->position = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable())->position;
            if ($request->limit == 0) {
                $pemutihanSupir->page = ceil($pemutihanSupir->position / (10));
            } else {
                $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));
            }
            $pemutihanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pemutihanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pemutihanSupir
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $data = PemutihanSupir::findAll($id);

        return response([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePemutihanSupirRequest $request, PemutihanSupir $pemutihansupir): JsonResponse
    {
        DB::beginTransaction();
        try {

            $coaPengembalian = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))->where('kodepenerimaan', 'PJP')->first();
            $nominalPosting = ($request->posting_nominal) ? array_sum($request->posting_nominal) : 0;
            $nominalNonPosting = ($request->nonposting_nominal) ? array_sum($request->nonposting_nominal) : 0;

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'supir_id' => $request->supir_id ?? '',
                'supir' => $request->supir ?? '',
                'nonposting_nominal' => $request->nonposting_nominal ?? '',
                'nonposting_nobukti' => $request->nonposting_nobukti ?? '',
                'posting_nominal' => $request->posting_nominal ?? 0,
                'pengeluaransupir' => $nominalPosting + $nominalNonPosting,
                'penerimaansupir' => $request->penerimaansupir ?? 0,
                'bank_id' => $request->bank_id ?? '',
                'coa' => $coaPengembalian->coapostingkredit ?? '',
                'pengeluarantrucking_nobukti' => $request->posting_nobukti ?? 0,
                'posting_nobukti' => $request->posting_nobukti ?? 0,
                'nominal' => $request->posting_nominal ?? 0,
                'posting_keterangan' => $request->posting_keterangan ?? '',
                'nonposting_keterangan' => $request->nonposting_keterangan ?? '',
                'postingId' => $request->postingId ?? '',
                'nonpostingId' => $request->nonpostingId ?? ''
            ];

            $pemutihanSupir = (new PemutihanSupir())->processUpdate($pemutihansupir, $data);
            if ($request->button == 'btnSubmit') {
                $pemutihanSupir->position = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable())->position;
                if ($request->limit == 0) {
                    $pemutihanSupir->page = ceil($pemutihanSupir->position / (10));
                } else {
                    $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));
                }
                $pemutihanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $pemutihanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $pemutihanSupir
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
    public function destroy(DestroyPemutihanSupirRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $pemutihanSupir = (new PemutihanSupir())->processDestroy($id, 'DELETE PEMUTIHAN SUPIR');
            $selected = $this->getPosition($pemutihanSupir, $pemutihanSupir->getTable(), true);
            $pemutihanSupir->position = $selected->position;
            $pemutihanSupir->id = $selected->id;
            if ($request->limit == 0) {
                $pemutihanSupir->page = ceil($pemutihanSupir->position / (10));
            } else {
                $pemutihanSupir->page = ceil($pemutihanSupir->position / ($request->limit ?? 10));
            }
            $pemutihanSupir->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pemutihanSupir->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pemutihanSupir
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            $post = $data->getPosting($supirId);

            return response([
                'post' => $post,
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }
    public function getNonPost()
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            $non = $data->getNonposting($supirId);
            return response([
                'non' => $non,
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getEditPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'post' => $data->getEditPost($id, $supirId),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getEditNonPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'non' => $data->getEditNonPost($id, $supirId),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }


    public function getDeletePost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'post' => $data->getDeletePost($id, $supirId),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'post' => [],
                'attributes' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function getDeleteNonPost($id)
    {
        $data = new PemutihanSupir();
        $supirId = request()->supir_id;
        if ($supirId != '') {
            return response([
                'non' => $data->getDeleteNonPost($id, $supirId),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else {
            return response([
                'non' => [],
                'attributesNon' => [
                    'totalRows' => 0,
                    'totalPages' => 0,
                ]
            ]);
        }
    }

    public function cekvalidasi($id)
    {
        $pemutihanSupir = new PemutihanSupir();
        $pemutihan = PemutihanSupir::from(DB::raw("pemutihansupirheader"))->where('id', $id)->first();
        $nobukti = $pemutihan->nobukti;
        // dd($penerimaan
        $penerimaan = $pemutihan->penerimaan_nobukti ?? '';
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
                goto cekpengeluaran;
            } else {
                return $validasipenerimaan;
            }
        }

        cekpengeluaran:
        $pengeluaran = $pemutihan->pengeluaran_nobukti ?? '';
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
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


        $now = date("Y-m-d");
        $statusdatacetak = $pemutihan->statuscetak ?? 0;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $aksi = request()->aksi ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($pemutihan->tglbukti != $now) {
            $keteranganerror = $error->cekKeteranganError('ETS') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;


            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'ETS',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else
         if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pemutihan->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
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

    public function cekvalidasiAksi($id)
    {
        $cekdata = (new PemutihanSupir())->cekvalidasiaksi($id);
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
            (new MyModel())->updateEditingBy('pemutihansupirheader', $id, 'EDIT');

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pemutihansupirheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pemutihanSupir = PemutihanSupir::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pemutihanSupir->statuscetak != $statusSudahCetak->id) {
                $pemutihanSupir->statuscetak = $statusSudahCetak->id;
                // $pemutihanSupir->tglbukacetak = date('Y-m-d H:i:s');
                // $pemutihanSupir->userbukacetak = auth('api')->user()->name;
                $pemutihanSupir->jumlahcetak = $pemutihanSupir->jumlahcetak + 1;
                if ($pemutihanSupir->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pemutihanSupir->getTable()),
                        'postingdari' => 'PRINT PEMUTIHAN SUPIR',
                        'idtrans' => $pemutihanSupir->id,
                        'nobuktitrans' => $pemutihanSupir->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pemutihanSupir->toArray(),
                        'modifiedby' => $pemutihanSupir->modifiedby
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
        $pemutihanSupir = new PemutihanSupir();
        return response([
            'data' => $pemutihanSupir->getExport($id)
        ]);
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
