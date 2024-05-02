<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Models\Bank;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Penerima;
use App\Models\AlatBayar;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\JurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\DestroyKasGantungHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Controllers\Api\PengeluaranHeaderController;
use DateTime;

class KasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     * KasGantungHeader
     * @Detail KasGantungDetailController
     * @Keterangan TAMPILKAN DATA
     */

    public function index(GetIndexRangeRequest $request)
    {
        $kasgantungHeader = new KasGantungHeader();

        return response([
            'data' => $kasgantungHeader->get(),
            'attributes' => [
                'totalRows' => $kasgantungHeader->totalRows,
                'totalPages' => $kasgantungHeader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $kasgantungHeader = new KasGantungHeader();
        return response([
            'status' => true,
            'data' => $kasgantungHeader->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreKasGantungHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $bank = Bank::find($request->bank_id);

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '1900/1/1',
                'penerima_id' => $request->penerima_id ?? '',
                'penerima' => $request->penerima ?? '',
                'bank_id' => $request->bank_id ?? 0,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti ?? '',
                'coakaskeluar' => $bank->coa ?? '',
                'postingdari' => $request->postingdari ?? 'ENTRY KAS GANTUNG',
                'tglkaskeluar' => date('Y-m-d', strtotime($request->tglbukti)),
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $request->statusformat,
                'statuscetak' => 0 ?? '',
                'userbukacetak' => '',
                'tglbukacetak' => '',

                'nominal' => $request->nominal,
                'keterangan_detail' => $request->keterangan_detail,
            ];


            $kasgantungHeader = (new KasGantungHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $kasgantungHeader->position = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable())->position;
                if ($request->limit == 0) {
                    $kasgantungHeader->page = ceil($kasgantungHeader->position / (10));
                } else {
                    $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
                }
                $kasgantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $kasgantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $kasgantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = KasGantungHeader::findUpdate($id);
        $detail = KasGantungDetail::findUpdate($id);
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
    public function update(UpdateKasGantungHeaderRequest $request, KasGantungHeader $kasgantungheader): JsonResponse
    {
        //   dd($request->all());

        DB::beginTransaction();

        try {
            $bank = Bank::find($request->bank_id);

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '1900/1/1',
                'penerima_id' => $request->penerima_id ?? '',
                'penerima' => $request->penerima ?? '',
                'bank_id' => $request->bank_id ?? 0,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti ?? '',
                'coakaskeluar' => $bank->coa ?? '',
                'postingdari' => $request->postingdari ?? 'ENTRY KAS GANTUNG',
                'tglkaskeluar' => date('Y-m-d', strtotime($request->tglbukti)),
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $request->statusformat,
                'statuscetak' => 0 ?? '',
                'userbukacetak' => '',
                'coakredit' => '',
                'coadebet' => '',

                'nominal' => $request->nominal ?? 0,
                'keterangan_detail' => $request->keterangan_detail ?? ''
            ];

            $kasgantungHeader = (new KasGantungHeader())->processUpdate($kasgantungheader, $data);
            $kasgantungHeader->position = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable())->position;
            if ($request->limit == 0) {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / (10));
            } else {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
            }
            $kasgantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $kasgantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $kasgantungHeader
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
    public function destroy(DestroyKasGantungHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $kasgantungHeader = (new KasGantungHeader())->processDestroy($id, 'DELETE KAS GANTUNG');
            $selected = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable(), true);
            $kasgantungHeader->position = $selected->position;
            $kasgantungHeader->id = $selected->id;
            if ($request->limit == 0) {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / (10));
            } else {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
            }
            $kasgantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $kasgantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $kasgantungHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'penerima' => Penerima::all(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $kasgantungHeader = KasgantungHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($kasgantungHeader->statuscetak != $statusSudahCetak->id) {
                $kasgantungHeader->statuscetak = $statusSudahCetak->id;
                $kasgantungHeader->tglbukacetak = date('Y-m-d H:i:s');
                $kasgantungHeader->userbukacetak = auth('api')->user()->name;
                $kasgantungHeader->jumlahcetak = $kasgantungHeader->jumlahcetak + 1;
                if ($kasgantungHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($kasgantungHeader->getTable()),
                        'postingdari' => 'PRINT KAS GANTUNG HEADER',
                        'idtrans' => $kasgantungHeader->id,
                        'nobuktitrans' => $kasgantungHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $kasgantungHeader->toArray(),
                        'modifiedby' => $kasgantungHeader->modifiedby
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
        $kasgantungHeader = new KasGantungHeader();
        $nobukti = KasGantungHeader::from(DB::raw("kasgantungheader"))->where('id', $id)->first();
        $cekdata = $kasgantungHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            (new MyModel())->updateEditingBy('kasgantungheader', $id, 'EDIT');
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function cekvalidasi($id)
    {
        $kasgantung = KasGantungHeader::find($id);
        $nobukti = $kasgantung->nobukti ?? '';
        $statusdatacetak = $kasgantung->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $pengeluaran = $kasgantung->pengeluaran_nobukti ?? '';
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
        $aksi = request()->aksi ?? '';

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

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $useredit = $kasgantung->editing_by ?? '';

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
        } else if ($tgltutup >= $kasgantung->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('kasgantung header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($kasgantung->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->updateEditingBy('kasgantungheader', $id, $aksi);
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
                (new MyModel())->updateEditingBy('kasgantungheader', $id, $aksi);
            }
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
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kasgantungheader')->getColumns();

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
        $kasgantung = new KasGantungHeader();
        return response([
            'data' => $kasgantung->getExport($id)
        ]);
    }
}
