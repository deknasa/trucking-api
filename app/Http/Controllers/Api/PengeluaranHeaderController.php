<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSupirApprovalHeader;
use App\Models\KasGantungHeader;
use App\Models\PengeluaranHeader;
use App\Models\PengeluaranDetail;
use App\Models\Parameter;
use App\Models\Pelanggan;
use App\Models\Bank;
use App\Models\AlatBayar;
use App\Models\AkunPusat;
use App\Models\LogTrail;

use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\GetIndexRangeRequest;

use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePengeluaranDetailRequest;
use App\Models\Error;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Exception;
use Illuminate\Database\QueryException;

class PengeluaranHeaderController extends Controller
{

    /**
     * @ClassName 
     * pengeluaranheadercontainer
     * @Detail PengeluaranDetailController
     * @Keterangan TAMPILKAN DATA
     */
    public function index(GetIndexRangeRequest $request)
    {
        $pengeluaran = new PengeluaranHeader();

        return response([
            'data' => $pengeluaran->get(),
            'attributes' => [
                'totalRows' => $pengeluaran->totalRows,
                'totalPages' => $pengeluaran->totalPages
            ]
        ]);
    }


    public function default()
    {


        $pengeluaranheader = new PengeluaranHeader();
        return response([
            'status' => true,
            'data' => $pengeluaranheader->default(),
        ]);
    }

