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
     * @Detail1 PengeluaranDetailController
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
     */
    public function store(StorePengeluaranHeaderRequest $request)
    {

        DB::beginTransaction();

        try {
            $pengeluaranHeader = (new PengeluaranHeader())->processStore($request->all());
            $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
            $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));

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
     */
    public function update(UpdatePengeluaranHeaderRequest $request, PengeluaranHeader $pengeluaranHeader,$id)
    {
        DB::beginTransaction();        
        try {
           /* Store header */
           $pengeluaranHeader = PengeluaranHeader::findOrFail($id);
           $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader,$request->all());
           /* Set position and page */
           $pengeluaranHeader->position = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable())->position;
           $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
           if (isset($request->limit)) {
               $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));
           }

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
     */
    public function destroy(DestroyPengeluaranHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranHeader = (new PengeluaranHeader())->processDestroy($id);
            $selected = $this->getPosition($pengeluaranHeader, $pengeluaranHeader->getTable(), true);
            $pengeluaranHeader->position = $selected->position;
            $pengeluaranHeader->id = $selected->id;
            $pengeluaranHeader->page = ceil($pengeluaranHeader->position / ($request->limit ?? 10));

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
        $cekdata = $pengeluaran->cekvalidasiaksi($pengeluaran->nobukti);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SAP'")
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
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->whereRaw("kodeerror = 'SDC'")
                ->get();
            $keterangan = $query['0'];
            $data = [
                'message' => $keterangan,
                'errors' => 'sudah cetak',
                'kodestatus' => '1',
                'kodenobukti' => '1'
            ];

            return response($data);
        } if ($cekdata['kondisi'] == true) {
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
        $pengeluaranHeader = new PengeluaranHeader();
        $nobukti = PengeluaranHeader::from(DB::raw("pengeluaranheader"))->where('id', $id)->first();
        $cekdata = $pengeluaranHeader->cekvalidasiaksi($nobukti->nobukti);
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
        $pengeluaranHeader = new PengeluaranHeader();
        return response([
            'data' => $pengeluaranHeader->getExport($id)
        ]);
    }
}
