<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanTruckingHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\GetPenerimaanTruckingHeaderRequest;
use App\Models\PenerimaanTruckingHeader;
use App\Models\PenerimaanTruckingTruckingHeader;
use App\Http\Requests\StorePenerimaanTruckingHeaderRequest;
use App\Http\Requests\UpdatePenerimaanTruckingHeaderRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanTruckingDetailRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Bank;
use App\Models\Error;
use App\Models\LogTrail;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\Supir;
use Illuminate\Database\QueryException;

class PenerimaanTruckingHeaderController extends Controller
{

    /**
     * @ClassName 
     * PenerimaanTruckingHeader
     * @Detail PenerimaanTruckingDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetPenerimaanTruckingHeaderRequest $request)
    {
        $penerimaantruckingheader = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaantruckingheader->get(),
            'attributes' => [
                'totalRows' => $penerimaantruckingheader->totalRows,
                'totalPages' => $penerimaantruckingheader->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePenerimaanTruckingHeaderRequest $request)
    {

        DB::beginTransaction();
        try {
            /* Store header */
            $penerimaanTruckingHeader = (new PenerimaanTruckingHeader())->processStore([
                "keteranganheader" =>$request->keteranganheader,
                "periodedari" =>$request->periodedari,
                "periodesampai" =>$request->periodesampai,
                "jenisorderan_id" =>$request->jenisorderan_id,
                "penerimaantrucking_id" =>$request->penerimaantrucking_id,
                "tanpaprosesnobukti" =>$request->tanpaprosesnobukti,
                "coa" =>$request->coa,
                "bank_id" =>$request->bank_id,
                "tglbukti" =>$request->tglbukti,
                "supirheader_id" =>$request->supirheader_id,
                "karyawanheader_id" =>$request->karyawanheader_id,
                "penerimaan_nobukti" =>$request->penerimaan_nobukti,
                "pendapatansupir_bukti" =>$request->pendapatansupir_bukti,
                "statusformat" =>$request->statusformat,
                "nominal" =>$request->nominal,
                "supir_id" =>$request->supir_id,
                "karyawan_id" =>$request->karyawan_id,
                "pengeluarantruckingheader_nobukti" =>$request->pengeluarantruckingheader_nobukti,
                "keterangan" =>$request->keterangan,
                "ebs" => false,
                "from" =>$request->from,
            ]);
            /* Set position and page */
            $penerimaanTruckingHeader->position = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable())->position;
            if ($request->limit==0) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / (10));
            } else {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }
            $penerimaanTruckingHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanTruckingHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTruckingHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = PenerimaanTruckingHeader::findAll($id);
        $detail = PenerimaanTruckingDetail::getAll($id);

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
    public function update(UpdatePenerimaanTruckingHeaderRequest $request, PenerimaanTruckingHeader $penerimaantruckingheader)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            // PenerimaanTruckingHeader::findOrFail($id);
            $penerimaanTruckingHeader = (new PenerimaanTruckingHeader())->processUpdate($penerimaantruckingheader, [
                "keteranganheader" =>$request->keteranganheader,
                "periodedari" =>$request->periodedari,
                "periodesampai" =>$request->periodesampai,
                "jenisorderan_id" =>$request->jenisorderan_id,
                "penerimaantrucking_id" =>$request->penerimaantrucking_id,
                "tanpaprosesnobukti" =>$request->tanpaprosesnobukti,
                "coa" =>$request->coa,
                "bank_id" =>$request->bank_id,
                "tglbukti" =>$request->tglbukti,
                "supirheader_id" =>$request->supirheader_id,
                "karyawanheader_id" =>$request->karyawanheader_id,
                "penerimaan_nobukti" =>$request->penerimaan_nobukti,
                "pendapatansupir_bukti" =>$request->pendapatansupir_bukti,
                "statusformat" =>$request->statusformat,
                "nominal" =>$request->nominal,
                "supir_id" =>$request->supir_id,
                "karyawan_id" =>$request->karyawan_id,
                "pengeluarantruckingheader_nobukti" =>$request->pengeluarantruckingheader_nobukti,
                "keterangan" =>$request->keterangan,
                "ebs" => false,
                "from" =>$request->from,
            ]);
            /* Set position and page */
            $penerimaanTruckingHeader->position = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable())->position;
            if ($request->limit==0) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / (10));
            } else {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }
            $penerimaanTruckingHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanTruckingHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanTruckingHeader
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
    public function destroy(DestroyPenerimaanTruckingHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerimaanTruckingHeader = (new PenerimaanTruckingHeader())->processDestroy($id, "PENERIMAAN TRUCKING HEADER");
            $selected = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable(), true);
            $penerimaanTruckingHeader->position = $selected->position;
            $penerimaanTruckingHeader->id = $selected->id;
            if ($request->limit==0) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / (10));
            } else {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }
            $penerimaanTruckingHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $penerimaanTruckingHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanTruckingHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function getPengembalianPinjaman($id, $aksi)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $getSupir = $penerimaanTrucking->find($id);
        if ($aksi == 'edit') {
            $data = $penerimaanTrucking->getPengembalianPinjaman($id, $getSupir->supir_id);
        } else {
            $data = $penerimaanTrucking->getDeletePengembalianPinjaman($id, $getSupir->supir_id);
        }
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getPengembalianPinjamanKaryawan($id, $aksi)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $getSupir = $penerimaanTrucking->find($id);
        if ($aksi == 'edit') {
            $data = $penerimaanTrucking->getPengembalianPinjamanKaryawan($id, $getSupir->karyawan_id);
        } else {
            $data = $penerimaanTrucking->getDeletePengembalianPinjamanKaryawan($id, $getSupir->karyawan_id);
        }
        return response([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getPinjaman($supir_id)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaanTrucking->getPinjaman($supir_id)
        ]);
    }

    public function getDataPengembalianTitipan(Request $request)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        $reloadGrid = $request->reloadGrid;
        if($reloadGrid != null){
            $data = $penerimaanTrucking->getPengembalianTitipanReload([
                "periodedari" => $request->periodedari,
                "periodesampai" => $request->periodesampai,
                "jenisorderan_id" => $request->jenisorderan_id,
                'id' => $request->id
            ]);
        } else {
            $data = $penerimaanTrucking->getPengembalianTitipan([
                "periodedari" => $request->periodedari,
                "periodesampai" => $request->periodesampai,
                "jenisorderan_id" => $request->jenisorderan_id,
                'id' => $request->id
            ]);
        }
        return response([
            'data' => $data
        ]);
    }
    public function getDataPengembalianTitipanShow($id)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaanTrucking->getPengembalianTitipanShow($id)
        ]);
    }

    public function getPinjamanKaryawan($karyawan_id)
    {
        $penerimaanTrucking = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaanTrucking->getPinjamanKaryawan($karyawan_id)
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanTruckingHeader = PenerimaanTruckingHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanTruckingHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanTruckingHeader->statuscetak = $statusSudahCetak->id;
                $penerimaanTruckingHeader->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanTruckingHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanTruckingHeader->jumlahcetak = $penerimaanTruckingHeader->jumlahcetak + 1;
                if ($penerimaanTruckingHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanTruckingHeader->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN TRUCKING HEADER',
                        'idtrans' => $penerimaanTruckingHeader->id,
                        'nobuktitrans' => $penerimaanTruckingHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanTruckingHeader->toArray(),
                        'modifiedby' => $penerimaanTruckingHeader->modifiedby
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
        $penerimaanTrucking = PenerimaanTruckingHeader::find($id);

        if ((new PenerimaanTruckingHeader())->printValidation($id)) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();

            $data = [
                'error' => true,
                'message' => $query->keterangan,
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
        $penerimaan = new PenerimaanTruckingHeader();
        $PenerimaanTruckingHeader = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader"))->where('id', $id)->first();

        $isUangJalanProcessed = $penerimaan->isUangJalanProcessed($PenerimaanTruckingHeader->nobukti);
        if ($isUangJalanProcessed) {
            $query = DB::table('error')->select(DB::raw("ltrim(rtrim(keterangan))+' (Proses Uang Jalan Supir )' as keterangan"))->where('kodeerror', '=', 'TDT')->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => 'TDT',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $isUangOut = $penerimaan->isUangOut($PenerimaanTruckingHeader->nobukti);
        if ($isUangOut) {
            $query = DB::table('error')->select(DB::raw("ltrim(rtrim(keterangan))+' (Proses Uang Jalan Supir )' as keterangan"))->where('kodeerror', '=', 'SATL')->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'kodeerror' => 'SATL',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $cekdata = $penerimaan->cekvalidasiaksi($PenerimaanTruckingHeader->nobukti);
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
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        }

        $data = [
            'error' => false,
            'message' => '',
            'statuspesan' => 'success',
        ];

        return response($data);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('penerimaantruckingheader')->getColumns();

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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $penerimaantruckingheader = new PenerimaanTruckingHeader();
        return response([
            'data' => $penerimaantruckingheader->getExport($id)
        ]);
    }

     /**
     * @ClassName 
     * @Keterangan HUTANG BBM
     */
    public function penerimaantruckinghutangbbm()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN PINJAMAN SUPIR
     */
    public function penerimaantruckingpengembalianpinjaman()
    {
    }
    /**
     * @ClassName 
     * @Keterangan DEPOSITO SUPIR
     */
    public function penerimaantruckingdepositosupir()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN PINJAMAN KARYAWAN
     */
    public function penerimaantruckingpengembalianpinjamankaryawan()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN TITIPAN EMKL
     */
    public function penerimaantruckingpengembaliantitipanemkl()
    {
    }
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN DEPOSITO KARYAWAN
     */
    public function penerimaantruckingpengembaliandepositokaryawan()
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
