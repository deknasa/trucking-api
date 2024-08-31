<?php

namespace App\Http\Controllers\Api;

use App\Models\Ritasi;
use App\Http\Requests\StoreRitasiRequest;
use App\Http\Requests\UpdateRitasiRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\Parameter;
use App\Models\Supir;
use App\Models\Trado;
use App\Http\Requests\GetUpahSupirRangeRequest;
use App\Models\Kota;
use App\Models\SuratPengantar;
use App\Models\UpahRitasi;
use App\Models\UpahRitasiRincian;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyRitasiRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\Error;
use App\Models\Locking;
use App\Models\MyModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RitasiController extends Controller
{
    /**
     * @ClassName 
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $ritasi = new Ritasi();
        return response([
            'data' => $ritasi->get(),
            'attributes' => [
                'totalRows' => $ritasi->totalRows,
                'totalPages' => $ritasi->totalPages
            ]
        ]);
    }

    public function default()
    {
        $ritasi = new Ritasi();
        return response([
            'status' => true,
            'data' => $ritasi->default()
        ]);
    }
    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreRitasiRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'statusritasi_id' => $request->statusritasi_id,
                'suratpengantar_nobukti' => $request->suratpengantar_nobukti,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
            ];
            $ritasi = (new Ritasi())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $ritasi->position = $this->getPosition($ritasi, $ritasi->getTable())->position;
                if ($request->limit == 0) {
                    $ritasi->page = ceil($ritasi->position / (10));
                } else {
                    $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
                }
                $ritasi->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $ritasi->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $ritasi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $ritasi = (new Ritasi)->find($id);
        return response([
            'status' => true,
            'data' => $ritasi
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateRitasiRequest $request, Ritasi $ritasi): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'statusritasi_id' => $request->statusritasi_id,
                'suratpengantar_nobukti' => $request->suratpengantar_nobukti,
                'supir_id' => $request->supir_id,
                'trado_id' => $request->trado_id,
                'dari_id' => $request->dari_id,
                'sampai_id' => $request->sampai_id,
            ];
            $ritasi = (new Ritasi())->processUpdate($ritasi, $data);
            $ritasi->position = $this->getPosition($ritasi, $ritasi->getTable())->position;
            if ($request->limit == 0) {
                $ritasi->page = ceil($ritasi->position / (10));
            } else {
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
            }
            $ritasi->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $ritasi->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $ritasi
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
    public function destroy(DestroyRitasiRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $ritasi = (new Ritasi())->processDestroy($id);
            $selected = $this->getPosition($ritasi, $ritasi->getTable(), true);
            $ritasi->position = $selected->position;
            $ritasi->id = $selected->id;
            if ($request->limit == 0) {
                $ritasi->page = ceil($ritasi->position / (10));
            } else {
                $ritasi->page = ceil($ritasi->position / ($request->limit ?? 10));
            }
            $ritasi->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $ritasi->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $ritasi
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('ritasi')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function combo(Request $request)
    {
        $data = [
            'statusritasi' => Parameter::where(['grp' => 'status ritasi'])->get(),
            'suratpengantar' => SuratPengantar::all(),
            'supir' => Supir::all(),
            'trado' => Trado::all(),
            'kota' => Kota::all(),
        ];

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
    public function export(Request $request)
    {
        $ritasi = new Ritasi();
        $data = $ritasi->getExport();

        $ritasiData = $data['data'];
        // dd($ritasiData);

        if ($request->export == true) {

            $tglDari = $ritasiData[0]->tgldari;
            $timeStamp = strtotime($tglDari);
            $datetglDari = date('d-m-Y', $timeStamp);
            $periodeDari = $datetglDari;

            $tglSampai = $ritasiData[0]->tglsampai;
            $timeStamp = strtotime($tglSampai);
            $datetglSampai = date('d-m-Y', $timeStamp);
            $periodeSampai = $datetglSampai;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $data['parameter']->judul);
            $sheet->setCellValue('A2', $data['parameter']->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:K1');
            $sheet->mergeCells('A2:K2');

            $header_start_row = 4;
            $detail_table_header_row = 7;
            $detail_start_row = $detail_table_header_row + 1;
            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'Periode Dari',
                    'index' => $periodeDari
                ],
                [
                    'label' => 'Periode Sampai',
                    'index' => $periodeSampai
                ]
            ];
            $columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NO BUKTI',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'TANGGAL',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'STATUS RITASI',
                    'index' => 'statusritasi',
                ],
                [
                    'label' => 'NO BUKTI RIC',
                    'index' => 'gajisupir_nobukti',
                ],
                [
                    'label' => 'NO BUKTI EBS',
                    'index' => 'prosesgajisupir_nobukti',
                ],
                [
                    'label' => 'NO BUKTI TRIP',
                    'index' => 'suratpengantar_nobukti',
                ],
                [
                    'label' => 'SUPIR',
                    'index' => 'supir_id',
                ],
                [
                    'label' => 'TRADO',
                    'index' => 'trado_id',
                ],
                [
                    'label' => 'DARI',
                    'index' => 'dari_id',
                ],
                [
                    'label' => 'SAMPAI',
                    'index' => 'sampai_id',
                ],
                [
                    'label' => 'JARAK (KM)',
                    'index' => 'jarak',
                ],
                [
                    'label' => 'GAJI',
                    'index' => 'gaji',
                ],
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $header_column['index']);
            }
            foreach ($columns as $detail_columns_index => $detail_column) {
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
            $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->applyFromArray($styleArray);

            $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->getFont()->setBold(true);
            $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->getAlignment()->setHorizontal('center');
            $nominal = 0;
            foreach ($ritasiData as $response_index => $response_detail) {
                $dateValue = ($response_detail->tglbukti != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglbukti))) : '';


                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->nobukti);
                $sheet->setCellValue("C$detail_start_row", $dateValue);
                $sheet->setCellValue("D$detail_start_row", $response_detail->statusritasi);
                $sheet->setCellValue("E$detail_start_row", $response_detail->gajisupir_nobukti);
                $sheet->setCellValue("F$detail_start_row", $response_detail->prosesgajisupir_nobukti);
                $sheet->setCellValue("G$detail_start_row", $response_detail->suratpengantar_nobukti);
                $sheet->setCellValue("H$detail_start_row", $response_detail->supir_id);
                $sheet->setCellValue("I$detail_start_row", $response_detail->trado_id);
                $sheet->setCellValue("J$detail_start_row", $response_detail->dari_id);
                $sheet->setCellValue("K$detail_start_row", $response_detail->sampai_id);
                $sheet->setCellValue("L$detail_start_row", $response_detail->jarak);
                $sheet->setCellValue("M$detail_start_row", $response_detail->gaji);

                $sheet->getStyle("A$detail_start_row:L$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("L$detail_start_row:M$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("M$detail_start_row")->applyFromArray($style_number);

                $sheet->getStyle("C$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $detail_start_row++;
            }
            $total_start_row = $detail_start_row;
            //Total
            $sheet->mergeCells('A' . $total_start_row . ':L' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':L' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);

            $total = "=SUM(M" . ($detail_table_header_row + 1) . ":M" . ($detail_start_row - 1) . ")";
            $sheet->setCellValue("M$total_start_row", $total)->getStyle("M$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("M$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setWidth(27);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);
            $sheet->getColumnDimension('M')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Ritasi' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $data
            ]);
        }

        return response([
            'data' => $ritasi->getExport()
        ]);
    }

    public function cekValidasi($id)
    {
        $ritasi = new Ritasi();
        $nobukti = DB::table("ritasi")->from(DB::raw("ritasi"))->where('id', $id)->first();
        $cekdata = $ritasi->cekvalidasiaksi($nobukti->nobukti);
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('ritasi', $id);
        $useredit = $getEditing->editing_by ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
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
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $nobukti->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('suratpengantar');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                (new MyModel())->createLockEditing($id, 'ritasi', $useredit);

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $nobukti->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
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
            (new MyModel())->createLockEditing($id, 'ritasi', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }
}
