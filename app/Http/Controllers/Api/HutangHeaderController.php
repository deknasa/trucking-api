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
     */
    public function store(StoreHutangHeaderRequest $request)
    {

        DB::beginTransaction();
        try {
            /* Store header */
            $hutangHeader = (new HutangHeader())->processStore($request->all());
            /* Set position and page */
            $hutangHeader->position = $this->getPosition($hutangHeader, $hutangHeader->getTable())->position;
            $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
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

    public function approval(ApprovalHutangHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->hutangId); $i++) {
                $hutangHeader = HutangHeader::find($request->hutangId[$i]);

                if ($hutangHeader->statusapproval == $statusApproval->id) {
                    $hutangHeader->statusapproval = $statusNonApproval->id;
                    $aksi = $statusNonApproval->text;
                } else {
                    $hutangHeader->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                }

                $hutangHeader->tglapproval = date('Y-m-d', time());
                $hutangHeader->userapproval = auth('api')->user()->name;

                if ($hutangHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutangHeader->getTable()),
                        'postingdari' => 'APPROVAL PENERIMAAN KAS/BANK',
                        'idtrans' => $hutangHeader->id,
                        'nobuktitrans' => $hutangHeader->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $hutangHeader->toArray(),
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
        } catch (\Throwable $th) {
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
     */
    public function update(UpdateHutangHeaderRequest $request, HutangHeader $hutangHeader,$id)
    {
        DB::beginTransaction();
        try {

            /* Store header */
            $hutangHeader = HutangHeader::findOrFail($id);
            $hutangHeader = (new HutangHeader())->processUpdate($hutangHeader,$request->all());
            /* Set position and page */
            $hutangHeader->position = $this->getPosition($hutangHeader, $hutangHeader->getTable())->position;
            $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
            if (isset($request->limit)) {
                $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));
            }
 
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
     */
    public function destroy(DestroyHutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $hutangHeader = (new HutangHeader())->processDestroy($id);
            $selected = $this->getPosition($hutangHeader, $hutangHeader->getTable(), true);
            $hutangHeader->position = $selected->position;
            $hutangHeader->id = $selected->id;
            $hutangHeader->page = ceil($hutangHeader->position / ($request->limit ?? 10));

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
            $hutang = HutangHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($hutang->statuscetak != $statusSudahCetak->id) {
                $hutang->statuscetak = $statusSudahCetak->id;
                $hutang->tglbukacetak = date('Y-m-d H:i:s');
                $hutang->userbukacetak = auth('api')->user()->name;
                $hutang->jumlahcetak = $hutang->jumlahcetak + 1;

                if ($hutang->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutang->getTable()),
                        'postingdari' => 'PRINT HUTANG HEADER',
                        'idtrans' => $hutang->id,
                        'nobuktitrans' => $hutang->nobukti,
                        'aksi' => 'PRINT',
                        'datajson' => $hutang->toArray(),
                        'modifiedby' => Auth('api')->user()->name
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
        $hutang = HutangHeader::find($id);

        $statusdatacetak = $hutang->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();
        if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
            $keterangan = [
                'keterangan' => 'No Bukti ' . $hutang->nobukti . ' ' . $query->keterangan
            ];

            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else {

            $data = [
                'message' => '',
                'errors' => 'belum approve',
                'kodestatus' => '0',
                'kodenobukti' => '1'
            ];

            return response($data);
        }
    }
    public function cekValidasiAksi($id)
    {
        $hutangHeader = new HutangHeader();
        $nobukti = HutangHeader::from(DB::raw("hutangheader"))->where('id', $id)->first();
        $cekdata = $hutangHeader->cekvalidasiaksi($nobukti->nobukti);
        if ($cekdata['kondisi'] == true) {
            $query = DB::table('error')
                ->select(
                    DB::raw("ltrim(rtrim(keterangan))+' (" . $cekdata['keterangan'] . ")' as keterangan")
                )
                ->where('kodeerror', '=', $cekdata['kodeerror'])
                ->get();
            $keterangan = $query['0'];

            $data = [
                'status' => false,
                'message' => $keterangan,
                'errors' => '',
                'kondisi' => $cekdata['kondisi'],
            ];

            return response($data);
        } else {

            $data = [
                'status' => false,
                'message' => '',
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
}