    /**
     * @ClassName 
     * @Keterangan TAMBAH DATA
     */
    public function store(StorePengeluaranHeaderRequest $request)
    {

        DB::beginTransaction();

        try {
            $pengeluaranHeader = (new PengeluaranHeader())->processStore([
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "postingdari" => $request->postingdari,
                "statusapproval" => $request->statusapproval,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "penerimaan_nobukti" => $request->nobukti_penerimaan,
                "statusformat" => $request->statusformat,
                "nominal_detail" => $request->nominal_detail,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                // "coakredit"=>$request->coakredit,
                "keterangan_detail" => $request->keterangan_detail,
                "noinvoice" => $request->noinvoice,
                "bank_detail" => $request->bank_detail,
            ]);
            $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = PengeluaranHeader::findAll($id);
        $detail = PengeluaranDetail::findAll($id);

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
    public function update(UpdatePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranheader)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranheader, [
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "postingdari" => $request->postingdari,
                "statusapproval" => $request->statusapproval,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "penerimaan_nobukti" => $request->nobukti_penerimaan,
                "statusformat" => $request->statusformat,
                "nominal_detail" => $request->nominal_detail,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                // "coakredit"=>$request->coakredit,
                "keterangan_detail" => $request->keterangan_detail,
                "noinvoice" => $request->noinvoice,
                "bank_detail" => $request->bank_detail,
            ]);
            /* Set position and page */
            $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
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
    public function destroy(DestroyPengeluaranHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranHeader = (new PengeluaranHeader())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable(), true);
            $pengeluaranHeader->position = $selected->position;
            $pengeluaranHeader->id = $selected->id;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    /**
     * @ClassName
     * @Keterangan APPROVAL DATA
     */
    public function approval(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->pengeluaranId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->pengeluaranId); $i++) {
                    $pengeluaranHeader = PengeluaranHeader::find($request->pengeluaranId[$i]);
                    if ($pengeluaranHeader->statusapproval == $statusApproval->id) {
                        $pengeluaranHeader->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $pengeluaranHeader->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $pengeluaranHeader->tglapproval = date('Y-m-d', time());
                    $pengeluaranHeader->userapproval = auth('api')->user()->name;

                    if ($pengeluaranHeader->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                            'postingdari' => 'APPROVAL PENGELUARAN KAS/BANK',
                            'idtrans' => $pengeluaranHeader->id,
                            'nobuktitrans' => $pengeluaranHeader->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $pengeluaranHeader->toArray(),
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
                        'penerimaan' => "PENGELUARAN $query->keterangan"
                    ],
                    'message' => "PENGELUARAN $query->keterangan",
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
            $pengeluaranHeader = PengeluaranHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranHeader->statuscetak = $statusSudahCetak->id;
                $pengeluaranHeader->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaranHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranHeader->jumlahcetak = $pengeluaranHeader->jumlahcetak + 1;
                if ($pengeluaranHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranHeader->getTable()),
                        'postingdari' => 'PRINT PENGELUARAN HEADER',
                        'idtrans' => $pengeluaranHeader->id,
                        'nobuktitrans' => $pengeluaranHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranHeader->toArray(),
                        'modifiedby' => $pengeluaranHeader->modifiedby
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
    public function editCoa(UpdatePengeluaranDetailRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pengeluaran = PengeluaranHeader::findOrFail($id);
            /* Store header */
            $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaran, [
                "bank_id" => $request->bank_id,
                "tglbukti" => $request->tglbukti,
                "pelanggan_id" => $request->pelanggan_id,
                "postingdari" => $request->postingdari,
                "statusapproval" => $request->statusapproval,
                "dibayarke" => $request->dibayarke,
                "alatbayar_id" => $request->alatbayar_id,
                "userapproval" => $request->userapproval,
                "tglapproval" => $request->tglapproval,
                "transferkeac" => $request->transferkeac,
                "transferkean" => $request->transferkean,
                "transferkebank" => $request->transferkebank,
                "penerimaan_nobukti" => $request->nobukti_penerimaan,
                "statusformat" => $request->statusformat,
                "nominal_detail" => $request->nominal_detail,
                "nowarkat" => $request->nowarkat,
                "tgljatuhtempo" => $request->tgljatuhtempo,
                "coadebet" => $request->coadebet,
                // "coakredit"=>$request->coakredit,
                "keterangan_detail" => $request->keterangan_detail,
                "noinvoice" => $request->noinvoice,
                "bank_detail" => $request->bank_detail,
            ]);
            /* Set position and page */
            $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
            if ($request->limit == 0) {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / (10));
            } else {
                $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
            }
            $pengeluaranHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $pengeluaranHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('pengeluaranheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }


    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranHeader::find($id);
        $nobukti=$pengeluaran->nobukti ?? '';
        // $cekdata = $pengeluaran->cekvalidasiaksi($pengeluaran->nobukti);
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

        $tgltutup=$parameter->cekText('TUTUP BUKU','TUTUP BUKU') ?? '1900-01-01';
        $tgltutup=date('Y-m-d', strtotime($tgltutup));        

        
        if ($status == $statusApproval->id && ($aksi == 'DELETE')) {
            $keteranganerror = $error->cekKeteranganError('SAP') ?? '';
            $keterror='No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.' <br> '.$keterangantambahanerror;
            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SAP',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror='No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.' <br> '.$keterangantambahanerror;

            $data = [
                'message' => $keterror,
                'error' => true,
                'kodeerror' => 'SDC',
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($tgltutup >= $pengeluaran->tglbukti) {
            $keteranganerror = $error->cekKeteranganError('TUTUPBUKU') ?? '';
            $keterror = 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> ( '.date('d-m-Y', strtotime($tgltutup)).' ) <br> '.$keterangantambahanerror;
            $data = [
                'error' => true,
                'message' => $keterror,
                'kodeerror' => 'TUTUPBUKU',
                'statuspesan' => 'warning',
            ];

            return response($data);            
        } else {

            $data = [
                'message' => '',
                'error' => false,
                'statuspesan' => 'success',
            ];

            return response($data);
        }
    }

    public function cekValidasiAksi($id)
    {
        $pengeluaranHeader = new PengeluaranHeader();
        $nobukti = PengeluaranHeader::from(DB::raw("pengeluaranheader"))->where('id', $id)->first();
        $cekdata = $pengeluaranHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $data = [
                'error' => true,
                'message' => $cekdata['keterangan'] ?? '',
                'statuspesan' => 'warning',
                'kodeerror' => $cekdata['kodeerror'],
                'editcoa' => $cekdata['editcoa']
            ];

            return response($data);
        } else {

            $data = [
                'error' => false,
                'message' => '',
                'statuspesan' => 'success',
                'editcoa' => false
            ];

            return response($data);
        }
    }


    /**
     * @ClassName
     * @Keterangan CETAK DATA
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     * @Keterangan APPROVAL BUKA CETAK
     */
    public function approvalbukacetak()
    {
    }


    /**
     * @ClassName
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $pengeluaranHeader = new PengeluaranHeader();
        return response([
            'data' => $pengeluaranHeader->getExport($id)
        ]);
    }
}
