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
use App\Models\Locking;
use App\Models\LogTrail;
use App\Models\MyModel;
use App\Models\PengeluaranTruckingHeader;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\PenerimaanTruckingDetail;
use App\Models\Supir;
use DateTime;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                "keteranganheader" => $request->keteranganheader,
                "periodedari" => $request->periodedari,
                "periodesampai" => $request->periodesampai,
                "jenisorderan_id" => $request->jenisorderan_id,
                "penerimaantrucking_id" => $request->penerimaantrucking_id,
                "tanpaprosesnobukti" => $request->tanpaprosesnobukti,
                "coa" => $request->coa,
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "supirheader_id" => $request->supirheader_id,
                "karyawanheader_id" => $request->karyawanheader_id,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "pendapatansupir_bukti" => $request->pendapatansupir_bukti,
                "statusformat" => $request->statusformat,
                "nominal" => $request->nominal,
                "supir_id" => $request->supir_id,
                "karyawan_id" => $request->karyawan_id,
                "pengeluarantruckingheader_nobukti" => $request->pengeluarantruckingheader_nobukti,
                "keterangan" => $request->keterangan,
                "ebs" => false,
                "from" => $request->from,
            ]);
            if ($request->button == 'btnSubmit') {
                /* Set position and page */
                $penerimaanTruckingHeader->position = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable())->position;
                if ($request->limit == 0) {
                    $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / (10));
                } else {
                    $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
                }
                $penerimaanTruckingHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $penerimaanTruckingHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            }

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
                "keteranganheader" => $request->keteranganheader,
                "periodedari" => $request->periodedari,
                "periodesampai" => $request->periodesampai,
                "jenisorderan_id" => $request->jenisorderan_id,
                "penerimaantrucking_id" => $request->penerimaantrucking_id,
                "tanpaprosesnobukti" => $request->tanpaprosesnobukti,
                "coa" => $request->coa,
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "supirheader_id" => $request->supirheader_id,
                "karyawanheader_id" => $request->karyawanheader_id,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "pendapatansupir_bukti" => $request->pendapatansupir_bukti,
                "statusformat" => $request->statusformat,
                "nominal" => $request->nominal,
                "supir_id" => $request->supir_id,
                "karyawan_id" => $request->karyawan_id,
                "pengeluarantruckingheader_nobukti" => $request->pengeluarantruckingheader_nobukti,
                "keterangan" => $request->keterangan,
                "ebs" => false,
                "from" => $request->from,
            ]);
            /* Set position and page */
            $penerimaanTruckingHeader->position = $this->getPosition($penerimaanTruckingHeader, $penerimaanTruckingHeader->getTable())->position;
            if ($request->limit == 0) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / (10));
            } else {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }
            $penerimaanTruckingHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $penerimaanTruckingHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));


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
            if ($request->limit == 0) {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / (10));
            } else {
                $penerimaanTruckingHeader->page = ceil($penerimaanTruckingHeader->position / ($request->limit ?? 10));
            }
            $penerimaanTruckingHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $penerimaanTruckingHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
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
        if ($reloadGrid != null) {
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
                // $penerimaanTruckingHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $penerimaanTruckingHeader->userbukacetak = auth('api')->user()->name;
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
        $nobukti = $penerimaanTrucking->nobukti ?? '';
        $aksi = request()->aksi;
        $penerimaantrucking_id = $penerimaanTrucking->penerimaantrucking_id;
        $aco_id = db::table("penerimaantrucking")->from(db::raw("penerimaantrucking a with (readuncommitted)"))
            ->select(
                'a.aco_id'
            )->where('a.id', $penerimaantrucking_id)
            ->first()->aco_id ?? 0;

        $user_id = auth('api')->user()->id;
        $user = auth('api')->user()->user;
        $role = db::table("userrole")->from(db::raw("userrole a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->join(db::raw("acl b with (readuncommitted)"), 'a.role_id', 'b.role_id')
            ->where('a.user_id', $user_id)
            ->where('b.aco_id', $aco_id)
            // ->tosql();
            ->first();

        if ($aksi == 'EDIT' || $aksi == 'DELETE') {

            if (!isset($role)) {
                $acl = db::table('useracl')->from(db::raw("useracl a with (readuncommitted)"))
                    ->select(
                        'a.id'
                    )->where('a.user_id', $user_id)
                    ->where('a.aco_id', $aco_id)
                    ->first();

                if (!isset($acl)) {
                    $query = DB::table('error')
                        ->select(db::raw("'USER " . $user . " '+keterangan as keterangan"))
                        ->where('kodeerror', '=', 'TPH')
                        ->first();

                    $data = [
                        'error' => true,
                        'message' => $query->keterangan,
                        'kodeerror' => 'TPH',
                        'statuspesan' => 'warning',
                    ];
                    $passes = false;
                    return response($data);
                }
            }
        }

        // dd($penerimaan
        $penerimaan = $penerimaanTrucking->penerimaan_nobukti ?? '';
        $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $penerimaan)
            ->first()->id ?? 0;
        if ($idpenerimaan != 0 && ($aksi == 'EDIT' || $aksi == 'DELETE')) {
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

        $getEditing = (new Locking())->getEditing('penerimaantruckingheader', $id);
        $user = auth('api')->user()->name;
        $useredit = $getEditing->editing_by ?? '';
        if ((new PenerimaanTruckingHeader())->printValidation($id)) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;


            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $penerimaanTrucking->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('PENERIMAAN TRUCKING');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'penerimaantruckingheader', $useredit);
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
                    // 'force' => $force
                ];

                return response($data);
            }
        } else {

            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->createLockEditing($id, 'penerimaantruckingheader', $useredit);
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
        $penerimaan = new PenerimaanTruckingHeader();
        $PenerimaanTruckingHeader = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader"))->where('id', $id)->first();
        $nobukti = $PenerimaanTruckingHeader->nobukti;

        $penerimaankb = $PenerimaanTruckingHeader->penerimaan_nobukti ?? '';
        // dd($penerimaankb);
        // $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
        //     ->select(
        //         'a.id'
        //     )
        //     ->where('a.nobukti', $penerimaankb)
        //     ->first()->id ?? 0;
        // $validasipenerimaan = app(PenerimaanHeaderController::class)->cekvalidasi($idpenerimaan);
        // $msg = json_decode(json_encode($validasipenerimaan), true)['original']['error'] ?? false;
        // if ($msg == false) {
        //     goto lanjut;
        // } else {
        //     return $validasipenerimaan;
        // }



        lanjut:
        $isUangJalanProcessed = $penerimaan->isUangJalanProcessed($PenerimaanTruckingHeader->nobukti);

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        if ($isUangJalanProcessed['kondisi'] == true) {
            $keteranganerror = $error->cekKeteranganError('TDT') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $query = DB::table('error')->select(DB::raw("ltrim(rtrim(keterangan))+' (Proses Uang Jalan Supir " . $isUangJalanProcessed['nobukti'] . ")' as keterangan"))->where('kodeerror', '=', 'TDT')->first();
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TDT',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $isUangOut = $penerimaan->isUangOut($PenerimaanTruckingHeader->nobukti);
        if ($isUangOut) {
            $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SATL2',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $cekdata = $penerimaan->cekvalidasiaksi($PenerimaanTruckingHeader->nobukti);
        if ($cekdata['kondisi'] == true) {

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        }


        $getEditing = (new Locking())->getEditing('penerimaantruckingheader', $id);
        $useredit = $getEditing->editing_by ?? '';
        (new MyModel())->createLockEditing($id, 'penerimaantruckingheader', $useredit);
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
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $penerimaanTruckingHeader = new PenerimaanTruckingHeader();
        $penerimaan_TruckingHeader = $penerimaanTruckingHeader->getExport($id);

        $penerimaanTruckingDetail = new PenerimaanTruckingDetail();
        $penerimaan_TruckingDetail = $penerimaanTruckingDetail->get();

        if ($request->export == true) {
            $tglBukti = $penerimaan_TruckingHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $penerimaan_TruckingHeader->tglbukti = $dateTglBukti;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $penerimaan_TruckingHeader->judul);
            $sheet->setCellValue('A2', $penerimaan_TruckingHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:E1');
            $sheet->mergeCells('A2:E2');

            $header_start_row = 4;
            $header_right_start_row = 4;
            $detail_table_header_row = 8;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');

            $styleArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            );

            $style_number = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],

                'borders' => [
                    'top' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'right' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'bottom' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'left' => ['borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                ]
            ];
            // dd($penerimaan_TruckingHeader->statusformat);

            switch ($penerimaan_TruckingHeader->statusformat) {
                case '125':
                    // DPO

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Penerimaan',
                            'index' => 'penerimaan_nobukti',
                        ],

                    ];
                    $header_right_columns = [
                        [
                            'label' => 'Penerimaan Trucking',
                            'index' => 'penerimaantrucking_id',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                    ];


                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaan_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row++, $header_right_column['label'] . ': ' . $penerimaan_TruckingHeader->{$header_right_column['index']});
                        // $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $penerimaantrucking[$header_right_column['index']]);
                    }
                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'SUPIR',
                            'index' => 'supir_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan'
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($penerimaan_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->supir_id);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('C')->setWidth(60);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalNominal = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalNominal)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);

                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENERIMAAN TRUCKING (DPO)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;

                case '265':
                    // BBM

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Penerimaan',
                            'index' => 'penerimaan_nobukti',
                        ],

                    ];
                    $header_right_columns = [
                        [
                            'label' => 'Penerimaan Trucking',
                            'index' => 'penerimaantrucking_id',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                    ];


                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row++, $header_column['label'] . ': ' . $penerimaan_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('C' . $header_right_start_row++, $header_right_column['label'] . ': ' . $penerimaan_TruckingHeader->{$header_right_column['index']});
                    }
                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan'
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($penerimaan_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("C$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("B$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('B')->setWidth(60);

                        $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("C$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':B' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':B' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalNominal = "=SUM(C" . ($detail_table_header_row + 1) . ":C" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("C$total_start_row", $totalNominal)->getStyle("C$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("C$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);

                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENERIMAAN TRUCKING (BBM)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;

                case '410':
                    // PBT

                    $penerimaan_TruckingHeader->periodedari = date('d-m-Y', strtotime($penerimaan_TruckingHeader->periodedari));
                    $penerimaan_TruckingHeader->periodesampai = date('d-m-Y', strtotime($penerimaan_TruckingHeader->periodesampai));
                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Penerimaan',
                            'index' => 'penerimaan_nobukti',
                        ],
                        [
                            'label' => 'Tanggal Dari',
                            'index' => 'periodedari',
                        ],
                        [
                            'label' => 'Keterangan',
                            'index' => 'keteranganheader',
                        ],

                    ];
                    $header_right_columns = [
                        [
                            'label' => 'Penerimaan Trucking',
                            'index' => 'penerimaantrucking_id',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                        [
                            'label' => 'Tanggal Sampai',
                            'index' => 'periodesampai',
                        ],
                    ];


                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaan_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row++, $header_right_column['label'] . ': ' . $penerimaan_TruckingHeader->{$header_right_column['index']});
                        // $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $penerimaantrucking[$header_right_column['index']]);
                    }

                    $detail_table_header_row = 10;
                    $detail_start_row = $detail_table_header_row + 1;
                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'NO BUKTI PENGELUARAN TRUCKING',
                            'index' => 'pengeluarantruckingheader_nobukti',
                        ],
                        [
                            'label' => 'JENIS ORDER',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan'
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($penerimaan_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->pengeluarantruckingheader_nobukti);
                        $sheet->setCellValue("C$detail_start_row", $penerimaan_TruckingHeader->jenisorder_id);
                        $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("E$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('D')->setWidth(60);

                        $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalNominal = "=SUM(E" . ($detail_table_header_row + 1) . ":E" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("E$total_start_row", $totalNominal)->getStyle("E$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("E$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setWidth(17);
                    $sheet->getColumnDimension('E')->setAutoSize(true);

                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENERIMAAN TRUCKING (TTE) ' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
                case '544':
                    // DPOK

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Penerimaan',
                            'index' => 'penerimaan_nobukti',
                        ],

                    ];
                    $header_right_columns = [
                        [
                            'label' => 'Penerimaan Trucking',
                            'index' => 'penerimaantrucking_id',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                    ];


                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaantrucking[$header_column['index']]);
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row++, $header_right_column['label'] . ': ' . $penerimaantrucking[$header_right_column['index']]);
                        // $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $penerimaantrucking[$header_right_column['index']]);
                    }
                    $detail_columns = [
                        [
                            'label' => 'NO',
                        ],
                        [
                            'label' => 'KARYAWAN',
                            'index' => 'karyawan_id',
                        ],
                        [
                            'label' => 'KETERANGAN',
                            'index' => 'keterangan'
                        ],
                        [
                            'label' => 'NOMINAL',
                            'index' => 'nominal',
                            'format' => 'currency'
                        ]
                    ];

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($penerimaantrucking_details as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail[$detail_column['index']] : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail['karyawan_id']);
                        $sheet->setCellValue("C$detail_start_row", $response_detail['keterangan']);
                        $sheet->setCellValue("D$detail_start_row", $response_detail['nominal']);

                        // $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('C')->setWidth(60);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalNominal = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("D$total_start_row", $totalNominal)->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("D$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);

                    $writer = new Xlsx($spreadsheet);
                    $filename = 'LAPORAN PENERIMAAN TRUCKING (DPOK)' . date('dmYHis');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    $writer->save('php://output');
                    break;

                default:
                    //PJP & PJPK

                    $header_columns = [
                        [
                            'label' => 'No Bukti',
                            'index' => 'nobukti',
                        ],
                        [
                            'label' => 'Tanggal',
                            'index' => 'tglbukti',
                        ],
                        [
                            'label' => 'No Bukti Penerimaan',
                            'index' => 'penerimaan_nobukti',
                        ],

                    ];
                    $header_right_columns = [
                        [
                            'label' => 'Penerimaan Trucking',
                            'index' => 'penerimaantrucking_id',
                        ],
                        [
                            'label' => 'Bank',
                            'index' => 'bank_id',
                        ],
                        [
                            'label' => 'Nama Perkiraan',
                            'index' => 'coa',
                        ],
                    ];


                    //LOOPING HEADER        
                    foreach ($header_columns as $header_column) {
                        $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                        $sheet->setCellValue('C' . $header_start_row++, ': ' . $penerimaan_TruckingHeader->{$header_column['index']});
                    }
                    foreach ($header_right_columns as $header_right_column) {
                        $sheet->setCellValue('D' . $header_right_start_row++, $header_right_column['label'] . ': ' . $penerimaan_TruckingHeader->{$header_right_column['index']});
                        // $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $penerimaan_TruckingHeader->{$header_right_column['index']});
                    }
                    if ($penerimaan_TruckingHeader->statusformat == 126) {

                        $detail_columns = [
                            [
                                'label' => 'NO',
                            ],
                            [
                                'label' => 'NO BUKTI PENGELUARAN TRUCKING',
                                'index' => 'pengeluarantruckingheader_nobukti',
                            ],
                            [
                                'label' => 'SUPIR',
                                'index' => 'supir_id',
                            ],
                            [
                                'label' => 'KETERANGAN',
                                'index' => 'keterangan'
                            ],
                            [
                                'label' => 'NOMINAL',
                                'index' => 'nominal',
                                'format' => 'currency'
                            ]
                        ];
                    } else if ($penerimaan_TruckingHeader->statusformat == 370) {

                        $detail_columns = [
                            [
                                'label' => 'NO',
                            ],
                            [
                                'label' => 'NO BUKTI PENGELUARAN TRUCKING',
                                'index' => 'pengeluarantruckingheader_nobukti',
                            ],
                            [
                                'label' => 'KARYAWAN',
                                'index' => 'karyawan_id',
                            ],
                            [
                                'label' => 'KETERANGAN',
                                'index' => 'keterangan'
                            ],
                            [
                                'label' => 'NOMINAL',
                                'index' => 'nominal',
                                'format' => 'currency'
                            ]
                        ];
                    }

                    foreach ($detail_columns as $detail_columns_index => $detail_column) {
                        $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
                    }
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

                    // LOOPING DETAIL
                    $nominal = 0;
                    foreach ($penerimaan_TruckingDetail as $response_index => $response_detail) {

                        foreach ($detail_columns as $detail_columns_index => $detail_column) {
                            $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                        }

                        $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                        $sheet->setCellValue("B$detail_start_row", $response_detail->pengeluarantruckingheader_nobukti);
                        if ($penerimaan_TruckingHeader->statusformat == 126) {
                            $sheet->setCellValue("C$detail_start_row", $response_detail->supir_id);
                        } else if ($penerimaan_TruckingHeader->statusformat == 370) {
                            $sheet->setCellValue("C$detail_start_row", $response_detail->karyawan_id);
                        }
                        $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                        $sheet->setCellValue("E$detail_start_row", $response_detail->nominal);

                        // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                        $sheet->getColumnDimension('D')->setWidth(60);

                        $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                        $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                        $detail_start_row++;
                    }

                    $total_start_row = $detail_start_row;
                    $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
                    $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
                    $totalNominal = "=SUM(E" . ($detail_table_header_row + 1) . ":E" . ($detail_start_row - 1) . ")";
                    $sheet->setCellValue("E$total_start_row", $totalNominal)->getStyle("E$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                    $sheet->getStyle("E$total_start_row")->getFont()->setBold(true);

                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);

                    $writer = new Xlsx($spreadsheet);

                    if ($penerimaan_TruckingHeader->statusformat == 126) {
                        $filename = 'LAPORAN PENERIMAAN TRUCKING (PJP)' . date('dmYHis');
                    } else if ($penerimaan_TruckingHeader->statusformat == 370) {
                        $filename = 'LAPORAN PENERIMAAN TRUCKING (PJPK)' . date('dmYHis');
                    }
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Filename: ' . $filename);
                    $writer->save('php://output');
                    break;
            }
        } else {
            return response([
                'data' => $penerimaan_TruckingHeader
            ]);
        }
    }

    /**
     * @ClassName 
     * @Keterangan HUTANG BBM
     */
    public function penerimaantruckinghutangbbm() {}
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN PINJAMAN SUPIR
     */
    public function penerimaantruckingpengembalianpinjaman() {}
    /**
     * @ClassName 
     * @Keterangan DEPOSITO SUPIR
     */
    public function penerimaantruckingdepositosupir() {}
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN PINJAMAN KARYAWAN
     */
    public function penerimaantruckingpengembalianpinjamankaryawan() {}
    /**
     * @ClassName 
     * @Keterangan PENGEMBALIAN TITIPAN EMKL
     */
    public function penerimaantruckingpengembaliantitipanemkl() {}
    /**
     * @ClassName 
     * @Keterangan DEPOSITO KARYAWAN
     */
    public function penerimaantruckingdepositokaryawan() {}

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}
}
