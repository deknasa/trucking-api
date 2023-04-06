<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurnalUmumDetailRequest;
use App\Http\Requests\StoreJurnalUmumHeaderRequest;
use App\Http\Requests\StoreLogTrailRequest;
use App\Models\PindahBuku;
use App\Http\Requests\StorePindahBukuRequest;
use App\Http\Requests\UpdateJurnalUmumHeaderRequest;
use App\Http\Requests\UpdatePindahBukuRequest;
use App\Models\Bank;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PindahBukuController extends Controller
{

    /**
     * @ClassName
     */
    public function index()
    {
        $pindahBuku = new PindahBuku();

        return response([
            'data' => $pindahBuku->get(),
            'attributes' => [
                'totalRows' => $pindahBuku->totalRows,
                'totalPages' => $pindahBuku->totalPages,
            ]
        ]);
    }


    public function default()
    {
        $pindahBuku = new PindahBuku();
        return response([
            'status' => true,
            'data' => $pindahBuku->default(),
        ]);
    }

    /**
     * @ClassName
     */
    public function store(StorePindahBukuRequest $request)
    {
        DB::beginTransaction();
        try {

            $pindahBuku = new PindahBuku();

            $group = 'PINDAH BUKU';
            $subgroup = 'NOMOR PINDAH BUKU';

            $format = DB::table('parameter')
                ->where('grp', $group)
                ->where('subgrp', $subgroup)
                ->first();

            $content = new Request();
            $content['group'] = $group;
            $content['subgroup'] = $subgroup;
            $content['table'] = 'pindahbuku';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $getCoadebet = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $request->bankdari_id)->first();
            $getCoakredit = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $request->bankke_id)->first();

            $pindahBuku->nobukti = $nobukti;
            $pindahBuku->tglbukti = date('Y-m-d', strtotime($request->tglbukti));
            $pindahBuku->bankdari_id = $request->bankdari_id;
            $pindahBuku->bankke_id = $request->bankke_id;
            $pindahBuku->coadebet = $getCoadebet->coa;
            $pindahBuku->coakredit = $getCoakredit->coa;
            $pindahBuku->alatbayar_id = $request->alatbayar_id;
            $pindahBuku->nowarkat = $request->nowarkat ?? '';
            $pindahBuku->tgljatuhtempo = date('Y-m-d', strtotime($request->tgljatuhtempo));
            $pindahBuku->nominal = $request->nominal;
            $pindahBuku->keterangan = $request->keterangan;
            $pindahBuku->statusformat = $format->id;
            $pindahBuku->modifiedby = auth('api')->user()->name;

            $pindahBuku->save();

            $logTrail = [
                'namatabel' => strtoupper($pindahBuku->getTable()),
                'postingdari' => 'ENTRY PINDAH BUKU',
                'idtrans' => $pindahBuku->id,
                'nobuktitrans' => $pindahBuku->nobukti,
                'aksi' => 'ENTRY',
                'datajson' => $pindahBuku->toArray(),
                'modifiedby' => $pindahBuku->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);


            $jurnalHeader = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $pindahBuku->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($pindahBuku->tglbukti)),
                'postingdari' => "ENTRY PINDAH BUKU",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
            ];

            $jurnaldetail = [
                [
                    'nobukti' => $pindahBuku->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($pindahBuku->tglbukti)),
                    'coa' =>  $getCoadebet->coa,
                    'nominal' => $request->nominal,
                    'keterangan' => $request->keterangan,
                    'modifiedby' => auth('api')->user()->name,
                    'baris' => 0,
                ],
                [
                    'nobukti' => $pindahBuku->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($pindahBuku->tglbukti)),
                    'coa' => $getCoakredit->coa,
                    'nominal' => '-' . $request->nominal,
                    'keterangan' => $request->keterangan,
                    'modifiedby' => auth('api')->user()->name,
                    'baris' => 0,
                ]
            ];

            $jurnal = $this->storeJurnal($jurnalHeader, $jurnaldetail);

            if (!$jurnal['status']) {
                throw new \Throwable($jurnal['message']);
            }

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';
            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pindahBuku, $pindahBuku->getTable());
            $pindahBuku->position = $selected->position;
            $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pindahBuku
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function show($id)
    {
        $pindahBuku = new PindahBuku();
        return response([
            'data' => $pindahBuku->findAll($id)
        ]);
    }

    /**
     * @ClassName
     */
    public function update(UpdatePindahBukuRequest $request, PindahBuku $pindahbuku)
    {
        DB::beginTransaction();
        try {
            $getCoadebet = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $request->bankdari_id)->first();
            $getCoakredit = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $request->bankke_id)->first();

            $pindahbuku->bankdari_id = $request->bankdari_id;
            $pindahbuku->bankke_id = $request->bankke_id;
            $pindahbuku->coadebet = $getCoadebet->coa;
            $pindahbuku->coakredit = $getCoakredit->coa;
            $pindahbuku->alatbayar_id = $request->alatbayar_id;
            $pindahbuku->nowarkat = $request->nowarkat ?? '';
            $pindahbuku->tgljatuhtempo = date('Y-m-d', strtotime($request->tgljatuhtempo));
            $pindahbuku->nominal = $request->nominal;
            $pindahbuku->keterangan = $request->keterangan;
            $pindahbuku->modifiedby = auth('api')->user()->name;

            $pindahbuku->save();
            $logTrail = [
                'namatabel' => strtoupper($pindahbuku->getTable()),
                'postingdari' => 'EDIT PINDAH BUKU',
                'idtrans' => $pindahbuku->id,
                'nobuktitrans' => $pindahbuku->nobukti,
                'aksi' => 'EDIT',
                'datajson' => $pindahbuku->toArray(),
                'modifiedby' => $pindahbuku->modifiedby
            ];
            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            $jurnalDetail =  [
                [
                    'nobukti' => $pindahbuku->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($pindahbuku->tglbukti)),
                    'coa' =>  $getCoadebet->coa,
                    'nominal' => $request->nominal,
                    'keterangan' => $request->keterangan,
                    'modifiedby' => auth('api')->user()->name,
                    'baris' => 0,
                ],
                [
                    'nobukti' => $pindahbuku->nobukti,
                    'tglbukti' => date('Y-m-d', strtotime($pindahbuku->tglbukti)),
                    'coa' => $getCoakredit->coa,
                    'nominal' => '-' . $request->nominal,
                    'keterangan' => $request->keterangan,
                    'modifiedby' => auth('api')->user()->name,
                    'baris' => 0,
                ]
                ];

            $jurnalHeader = [
                'isUpdate' => 1,
                'postingdari' => "EDIT PINDAH BUKU",
                'modifiedby' => auth('api')->user()->name,
                'datadetail' => $jurnalDetail
            ];

            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $pindahbuku->nobukti)->first();
            $newJurnal = new JurnalUmumHeader();
            $newJurnal = $newJurnal->find($getJurnal->id);
            $jurnal = new UpdateJurnalUmumHeaderRequest($jurnalHeader);
            app(JurnalUmumHeaderController::class)->update($jurnal, $newJurnal);

            $request->sortname = $request->sortname ?? 'id';
            $request->sortorder = $request->sortorder ?? 'asc';

            DB::commit();

            /* Set position and page */
            $selected = $this->getPosition($pindahbuku, $pindahbuku->getTable());
            $pindahbuku->position = $selected->position;
            $pindahbuku->page = ceil($pindahbuku->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil disimpan',
                'data' => $pindahbuku
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @ClassName
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();


        $pindahBuku = new PindahBuku();
        $pindahBuku = $pindahBuku->lockAndDestroy($id);
        $getJurnalHeader = JurnalUmumHeader::lockForUpdate()->where('nobukti', $pindahBuku->nobukti)->first();

        $getJurnalDetail = JurnalUmumDetail::lockForUpdate()->where('nobukti', $pindahBuku->nobukti)->get();
        JurnalUmumHeader::where('nobukti', $pindahBuku->nobukti)->delete();

        if ($pindahBuku) {
            // DELETE PINDAH BUKU
            $logTrail = [
                'namatabel' => strtoupper($pindahBuku->getTable()),
                'postingdari' => 'DELETE PINDAH BUKU',
                'idtrans' => $pindahBuku->id,
                'nobuktitrans' => $pindahBuku->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pindahBuku->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE JURNAL HEADER
            $logTrailJurnalHeader = [
                'namatabel' => 'JURNALUMUMHEADER',
                'postingdari' => 'DELETE PINDAH BUKU',
                'idtrans' => $getJurnalHeader->id,
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalHeader->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalHeader = new StoreLogTrailRequest($logTrailJurnalHeader);
            $storedLogTrailJurnal = app(LogTrailController::class)->store($validatedLogTrailJurnalHeader);


            // DELETE JURNAL DETAIL
            $logTrailJurnalDetail = [
                'namatabel' => 'JURNALUMUMDETAIL',
                'postingdari' => 'DELETE PINDAH BUKU',
                'idtrans' => $storedLogTrailJurnal['id'],
                'nobuktitrans' => $getJurnalHeader->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getJurnalDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailJurnalDetail = new StoreLogTrailRequest($logTrailJurnalDetail);
            app(LogTrailController::class)->store($validatedLogTrailJurnalDetail);

            DB::commit();

            $selected = $this->getPosition($pindahBuku, $pindahBuku->getTable(), true);
            $pindahBuku->position = $selected->position;
            $pindahBuku->id = $selected->id;
            $pindahBuku->page = ceil($pindahBuku->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pindahBuku
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
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
                'postingdari' => $header['postingdari'],
                'idtrans' =>  $jurnals->original['idlogtrail'],
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

            throw $th;
        }
    }
}
