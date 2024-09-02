<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\NotaDebetDetail;
use App\Models\NotaDebetHeader;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PelunasanPiutangHeader;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalNotaDebetRequest;
use App\Http\Requests\StoreNotaDebetDetailRequest;
use App\Http\Requests\StoreNotaDebetHeaderRequest;
use App\Http\Requests\UpdateNotaDebetHeaderRequest;
use App\Http\Requests\DestroyNotaDebetHeaderRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NotaDebetHeaderController extends Controller
{

    /**
     * @ClassName 
     * NotaDebetHeader
     * @Detail NotaDebetDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $notaDebetHeader = new NotaDebetHeader();
        return response([
            'data' => $notaDebetHeader->get(),
            'attributes' => [
                'totalRows' => $notaDebetHeader->totalRows,
                'totalPages' => $notaDebetHeader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $notaDebet = new NotaDebetHeader();
        return response([
            'status' => true,
            'data' => $notaDebet->default(),
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreNotaDebetHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen' => $request->agen,
                'agen_id' => $request->agen_id,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'pelunasanpiutang_nobukti' => '',
                'tanpaprosesnobukti' => 0,
                'keterangan_detail' => $request->keterangan_detail,
                'nominallebihbayar' => $request->nominal_detail
            ];
            $notaDebetHeader = (new NotaDebetHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $notaDebetHeader->position = $this->getPosition($notaDebetHeader, $notaDebetHeader->getTable())->position;
                if ($request->limit == 0) {
                    $notaDebetHeader->page = ceil($notaDebetHeader->position / (10));
                } else {
                    $notaDebetHeader->page = ceil($notaDebetHeader->position / ($request->limit ?? 10));
                }
                $notaDebetHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $notaDebetHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $notaDebetHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(NotaDebetHeader $notaDebetHeader, $id)
    {
        $data = $notaDebetHeader->findAll($id);
        $detail = (new NotaDebetDetail())->findAll($id);

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
    public function update(UpdateNotaDebetHeaderRequest $request, NotaDebetHeader $notadebetheader)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'agen' => $request->agen,
                'agen_id' => $request->agen_id,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'alatbayar_id' => $request->alatbayar_id,
                'nowarkat' => $request->nowarkat,
                'pelunasanpiutang_nobukti' => '',
                'tanpaprosesnobukti' => 0,
                'keterangan_detail' => $request->keterangan_detail,
                'nominallebihbayar' => $request->nominal_detail
            ];
            $notaDebetHeader = (new NotaDebetHeader())->processUpdate($notadebetheader, $data);
            $notaDebetHeader->position = $this->getPosition($notaDebetHeader, $notaDebetHeader->getTable())->position;
            if ($request->limit == 0) {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / (10));
            } else {
                $notaDebetHeader->page = ceil($notaDebetHeader->position / ($request->limit ?? 10));
            }
            $notaDebetHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaDebetHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();
            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $notaDebetHeader
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
    public function destroy(DestroyNotaDebetHeaderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $notaDebet = (new NotaDebetHeader())->processDestroy($id, 'DELETE NOTA DEBET');
            $selected = $this->getPosition($notaDebet, $notaDebet->getTable(), true);
            $notaDebet->position = $selected->position;
            $notaDebet->id = $selected->id;
            if ($request->limit == 0) {
                $notaDebet->page = ceil($notaDebet->position / (10));
            } else {
                $notaDebet->page = ceil($notaDebet->position / ($request->limit ?? 10));
            }
            $notaDebet->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $notaDebet->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $notaDebet
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getPelunasan($id)
    {
        $pelunasanPiutang = new PelunasanPiutangHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pelunasanPiutang->getPelunasanNotaDebet($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pelunasanPiutang->totalRows,
                'totalPages' => $pelunasanPiutang->totalPages
            ]
        ]);
    }
    public function getNotaDebet($id)
    {
        $notaDebet = new NotaDebetHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $notaDebet->getNotaDebet($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $notaDebet->totalRows,
                'totalPages' => $notaDebet->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalNotaDebetRequest $request)
    {
        DB::beginTransaction();

        try {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->debetId); $i++) {
                $notaDebet = NotaDebetHeader::find($request->debetId[$i]);
                if ($notaDebet->statusapproval == $statusApproval->id) {
                    $notaDebet->statusapproval = $statusNonApproval->id;
                    $aksi = $statusNonApproval->text;
                } else {
                    $notaDebet->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                }

                $notaDebet->tglapproval = date('Y-m-d', time());
                $notaDebet->userapproval = auth('api')->user()->name;

                if ($notaDebet->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notaDebet->getTable()),
                        'postingdari' => 'APPROVAL NOTA DEBET',
                        'idtrans' => $notaDebet->id,
                        'nobuktitrans' => $notaDebet->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $notaDebet->toArray(),
                        'modifiedby' => auth('api')->user()->name
                    ];

                    $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                    $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);
                }
            }
            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('notadebetheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }
    public function cekvalidasi($id)
    {
        $notaDebet = NotaDebetHeader::find($id);
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        if ($notaDebet == '') {
            $keterangan = $error->cekKeteranganError('DTA') ?? '';

            $keterror = $keterangan . ' <br> ' . $keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];
            return response($data);
        }

        $nobukti = $notaDebet->nobukti ?? '';
        $status = $notaDebet->statusapproval;
        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $notaDebet->statuscetak;
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $penerimaan = $notaDebet->penerimaan_nobukti ?? '';
        // dd($penerimaan);
        $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
            ->select(
                'a.id'
            )
            ->where('a.nobukti', $penerimaan)
            ->first()->id ?? 0;
        // $aksi = request()->aksi ?? '';

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

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('notadebetheader', $id);
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
        } else if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $notaDebet->tglbukti) {
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

            $waktu = (new Parameter())->cekBatasWaktuEdit('Nota Debet Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'notadebetheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'notadebetheader', $useredit);
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
        $notaDebetHeader = new NotaDebetHeader();
        $cekdata = $notaDebetHeader->cekvalidasiaksi($id);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('notadebetheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'notadebetheader', $useredit);

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
            $notaDebetHeader = NotaDebetHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($notaDebetHeader->statuscetak != $statusSudahCetak->id) {
                $notaDebetHeader->statuscetak = $statusSudahCetak->id;
                // $notaDebetHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $notaDebetHeader->userbukacetak = auth('api')->user()->name;
                $notaDebetHeader->jumlahcetak = $notaDebetHeader->jumlahcetak + 1;
                if ($notaDebetHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($notaDebetHeader->getTable()),
                        'postingdari' => 'PRINT NOTA DEBET HEADER',
                        'idtrans' => $notaDebetHeader->id,
                        'nobuktitrans' => $notaDebetHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $notaDebetHeader->toArray(),
                        'modifiedby' => $notaDebetHeader->modifiedby
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
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak() {}
    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $notaDebetHeader = new NotaDebetHeader();
        $nota_DebetHeader = $notaDebetHeader->getExport($id);

        $notaDebetDetail = new NotaDebetDetail();
        $nota_DebetDetail = $notaDebetDetail->get();

        if ($request->export == true) {
            $tglBukti = $nota_DebetHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $nota_DebetHeader->tglbukti = $dateTglBukti;

            $tgllunas = $nota_DebetHeader->tgllunas;
            $timeStamp = strtotime($tgllunas);
            $datetgllunas = date('d-m-Y', $timeStamp);
            $nota_DebetHeader->tgllunas = $datetgllunas;

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $nota_DebetHeader->judul);
            $sheet->setCellValue('A2', $nota_DebetHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:E1');
            $sheet->mergeCells('A2:E2');

            $header_start_row = 4;
            $detail_table_header_row = 9;
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
                [
                    'label' => 'No Bukti Pelunasan Piutang',
                    'index' => 'pelunasanpiutang_nobukti'
                ],
                [
                    'label' => 'Tanggal lunas',
                    'index' => 'tgllunas'
                ],

            ];
            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NO BUKTI INVOICE',
                    'index' => 'invoice_nobukti'
                ],
                [
                    'label' => 'NAMA PERKIRAAN LEBIH BAYAR',
                    'index' => 'coalebihbayar'
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan'
                ],
                [
                    'label' => 'NOMINAL LEBIH BAYAR',
                    'index' => 'lebihbayar',
                    'format' => 'currency',
                ],
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $nota_DebetHeader->{$header_column['index']});
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

            $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $lebihbayar = 0;
            foreach ($nota_DebetDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }
                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->invoice_nobukti);
                $sheet->setCellValue("C$detail_start_row", $response_detail->coalebihbayar);
                $sheet->setCellValue("D$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("E$detail_start_row", $response_detail->lebihbayar);

                $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('D')->setWidth(40);

                $sheet->getStyle("A$detail_start_row:E$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("E$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

            $sheet->setCellValue("E$detail_start_row", "=SUM(E10:E" . ($detail_start_row - 1) . ")")->getStyle("E$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("E$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Nota Debet' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $nota_DebetHeader
            ]);
        }
    }
}
