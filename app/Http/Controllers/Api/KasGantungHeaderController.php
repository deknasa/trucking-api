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
use App\Models\Locking;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                // $kasgantungHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $kasgantungHeader->userbukacetak = auth('api')->user()->name;
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
            $getEditing = (new Locking())->getEditing('kasgantungheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'kasgantungheader', $useredit);
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
        $getEditing = (new Locking())->getEditing('kasgantungheader', $id);
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

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'kasgantungheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'kasgantungheader', $useredit);
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
        $kasGantungHeader = new KasGantungHeader();
        $kas_GantungHeader = $kasGantungHeader->getExport($id);

        $kasGantungDetail = new KasGantungDetail();
        $kas_GantungDetail = $kasGantungDetail->get();

        if ($request->export == true) {
            $tglBukti = $kas_GantungHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $kas_GantungHeader->tglbukti = $dateTglBukti;

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $kas_GantungHeader->judul);
            $sheet->setCellValue('A2', $kas_GantungHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');

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
                    'label' => 'Penerima',
                    'index' => 'penerima',
                ]
            ];
            $header_right_columns = [
                [
                    'label' => 'Bank',
                    'index' => 'bank_id',
                ],
                [
                    'label' => 'No Bukti Kas Keluar',
                    'index' => 'pengeluaran_nobukti',
                ],
                [
                    'label' => 'Posting Dari',
                    'index' => 'postingdari',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'KODE PERKIRAAN',
                    'index' => 'coa',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
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
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $kas_GantungHeader->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $kas_GantungHeader->{$header_right_column['index']});
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
            foreach ($kas_GantungDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:D$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->coa);
                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                $sheet->setCellValue("D$detail_start_row", $response_detail->nominal);

                $sheet->getColumnDimension('C')->setWidth(50);

                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':C' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':C' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("D$total_start_row", "=SUM(D9:D" . ($detail_start_row - 1) . ")")->getStyle("D$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("D$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
            //set autosize
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Kas Gantung' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $kas_GantungHeader
            ]);
        }
    }
}
