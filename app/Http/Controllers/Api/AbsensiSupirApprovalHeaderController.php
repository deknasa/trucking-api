<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\AbsensiSupirHeader;
use App\Models\AbsensiSupirApprovalDetail;

use App\Models\KasGantungHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;

use App\Http\Requests\StoreAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\UpdateAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\DestroyAbsensiSupirApprovalHeaderRequest;
use App\Http\Requests\StoreAbsensiSupirApprovalDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class AbsensiSupirApprovalHeaderController extends Controller
{
    /**
     * @ClassName 
     * AbsensiSupirApprovalHeader
     * @Detail AbsensiSupirApprovalDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();

        return response([
            'data' => $absensiSupirApprovalHeader->get(),
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreAbsensiSupirApprovalHeaderRequest $request)
    {

        // dd($request->all());
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "absensisupir_nobukti" => $request->absensisupir_nobukti,
                "kasgantung_nobukti" => $request->kasgantung_nobukti,
                "pengeluaran_nobukti" => $request->pengeluaran_nobukti,
                "tglkaskeluar" => $request->tglkaskeluar,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
            ];
            /* Store header */
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirApprovalHeader->position = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable())->position;
            if ($request->limit == 0) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / (10));
            } else {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            }
            $absensiSupirApprovalHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $absensiSupirApprovalHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function show(AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, $id)
    {
        $data = $absensiSupirApprovalHeader->find($id);
        $detail = AbsensiSupirApprovalDetail::getAll($id);

        // dd($detail);
        //  $detail = NotaDebetHeaderDetail::findAll($id);

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
    public function update(UpdateAbsensiSupirApprovalHeaderRequest $request, AbsensiSupirApprovalHeader $absensiSupirApprovalHeader, $id)
    {
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "absensisupir_nobukti" => $request->absensisupir_nobukti,
                "kasgantung_nobukti" => $request->kasgantung_nobukti,
                "pengeluaran_nobukti" => $request->pengeluaran_nobukti,
                "tglkaskeluar" => $request->tglkaskeluar,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'uangjalan' => $request->uangjalan,
            ];
            /* Store header */
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processStore($data);
            /* Set position and page */
            $absensiSupirApprovalHeader->position = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable())->position;
            if ($request->limit == 0) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / (10));
            } else {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            }
            $absensiSupirApprovalHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $absensiSupirApprovalHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        return response($request->all(), 442);
    }
    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyAbsensiSupirApprovalHeaderRequest $request, $id)
    {


        DB::beginTransaction();
        try {
            // dd($absensiSupirApprovalHeader);
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processDestroy($id);
            /* Set position and page */
            $selected = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable(), true);
            $absensiSupirApprovalHeader->position = $selected->position;
            $absensiSupirApprovalHeader->id = $selected->id;
            if ($request->limit == 0) {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / (10));
            } else {
                $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
            }
            $absensiSupirApprovalHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $absensiSupirApprovalHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $absensiSupirApprovalHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan APPROVAL DATA
     */
    public function approval($id)
    {
        DB::beginTransaction();
        $absensiSupirApprovalHeader = AbsensiSupirApprovalHeader::lockForUpdate()->findOrFail($id);
        try {
            $statusApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            if ($absensiSupirApprovalHeader->statusapproval == $statusApproval->id) {
                $absensiSupirApprovalHeader->statusapproval = $statusNonApproval->id;
            } else {
                $absensiSupirApprovalHeader->statusapproval = $statusApproval->id;
            }

            $absensiSupirApprovalHeader->tglapproval = date('Y-m-d', time());
            $absensiSupirApprovalHeader->userapproval = auth('api')->user()->name;

            if ($absensiSupirApprovalHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($absensiSupirApprovalHeader->getTable()),
                    'postingdari' => 'UN/APPROVE ABSENSI SUPIR APPROVAL',
                    'idtrans' => $absensiSupirApprovalHeader->id,
                    'nobuktitrans' => $absensiSupirApprovalHeader->id,
                    'aksi' => 'UN/APPROVE',
                    'datajson' => $absensiSupirApprovalHeader->toArray(),
                    'modifiedby' => $absensiSupirApprovalHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil',
                'data' => $absensiSupirApprovalHeader
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength(Type $var = null)
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('AbsensiSupirApprovalHeader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getAbsensi($absensi)
    {
        $absensiSupir = new AbsensiSupirHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupir->getAbsensi($absensi),
            // 'data' => $absensi,
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupir->totalRows,
                'totalPages' => $absensiSupir->totalPages,
                'totalUangJalan' => $absensiSupir->totalUangJalan,
            ]
        ]);
    }


    public function cekvalidasi($id)
    {
        $absensisupirapproval = AbsensiSupirApprovalHeader::find($id);

        //validasi cetak
        $printValidation = AbsensiSupirApprovalHeader::printValidation($id);
        if (!$printValidation) {
            $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SDC')->first();
            $data = [
                'error' => true,
                'message' =>  'No Bukti ' . $absensisupirapproval->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SDC',
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
        $absensiSupirHeader = new AbsensiSupirApprovalHeader();
        $cekdata = $absensiSupirHeader->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', 'SATL')
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


    public function getApproval($absensi)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensiSupir = $absensiSupirApprovalHeader->find($absensi);
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $absensiSupirApprovalHeader->getApproval($absensiSupir->absensisupir_nobukti),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $absensiSupirApprovalHeader->totalRows,
                'totalPages' => $absensiSupirApprovalHeader->totalPages
            ]
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $absensisupirapproval = AbsensiSupirApprovalHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($absensisupirapproval->statuscetak != $statusSudahCetak->id) {
                $absensisupirapproval->statuscetak = $statusSudahCetak->id;
                $absensisupirapproval->tglbukacetak = date('Y-m-d H:i:s');
                $absensisupirapproval->userbukacetak = auth('api')->user()->name;
                $absensisupirapproval->jumlahcetak = $absensisupirapproval->jumlahcetak + 1;
                if ($absensisupirapproval->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($absensisupirapproval->getTable()),
                        'postingdari' => 'PRINT ABSENSI SUPIR APPROVAL HEADER',
                        'idtrans' => $absensisupirapproval->id,
                        'nobuktitrans' => $absensisupirapproval->id,
                        'aksi' => 'PRINT',
                        'datajson' => $absensisupirapproval->toArray(),
                        'modifiedby' => $absensisupirapproval->modifiedby
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
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();

        return response([
            'data' => $absensiSupirApprovalHeader->getExport($id)
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
}
