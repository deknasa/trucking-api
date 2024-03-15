<?php

namespace App\Http\Controllers\Api;

use App\Models\HutangHeader;
use App\Models\HutangDetail;
use App\Http\Requests\StoreHutangHeaderRequest;
use App\Http\Requests\DestroyHutangHeaderRequest;
use App\Http\Requests\StoreHutangDetailRequest;
use App\Http\Requests\UpdateHutangDetailRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalHutangHeaderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Supplier;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateHutangHeaderRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Models\Error;
use App\Models\Pelanggan;
use PhpParser\Builder\Param;
use Illuminate\Database\QueryException;

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
                $hutangHeader->tglbukacetak = date('Y-m-d H:i:s');
                $hutangHeader->userbukacetak = auth('api')->user()->name;
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
        $nobukti=$hutang->nobukti ?? '';
        $statusdatacetak = $hutang->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $parameter = new Parameter();

        $tgltutup=$parameter->cekText('TUTUP BUKU','TUTUP BUKU') ?? '1900-01-01';
        $tgltutup=date('Y-m-d', strtotime($tgltutup));        
        
        if ($statusdatacetak == $statusCetak->id) {
            $keteranganerror = $error->cekKeteranganError('SDC') ?? '';
            $keterror='No Bukti <b>'. $nobukti . '</b><br>' .$keteranganerror.' <br> '.$keterangantambahanerror;
            
            // $query = DB::table('error')
            //     ->select('keterangan')
            //     ->where('kodeerror', '=', 'SDC')
            //     ->first();
            // $keterangan = [
            //     'keterangan' => 'No Bukti ' . $hutang->nobukti . ' ' . $query->keterangan
            // ];

            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1',
                'statuspesan' => 'warning',
                'error' => true,
                'kodeerror' => 'SDC',                
            ];

            return response($data);
        } else if ($tgltutup >= $hutang->tglbukti) {
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
        $hutangHeader = new HutangHeader();
        return response([
            'data' => $hutangHeader->getExport($id)
        ]);
    }
}
