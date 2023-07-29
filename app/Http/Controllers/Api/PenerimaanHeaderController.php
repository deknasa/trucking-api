<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalPenerimaanHeaderRequest;
use App\Http\Requests\DestroyPenerimaanHeaderRequest;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\PenerimaanHeader;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\LogTrail;
use App\Models\AkunPusat;
use App\Models\Bank;
use App\Models\BankPelanggan;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Parameter;
use App\Models\PelunasanPiutangHeader;
use App\Models\PenerimaanDetail;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePenerimaanHeaderRequest;
use App\Models\Error;
use Exception;
use Illuminate\Database\QueryException;
use PhpParser\Builder\Param;

class PenerimaanHeaderController extends Controller
{
    /**
     * @ClassName 
     * PenerimaanHeaderController
     * @Detail1 PenerimaanDetailController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $penerimaan = new PenerimaanHeader();
        return response([
            'data' => $penerimaan->get(),
            'attributes' => [
                'totalRows' => $penerimaan->totalRows,
                'totalPages' => $penerimaan->totalPages
            ]
        ]);
    }


    public function default()
    {


        $penerimaanheader = new PenerimaanHeader();
        return response([
            'status' => true,
            'data' => $penerimaanheader->default(),
        ]);
    }


    /**
     * @ClassName
     */
    public function store(StorePenerimaanHeaderRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'coakredit' => $request->coakredit,
                'keterangan_detail' => $request->keterangan_detail,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'penerimaangiro_nobukti' => $request->penerimaangiro_nobukti,
            ];
            $penerimaanHeader = (new penerimaanHeader())->processStore($data);
            $penerimaanHeader->position = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable())->position;
            if ($request->limit==0) {
                $penerimaanHeader->page = ceil($penerimaanHeader->position / (10));
            } else {
                $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($id)
    {
        $data = PenerimaanHeader::findAll($id);
        $detail = PenerimaanDetail::findAll($id);
        return response([
            'status' => true,
            'data' => $data,
            'detail' => $detail
        ]);
    }


    /**
     * @ClassName
     */

    public function update(UpdatePenerimaanHeaderRequest $request, PenerimaanHeader $penerimaanheader)
    {
        DB::beginTransaction();
        try {
            $data = [
                'tglbukti' => $request->tglbukti,
                'pelanggan_id' => $request->pelanggan_id,
                'agen_id' => $request->agen_id,
                'diterimadari' => $request->diterimadari,
                'tgllunas' => $request->tgllunas,
                'bank_id' => $request->bank_id,
                'nowarkat' => $request->nowarkat,
                'tgljatuhtempo' => $request->tgljatuhtempo,
                'nominal_detail' => $request->nominal_detail,
                'coakredit' => $request->coakredit,
                'keterangan_detail' => $request->keterangan_detail,
                'bankpelanggan_id' => $request->bankpelanggan_id,
                'penerimaangiro_nobukti' => $request->penerimaangiro_nobukti,
            ];
            /* Store header */
            $penerimaanheader = (new PenerimaanHeader())->processUpdate($penerimaanheader, $data);
            /* Set position and page */
            $penerimaanheader->position = $this->getPosition($penerimaanheader, $penerimaanheader->getTable())->position;
            if ($request->limit==0) {
                $penerimaanheader->page = ceil($penerimaanheader->position / (10));
            } else {
                $penerimaanheader->page = ceil($penerimaanheader->position / ($request->limit ?? 10));
            }

            DB::commit();
            return response()->json([
                'message' => 'Berhasil disimpan',
                'data' => $penerimaanheader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function destroy(DestroyPenerimaanHeaderRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = (new PenerimaanHeader())->processDestroy($id);
            $selected = $this->getPosition($penerimaanHeader, $penerimaanHeader->getTable(), true);
            $penerimaanHeader->position = $selected->position;
            $penerimaanHeader->id = $selected->id;
            if ($request->limit==0) {
                $penerimaanHeader->page = ceil($penerimaanHeader->position / (10));
            } else {
                $penerimaanHeader->page = ceil($penerimaanHeader->position / ($request->limit ?? 10));
            }
            DB::commit();

            return response()->json([
                'message' => 'Berhasil dihapus',
                'data' => $penerimaanHeader
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * @ClassName
     */
    public function approval(ApprovalPenerimaanHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
            $statusNonApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

            for ($i = 0; $i < count($request->penerimaanId); $i++) {
                $penerimaanHeader = PenerimaanHeader::find($request->penerimaanId[$i]);

                if ($penerimaanHeader->statusapproval == $statusApproval->id) {
                    $penerimaanHeader->statusapproval = $statusNonApproval->id;
                    $aksi = $statusNonApproval->text;
                } else {
                    $penerimaanHeader->statusapproval = $statusApproval->id;
                    $aksi = $statusApproval->text;
                }

                $penerimaanHeader->tglapproval = date('Y-m-d', time());
                $penerimaanHeader->userapproval = auth('api')->user()->name;

                if ($penerimaanHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanHeader->getTable()),
                        'postingdari' => 'APPROVAL PENERIMAAN KAS/BANK',
                        'idtrans' => $penerimaanHeader->id,
                        'nobuktitrans' => $penerimaanHeader->nobukti,
                        'aksi' => $aksi,
                        'datajson' => $penerimaanHeader->toArray(),
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

    public function bukaCetak($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = PenerimaanHeader::find($id);
            $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
                ->where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanHeader->statuscetak == $statusCetak->id) {
                $penerimaanHeader->statuscetak = $statusBelumCetak->id;
            } else {
                $penerimaanHeader->statuscetak = $statusCetak->id;
            }

            $penerimaanHeader->tglbukacetak = date('Y-m-d', time());
            $penerimaanHeader->userbukacetak = auth('api')->user()->name;

            if ($penerimaanHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($penerimaanHeader->getTable()),
                    'postingdari' => 'BUKA/BELUM CETAK PENERIMAANHEADER',
                    'idtrans' => $penerimaanHeader->id,
                    'nobuktitrans' => $penerimaanHeader->id,
                    'aksi' => 'BUKA/BELUM CETAK',
                    'datajson' => $penerimaanHeader->toArray(),
                    'modifiedby' => $penerimaanHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                DB::commit();
            }

            return response([
                'message' => 'Berhasil'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    public function tarikPelunasan($id)
    {
        $penerimaan = new PenerimaanHeader();
        // ($id!='') ? $tarik = $penerimaan->tarikPelunasan($id) : $tarik = $penerimaan->tarikPelunasan();
        return response([
            'data' => $penerimaan->tarikPelunasan($id),
        ]);
    }
    public function getPelunasan($id, $table)
    {
        $get = new PenerimaanHeader();
        return response([
            'data' => $get->getPelunasan($id, $table),
        ]);
    }


    public function cekvalidasi($id)
    {
        $pengeluaran = PenerimaanHeader::find($id);
        $status = $pengeluaran->statusapproval;
        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusdatacetak = $pengeluaran->statuscetak;
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        if ($status == $statusApproval->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SAP')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
                'statuspesan' => 'warning',
            ];

            return response($data);
        } else if ($statusdatacetak == $statusCetak->id) {
            $query = Error::from(DB::raw("error with (readuncommitted)"))
                ->select('keterangan')
                ->where('kodeerror', '=', 'SDC')
                ->first();
            $data = [
                'error' => true,
                'message' => $query->keterangan,
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


    public function cekValidasiAksi($id)
    {
        $penerimaanHeader = new PenerimaanHeader();
        $nobukti = PenerimaanHeader::from(DB::raw("penerimaanheader"))->where('id', $id)->first();
        $cekdata = $penerimaanHeader->cekvalidasiaksi($nobukti->nobukti);
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

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $penerimaanHeader = PenerimaanHeader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($penerimaanHeader->statuscetak != $statusSudahCetak->id) {
                $penerimaanHeader->statuscetak = $statusSudahCetak->id;
                $penerimaanHeader->tglbukacetak = date('Y-m-d H:i:s');
                $penerimaanHeader->userbukacetak = auth('api')->user()->name;
                $penerimaanHeader->jumlahcetak = $penerimaanHeader->jumlahcetak + 1;
                if ($penerimaanHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($penerimaanHeader->getTable()),
                        'postingdari' => 'PRINT PENERIMAAN HEADER',
                        'idtrans' => $penerimaanHeader->id,
                        'nobuktitrans' => $penerimaanHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $penerimaanHeader->toArray(),
                        'modifiedby' => $penerimaanHeader->modifiedby
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
     */
    public function report()
    {}

    /**
     * @ClassName 
     */
    public function export($id)
    {
        $penerimaanHeader = new PenerimaanHeader();
        return response([
            'data' => $penerimaanHeader->getExport($id)
        ]);
    }
}
