<?php

namespace App\Http\Controllers\Api;

use App\Helpers\App;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyKasGantungHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\KasGantungDetail;
use App\Models\KasGantungHeader;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\PengeluaranDetail;
use App\Models\PengeluaranHeader;
use App\Models\Bank;
use App\Models\Penerima;
use App\Http\Requests\StoreKasGantungHeaderRequest;
use App\Http\Requests\UpdateKasGantungHeaderRequest;
use App\Http\Requests\StoreKasGantungDetailRequest;
use App\Http\Requests\JurnalUmumHeaderRequest;
use App\Models\LogTrail;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StorePengeluaranHeaderRequest;
use App\Http\Requests\StorePengeluaranDetailRequest;
use App\Http\Requests\UpdatePengeluaranHeaderRequest;
use App\Models\AlatBayar;
use Illuminate\Database\QueryException;
use App\Http\Requests\DestroyPengeluaranHeaderRequest;
use Illuminate\Http\JsonResponse;

class KasGantungHeaderController extends Controller
{
    /**
     * @ClassName 
     * KasGantungHeader
     * @Detail1 KasGantungDetailController
     */

    public function index(GetIndexRangeRequest $request)
    {
        $kasgantungHeader = new KasGantungHeader();

        return response([
            'data' => $kasgantungHeader->get(),
            'attributes' => [
                'totalRows' => $kasgantungHeader->totalRows,
                'totalPages' => $kasgantungHeader->totalPages
            ]
        ]);
    }

    public function default()
    {
        $kasgantungHeader = new KasGantungHeader();
        return response([
            'status' => true,
            'data' => $kasgantungHeader->default(),
        ]);
    }

    /**
     * @ClassName 
     */
    public function store(StoreKasGantungHeaderRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $bank = Bank::find($request->bank_id);

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '1900/1/1',
                'penerima_id' => $request->penerima_id ?? '',
                'penerima' => $request->penerima ?? '',
                'bank_id' => $request->bank_id ?? 0,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti ?? '',
                'coakaskeluar' => $bank->coa ?? '',
                'postingdari' => $request->postingdari ?? 'ENTRY KAS GANTUNG',
                'tglkaskeluar' => date('Y-m-d', strtotime($request->tglbukti)),
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $request->statusformat,
                'statuscetak' => 0 ?? '',
                'userbukacetak' => '',
                'tglbukacetak' => '',

                'nominal' => $request->nominal,
                'keterangan_detail' => $request->keterangan_detail,
            ];


            $kasgantungHeader = (new KasGantungHeader())->processStore($data);
            $kasgantungHeader->position = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable())->position;
            if ($request->limit == 0) {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / (10));
            } else {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
            }
            $kasgantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $kasgantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $kasgantungHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = KasGantungHeader::findUpdate($id);
        $detail = KasGantungDetail::findUpdate($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName 
     */
    public function update(UpdateKasGantungHeaderRequest $request, KasGantungHeader $kasgantungheader): JsonResponse
    {
        //   dd($request->all());

        DB::beginTransaction();

        try {
            $bank = Bank::find($request->bank_id);

            $data = [
                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)) ?? '1900/1/1',
                'penerima_id' => $request->penerima_id ?? '',
                'penerima' => $request->penerima ?? '',
                'bank_id' => $request->bank_id ?? 0,
                'pengeluaran_nobukti' => $request->pengeluaran_nobukti ?? '',
                'coakaskeluar' => $bank->coa ?? '',
                'postingdari' => $request->postingdari ?? 'ENTRY KAS GANTUNG',
                'tglkaskeluar' => date('Y-m-d', strtotime($request->tglbukti)),
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => $request->statusformat,
                'statuscetak' => 0 ?? '',
                'userbukacetak' => '',
                'coakredit' => '',
                'coadebet' => '',

                'nominal' => $request->nominal ?? 0,
                'keterangan_detail' => $request->keterangan_detail ?? ''
            ];

            $kasgantungHeader = (new KasGantungHeader())->processUpdate($kasgantungheader, $data);
            $kasgantungHeader->position = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable())->position;
            if ($request->limit == 0) {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / (10));
            } else {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
            }
            $kasgantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $kasgantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
            DB::commit();

            return response()->json([
                'message' => 'Berhasil diubah',
                'data' =>  $kasgantungHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    /**
     * @ClassName 
     */
    public function destroy(DestroyKasGantungHeaderRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $kasgantungHeader = (new KasGantungHeader())->processDestroy($id, 'DELETE KAS GANTUNG');
            $selected = $this->getPosition($kasgantungHeader, $kasgantungHeader->getTable(), true);
            $kasgantungHeader->position = $selected->position;
            $kasgantungHeader->id = $selected->id;
            if ($request->limit == 0) {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / (10));
            } else {
                $kasgantungHeader->page = ceil($kasgantungHeader->position / ($request->limit ?? 10));
            }
            $kasgantungHeader->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            $kasgantungHeader->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));

            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $kasgantungHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function combo(Request $request)
    {
        $data = [
            'penerima' => Penerima::all(),
            'bank' => Bank::all(),
        ];

        return response([
            'data' => $data
        ]);
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $kasgantungHeader = KasgantungHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($kasgantungHeader->statuscetak != $statusSudahCetak->id) {
                $kasgantungHeader->statuscetak = $statusSudahCetak->id;
                $kasgantungHeader->tglbukacetak = date('Y-m-d H:i:s');
                $kasgantungHeader->userbukacetak = auth('api')->user()->name;
                $kasgantungHeader->jumlahcetak = $kasgantungHeader->jumlahcetak + 1;
                if ($kasgantungHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($kasgantungHeader->getTable()),
                        'postingdari' => 'PRINT KAS GANTUNG HEADER',
                        'idtrans' => $kasgantungHeader->id,
                        'nobuktitrans' => $kasgantungHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $kasgantungHeader->toArray(),
                        'modifiedby' => $kasgantungHeader->modifiedby
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

    public function cekValidasiAksi($id)
    {
        $kasgantungHeader = new KasGantungHeader();
        $nobukti = KasGantungHeader::from(DB::raw("kasgantungheader"))->where('id', $id)->first();
        $cekdata = $kasgantungHeader->cekvalidasiaksi($nobukti->nobukti);
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

    public function cekvalidasi($id)
    {
        $kasgantung = KasGantungHeader::find($id);
        $statusdatacetak = $kasgantung->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($statusdatacetak == $statusCetak->id) {
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

    public function fieldLength()
    {
        $data = [];
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableDetails('kasgantungheader')->getColumns();

        foreach ($columns as $index => $column) {
            $data[$index] = $column->getLength();
        }

        return response([
            'data' => $data
        ]);
    }

    /**
     * @ClassName 
     */
    public function report()
    {
    }

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $kasgantung = new KasGantungHeader();
        return response([
            'data' => $kasgantung->getExport($id)
        ]);
    }
}
