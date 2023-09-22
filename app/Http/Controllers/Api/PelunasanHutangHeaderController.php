<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StorePelunasanHutangHeaderRequest;
use App\Http\Requests\DestroyPelunasanHutangHeaderRequest;
use App\Http\Requests\StorePelunasanHutangDetailRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\UpdatePelunasanHutangHeaderRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\AlatBayar;
use App\Models\Bank;
use App\Models\AkunPusat;
use App\Models\Error;
use App\Models\Supplier;
use App\Models\PelunasanHutangHeader;
use App\Models\PelunasanHutangDetail;
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

class PelunasanHutangHeaderController extends Controller
{
   /**
     * @ClassName 
     * PelunasanHutangHeader
     * @Detail1 PelunasanHutangDetailController
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
     * @ClassName store
     */
    public function store(StorePelunasanHutangHeaderRequest $request)
    {
        DB::beginTransaction();
        try {
            /* Store header */
            $PelunasanHutangHeader = (new PelunasanHutangHeader())->processStore([
                'bank_id' =>$request->bank_id,
                'tglbukti' =>$request->tglbukti,
                'supplier_id' =>$request->supplier_id,
                'statusapproval' =>$request->statusapproval,
                'alatbayar_id' =>$request->alatbayar_id,
                'tglcair' =>$request->tglcair,
                'hutang_id' =>$request->hutang_id,
                'hutang_nobukti' =>$request->hutang_nobukti,
                'bayar' =>$request->bayar,
                'potongan' =>$request->potongan,
                'keterangan' =>$request->keterangan,
                // 'coadebet' =>$request->coadebet,
            ]);
            /* Set position and page */
            $PelunasanHutangHeader->position = $this->getPosition($PelunasanHutangHeader, $PelunasanHutangHeader->getTable())->position;
            if ($request->limit==0) {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / (10));
            } else {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / ($request->limit ?? 10));
            }
            $PelunasanHutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $PelunasanHutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            

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
     * @ClassName update
     */

    public function update(UpdatePelunasanHutangHeaderRequest $request, PelunasanHutangHeader $PelunasanHutangHeader, $id)
    {

        DB::beginTransaction();
        try {
            /* Store header */
            $PelunasanHutang = PelunasanHutangHeader::findOrFail($id);
            $PelunasanHutangHeader = (new PelunasanHutangHeader())->processUpdate($PelunasanHutang, [
                'bank_id' =>$request->bank_id,
                'tglbukti' =>$request->tglbukti,
                'supplier_id' =>$request->supplier_id,
                'statusapproval' =>$request->statusapproval,
                'alatbayar_id' =>$request->alatbayar_id,
                'tglcair' =>$request->tglcair,
                'hutang_id' =>$request->hutang_id,
                'hutang_nobukti' =>$request->hutang_nobukti,
                'bayar' =>$request->bayar,
                'potongan' =>$request->potongan,
                'keterangan' =>$request->keterangan,
                // 'coadebet' =>$request->coadebet,
            ]);
            /* Set position and page */
            $PelunasanHutangHeader->position = $this->getPosition($PelunasanHutangHeader, $PelunasanHutangHeader->getTable())->position;
            if ($request->limit==0) {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / (10));
            } else {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / ($request->limit ?? 10));
            }
            $PelunasanHutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $PelunasanHutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
           

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
     * @ClassName destroy
     */
    public function destroy(DestroyPelunasanHutangHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $PelunasanHutangHeader = (new PelunasanHutangHeader())->processDestroy($id, 'DELETE PELUNASAN HUTANG');
            $selected = $this->getPosition($PelunasanHutangHeader, $PelunasanHutangHeader->getTable(), true);
            $PelunasanHutangHeader->position = $selected->position;
            $PelunasanHutangHeader->id = $selected->id;
            if ($request->limit==0) {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / (10));
            } else {
                $PelunasanHutangHeader->page = ceil($PelunasanHutangHeader->position / ($request->limit ?? 10));
            }
            $PelunasanHutangHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $PelunasanHutangHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
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
                    $PelunasanHutang = PelunasanHutangHeader::find($request->bayarId[$i]);
                    if ($PelunasanHutang->statusapproval == $statusApproval->id) {
                        $PelunasanHutang->statusapproval = $statusNonApproval->id;
                        $aksi = $statusNonApproval->text;
                    } else {
                        $PelunasanHutang->statusapproval = $statusApproval->id;
                        $aksi = $statusApproval->text;
                    }

                    $PelunasanHutang->tglapproval = date('Y-m-d', time());
                    $PelunasanHutang->userapproval = auth('api')->user()->name;

                    if ($PelunasanHutang->save()) {
                        $logTrail = [
                            'namatabel' => strtoupper($PelunasanHutang->getTable()),
                            'postingdari' => 'APPROVAL HUTANG BAYAR',
                            'idtrans' => $PelunasanHutang->id,
                            'nobuktitrans' => $PelunasanHutang->nobukti,
                            'aksi' => $aksi,
                            'datajson' => $PelunasanHutang->toArray(),
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
                        'penerimaan' => "PELUNASAN HUTANG $query->keterangan"
                    ],
                    'message' => "PELUNASAN HUTANG $query->keterangan",
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
            $PelunasanHutang = PelunasanHutangHeader::lockForUpdate()->findOrFail($id);
            $statusSudahCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($PelunasanHutang->statuscetak != $statusSudahCetak->id) {
                $PelunasanHutang->statuscetak = $statusSudahCetak->id;
                $PelunasanHutang->tglbukacetak = date('Y-m-d H:i:s');
                $PelunasanHutang->userbukacetak = auth('api')->user()->name;
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
        // dd($PelunasanHutang->statusapproval);
        $status = $PelunasanHutang->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $PelunasanHutang->statuscetak;
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
        $hutang = DB::table("pelunasanhutangheader")->from(DB::raw("pelunasanhutangheader"))->where('id', $id)->first();

        $cekdata = (new PelunasanHutangHeader())->cekvalidasiaksi($hutang->nobukti);
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
     */
    public function report()
    { }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $PelunasanHutang = new PelunasanHutangHeader();
        return response([
            'data' => $PelunasanHutang->getExport($id)
        ]);
    }

}
