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
use App\Models\Error;
use App\Models\Locking;
use Exception;
use App\Models\MyModel;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
            ];
            /* Store header */
            $absensiSupirApprovalHeader = (new AbsensiSupirApprovalHeader())->processStore($data);

            if ($request->button == 'btnSubmit') {
                /* Set position and page */
                $absensiSupirApprovalHeader->position = $this->getPosition($absensiSupirApprovalHeader, $absensiSupirApprovalHeader->getTable())->position;
                if ($request->limit == 0) {
                    $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / (10));
                } else {
                    $absensiSupirApprovalHeader->page = ceil($absensiSupirApprovalHeader->position / ($request->limit ?? 10));
                }
                $absensiSupirApprovalHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $absensiSupirApprovalHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            }
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
            $absensiSupirApprovalHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $absensiSupirApprovalHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

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
            $absensiSupirApprovalHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $absensiSupirApprovalHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

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
        $nobukti = $absensisupirapproval->nobukti ?? '';
        $pengeluaran = $absensisupirapproval->pengeluaran_nobukti ?? '';
        $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $pengeluaran)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';
        if ($idpengeluaran != 0) {
            $validasipengeluaran = app(PengeluaranHeaderController::class)->cekvalidasi($idpengeluaran);
            $msg = json_decode(json_encode($validasipengeluaran), true)['original']['error'] ?? false;
            if ($msg == false) {
                goto lanjut;
            } else {
                return $validasipengeluaran;
            }
        }


        //validasi cetak
        lanjut:
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('absensisupirapprovalheader', $id);
        $useredit = $getEditing->editing_by ?? '';
        $aksi = request()->aksi ?? '';

        $printValidation = AbsensiSupirApprovalHeader::printValidation($id);
        if (!$printValidation) {
            // $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'SDC')->first();
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' => $keterror,
                // 'message' =>  'No Bukti ' . $absensisupirapproval->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $absensisupirapproval->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('ABSENSI SUPIR APPROVAL BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    // (new MyModel())->updateEditingBy('absensisupirapprovalheader', $id, $aksi);
                    (new MyModel())->createLockEditing($id, 'absensisupirapprovalheader', $useredit);
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
                // (new MyModel())->updateEditingBy('absensisupirapprovalheader', $id, $aksi);
                (new MyModel())->createLockEditing($id, 'absensisupirapprovalheader', $useredit);
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
        $absensiSupirHeader = new AbsensiSupirApprovalHeader();
        $nobukti = AbsensiSupirApprovalHeader::from(DB::raw("AbsensiSupirApprovalHeader"))->where('id', $id)->first();

        $cekdata = $absensiSupirHeader->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', 'SATL')
            //     ->first();

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('AbsensiSupirApprovalHeader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'AbsensiSupirApprovalHeader', $useredit);
            // (new MyModel())->updateEditingBy('AbsensiSupirApprovalHeader', $id, 'EDIT');

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
                'totalPages' => $absensiSupirApprovalHeader->totalPages,
                'totalUangJalan' => $absensiSupirApprovalHeader->totalUangJalan
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
                // $absensisupirapproval->tglbukacetak = date('Y-m-d H:i:s');
                // $absensisupirapproval->userbukacetak = auth('api')->user()->name;
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
    public function export($id, Request $request)
    {
        $absensiSupirApprovalHeader = new AbsensiSupirApprovalHeader();
        $absensi_SupirApprovalHeader = $absensiSupirApprovalHeader->getExport($id);
        
        if ($request->export == true) {

            $absensiSupirApprovalDetail = new AbsensiSupirApprovalDetail();
            $absensi_SupirApprovalDetail = $absensiSupirApprovalDetail->get();

            $data = $absensi_SupirApprovalHeader->statusapproval;
            $result = json_decode($data, true);
            $parameters = $result['MEMO'];
            $absensi_SupirApprovalHeader->statusapproval =  $parameters;

            $tglBukti = $absensi_SupirApprovalHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $absensi_SupirApprovalHeader->tglbukti = $dateTglBukti;

            $tglKasKeluar = $absensi_SupirApprovalHeader->tglkaskeluar;
            $timeStamp = strtotime($tglKasKeluar);
            $dateKasKeluar = date('d-m-Y', $timeStamp);
            $absensi_SupirApprovalHeader->tglkaskeluar = $dateKasKeluar;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $absensi_SupirApprovalHeader->judul);
            $sheet->setCellValue('A2', $absensi_SupirApprovalHeader->judulLaporan);
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
            $detail_table_header_row = 7;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');
            $header_columns = [
                [
                    'label' => 'No Bukti',
                    'index' => 'nobukti'
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti'
                ],
            ];
            $header_right_columns = [
                [
                    'label' => 'No Bukti Absensi',
                    'index' => 'absensisupir_nobukti'
                ],
                [
                    'label' => 'No Bukti Pengeluaran',
                    'index' => 'pengeluaran_nobukti'
                ],
            ];
            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'TRADO',
                    'index' => 'trado'
                ],
                [
                    'label' => 'SUPIR',
                    'index' => 'supir'
                ],
                [
                    'label' => 'SUPIR SERAP',
                    'index' => 'supirserap'
                ],
                [
                    'label' => 'UANG JALAN',
                    'index' => 'uangjalan'
                ],


            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $absensi_SupirApprovalHeader->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $absensi_SupirApprovalHeader->{$header_right_column['index']});
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

            // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);


            // LOOPING DETAIL

            foreach ($absensi_SupirApprovalDetail as $response_index => $response_detail) {
                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->trado);
                $sheet->setCellValue("C$detail_start_row", $response_detail->supir);
                $sheet->setCellValue("D$detail_start_row", $response_detail->supirserap);
                $sheet->setCellValue("E$detail_start_row", $response_detail->uangjalan);

                $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("E$total_start_row", "=SUM(E7:E" . ($detail_start_row - 1) . ")")->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            //set autosize
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Absensi Supir Posting ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $absensi_SupirApprovalHeader
            ]);
        }

        return response([
            'data' => $absensiSupirApprovalHeader->getExport($id)
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan CETAK DATA
     */
    public function report() {}
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
