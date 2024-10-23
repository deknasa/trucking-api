<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\PengeluaranHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\RekapPengeluaranDetail;

use App\Models\RekapPengeluaranHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\ApprovalRekapPenerimaanRequest;
use App\Http\Requests\StoreRekapPengeluaranDetailRequest;
use App\Http\Requests\StoreRekapPengeluaranHeaderRequest;
use App\Http\Requests\UpdateRekapPengeluaranHeaderRequest;
use App\Http\Requests\DestroyRekapPengeluaranHeaderRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapPengeluaranHeaderController extends Controller
{
    /**
     * @ClassName 
     * RekapPengeluaranHeader
     * @Detail RekapPengeluaranDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        return response([
            'data' => $rekapPengeluaranHeader->get(),
            'attributes' => [
                'totalRows' => $rekapPengeluaranHeader->totalRows,
                'totalPages' => $rekapPengeluaranHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreRekapPengeluaranHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'tgltransaksi' => $request->tgltransaksi,
                'bank_id' => $request->bank_id,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti,
                'tgltransaksi_detail' => $request->tgltransaksi_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal
            ];
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $rekapPengeluaranHeader->position = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable())->position;
                if ($request->limit == 0) {
                    $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
                } else {
                    $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
                }
                $rekapPengeluaranHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $rekapPengeluaranHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $rekapPengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function show(RekapPengeluaranHeader $rekapPengeluaranHeader, $id)
    {
        $data = $rekapPengeluaranHeader->findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $rekapPengeluaranHeader->getRekapPengeluaranHeader($id)
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateRekapPengeluaranHeaderRequest $request, RekapPengeluaranHeader $rekappengeluaranheader)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'tgltransaksi' => $request->tgltransaksi,
                'bank_id' => $request->bank_id,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti,
                'tgltransaksi_detail' => $request->tgltransaksi_detail,
                'keterangan_detail' => $request->keterangan_detail,
                'nominal' => $request->nominal
            ];

            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processUpdate($rekappengeluaranheader, $data);
            $rekapPengeluaranHeader->position = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
            $rekapPengeluaranHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $rekapPengeluaranHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $rekapPengeluaranHeader
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
    public function destroy(DestroyRekapPengeluaranHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processDestroy($id, 'DELETE REKAP PENGELUARAN HEADER');
            $selected = $this->getPosition($rekapPengeluaranHeader, $rekapPengeluaranHeader->getTable(), true);
            $rekapPengeluaranHeader->position = $selected->position;
            $rekapPengeluaranHeader->id = $selected->id;
            if ($request->limit == 0) {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / (10));
            } else {
                $rekapPengeluaranHeader->page = ceil($rekapPengeluaranHeader->position / ($request->limit ?? 10));
            }
            $rekapPengeluaranHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $rekapPengeluaranHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $rekapPengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    /**
     * @ClassName 
     * @Keterangan APRROVAL DATA
     */
    public function approval(ApprovalRekapPenerimaanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = [
                'rekapId' => $request->rekapId
            ];
            $rekapPengeluaranHeader = (new RekapPengeluaranHeader())->processApproval($data);

            DB::commit();

            return response([
                'message' => 'Berhasil',
                'data' => $rekapPengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = RekapPengeluaranHeader::findOrFail($id);
        $nobukti = $pengeluaran->nobukti ?? '';
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('rekappengeluaranheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('Rekap Pengeluaran Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                (new MyModel())->createLockEditing($id, 'rekappengeluaranheader', $useredit);

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
            (new MyModel())->createLockEditing($id, 'rekappengeluaranheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function getPengeluaran(Request $request)
    {
        $pengeluaran = new PengeluaranHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $pengeluaran->getRekapPengeluaranHeader($request->bank, date('Y-m-d', strtotime($request->tglbukti))),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages,
                'totalNominal' => $pengeluaran->totalNominal,
            ]
        ]);
    }

    public function getRekapPengeluaran($id)
    {
        $rekapPengeluaran = new RekapPengeluaranHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $rekapPengeluaran->getRekapPengeluaranHeader($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $rekapPengeluaran->totalRows,
                'totalPages' => $rekapPengeluaran->totalPages,
                'totalNominal' => $rekapPengeluaran->totalNominal,
            ]
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $rekapPengeluaran = RekapPengeluaranHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($rekapPengeluaran->statuscetak != $statusSudahCetak->id) {
                $rekapPengeluaran->statuscetak = $statusSudahCetak->id;
                // $rekapPengeluaran->tglbukacetak = date('Y-m-d H:i:s');
                // $rekapPengeluaran->userbukacetak = auth('api')->user()->name;
                $rekapPengeluaran->jumlahcetak = $rekapPengeluaran->jumlahcetak + 1;
                if ($rekapPengeluaran->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($rekapPengeluaran->getTable()),
                        'postingdari' => 'PRINT REKAP PENGELUARAN HEADER',
                        'idtrans' => $rekapPengeluaran->id,
                        'nobuktitrans' => $rekapPengeluaran->id,
                        'aksi' => 'PRINT',
                        'datajson' => $rekapPengeluaran->toArray(),
                        'modifiedby' => $rekapPengeluaran->modifiedby
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
        $rekapPengeluaranHeader = new RekapPengeluaranHeader();
        $rekap_PengeluaranHeader = $rekapPengeluaranHeader->getExport($id);
        if ($request->export == true) {
            request()->formatcetakan = $rekap_PengeluaranHeader->formatcetakan;
        }
        $rekapPengeluaranDetail = new RekapPengeluaranDetail();
        $rekap_PengeluaranDetail = $rekapPengeluaranDetail->get();

        if ($request->export == true) {

            $tglBukti = $rekap_PengeluaranHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $rekap_PengeluaranHeader->tglbukti = $dateTglBukti;

            $tgltransaksi = $rekap_PengeluaranHeader->tgltransaksi;
            $timeStamp = strtotime($tgltransaksi);
            $datetgltransaksi = date('d-m-Y', $timeStamp);
            $rekap_PengeluaranHeader->tgltransaksi = $datetgltransaksi;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $rekap_PengeluaranHeader->judul);
            $sheet->setCellValue('A2', $rekap_PengeluaranHeader->judulLaporan . $rekap_PengeluaranHeader->bank);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');

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
                    'label' => 'Bank/Kas',
                    'index' => 'bank'
                ],
                [
                    'label' => 'Tanggal Transaksi',
                    'index' => 'tgltransaksi'
                ]
            ];
            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NAMA PERKIRAAN',
                    'index' => 'keterangancoa'
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan'
                ],
                [
                    'label' => 'NOMINAL',
                    'index' => 'nominal',
                    'format' => 'currency'
                ],

            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $rekap_PengeluaranHeader->{$header_column['index']});
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
            $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $nominal = 0;
            foreach ($rekap_PengeluaranDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->keterangancoa);
                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('C')->setWidth(50);

                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("D$total_start_row", "=SUM(D10:D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("D$total_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            //set autosize
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN REKAP PENGELUARAN ' . $rekap_PengeluaranHeader->bank . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Filename: ' . $filename);
            $writer->save('php://output');
        } else {
            return response([
                'data' => $rekap_PengeluaranHeader
            ]);
        }
    }
}
