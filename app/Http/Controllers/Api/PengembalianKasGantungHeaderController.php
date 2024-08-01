<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Bank;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Else_;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\PenerimaanDetail;
use App\Models\PenerimaanHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;

use App\Models\PengembalianKasGantungDetail;
// use App\Http\Controllers\ParameterController;
use App\Models\PengembalianKasGantungHeader;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Controllers\Api\PenerimaanHeaderController;
use App\Http\Requests\GetPengembalianKasGantungHeaderRequest;
use App\Http\Requests\StorePengembalianKasGantungDetailRequest;

use App\Http\Requests\StorePengembalianKasGantungHeaderRequest;
use App\Http\Requests\UpdatePengembalianKasGantungHeaderRequest;
use App\Http\Requests\DestroyPengembalianKasGantungHeaderRequest;
use App\Models\Locking;

class PengembalianKasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     * PengembalianKasGantungHeader
     * @Detail PengembalianKasGantungDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
        return response([
            'data' => $pengembalianKasGantungHeader->get(),
            'attributes' => [
                'totalRows' => $pengembalianKasGantungHeader->totalRows,
                'totalPages' => $pengembalianKasGantungHeader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $pengembaliankasgantung = new PengembalianKasGantungHeader();
        return response([
            'status' => true,
            'data' => $pengembaliankasgantung->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengembalianKasGantungHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $pengembalianKasGantungHeader = (new PengembalianKasGantungHeader())->processStore([
                "tanpaprosesnobukti" => $request->tanpaprosesnobukti ?? null,
                "tglbukti" => $request->tglbukti ?? null,
                "bank_id" => $request->bank_id ?? null,
                "tgldari" => $request->tgldari ?? null,
                "tglsampai" => $request->tglsampai ?? null,
                "postingdari" => $request->postingdari ?? null,
                "statusformat" => $request->statusformat ?? null,
                "penerimaan_nobukti" => $request->penerimaan_nobukti ?? null,


                "nominal" => $request->nominal ?? [],
                "sisa" => $request->sisa ?? [],
                "coadetail" => $request->coadetail ?? [],
                "keterangandetail" => $request->keterangandetail ?? [],
                "kasgantung_nobukti" => $request->kasgantung_nobukti ?? [],
                "kasgantungdetail_id" => $request->kasgantungdetail_id ?? [],
            ]);
            if ($request->button == 'btnSubmit') {
                /* Set position and page */
                $pengembalianKasGantungHeader->position = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable())->position;
                if ($request->limit == 0) {
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / (10));
                } else {
                    $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));
                }
                $pengembalianKasGantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $pengembalianKasGantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengembalianKasGantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show(PengembalianKasGantungHeader $pengembalianKasGantungHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengembalianKasGantungHeader->findAll($id),
            'detail' => PengembalianKasGantungDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdatePengembalianKasGantungHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            /* Store header */
            $pengembalianKasGantungHeader = PengembalianKasGantungHeader::findOrFail($id);

            $pengembalianKasGantungHeader = (new PengembalianKasGantungHeader())->processUpdate($pengembalianKasGantungHeader, [
                "tanpaprosesnobukti" => $request->tanpaprosesnobukti ?? null,
                "tglbukti" => $request->tglbukti ?? null,
                "bank_id" => $request->bank_id ?? null,
                "tgldari" => $request->tgldari ?? null,
                "tglsampai" => $request->tglsampai ?? null,
                "postingdari" => $request->postingdari ?? null,
                "statusformat" => $request->statusformat ?? null,
                "penerimaan_nobukti" => $request->penerimaan_nobukti ?? null,

                "nominal" => $request->nominal ?? [],
                "sisa" => $request->sisa ?? [],
                "coadetail" => $request->coadetail ?? [],
                "keterangandetail" => $request->keterangandetail ?? [],
                "kasgantung_nobukti" => $request->kasgantung_nobukti ?? [],
                "kasgantungdetail_id" => $request->kasgantungdetail_id ?? [],
            ]);

            /* Set position and page */
            $pengembalianKasGantungHeader->position = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / (10));
            } else {
                $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));
            }
            $pengembalianKasGantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengembalianKasGantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengembalianKasGantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyPengembalianKasGantungHeaderRequest $request, $id)
    {

        DB::beginTransaction();
        try {

            /* delete header */
            $pengembalianKasGantungHeader = (new PengembalianKasGantungHeader())->processDestroy($id);

            /* Set position and page */
            $selected = $this->getPosition($pengembalianKasGantungHeader, $pengembalianKasGantungHeader->getTable(), true);
            $pengembalianKasGantungHeader->position = $selected->position;
            $pengembalianKasGantungHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / (10));
            } else {
                $pengembalianKasGantungHeader->page = ceil($pengembalianKasGantungHeader->position / ($request->limit ?? 10));
            }
            $pengembalianKasGantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengembalianKasGantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengembalianKasGantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('PengembalianKasGantungHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    //untuk create
    public function getKasGantung(GetPengembalianKasGantungHeaderRequest $request)
    {

        try {
            $KasGantung = new KasGantungHeader();
            $currentURL = url()->current();
            $previousURL = url()->previous();

            $dari = date('Y-m-d', strtotime($request->tgldari));
            $sampai = date('Y-m-d', strtotime($request->tglsampai));

            return response([
                'data' => $KasGantung->getKasGantung($dari, $sampai),
                'currentURL' => $currentURL,
                'previousURL' => $previousURL,
                'attributes' => [
                    'totalRows' => $KasGantung->totalRows,
                    'totalPages' => $KasGantung->totalPages
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getPengembalian(Request $request, $id, $aksi)
    {
        $pengembalianKasGantung = new PengembalianKasGantungHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        if ($aksi == 'edit') {
            $data = $pengembalianKasGantung->getPengembalian($id, $dari, $sampai);
        } else {
            $data = $pengembalianKasGantung->getDeletePengembalian($id, $dari, $sampai);
        }
        return response([
            'status' => true,
            'data' => $data
        ]);


        // $pengembalian = new PengembalianKasGantungHeader();
        // $currentURL = url()->current();
        // $previousURL = url()->previous();

        // $dari = date('Y-m-d', strtotime($request->tgldari));
        // $sampai = date('Y-m-d', strtotime($request->tglsampai));
        // dd($sampai);

        // return response([
        //     'data' => $pengembalian->getPengembalian($id),
        //     'currentURL' => $currentURL,
        //     'previousURL' => $previousURL,
        //     'attributes' => [
        //         'totalRows' => $pengembalian->totalRows,
        //         'totalPages' => $pengembalian->totalPages
        //     ]
        // ]);
        // if ($aksi == 'edit') {
        //     $data = $pengembalian->getPengembalian($id);
        // } else {
        //     $data = $pengembalian->getDeletePengembalian($id);
        // }
        // return response([
        //     'status' => true,
        //     'data' => $data
        // ]);
    }

    public function cekvalidasi($id)
    {

        $pengembaliankasgantung = PengembalianKasGantungHeader::find($id);
        $nobukti = $pengembaliankasgantung->nobukti ?? '';
        $statusdatacetak = $pengembaliankasgantung->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';

        $penerimaan = $pengembaliankasgantung->penerimaan_nobukti ?? '';
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

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pengembaliankasgantungheader', $id);
        $useredit = $getEditing->editing_by ?? '';

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
        } else if ($tgltutup >= $pengembaliankasgantung->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('pengembalian kasgantung header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'pengembaliankasgantungheader',$useredit); 
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
                (new MyModel())->createLockEditing($id, 'pengembaliankasgantungheader',$useredit); 
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
        $pengembalianKasGantung = new PengembalianKasGantungHeader();
        $nobukti = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader"))->where('id', $id)->first();
        $cekdata = $pengembalianKasGantung->cekvalidasiaksi($nobukti->nobukti);
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
            $getEditing = (new Locking())->getEditing('pengembaliankasgantungheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'pengembaliankasgantungheader',$useredit); 
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
            $pengembalianKasGantung = PengembalianKasGantungHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengembalianKasGantung->statuscetak != $statusSudahCetak->id) {
                $pengembalianKasGantung->statuscetak = $statusSudahCetak->id;
                // $pengembalianKasGantung->tglbukacetak = date('Y-m-d H:i:s');
                // $pengembalianKasGantung->userbukacetak = auth('api')->user()->name;
                $pengembalianKasGantung->jumlahcetak = $pengembalianKasGantung->jumlahcetak + 1;
                if ($pengembalianKasGantung->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengembalianKasGantung->getTable()),
                        'postingdari' => 'PRINT PENGEMBALIAN KAS GANTUNG HEADER',
                        'idtrans' => $pengembalianKasGantung->id,
                        'nobuktitrans' => $pengembalianKasGantung->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengembalianKasGantung->toArray(),
                        'modifiedby' => $pengembalianKasGantung->modifiedby
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();
        return response([
            'data' => $pengembalianKasGantungHeader->getExport($id)
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
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas()
    {
    }
}
