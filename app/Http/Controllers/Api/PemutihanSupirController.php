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
use App\Models\Locking;
use App\Models\MyModel;
use App\Models\Parameter;
use App\Models\PemutihanSupirDetail;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanTrucking;
use App\Models\Supir;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
                'supir_id' => $request->supir_id ?? 0,
                'supir' => $request->supir ?? '',
                'karyawan_id' => $request->karyawan_id ?? 0,
                'karyawan' => $request->karyawan ?? '',
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
            $pemutihanSupir->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $pemutihanSupir->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

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
                'supir_id' => $request->supir_id ?? 0,
                'supir' => $request->supir ?? '',
                'karyawan_id' => $request->karyawan_id ?? 0,
                'karyawan' => $request->karyawan ?? '',
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
                $pemutihanSupir->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $pemutihanSupir->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
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
            $pemutihanSupir->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $pemutihanSupir->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

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
        $supirId = request()->supir_id ?? 0;
        $karyawanId = request()->karyawan_id ?? 0;
        if ($supirId != 0) {
            $post = $data->getPosting($supirId, 'supir');

            return response([
                'post' => $post,
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else if ($karyawanId != 0) {
            $post = $data->getPosting($karyawanId, 'karyawan');

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
        $supirId = request()->supir_id ?? 0;
        $karyawanId = request()->karyawan_id ?? 0;
        if ($supirId != 0) {
            $non = $data->getNonposting($supirId, 'supir');
            return response([
                'non' => $non,
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else if ($karyawanId != 0) {
            $non = $data->getNonposting($karyawanId, 'karyawan');
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
        $supirId = request()->supir_id ?? 0;
        $karyawanId = request()->karyawan_id ?? 0;
        if ($supirId != 0) {
            return response([
                'post' => $data->getEditPost($id, $supirId, 'supir'),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else if ($karyawanId != 0) {
            return response([
                'post' => $data->getEditPost($id, $karyawanId, 'karyawan'),
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
        $supirId = request()->supir_id ?? 0;
        $karyawanId = request()->karyawan_id ?? 0;
        if ($supirId != 0) {
            return response([
                'non' => $data->getEditNonPost($id, $supirId, 'supir'),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else if ($karyawanId != 0) {
            return response([
                'non' => $data->getEditNonPost($id, $karyawanId, 'karyawan'),
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
        $supirId = request()->supir_id ?? 0;
        $karyawanId = request()->karyawan_id ?? 0;
        if ($supirId != 0) {
            return response([
                'post' => $data->getDeletePost($id, $supirId, 'supir'),
                'attributes' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else if ($karyawanId != 0) {
            return response([
                'post' => $data->getDeletePost($id, $karyawanId, 'karyawan'),
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
        $supirId = request()->supir_id ?? 0;
        $karyawanId = request()->karyawan_id ?? 0;
        if ($supirId != 0) {
            return response([
                'non' => $data->getDeleteNonPost($id, $supirId, 'supir'),
                'attributesNon' => [
                    'totalRows' => $data->totalRows,
                    'totalPages' => $data->totalPages,
                ]
            ]);
        } else if ($karyawanId != 0) {
            return response([
                'non' => $data->getDeleteNonPost($id, $karyawanId, 'karyawan'),
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
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pemutihansupirheader', $id);
        $useredit = $getEditing->editing_by ?? '';

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
        } else if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('Nota Kredit Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'pemutihansupirheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'pemutihansupirheader', $useredit);
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
            $getEditing = (new Locking())->getEditing('pemutihansupirheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'pemutihansupirheader', $useredit);

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
    public function report() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $pemutihanSupirHeader = new PemutihanSupir();
        $pemutihan_SupirHeader = $pemutihanSupirHeader->getExport($id);

        $pemutihanSupirDetail = new PemutihanSupirDetail();
        $pemutihan_SupirDetail = $pemutihanSupirDetail->get();

        if ($request->export == true) {
            $tglBukti = $pemutihan_SupirHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $pemutihan_SupirHeader->tglbukti = $dateTglBukti;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pemutihan_SupirHeader->judul);
            $sheet->setCellValue('A2', $pemutihan_SupirHeader->judulLaporan);
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
                    'label' => 'Supir',
                    'index' => 'supir',
                ]
            ];
            $header_right_columns = [
                [
                    'label' => 'Bank',
                    'index' => 'bank',
                ],
                [
                    'label' => 'No Bukti Penerimaan',
                    'index' => 'penerimaan_nobukti',
                ],
                [
                    'label' => 'Nama Perkiraan',
                    'index' => 'coa',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NO BUKTI PENGELUARAN TRUCKING',
                    'index' => 'pengeluarantrucking_nobukti',
                ],
                [
                    'label' => 'STATUS POSTING',
                    'index' => 'statusposting',
                ],
                [
                    'label' => 'NOMINAL',
                    'index' => 'nominal',
                    'format' => 'currency'
                ]
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $pemutihan_SupirHeader->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $pemutihan_SupirHeader->{$header_right_column['index']});
            }
            foreach ($detail_columns as $detail_columns_index => $detail_column) {
                $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_table_header_row, $detail_column['label'] ?? $detail_columns_index + 1);
            }
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
            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $nominal = 0;
            foreach ($pemutihan_SupirDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->pengeluarantrucking_nobukti);
                $sheet->setCellValue("C$detail_start_row", $response_detail->statusposting);
                $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($style_number)->getFont()->setBold(true);
            $total = "=SUM(D" . ($detail_table_header_row + 1) . ":D" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("D$total_start_row", $total)->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("D$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Pemutihan Supir' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $pemutihan_SupirHeader
            ]);
        }
    }

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
