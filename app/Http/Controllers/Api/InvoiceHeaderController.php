<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Error;
use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Models\InvoiceHeader;
use App\Models\PiutangDetail;
use App\Models\PiutangHeader;
use App\Models\SuratPengantar;

use App\Models\OrderanTrucking;
use App\Models\JurnalUmumDetail;

use App\Models\JurnalUmumHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreInvoiceDetailRequest;
use App\Http\Requests\StoreInvoiceHeaderRequest;
use App\Http\Requests\StorePiutangDetailRequest;
use App\Http\Requests\StorePiutangHeaderRequest;
use App\Http\Controllers\InvoiceDetailController;
use App\Http\Requests\UpdateInvoiceHeaderRequest;
use App\Http\Requests\UpdatePiutangHeaderRequest;
use App\Http\Requests\DestroyInvoiceHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Controllers\Api\InvoiceDetailController as ApiInvoiceDetailController;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceHeaderController extends Controller
{
    /**
     * @ClassName 
     * InvoiceHeader
     * @Detail InvoiceDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $invoice = new InvoiceHeader();
        return response([
            "data" => $invoice->get(),
            "attributes" => [
                'totalRows' => $invoice->totalRows,
                'totalPages' => $invoice->totalPages
            ]
        ]);
    }



    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreInvoiceHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->detail, true);
            // dd($requestData);
            $data = [
                'tglbukti' => $request->tglbukti,
                'tglterima' => $request->tglterima,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'agen_id' => $request->agen_id,
                'statusjeniskendaraan' => $request->statusjeniskendaraan,
                'jenisorder_id' => $request->jenisorder_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'statuspilihaninvoice' => $request->statuspilihaninvoice,
                'noinvoicepajak' => $request->noinvoicepajak,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'sp_id' => $requestData['sp_id'],
                'nominalretribusi' => $requestData['nominalretribusi'],
                'nominalextra' => $requestData['nominalextra'],
                'omset' => $requestData['omset'],
                'keterangan' => $requestData['keterangan'],
                'agen' => $request->agen,
                'jenisorder' => $request->jenisorder
            ];
            $invoiceHeader = (new InvoiceHeader())->processStore($data);

            if ($request->button == 'btnSubmit') {
                $invoiceHeader->position = $this->getPosition($invoiceHeader, $invoiceHeader->getTable())->position;
                if ($request->limit == 0) {
                    $invoiceHeader->page = ceil($invoiceHeader->position / (10));
                } else {
                    $invoiceHeader->page = ceil($invoiceHeader->position / ($request->limit ?? 10));
                }
                $invoiceHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
                $invoiceHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $invoiceHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {
        $invoice = (new InvoiceHeader)->findAll($id);
        return response([
            'status' => true,
            'data' => $invoice
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan EDIT DATA
     */
    public function update(UpdateInvoiceHeaderRequest $request, InvoiceHeader $invoiceheader): JsonResponse
    {
        DB::beginTransaction();

        try {
            $requestData = json_decode($request->detail, true);
            $data = [
                'tglbukti' => $request->tglbukti,
                'tglterima' => $request->tglterima,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'agen_id' => $request->agen_id,
                'noinvoicepajak' => $request->noinvoicepajak,
                'jenisorder_id' => $request->jenisorder_id,
                'piutang_nobukti' => $request->piutang_nobukti,
                'tgldari' => $request->tgldari,
                'tglsampai' => $request->tglsampai,
                'statuspilihaninvoice' => $request->statuspilihaninvoice,
                'sp_id' => $requestData['sp_id'],
                'nominalretribusi' => $requestData['nominalretribusi'],
                'nominalextra' => $requestData['nominalextra'],
                'omset' => $requestData['omset'],
                'keterangan' => $requestData['keterangan'],
                'agen' => $request->agen,
                'jenisorder' => $request->jenisorder
            ];

            $invoiceHeader = (new InvoiceHeader())->processUpdate($invoiceheader, $data);
            $invoiceHeader->position = $this->getPosition($invoiceHeader, $invoiceHeader->getTable())->position;
            if ($request->limit == 0) {
                $invoiceHeader->page = ceil($invoiceHeader->position / (10));
            } else {
                $invoiceHeader->page = ceil($invoiceHeader->position / ($request->limit ?? 10));
            }
            $invoiceHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' => $invoiceHeader
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
    public function destroy(DestroyInvoiceHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $invoiceHeader = (new InvoiceHeader())->processDestroy($id, 'DELETE INVOICE');
            $selected = $this->getPosition($invoiceHeader, $invoiceHeader->getTable(), true);
            $invoiceHeader->position = $selected->position;
            $invoiceHeader->id = $selected->id;
            if ($request->limit == 0) {
                $invoiceHeader->page = ceil($invoiceHeader->position / (10));
            } else {
                $invoiceHeader->page = ceil($invoiceHeader->position / ($request->limit ?? 10));
            }
            $invoiceHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $invoiceHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $invoiceHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('invoiceheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    public function getSP(Request $request)
    {
        $invoice = new InvoiceHeader();
        $datahasil = $invoice->getSPSearch($request,0,false);
        // $dari = date('Y-m-d', strtotime($request->tgldari));
        // $sampai = date('Y-m-d', strtotime($request->tglsampai));

        // $cekSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
        //     ->whereRaw("agen_id = $request->agen_id")
        //     ->whereRaw("jenisorder_id = $request->jenisorder_id")
        //     ->whereRaw("tglbukti >= '$dari'")
        //     ->whereRaw("tglbukti <= '$sampai'")
        //     ->whereRaw("nocont != ''")
        //     ->whereRaw("noseal != ''")
        //     ->whereRaw("suratpengantar.jobtrucking not in(select orderantrucking_nobukti from invoicedetail)");

        // dd($datahasil);

        if (isset($datahasil)) {
            return response([
                // "data" => $invoice->getSP($request)
                "data" => $datahasil
            ]);
        } else {
            return response([
                // "data" => $invoice->getSP($request)
                "data" => []
            ]);
        }
        // if ($cekSP->first()) {
        //     return response([
        //         // "data" => $invoice->getSP($request)
        //         "data" => $datahasil
        //     ]);
        // } else {
        //     return response([
        //         "data" => []
        //     ]);
        // }
    }

    public function getEdit($id, Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        return response([
            //  "data" => $invoice->getEdit($id, $request)
             "data" => $invoice->getSPSearch($request,$id,true)

        ]);
    }

    public function getAllEdit($id, Request $request)
    {
        $invoice = new InvoiceHeader();
        $dari = date('Y-m-d', strtotime($request->tgldari));
        $sampai = date('Y-m-d', strtotime($request->tglsampai));

        return response([
            // "data" => $invoice->getAllEdit($id, $request)
            "data" => $invoice->getSPSearch($request,0,false)
        ]);
    }

    public function comboapproval(Request $request)
    {

        $params = [
            'status' => $request->status ?? '',
            'grp' => $request->grp ?? '',
            'subgrp' => $request->subgrp ?? '',
        ];
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        if ($params['status'] == 'entry') {
            $query = Parameter::select('id', 'text as keterangan')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);
        } else {
            Schema::create($temp, function ($table) {
                $table->integer('id')->length(11)->nullable();
                $table->string('parameter', 50)->nullable();
                $table->string('param', 50)->nullable();
            });

            DB::table($temp)->insert(
                [
                    'id' => '0',
                    'parameter' => 'ALL',
                    'param' => '',
                ]
            );

            $queryall = Parameter::select('id', 'text as parameter', 'text as param')
                ->where('grp', "=", $params['grp'])
                ->where('subgrp', "=", $params['subgrp']);

            $query = DB::table($temp)
                ->unionAll($queryall);
        }

        $data = $query->get();

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->invoiceId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->invoiceId); $i++) {
                    $invoice = InvoiceHeader::find($request->invoiceId[$i]);
                    if ($invoice->statusapproval == $statusApproval->id) {
                        $invoice->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $invoice->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $invoice->tglapproval = date('Y-m-d', time());
                    $invoice->userapproval = auth('api')->user()->name;

                    if ($invoice->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($invoice->getTable()),
                            'postingdari' => 'APPROVAL INVOICE',
                            'idtrans' => $invoice->id,
                            'nobuktitrans' => $invoice->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $invoice->toArray(),
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
            } else {
                $query = DB::table('error')->select('keterangan')->where('kodeerror', '=', 'WP')
                    ->first();
                return response([
                    'errors' => [
                        'penerimaan' => "INVOICE $query->keterangan"
                    ],
                    'message' => "INVOICE $query->keterangan",
                ], 422);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $invoiceHeader = InvoiceHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($invoiceHeader->statuscetak != $statusSudahCetak->id) {
                $invoiceHeader->statuscetak = $statusSudahCetak->id;
                // $invoiceHeader->tglbukacetak = date('Y-m-d H:i:s');
                // $invoiceHeader->userbukacetak = auth('api')->user()->name;
                $invoiceHeader->jumlahcetak = $invoiceHeader->jumlahcetak + 1;
                if ($invoiceHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($invoiceHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE HEADER',
                        'idtrans' => $invoiceHeader->id,
                        'nobuktitrans' => $invoiceHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $invoiceHeader->toArray(),
                        'modifiedby' => $invoiceHeader->modifiedby
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
        $pengeluaran = InvoiceHeader::find($id);
        $nobukti = $pengeluaran->nobukti ?? '';
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('invoiceheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));


        if ($status == $statusApproval->id && ($aksi == 'DELETE' || $aksi == 'EDIT')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = Error::from(DB::raw("error with (readuncommitted)"))
            //     ->select('keterangan')
            //     ->whereRaw("kodeerror = 'SAP'")
            //     ->first();

            $data = [
                'error' => true,
                'message' => $keterror,
                // 'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;

            // $query = Error::from(DB::raw("error with (readuncommitted)"))
            //     ->select('keterangan')
            //     ->whereRaw("kodeerror = 'SDC'")
            //     ->first();

            $data = [
                'error' => true,
                // 'message' =>  'No Bukti ' . $pengeluaran->nobukti . ' ' . $query->keterangan,
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

            $waktu = (new Parameter())->cekBatasWaktuEdit('Invoice Header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {

                    (new MyModel())->createLockEditing($id, 'invoiceheader', $useredit);
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
            if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                (new MyModel())->createLockEditing($id, 'invoiceheader', $useredit);
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
        $invoiceHeader = new InvoiceHeader();
        $nobukti = InvoiceHeader::from(DB::raw("invoiceheader"))->where('id', $id)->first();
        $cekdata = $invoiceHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                // 'message' => $query->keterangan,
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('invoiceheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'invoiceheader', $useredit);
            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
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
        $invoiceHeader = new InvoiceHeader();
        $invoice_Header = $invoiceHeader->getExport($id);

        $invoiceDetail = new InvoiceDetail();
        $invoice_Detail = $invoiceDetail->get();

        if ($request->export == true) {
            $tglBukti = $invoice_Header->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $invoice_Header->tglbukti = $dateTglBukti;

            $tglterima = $invoice_Header->tglterima;
            $timeStamp = strtotime($tglterima);
            $datetglterima = date('d-m-Y', $timeStamp);
            $invoice_Header->tglterima = $datetglterima;

            $tgljatuhtempo = $invoice_Header->tgljatuhtempo;
            $timeStamp = strtotime($tgljatuhtempo);
            $datetgljatuhtempo = date('d-m-Y', $timeStamp);
            $invoice_Header->tgljatuhtempo = $datetgljatuhtempo;

            //PRINT TO EXCEL
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $invoice_Header->judul);
            $sheet->setCellValue('A2', $invoice_Header->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:M1');
            $sheet->mergeCells('A2:M2');

            $header_start_row = 4;
            $header_right_start_row = 4;
            $detail_table_header_row = 8;
            $detail_start_row = $detail_table_header_row + 1;

            $alphabets = range('A', 'Z');

            $header_columns = [
                [
                    'label' => 'No Invoice',
                    'index' => 'nobukti',
                ],
                [
                    'label' => 'Tanggal',
                    'index' => 'tglbukti',
                ],
                [
                    'label' => 'Tanggal Jatuh Tempo',
                    'index' => 'tgljatuhtempo',
                ]
            ];

            $header_right_columns = [
                [
                    'label' => 'Customer',
                    'index' => 'agen',
                ],
                [
                    'label' => 'Jenis Order',
                    'index' => 'jenisorder_id',
                ],
                [
                    'label' => 'No Bukti Piutang',
                    'index' => 'piutang_nobukti',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'TANGGAL SP',
                    'index' => 'tglsp',
                ],
                [
                    'label' => 'SHIPPER',
                    'index' => 'shipper',
                ],
                [
                    'label' => 'TUJUAN',
                    'index' => 'tujuan',
                ],
                [
                    'label' => 'NO CONT',
                    'index' => 'nocont',
                ],
                [
                    'label' => 'UK. CONT',
                    'index' => 'ukcont',
                ],
                [
                    'label' => 'FULL',
                    'index' => 'full'
                ],
                [
                    'label' => 'EMPTY',
                    'index' => 'empty'
                ],
                [
                    'label' => 'FULL / EMPTY',
                    'index' => 'fullEmpty'
                ],
                [
                    'label' => 'OMSET',
                    'index' => 'omset',
                    'format' => 'currency'
                ],
                [
                    'label' => 'EXTRA',
                    'index' => 'extra',
                    'format' => 'currency'
                ],
                [
                    'label' => 'JUMLAH',
                    'index' => 'jumlah',
                    'format' => 'currency'
                ],
                [
                    'label' => 'KETERANGAN',
                    'index' => 'keterangan'
                ]
            ];

            //LOOPING HEADER       
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $invoice_Header->{$header_column['index']});
            }
            foreach ($header_right_columns as $header_right_column) {
                $sheet->setCellValue('D' . $header_right_start_row, $header_right_column['label']);
                $sheet->setCellValue('E' . $header_right_start_row++, ': ' . $invoice_Header->{$header_right_column['index']});
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
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ]
            ];

            // $sheet->getStyle("A$detail_table_header_row:G$detail_table_header_row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F456E');
            $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $jumlah = 0;
            foreach ($invoice_Detail as $response_index => $response_detail) {

                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:M$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $tglSp = ($response_detail->tglsp != null) ? Date::PHPToExcel(date('Y-m-d', strtotime($response_detail->tglsp))) : '';

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $tglSp);
                $sheet->setCellValue("C$detail_start_row", $response_detail->shipper);
                $sheet->setCellValue("D$detail_start_row", $response_detail->tujuan);
                $sheet->setCellValue("E$detail_start_row", $response_detail->nocont);
                $sheet->setCellValue("F$detail_start_row", $response_detail->ukcont);
                $sheet->setCellValueExplicit("G$detail_start_row", $response_detail->full, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("H$detail_start_row", $response_detail->empty, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("I$detail_start_row", $response_detail->fullEmpty, DataType::TYPE_STRING);
                $sheet->setCellValue("J$detail_start_row", $response_detail->omset);
                $sheet->setCellValue("K$detail_start_row", $response_detail->extra);
                $sheet->setCellValue("L$detail_start_row", $response_detail->jumlah);
                $sheet->setCellValue("M$detail_start_row", $response_detail->keterangan);

                $sheet->getColumnDimension('M')->setWidth(30);

                $sheet->getStyle("A$detail_start_row:M$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("B$detail_start_row")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                $sheet->getStyle("J$detail_start_row:L$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $detail_start_row++;
            }

            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':K' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':M' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("L$detail_start_row", "=SUM(L9:L" . ($detail_start_row - 1) . ")")->getStyle("L$detail_start_row")->applyFromArray($style_number)->getFont()->setBold(true);

            $sheet->getStyle("L$detail_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Invoice ' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $invoice_Header
            ]);
        }
    }
}
