<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreHutangBayarHeaderRequest;
use App\Http\Requests\DestroyHutangBayarHeaderRequest;
use App\Http\Requests\StoreHutangBayarDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdateHutangBayarHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\AkunPusat;
use App\Models\Error;
use App\Models\Supplier;
use App\Models\HutangBayarHeader;
use App\Models\HutangBayarDetail;
use App\Models\HutangDetail;
use App\Models\Parameter;
use App\Models\HutangHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\LogTrail;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\SaldoHutang;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class HutangBayarHeaderController extends Controller
{
    /**
     * @ClassName 
     * HutangBayarHeader
     * @Detail HutangBayarDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $hutangbayarheader = new HutangBayarHeader();
        return response([
            'data' => $hutangbayarheader->get(),
            'attributes' => [
                'totalRows' => $hutangbayarheader->totalRows,
                'totalPages' => $hutangbayarheader->totalPages
            ]
        ]);
    }

    /**
     * @ClassName
     * @Keterangan TAMBAH DATA
     */
    public function store(StoreHutangBayarHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $hutangBayarHeader = (new HutangBayarHeader())->processStore($request->all());
            /* Set position and page */
            $hutangBayarHeader->position = $this->getPosition($hutangBayarHeader, $hutangBayarHeader->getTable())->position;
            if ($request->limit==0) {
                $hutangBayarHeader->page = ceil($hutangBayarHeader->position / (10));
            } else {
                $hutangBayarHeader->page = ceil($hutangBayarHeader->position / ($request->limit ?? 10));
            }
            $hutangBayarHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangBayarHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $hutangBayarHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }


    public function show($id)
    {

        $data = HutangBayarHeader::findAll($id);
        $detail = HutangBayarDetail::getAll($id);

        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }

    /**
     * @ClassName update
     */

    public function update(UpdateHutangBayarHeaderRequest $request, HutangBayarHeader $hutangBayarHeader, $id)
    {

        DB::beginTransaction();
        try {
            /* Store header */
            $hutangBayar = HutangBayarHeader::findOrFail($id);
            $hutangBayarHeader = (new HutangBayarHeader())->processUpdate($hutangBayar, $request->all());
            /* Set position and page */
            $hutangBayarHeader->position = $this->getPosition($hutangBayarHeader, $hutangBayarHeader->getTable())->position;
            if ($request->limit==0) {
                $hutangBayarHeader->page = ceil($hutangBayarHeader->position / (10));
            } else {
                $hutangBayarHeader->page = ceil($hutangBayarHeader->position / ($request->limit ?? 10));
            }
            $hutangBayarHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangBayarHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
           
            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $hutangBayarHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }

        DB::beginTransaction();
    }

    /**
     * @ClassName destroy
     */
    public function destroy(DestroyHutangBayarHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $hutangBayarHeader = (new HutangBayarHeader())->processDestroy($id);
            $selected = $this->getPosition($hutangBayarHeader, $hutangBayarHeader->getTable(), true);
            $hutangBayarHeader->position = $selected->position;
            $hutangBayarHeader->id = $selected->id;
            if ($request->limit==0) {
                $hutangBayarHeader->page = ceil($hutangBayarHeader->position / (10));
            } else {
                $hutangBayarHeader->page = ceil($hutangBayarHeader->position / ($request->limit ?? 10));
            }
            $hutangBayarHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $hutangBayarHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
           
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $hutangBayarHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('hutangbayarheader')->getColumns();

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
            'hutangbayar' => HutangBayarHeader::all(),
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
        $hutangBayar = new HutangBayarHeader();
        return response([
            'data' => $hutangBayar->getPembayaran($id, $supplierId),
            'attributes' => [
                'totalRows' => $hutangBayar->totalRows,
                'totalPages' => $hutangBayar->totalPages
            ]
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
            if ($request->bayarId != '') {

                $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
                $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                    ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

                for ($i = 0; $i < count($request->bayarId); $i++) {
                    $hutangBayar = HutangBayarHeader::find($request->bayarId[$i]);
                    if ($hutangBayar->statusapproval == $statusApproval->id) {
                        $hutangBayar->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $hutangBayar->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $hutangBayar->tglapproval = date('Y-m-d', time());
                    $hutangBayar->userapproval = auth('api')->user()->name;

                    if ($hutangBayar->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($hutangBayar->getTable()),
                            'postingdari' => 'APPROVAL HUTANG BAYAR',
                            'idtrans' => $hutangBayar->id,
                            'nobuktitrans' => $hutangBayar->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $hutangBayar->toArray(),
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
                        'penerimaan' => "PEMBAYARAN HUTANG $query->keterangan"
                    ],
                    'message' => "PEMBAYARAN HUTANG $query->keterangan",
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
            $hutangBayar = HutangBayarHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($hutangBayar->statuscetak != $statusSudahCetak->id) {
                $hutangBayar->statuscetak = $statusSudahCetak->id;
                $hutangBayar->tglbukacetak = date('Y-m-d H:i:s');
                $hutangBayar->userbukacetak = auth('api')->user()->name;
                $hutangBayar->jumlahcetak = $hutangBayar->jumlahcetak + 1;
                if ($hutangBayar->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($hutangBayar->getTable()),
                        'postingdari' => 'PRINT HUTANG BAYAR HEADER',
                        'idtrans' => $hutangBayar->id,
                        'nobuktitrans' => $hutangBayar->id,
                        'aksi' => 'PRINT',
                        'datajson' => $hutangBayar->toArray(),
                        'modifiedby' => $hutangBayar->modifiedby
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
        $hutangBayar = HutangBayarHeader::find($id);
        // dd($hutangBayar->statusapproval);
        $status = $hutangBayar->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $hutangBayar->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah approve',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = DB::table('error')
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->get();
            $keterangan = $query['0'];
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
    public function cekvalidasiAksi($id)
    {
        $hutang = DB::table("hutangbayarheader")->from(DB::raw("hutangbayarheader"))->where('id', $id)->first();

        $cekdata = (new HutangBayarHeader())->cekvalidasiaksi($hutang->nobukti);
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
                'kodeerror' => $cekdata['kodeerror'],
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else {

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
    public function report()
    { }

    /**
     * @ClassName 
     * @Keterangan EXPORT KE EXCEL
     */
    public function export($id)
    {
        $hutangbayar = new HutangBayarHeader();
        return response([
            'data' => $hutangbayar->getExport($id)
        ]);
    }
}
