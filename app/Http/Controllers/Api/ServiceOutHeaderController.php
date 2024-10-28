<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\ServiceInHeader;
use App\Models\ServiceOutDetail;
use App\Models\ServiceOutHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreServiceOutDetailRequest;
use App\Http\Requests\StoreServiceOutHeaderRequest;
use App\Http\Requests\UpdateServiceOutHeaderRequest;
use App\Http\Requests\DestroyServiceOutHeaderRequest;
use App\Models\Locking;
use LDAP\Result;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ServiceOutHeaderController extends Controller
{

    /**
     * @ClassName 
     * ServiceOutHeader
     * @Detail ServiceOutDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $serviceout = new ServiceOutHeader();

        return response([
            'data' => $serviceout->get(),
            'attributes' => [
                'totalRows' => $serviceout->totalRows,
                'totalPages' => $serviceout->totalPages
            ]
        ]);
    }


    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreServiceOutHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                'trado_id' => $request->trado_id,
                'tglkeluar' => $request->tglkeluar,
                'servicein_nobukti' => $request->servicein_nobukti,
                'keterangan_detail' => $request->keterangan_detail
            ];

            $serviceOutHeader = (new ServiceOutHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $serviceOutHeader->position = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable())->position;
                if ($request->limit == 0) {
                    $serviceOutHeader->page = ceil($serviceOutHeader->position / (10));
                } else {
                    $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));
                }
                $serviceOutHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $serviceOutHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $serviceOutHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = ServiceOutHeader::findAll($id);
        $detail = ServiceOutDetail::getAll($id);

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
    public function update(UpdateServiceOutHeaderRequest $request, ServiceOutHeader $serviceoutheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '',
                'trado_id' => $request->trado_id ?? 0,
                'tglkeluar' =>  $request->tglkeluar,
                'servicein_nobukti' => $request->servicein_nobukti ?? '',
                'keterangan_detail' => $request->keterangan_detail ?? ''
            ];

            $serviceOutHeader = (new ServiceOutHeader())->processUpdate($serviceoutheader, $data);
            $serviceOutHeader->position = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable())->position;
            if ($request->limit == 0) {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / (10));
            } else {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));
            }
            $serviceOutHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $serviceOutHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $serviceoutheader
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
    public function destroy(DestroyServiceOutHeaderRequest $request, $id)
    {

        DB::beginTransaction();
        try {
            $serviceOutHeader = (new ServiceOutHeader())->processDestroy($id);
            $selected = $this->getPosition($serviceOutHeader, $serviceOutHeader->getTable(), true);
            $serviceOutHeader->position = $selected->position;
            $serviceOutHeader->id = $selected->id;
            if ($request->limit == 0) {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / (10));
            } else {
                $serviceOutHeader->page = ceil($serviceOutHeader->position / ($request->limit ?? 10));
            }
            $serviceOutHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $serviceOutHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $serviceOutHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceOutHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        // if ($status == $statusApproval->id) {
        //     $query = Error::from(DB::raw("error with (readuncommitted)"))
        //         ->select('keterangan')
        //         ->whereRaw("kodeerror = 'SAP'")
        //         ->get();
        //     $keterangan = $query['0'];
        //     $data = [
        //         'message' => $keterangan,
        //         'errors' => 'sudah approve',
        //         'kodestatus' => '1',
        //         'kodenobukti' => '1'
        //     ];

        //     return response($data);
        // } else 
        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('serviceoutheader', $id);
        $useredit = $getEditing->editing_by ?? '';
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $tgltutup = (new Parameter())->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

        if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $pengeluaran->nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $pengeluaran->nobukti . '</b><br>' . $keteranganerror . '<br> ( ' . date('d-m-Y', strtotime($tgltutup)) . ' ) <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($useredit != '' && $useredit != $user) {

            $waktu = (new Parameter())->cekBatasWaktuEdit('Service Out Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'serviceoutheader', $useredit);
                }

                $data = [
                    'message' => '',
                    'error' => false,
                    'statuspesan' => 'success',
                ];

                return response($data);
            } else {

                $keteranganerror = $error->cekKeteranganError('SDE') ?? '';
                $keterror = 'No Bukti <b>' . $pengeluaran->nobukti . '</b><br>' . $keteranganerror . ' <b>' . $useredit . '</b> <br> ' . $keterangantambahanerror;
                $data = [
                    'error' => true,
                    'message' => $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {
            (new MyModel())->createLockEditing($id, 'serviceoutheader', $useredit);


            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];
            return response($data);
        }
    }
    public function combo(Request $request)
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
            'serviceout' => ServiceOutDetail::all(),
            'servicein' => ServiceInHeader::all()
        ];

        return response([
            'data' => $data
        ]);
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceoutheader')->getColumns();

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
            $serviceOutHeader = ServiceOutHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($serviceOutHeader->statuscetak != $statusSudahCetak->id) {
                $serviceOutHeader->statuscetak = $statusSudahCetak->id;
                // $serviceOutHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $serviceOutHeader->userbukacetak = auth('api')->user()->name;
                $serviceOutHeader->jumlahcetak = $serviceOutHeader->jumlahcetak + 1;
                if ($serviceOutHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($serviceOutHeader->getTable()),
                        'postingdari' => 'PRINT SERVICE OUT HEADER',
                        'idtrans' => $serviceOutHeader->id,
                        'nobuktitrans' => $serviceOutHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $serviceOutHeader->toArray(),
                        'modifiedby' => $serviceOutHeader->modifiedby
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
        $serviceOutHeader = new ServiceOutHeader();
        $service_OutHeader = $serviceOutHeader->getExport($id);

        $serviceOutDetail = new ServiceOutDetail();
        $service_OutDetail = $serviceOutDetail->get();

        if ($request->export == true) {
            $tglBukti = $service_OutHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $service_OutHeader->tglbukti = $dateTglBukti;

            $tglKeluar = $service_OutHeader->tglkeluar;
            $timeStamp = strtotime($tglKeluar);
            $datetglKeluar = date('d-m-Y', $timeStamp);
            $service_OutHeader->tglkeluar = $datetglKeluar;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $service_OutHeader->judul);
            $sheet->setCellValue('A2', $service_OutHeader->judulLaporan);
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
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Trado',
                    'index' => 'trado_id',
                ],
                [
                    'label' => 'Tanggal Keluar',
                    'index' => 'tglkeluar',
                ],
            ];

            $detail_columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'NO BUKTI SERVICE IN',
                    'index' => 'servicein_nobukti',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
                ]
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $service_OutHeader->{$header_column['index']});
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
            $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            foreach ($service_OutDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }
                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->servicein_nobukti);
                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);
                $sheet->getColumnDimension('C')->setWidth(50);

                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                $detail_start_row++;
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan ServiceOut  ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $service_OutHeader
            ]);
        }
    }
}
