<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Models\Bank;
use App\Models\Error;

use App\Models\MyModel;
use App\Models\LogTrail;
use App\Models\Supplier;
use App\Models\AkunPusat;
use App\Models\AlatBayar;
use App\Models\Parameter;
use App\Models\SaldoHutang;
use App\Models\HutangDetail;
use App\Models\HutangHeader;
use Illuminate\Http\Request;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalPelunasanHutangRequest;
use App\Models\PelunasanHutangDetail;
use App\Models\PelunasanHutangHeader;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Requests\GetIndexRangeRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\StorePelunasanHutangDetailRequest;
use App\Http\Requests\StorePelunasanHutangHeaderRequest;
use App\Http\Requests\UpdatePelunasanHutangHeaderRequest;
use App\Http\Requests\DestroyPelunasanHutangHeaderRequest;
use App\Models\Locking;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PelunasanHutangHeaderController extends Controller
{
    /**
     * @ClassName 
     * PelunasanHutangHeader
     * @Detail PelunasanHutangDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $PelunasanHutangheader = new PelunasanHutangHeader();
        return response([
            'data' => $PelunasanHutangheader->get(),
            'attributes' => [
                'totalRows' => $PelunasanHutangheader->totalRows,
                'totalPages' => $PelunasanHutangheader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePelunasanHutangHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $PelunasanHutangHeader = (new PelunasanHutangHeader())->processStore([
                'bank_id' => $request->bank_id,
                'tglbukti' => $request->tglbukti,
                'supplier_id' => $request->supplier_id,
                'statusapproval' => $request->statusapproval,
                'alatbayar_id' => $request->alatbayar_id,
                'tglcair' => $request->tglcair,
                'nowarkat' => $request->nowarkat,
                'hutang_id' => $request->hutang_id,
                'hutang_nobukti' => $request->hutang_nobukti,
                'bayar' => $request->bayar,
                'potongan' => $request->potongan,
                'keterangan' => $request->keterangan,
                // 'coadebet' =>$request->coadebet,
            ]);
            if ($request->button == 'btnSubmit') {
                /* Set position and page */
                $PelunasanHutangHeader->position = $this->getPosition($PelunasanHutangHeader, $PelunasanHutangHeader->getTable())->position;
                if ($request->limit == 0) {
                    $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / (10));
                } else {
                    $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / ($request->limit ?? 10));
                }
                $PelunasanHutangHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
                $PelunasanHutangHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $PelunasanHutangHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = PelunasanHutangHeader::findAll($id);
        $detail = PelunasanHutangDetail::getAll($id);

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

    public function update(UpdatePelunasanHutangHeaderRequest $request, PelunasanHutangHeader $PelunasanHutangHeader, $id)
    {

        DB::beginTransaction();
        try {
            /* Store header */
            $PelunasanHutang = PelunasanHutangHeader::findOrFail($id);
            $PelunasanHutangHeader = (new PelunasanHutangHeader())->processUpdate($PelunasanHutang, [
                'bank_id' => $request->bank_id,
                'tglbukti' => $request->tglbukti,
                'supplier_id' => $request->supplier_id,
                'statusapproval' => $request->statusapproval,
                'alatbayar_id' => $request->alatbayar_id,
                'tglcair' => $request->tglcair,
                'nowarkat' => $request->nowarkat,
                'hutang_id' => $request->hutang_id,
                'hutang_nobukti' => $request->hutang_nobukti,
                'bayar' => $request->bayar,
                'potongan' => $request->potongan,
                'keterangan' => $request->keterangan,
                // 'coadebet' =>$request->coadebet,
            ]);
            /* Set position and page */
            $PelunasanHutangHeader->position = $this->getPosition($PelunasanHutangHeader, $PelunasanHutangHeader->getTable())->position;
            if ($request->limit == 0) {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / (10));
            } else {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / ($request->limit ?? 10));
            }
            $PelunasanHutangHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $PelunasanHutangHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));


            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $PelunasanHutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        DB::beginTransaction();
    }

    /**
     * @ClassName 
     * @Keterangan HAPUS DATA
     */
    public function destroy(DestroyPelunasanHutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $PelunasanHutangHeader = (new PelunasanHutangHeader())->processDestroy($id, 'DELETE PELUNASAN HUTANG');
            $selected = $this->getPosition($PelunasanHutangHeader, $PelunasanHutangHeader->getTable(), true);
            $PelunasanHutangHeader->position = $selected->position;
            $PelunasanHutangHeader->id = $selected->id;
            if ($request->limit == 0) {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / (10));
            } else {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / ($request->limit ?? 10));
            }
            $PelunasanHutangHeader->tgldariheader = date('Y-m-d', strtotime(request()->tgldariheader));
            $PelunasanHutangHeader->tglsampaiheader = date('Y-m-d', strtotime(request()->tglsampaiheader));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $PelunasanHutangHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('PelunasanHutangheader')->getColumns();

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
            'supplier' => Supplier::all(),
            'bank' => Bank::all(),
            'coa' => AkunPusat::all(),
            'alatbayar' => AlatBayar::all(),
            'PelunasanHutang' => PelunasanHutangHeader::all(),
            'pengeluaran' => PengeluaranHeader::all(),
            'hutangheader' => HutangHeader::all(),

        ];

        return response([
            'data' => $data
        ]);
    }

    public function getHutang($id)
    {
        $hutang = new HutangHeader();
        return response([
            'data' => $hutang->getHutang($id),
            'id' => $id,
            'attributes' => [
                'totalRows' => $hutang->totalRows,
                'totalPages' => $hutang->totalPages
            ]
        ]);
    }

    public function getPembayaran($id, $supplierId)
    {
        $PelunasanHutang = new PelunasanHutangHeader();
        return response([
            'data' => $PelunasanHutang->getPembayaran($id, $supplierId),
            'attributes' => [
                'totalRows' => $PelunasanHutang->totalRows,
                'totalPages' => $PelunasanHutang->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(ApprovalPelunasanHutangRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'bayarId' => $request->bayarId
            ];

            (new PelunasanHutangHeader())->processApproval($data);
            DB::commit();
            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $PelunasanHutang = PelunasanHutangHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($PelunasanHutang->statuscetak != $statusSudahCetak->id) {
                $PelunasanHutang->statuscetak = $statusSudahCetak->id;
                // $PelunasanHutang->tglbukacetak = date('Y-m-d H:i:s');
                // $PelunasanHutang->userbukacetak = auth('api')->user()->name;
                $PelunasanHutang->jumlahcetak = $PelunasanHutang->jumlahcetak + 1;

                if ($PelunasanHutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($PelunasanHutang->getTable()),
                        'postingdari' => 'PRINT HUTANG BAYAR HEADER',
                        'idtrans' => $PelunasanHutang->id,
                        'nobuktitrans' => $PelunasanHutang->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $PelunasanHutang->toArray(),
                        'modifiedby' => auth('api')->user()->name,
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
        $PelunasanHutang = PelunasanHutangHeader::find($id);
        $nobukti = $PelunasanHutang->nobukti ?? '';
        // dd($PelunasanHutang->statusapproval);
        $status = $PelunasanHutang->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $PelunasanHutang->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        $aksi = request()->aksi ?? '';

        if ($aksi == 'PRINTER BESAR' || $aksi == 'PRINTER KECIL') {
            goto lanjut;
        }

        $pengeluaran = $PelunasanHutang->pengeluaran_nobukti ?? '';
        // dd($pengeluaran);
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

        lanjut:

        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $parameter = new Parameter();

        $tgltutup = $parameter->cekText('TUTUP BUKU', 'TUTUP BUKU') ?? '1900-01-01';
        $tgltutup = date('Y-m-d', strtotime($tgltutup));
        $user = auth('api')->user()->name;
        $getEditing = (new Locking())->getEditing('pelunasanhutangheader', $id);
        $useredit = $getEditing->editing_by ?? '';

        if ($statusdatacetak == $statusCetak->id && ($aksi != 'EDIT' || !($status == $statusApproval->id))) {
            // dd($aksi,($aksi !='EDIT'));
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];


            return response($data);
        } else if ($tgltutup >= $PelunasanHutang->tglbukti) {
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
            $waktu = (new Parameter())->cekBatasWaktuEdit('pelunasan hutang header BUKTI');

            $editingat = new DateTime(date('Y-m-d H:i:s', strtotime($getEditing->editing_at)));
            $diffNow = $editingat->diff(new DateTime(date('Y-m-d H:i:s')));
            $totalminutes =  ($diffNow->days * 24 * 60) + ($diffNow->h * 60) + $diffNow->i;
            if ($totalminutes > $waktu) {
                if ($aksi != 'DELETE' && $aksi != 'EDIT') {
                    (new MyModel())->createLockEditing($id, 'pelunasanhutangheader', $useredit);
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
                (new MyModel())->createLockEditing($id, 'pelunasanhutangheader', $useredit);
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
        $hutang = DB::table("pelunasanhutangheader")->from(DB::raw("pelunasanhutangheader"))->where('id', $id)->first();

        $cekdata = (new PelunasanHutangHeader())->cekvalidasiaksi($hutang->nobukti, $hutang->pengeluaran_nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->first();

            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'],
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

            $getEditing = (new Locking())->getEditing('pelunasanhutangheader', $id);
            $useredit = $getEditing->editing_by ?? '';
            (new MyModel())->createLockEditing($id, 'pelunasanhutangheader', $useredit);

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
            ];

            return response($data);
        }
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
        $pelunasanHutangHeader = new PelunasanHutangHeader();
        $pelunasan_HutangHeader = $pelunasanHutangHeader->getExport($id);

        $pelunasanHutangDetail = new PelunasanHutangDetail();
        $pelunasan_HutangDetail = $pelunasanHutangDetail->get();

        if ($request->export == true) {
            $tglBukti = $pelunasan_HutangHeader->tglbukti;
            $timeStamp = strtotime($tglBukti);
            $dateTglBukti = date('d-m-Y', $timeStamp);
            $pelunasan_HutangHeader->tglbukti = $dateTglBukti;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $sheet->setCellValue('A1', $pelunasan_HutangHeader->judul);
            $sheet->setCellValue('A2', $pelunasan_HutangHeader->judulLaporan);
            $sheet->getStyle("A1")->getFont()->setSize(11);
            $sheet->getStyle("A2")->getFont()->setSize(11);
            $sheet->getStyle("A1")->getFont()->setBold(true);
            $sheet->getStyle("A2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
            $sheet->mergeCells('A1:H1');
            $sheet->mergeCells('A2:H2');

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
                    'label' => 'Supplier',
                    'index' => 'supplier_id',
                ]
            ];

            $detail_columns = [
                [
                    'label' => 'NO',
                ],
                [
                    'label' => 'NO BUKTI HUTANG',
                    'index' => 'hutang_nobukti',
                ],
                [
                    'label' => 'NO SPB / HUTANG EXTRA',
                    'index' => 'spb_nobukti',
                ],
                [
                    'label' => 'NOMINAL HUTANG',
                    'index' => 'nominalhutang',
                    'format' => 'currency'
                ],
                [
                    'label' => 'NOMINALBAYAR',
                    'index' => 'nominaLbayar',
                    'format' => 'currency'
                ],
                [
                    'label' => 'DISKON',
                    'index' => 'diskon',
                    'format' => 'currency'
                ],
                [
                    'label' => 'KETERANGAN DISKON',
                    'index' => 'keterangandiskon',
                ],
                [
                    'label' => 'SISA PIUTANG',
                    'index' => 'sisahutang',
                    'format' => 'currency'
                ],

            ];

            //LOOPING HEADER        
            foreach ($header_columns as $header_column) {
                $sheet->setCellValue('B' . $header_start_row, $header_column['label']);
                $sheet->setCellValue('C' . $header_start_row++, ': ' . $pelunasan_HutangHeader->{$header_column['index']});
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

            $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->applyFromArray($styleArray);

            // LOOPING DETAIL
            $nominalbayar = 0;
            foreach ($pelunasan_HutangDetail as $response_index => $response_detail) {
                // dd($response_detail);
                foreach ($detail_columns as $detail_columns_index => $detail_column) {
                    $sheet->setCellValue($alphabets[$detail_columns_index] . $detail_start_row, isset($detail_column['index']) ? $response_detail->{$detail_column['index']} : $response_index + 1);
                    $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->getFont()->setBold(true);
                    $sheet->getStyle("A$detail_table_header_row:H$detail_table_header_row")->getAlignment()->setHorizontal('center');
                }

                $sheet->setCellValue("A$detail_start_row", $response_index + 1);
                $sheet->setCellValue("B$detail_start_row", $response_detail->hutang_nobukti);
                $sheet->setCellValue("C$detail_start_row", $response_detail->spb_nobukti);
                $sheet->setCellValue("D$detail_start_row", $response_detail->nominalhutang);
                $sheet->setCellValue("E$detail_start_row", $response_detail->nominaLbayar);
                $sheet->setCellValue("F$detail_start_row", $response_detail->diskon);
                $sheet->setCellValue("G$detail_start_row", $response_detail->keterangandiskon);
                $sheet->setCellValue("H$detail_start_row", $response_detail->sisahutang);

                $sheet->getStyle("G$detail_start_row")->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('G')->setWidth(50);
                $sheet->getStyle("D$detail_start_row:F$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");
                $sheet->getStyle("H$detail_start_row")->applyFromArray($style_number)->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

                $sheet->getStyle("A$detail_start_row:H$detail_start_row")->applyFromArray($styleArray);
                $sheet->getStyle("D$detail_start_row:F$detail_start_row")->applyFromArray($style_number);
                $sheet->getStyle("H$detail_start_row")->applyFromArray($style_number);

                $nominalbayar += $response_detail->nominaLbayar;
                $detail_start_row++;
            }
            $total_start_row = $detail_start_row;
            $sheet->mergeCells('A' . $total_start_row . ':D' . $total_start_row);
            $sheet->setCellValue("A$total_start_row", 'Total')->getStyle('A' . $total_start_row . ':D' . $total_start_row)->applyFromArray($styleArray)->getFont()->setBold(true);
            $sheet->setCellValue("E$total_start_row", "=SUM(E9:E" . ($detail_start_row - 1) . ")")->getStyle("E$total_start_row")->applyFromArray($style_number)->getFont()->setBold(true);
            $sheet->getStyle("E$total_start_row")->getNumberFormat()->setFormatCode("#,##0.00_);(#,##0.00)");

            $sheet->getStyle("F$detail_start_row:H$detail_start_row")->applyFromArray($styleArray);

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $filename = 'Laporan Pelunasan Hutang' . date('dmYHis');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } else {
            return response([
                'data' => $pelunasan_HutangHeader
            ]);
        }
    }
}
