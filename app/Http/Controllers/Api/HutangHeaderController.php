<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Bank;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Supplier;
use App\Models\AkunPusat;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\HutangDetail;
use App\Models\HutangHeader;
use Illuminate\Http\Request;
use PhpParser\Builder\Param;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Requests\UpdateHutangHeaderRequest;
use App\Http\Requests\DestroyHutangHeaderRequest;
use App\Http\Requests\ApprovalHutangHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HutangHeaderController extends Controller
{
    /**
     * @ClassName 
     * HutangHeader
     * @Detail HutangDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $hutang = new HutangHeader();

        return response([
            'data' => $hutang->get(),
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }



    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreHutangHeaderRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "total" => $request->total,
                "coa" => $request->coa,
                "supplier_id" => $request->supplier_id,
                "postingdari" => $request->postingdari,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "keterangan_detail" => $request->keterangan_detail,
                "coakredit" => $request->coakredit,
                "coadebet" => $request->coadebet,
                "total_detail" => $request->total_detail,
                "proseslain" => $request->proseslain,
            ];
            /* Store header */
            $hutangHeader = (new HutangHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                /* Set position and page */
                $hutangHeader->position = $this->getPosition($hutangHeader, $hutangHeader->getTable())->position;
                if ($request->limit == 0) {
                    $hutangHeader->page = ceil($hutangHeader->position / (10));
                } else {
                    $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
                }
                $hutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $hutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
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
    public function approval(ApprovalHutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = [
                'hutangId' => $request->hutangId
            ];
            $hutangHeader = (new HutangHeader())->processApproval($data);

            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {

        $data = HutangHeader::findAll($id);
        $detail = HutangDetail::getAll($id);

        // dd($details);
        // $datas = array_merge($data, $detail);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'coa'           => AkunPusat::all(),
            'parameter'     => Parameter::all(),
            'pelanggan'     => Pelanggan::all(),
            'supplier'      => Supplier::all(),

            'statuskas'     => Parameter::where('grp', 'STATUS KAS')->get(),
            'statusapproval' => Parameter::where('grp', 'STATUS APPROVAL')->get(),
            'statusberkas'  => Parameter::where('grp', 'STATUS BERKAS')->get(),

        ];

        return response([
            'data' => $data
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateHutangHeaderRequest $request, HutangHeader $hutangHeader, $id)
    {
        DB::beginTransaction();
        try {
            $data = [
                "tglbukti" => $request->tglbukti,
                "total" => $request->total,
                "coa" => $request->coa,
                "supplier_id" => $request->supplier_id,
                "postingdari" => $request->postingdari,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "keterangan_detail" => $request->keterangan_detail,
                "coakredit" => $request->coakredit,
                "coadebet" => $request->coadebet,
                "total_detail" => $request->total_detail,
                "proseslain" => $request->proseslain,
            ];
            /* Store header */
            $hutangHeader = HutangHeader::findOrFail($id);
            $hutangHeader = (new HutangHeader())->processUpdate($hutangHeader, $data);
            /* Set position and page */
            $hutangHeader->position = $this->getPosition($hutangHeader, $hutangHeader->getTable())->position;
            if ($request->limit == 0) {
                $hutangHeader->page = ceil($hutangHeader->position / (10));
            } else {
                $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
            }
            $hutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $hutangHeader
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
    public function destroy(DestroyHutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $hutangHeader = (new HutangHeader())->processDestroy($id, "DELETE HUTANG HEADER");
            $selected = $this->getPosition($hutangHeader, $hutangHeader->getTable(), true);
            $hutangHeader->position = $selected->position;
            $hutangHeader->id = $selected->id;
            if ($request->limit == 0) {
                $hutangHeader->page = ceil($hutangHeader->position / (10));
            } else {
                $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
            }
            $hutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $hutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    private function storeJurnal($header, $detail)
    {
        DB::beginTransaction();

        try {

            $jurnal = new StoreJurnalUmumHeaderRequest($header);
            $jurnals = app(JurnalUmumHeaderController::class)->store($jurnal);

            $detailLog = [];

            foreach ($detail as $value) {
                $value['jurnalumum_id'] = $jurnals->original['data']['id'];
                $detail = new StoreJurnalUmumDetailRequest($value);
                $datadetails = app(JurnalUmumDetailController::class)->store($detail);

                $detailLog[] = $datadetails['detail']->toArray();
            }

            $datalogtrail = [
                'namatabel' => strtoupper($datadetails['tabel']),
                'postingdari' => 'ENTRY HUTANG',
                'idtrans' => $jurnals->original['idlogtrail'],
                'nobuktitrans' => $header['nobukti'],
                'aksi' => 'ENTRY',
                'datajson' => $detailLog,
                'modifiedby' => auth('api')->user()->name,
            ];

            $data = new StoreLogTrailRequest($datalogtrail);
            app(LogTrailController::class)->store($data);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response($th->getMessage());
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $hutangHeader = HutangHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($hutangHeader->statuscetak != $statusSudahCetak->id) {
                $hutangHeader->statuscetak = $statusSudahCetak->id;
                // $hutangHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $hutangHeader->userbukacetak = auth('api')->user()->name;
                $hutangHeader->jumlahcetak = $hutangHeader->jumlahcetak + 1;
                if ($hutangHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutangHeader->getTable()),
                        'postingdari' => 'PRINT HUTANG HEADER',
                        'idtrans' => $hutangHeader->id,
                        'nobuktitrans' => $hutangHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $hutangHeader->toArray(),
                        'modifiedby' => $hutangHeader->modifiedby
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

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $hutang = HutangHeader::find($id);
        $nobukti = $hutang->nobukti ?? '';
        $statusdatacetak = $hutang->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $status = $hutang->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();

        $parameter = new Parameter();
        $aksi = request()->aksi ?? '';

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('hutangheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = DB::table('error')
            //     ->select('keterangan')
            //     ->where('kodeerror', '=', 'SDC')
            //     ->first();
            // $keterangan = [
            //     'keterangan' => 'No Bukti ' . $hutang->nobukti . ' ' . $query->keterangan
            // ];

            $data = [
                'message' => $keterror,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1',
                'statuspesan' => 'warning',
                'error' => true,
                'kodeerror' => 'SDC',
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
        } else if ($tgltutup >= $hutang->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('Hutang Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'hutangheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'hutangheader', $useredit);
            }
            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1',
                'error' => false,
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
    public function cekValidasiAksi($id)
    {
        $hutangHeader = new HutangHeader();

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $nobukti = HutangHeader::from(DB::raw("hutangheader"))->where('id', $id)->first();
        $cekdata = $hutangHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            // $query = DB::table('error')
            //     ->select(
            //         DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
            //     )
            //     ->where('kodeerror', '=', $cekdata['kodeerror'])
            //     ->get();
            // $keterangan = $query['0'];

            $data = [
                'status' => false,
                // 'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'kodeerror' => $cekdata['kodeerror'],
            ];

            return response($data);
        } else {
            $getEditing = (new Locking())->getEditing('hutangheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'hutangheader', $useredit);

            $data = [
                'status' => false,
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],

            ];

            return response($data);
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('hutangheader')->getColumns();

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
        $hutangHeader = new HutangHeader();
        $hutang_Header = $hutangHeader->getExport($id);

        $hutangDetail = new HutangDetail();
        $hutang_Detail = $hutangDetail->get();

        if ($request->export == true) {
            $tglbukti = $hutang_Header->tglbukti;
            $timeStamp = strtotime($tglbukti);
            $datetglbukti = date('d-m-Y', $timeStamp);
            $hutang_Header->tglbukti = $datetglbukti;

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $hutang_Header->judul);
            $sheet->setCellValue('A2', $hutang_Header->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');

            $header_start_row = 4;
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
                    'label' => 'Supplier',
                    'index' => 'supplier_id',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'TANGGAL JATUH TEMPO',
                    'index' => 'tgljatuhtempo',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
                ],
                [
                    'label' => 'NOMINAL',
                    'index' => 'total',
                    'format' => 'currency'
                ]
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $hutang_Header->{$header_column['index']});
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
            $total = 0;
            foreach ($hutang_Detail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:E$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $tgljatuhtempo = $response_detail->tgljatuhtempo;
                $timeStamp = strtotime($tgljatuhtempo);
                $datetgljatuhtempo = date('d-m-Y', $timeStamp);
                $response_detail->tgljatuhtempo = $datetgljatuhtempo;

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $dateValue = ($response_detail->tgljatuhtempo != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tgljatuhtempo))) : '';
                $sheet->setCellValue("B$detail_start_row", $dateValue);
                $sheet->getStyle("B$detail_start_row")
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("D$detail_start_row", $response_detail->total);

                $sheet->getStyle("C$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('C')->setWidth(50);
                $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getStyle("A$detail_start_row:D$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number);

                $total += $response_detail->total;
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("D$total_start_row", "=SUM(D9:D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("D$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Hutang' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $hutang_Header
            ]);
        }
    }
}
