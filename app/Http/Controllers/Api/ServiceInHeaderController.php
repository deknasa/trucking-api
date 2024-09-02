<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\Trado;
use App\Models\Mekanik;
use App\Models\MyModel;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\ServiceInDetail;
use App\Models\ServiceInHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreServiceInHeaderRequest;
use App\Http\Requests\UpdateServiceInHeaderRequest;
use App\Http\Requests\DestroyServiceInHeaderRequest;
use App\Models\Locking;
use LDAP\Result;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ServiceInHeaderController extends Controller
{
    /**
     * @ClassName 
     * ServiceInHeaderHeader
     * @Detail ServiceInDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index()
    {
        $serviceInHeader = new ServiceInHeader();

        return response([
            'data' => $serviceInHeader->get(),
            'attributes' => [
                'totalRows' => $serviceInHeader->totalRows,
                'totalPages' => $serviceInHeader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreServiceInHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'trado_id' => $request->trado_id,
                'tglmasuk' => $request->tglmasuk,
                'statusserviceout' => $request->statusserviceout,
                'karyawan_id' => $request->karyawan_id,
                'statusserviceout' => $request->statusserviceout,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $serviceInHeader = (new ServiceInHeader())->processStore($data);
            if ($request->button == 'btnSubmit') {
                $serviceInHeader->position = $this->getPosition($serviceInHeader, $serviceInHeader->getTable())->position;
                if ($request->limit == 0) {
                    $serviceInHeader->page = ceil($serviceInHeader->position / (10));
                } else {
                    $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));
                }
                $serviceInHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $serviceInHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $serviceInHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        $serviceInHeader = (new ServiceInHeader)->findAll($id);
        $serviceInDetails = (new ServiceInDetail)->getAll($id);

        return response([
            'status' => true,
            'data' => $serviceInHeader,
            'detail' => $serviceInDetails
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateServiceInHeaderRequest $request, ServiceInHeader $serviceInHeader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'trado_id' => $request->trado_id,
                'tglmasuk' => $request->tglmasuk,
                'statusserviceout' => $request->statusserviceout,
                'karyawan_id' => $request->karyawan_id,
                'statusserviceout' => $request->statusserviceout,
                'keterangan_detail' => $request->keterangan_detail,
            ];
            $serviceInHeader = (new ServiceInHeader())->processUpdate($serviceInHeader, $data);
            $serviceInHeader->position = $this->getPosition($serviceInHeader, $serviceInHeader->getTable())->position;
            if ($request->limit == 0) {
                $serviceInHeader->page = ceil($serviceInHeader->position / (10));
            } else {
                $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));
            }
            $serviceInHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceInHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $serviceInHeader
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
    public function destroy(DestroyServiceInHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $serviceInHeader = (new ServiceInHeader())->processDestroy($id);
            $selected = $this->getPosition($serviceInHeader, $serviceInHeader->getTable(), true);
            $serviceInHeader->position = $selected->position;
            $serviceInHeader->id = $selected->id;
            if ($request->limit == 0) {
                $serviceInHeader->page = ceil($serviceInHeader->position / (10));
            } else {
                $serviceInHeader->page = ceil($serviceInHeader->position / ($request->limit ?? 10));
            }
            $serviceInHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $serviceInHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $serviceInHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function default()
    {

        $serviceInHeader = new ServiceInHeader();
        return response([
            'status' => true,
            'data' => $serviceInHeader->default(),
        ]);
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = ServiceInHeader::findOrFail($id);
        $status = $pengeluaran->statusapproval;
        $nobukti = $pengeluaran->nobukti ?? '';
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('serviceinheader', $id);
        $useredit = $getEditing->editing_by ?? '';
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $tgltutup = (new Parameter())->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));

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

            $waktu = (new Parameter())->cekBatasWaktuEdit('Service In Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            if ($diffNow->i > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'serviceinheader', $useredit);
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
                    'message' =>  $keterror,
                    'kodeerror' => 'SDE',
                    'statuspesan' => 'warning',
                ];

                return response($data);
            }
        } else {
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->createLockEditing($id, 'serviceinheader', $useredit);
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
        $serviceinheader = new ServiceInHeader();
        $nobukti = ServiceInHeader::from(DB::raw("serviceinheader"))->where('id', $id)->first();
        $cekdata = $serviceinheader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'editcoa' => $cekdata['editcoa']
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('serviceinheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'serviceinheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'editcoa' => false
            ];

            return response($data);
        }
    }
    public function combo()
    {
        $data = [
            'mekanik' => Mekanik::all(),
            'trado' => Trado::all(),
        ];

        return response([
            'data' => $data
        ]);
    }
    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('serviceinheader')->getColumns();

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
            $serviceInHeader = ServiceInHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($serviceInHeader->statuscetak != $statusSudahCetak->id) {
                $serviceInHeader->statuscetak = $statusSudahCetak->id;
                // $serviceInHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $serviceInHeader->userbukacetak = auth('api')->user()->name;
                $serviceInHeader->jumlahcetak = $serviceInHeader->jumlahcetak + 1;
                if ($serviceInHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($serviceInHeader->getTable()),
                        'postingdari' => 'PRINT SERVICE IN HEADER',
                        'idtrans' => $serviceInHeader->id,
                        'nobuktitrans' => $serviceInHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $serviceInHeader->toArray(),
                        'modifiedby' => $serviceInHeader->modifiedby
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
        $serviceInHeader = new ServiceInHeader();
        $service_InHeader = $serviceInHeader->getExport($id);

        $serviceInDetail = new ServiceInDetail();
        $service_InDetail = $serviceInDetail->get();

        if ($request->export == true) {
            $tglBukti = $service_InHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $service_InHeader->tglbukti = $dateTglBukti;

            $tglMasuk = $service_InHeader->tglmasuk;
            $timeStamp = strtotime($tglMasuk);
            $datetglMasuk = date('d-m-Y', $timeStamp);
            $service_InHeader->tglmasuk = $datetglMasuk;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $service_InHeader->judul);
            $sheet->setCellValue('A2', $service_InHeader->judulLaporan);
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
                    'label' => 'Tanggal Masuk',
                    'index' => 'tglmasuk',
                ],
            ];

            $detail_columns = [
                [
                    'label' => 'No',
                ],
                [
                    'label' => 'MEKANIK',
                    'index' => 'karyawan_id',
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan',
                ]
            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $service_InHeader->{$header_column['index']});
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

            // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
            $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            foreach ($service_InDetail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:C$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }
                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->karyawan_id);
                $sheet->setCellValue("C$detail_start_row", $response_detail->keterangan);

                $sheet->getColumnDimension('C')->setWidth(50);

                $sheet->getStyle("A$detail_start_row:C$detail_start_row")->applyFromArray($styleArray);
                $detail_start_row++;
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan ServiceIn  ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $service_InHeader
            ]);
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
}
