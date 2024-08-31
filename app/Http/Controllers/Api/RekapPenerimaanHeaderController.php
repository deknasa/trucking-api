<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;

use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\PenerimaanHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\RekapPenerimaanDetail;
use App\Models\RekapPenerimaanHeader;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Controllers\Api\LogTrailController;
use App\Http\Requests\ApprovalRekapPenerimaanRequest;
use App\Http\Requests\StoreRekapPenerimaanDetailRequest;
use App\Http\Requests\StoreRekapPenerimaanHeaderRequest;
use App\Http\Requests\UpdateRekapPenerimaanHeaderRequest;
use App\Http\Requests\DestroyRekapPenerimaanHeaderRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapPenerimaanHeaderController extends Controller
{
    /**
     * @ClassName 
     * RekapPenerimaanHeader
     * @Detail RekapPenerimaanDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $rekapPenerimaanHeader = new RekapPenerimaanHeader();
        return response([
            'data' => $rekapPenerimaanHeader->get(),
            'attributes' => [
                'totalRows' => $rekapPenerimaanHeader->totalRows,
                'totalPages' => $rekapPenerimaanHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreRekapPenerimaanHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();


        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgltransaksi'  => date('Y-m-d', strtotime($request->tgltransaksi)),
                'bank_id' => $request->bank_id,

                "tgltransaksi_detail" => $request->tgltransaksi_detail,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "nominal" => $request->nominal,
                "keterangan_detail" => $request->keterangan_detail,

            ];

            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $rekapPenerimaanHeader->position = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable())->position;
                if ($request->limit == 0) {
                    $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / (10));
                } else {
                    $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
                }
                $rekapPenerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $rekapPenerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(RekapPenerimaanHeader $rekapPenerimaanHeader, $id)
    {
        $data = $rekapPenerimaanHeader->findAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $rekapPenerimaanHeader->getRekapPenerimaanHeader($id),
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateRekapPenerimaanHeaderRequest $request, RekapPenerimaanHeader $rekappenerimaanheader)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'tgltransaksi'  => date('Y-m-d', strtotime($request->tgltransaksi)),
                'bank_id' => $request->bank_id,

                "tgltransaksi_detail" => $request->tgltransaksi_detail,
                "penerimaan_nobukti" => $request->penerimaan_nobukti,
                "nominal" => $request->nominal,
                "keterangan_detail" => $request->keterangan_detail,

            ];

            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processUpdate($rekappenerimaanheader, $data);
            $rekapPenerimaanHeader->position = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable())->position;
            if ($request->limit == 0) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / (10));
            } else {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            }
            $rekapPenerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPenerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $rekapPenerimaanHeader
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
    public function destroy(DestroyRekapPenerimaanHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processDestroy($id, 'DELETE REKAP PENERIMAAN HEADER');
            $selected = $this->getPosition($rekapPenerimaanHeader, $rekapPenerimaanHeader->getTable(), true);
            $rekapPenerimaanHeader->position = $selected->position;
            $rekapPenerimaanHeader->id = $selected->id;
            if ($request->limit == 0) {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / (10));
            } else {
                $rekapPenerimaanHeader->page = ceil($rekapPenerimaanHeader->position / ($request->limit ?? 10));
            }
            $rekapPenerimaanHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $rekapPenerimaanHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $rekapPenerimaanHeader
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
            $rekapPenerimaanHeader = (new RekapPenerimaanHeader())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil',
                'data' => $rekapPenerimaanHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = RekapPenerimaanHeader::findOrFail($id);
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

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('rekappenerimaanheader', $id);
        $useredit = $getEditing->editing_by ?? '';
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('Rekap Penerimaan Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                (new MyModel())->createLockEditing($id, 'rekappenerimaanheader', $useredit);


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
            (new MyModel())->createLockEditing($id, 'rekappenerimaanheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function getPenerimaan(Request $request)
    {
        $penerimaan = new PenerimaanHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $penerimaan->getRekapPenerimaanHeader($request->bank, date('Y-m-d', strtotime($request->tglbukti))),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $penerimaan->totalRows,
                'totalPages' => $penerimaan->totalPages,
                'totalNominal' => $penerimaan->totalNominal,
            ]
        ]);
    }

    public function getRekapPenerimaan($id)
    {
        $rekapPenerimaan = new RekapPenerimaanHeader();
        $currentURL = url()->current();
        $previousURL = url()->previous();
        return response([
            'data' => $rekapPenerimaan->getRekapPenerimaanHeader($id),
            'currentURL' => $currentURL,
            'previousURL' => $previousURL,
            'attributes' => [
                'totalRows' => $rekapPenerimaan->totalRows,
                'totalPages' => $rekapPenerimaan->totalPages,
                'totalNominal' => $rekapPenerimaan->totalNominal,
            ]
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $rekapPenerimaan = RekapPenerimaanHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($rekapPenerimaan->statuscetak != $statusSudahCetak->id) {
                $rekapPenerimaan->statuscetak = $statusSudahCetak->id;
                // $rekapPenerimaan->tglbukacetak = date('Y-m-d H:i:s');
                // $rekapPenerimaan->userbukacetak = auth('api')->user()->name;
                $rekapPenerimaan->jumlahcetak = $rekapPenerimaan->jumlahcetak + 1;
                if ($rekapPenerimaan->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($rekapPenerimaan->getTable()),
                        'postingdari' => 'PRINT REKAP PENERIMAAN HEADER',
                        'idtrans' => $rekapPenerimaan->id,
                        'nobuktitrans' => $rekapPenerimaan->id,
                        'aksi' => 'PRINT',
                        'datajson' => $rekapPenerimaan->toArray(),
                        'modifiedby' => $rekapPenerimaan->modifiedby
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
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id, Request $request)
    {
        $rekapPenerimaanHeader = new RekapPenerimaanHeader();
        $rekap_PenerimaanHeader = $rekapPenerimaanHeader->getExport($id);

        $rekapPenerimaanDetail = new RekapPenerimaanDetail();
        $rekap_PenerimaanDetail = $rekapPenerimaanDetail->get();

        if ($request->export == true) {
            $tglBukti = $rekap_PenerimaanHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $rekap_PenerimaanHeader->tglbukti = $dateTglBukti;

            $tgltransaksi = $rekap_PenerimaanHeader->tgltransaksi;
            $timeStamp = strtotime($tgltransaksi);
            $datetgltransaksi = date('d-m-Y', $timeStamp);
            $rekap_PenerimaanHeader->tgltransaksi = $datetgltransaksi;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $rekap_PenerimaanHeader->judul);
            $sheet->setCellValue('A2', $rekap_PenerimaanHeader->judulLaporan . $rekap_PenerimaanHeader->bank);
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
                    'label' => 'Bank',
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
                ]
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $rekap_PenerimaanHeader->{$header_column['index']});
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
            foreach ($rekap_PenerimaanDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->keterangancoa);
                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                // $sheet->getStyle("D$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('D')->setWidth(50);

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
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'LAPORAN REKAP PENERIMAAN ' . $rekap_PenerimaanHeader->bank . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Filename: ' . $filename);
            $writer->save('php://output');
        } else {
            return response([
                'data' => $rekap_PenerimaanHeader
            ]);
        }
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL KIRIM BERKAS
     */
    public function approvalkirimberkas() {}
}
