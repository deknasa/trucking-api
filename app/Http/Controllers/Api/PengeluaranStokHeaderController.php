<?php

namespace App\Http\Controllers\Api;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\PengeluaranStok;
use App\Models\PengeluaranStokHeader;
use App\Models\PengeluaranStokDetail;
use App\Models\PenerimaanStokDetail;
use App\Models\PengeluaranStokDetailFifo;
use App\Models\StokPersediaan;
use App\Models\Stok;
use App\Models\Bank;
use App\Models\Error;

use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreLogTrailRequest;
use App\Http\Requests\StorePengeluaranStokHeaderRequest;
use App\Http\Requests\UpdatePengeluaranStokHeaderRequest;
use App\Http\Requests\StorePengeluaranStokDetailRequest;
use App\Http\Requests\StorePengeluaranStokDetailFifoRequest;
use App\Http\Requests\StorePenerimaanHeaderRequest;
use App\Http\Requests\StorePenerimaanDetailRequest;
use App\Models\PenerimaanHeader;
use App\Models\PenerimaanDetail;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;


class PengeluaranStokHeaderController extends Controller
{
    /**
     * @ClassName 
     */
    public function index()
    {
        $pengeluaranStokHeader = new PengeluaranStokHeader();
        return response([
            'data' => $pengeluaranStokHeader->get(),
            'attributes' => [
                'totalRows' => $pengeluaranStokHeader->totalRows,
                'totalPages' => $pengeluaranStokHeader->totalPages
            ]
        ]);
    }
    /**
     * @ClassName 
     */
    public function store(StorePengeluaranStokHeaderRequest $request)
    {
        DB::beginTransaction();

        try {

            $idpenerimaan = $request->pengeluaranstok_id;
            $fetchFormat =  Pengeluaranstok::where('id', $idpenerimaan)->first();
            // dd($fetchFormat);
            $statusformat = $fetchFormat->format;

            $fetchGrp = Parameter::where('id', $statusformat)->first();
            // return response([$fetchFormat],422);
            // die();
            $format = Parameter::where('grp', $fetchGrp->grp)->where('subgrp', $fetchGrp->subgrp)->first();

            $content = new Request();
            $content['group'] = $fetchGrp->grp;
            $content['subgroup'] = $fetchGrp->subgrp;
            $content['table'] = 'pengeluaranstokheader';
            $content['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

            $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();

            if ($request->pengeluaranstok_id == $spk->text) {
                $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();
                $request->gudang_id = $gudangkantor->text;
            }

            /* Store header */
            $pengeluaranStokHeader = new PengeluaranStokHeader();
            $pengeluaranStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranStokHeader->pengeluaranstok_id = ($request->pengeluaranstok_id == null) ? "" : $request->pengeluaranstok_id;
            $pengeluaranStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $pengeluaranStokHeader->gandengan_id          = ($request->gandengan_id == null) ? "" : $request->gandengan_id;
            $pengeluaranStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $pengeluaranStokHeader->supir_id         = ($request->supir_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $pengeluaranStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $pengeluaranStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $pengeluaranStokHeader->servicein_nobukti    = ($request->servicein_nobukti == null) ? "" : $request->servicein_nobukti;
            $pengeluaranStokHeader->kerusakan_id         = ($request->kerusakan_id == null) ? "" : $request->kerusakan_id;
            $pengeluaranStokHeader->statusformat      = ($statusformat == null) ? "" : $statusformat;
            $pengeluaranStokHeader->statuspotongretur      = ($request->statuspotongretur == null) ? "" : $request->statuspotongretur;
            $pengeluaranStokHeader->bank_id      = ($request->bank_id == null) ? "" : $request->bank_id;
            $pengeluaranStokHeader->tglkasmasuk      = date('Y-m-d', strtotime($request->tglkasmasuk));
            $pengeluaranStokHeader->modifiedby        = auth('api')->user()->name;
            $pengeluaranStokHeader->statuscetak        = $statusCetak->id ?? 0;
            $request->sortname                 = $request->sortname ?? 'id';
            $request->sortorder                = $request->sortorder ?? 'asc';
            TOP:
            $nobukti = app(Controller::class)->getRunningNumber($content)->original['data'];
            $pengeluaranStokHeader->nobukti = $nobukti;
            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'ENTRY PENGELUARAN STOK HEADER',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => 'ENTRY',
                    'datajson' => $pengeluaranStokHeader->toArray(),
                    'modifiedby' => $pengeluaranStokHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                if ($request->detail_harga) {

                    /* Store detail */
                    $detaillog = [];

                    for ($i = 0; $i < count($request->detail_harga); $i++) {
                        $datadetail = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "qty" => $request->detail_qty[$i],
                            "harga" => $request->detail_harga[$i],
                            "persentasediscount" => $request->detail_persentasediscount[$i],
                            "vulkanisirke" => $request->detail_vulkanisirke[$i],
                            "detail_keterangan" => $request->detail_keterangan[$i],
                            "trado_id" => ($request->trado_id == null) ? "" : $request->trado_id,
                            "gandengan_id" => ($request->gandengan_id == null) ? "" : $request->gandengan_id,
                            "gudang_id" => ($request->gudang_id == null) ? "" : $request->gudang_id,
                        ];

                        $data = new StorePengeluaranStokDetailRequest($datadetail);
                        $pengeluaranStokDetail = app(PengeluaranStokDetailController::class)->store($data);

                        if ($pengeluaranStokDetail['error']) {
                            return response($pengeluaranStokDetail, 422);
                        } else {
                            $iddetail = $pengeluaranStokDetail['id'];
                            $tabeldetail = $pengeluaranStokDetail['tabel'];
                        }
                        $detaillog[] = $pengeluaranStokDetail['detail']->toArray();


                        $datadetailfifo = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "pengeluaranstok_id" => $request->pengeluaranstok_id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "gudang_id" => $request->gudang_id,
                            "tglbukti" => $request->tglbukti,
                            "qty" => $request->detail_qty[$i],
                            "modifiedby" => auth('api')->user()->name,
                            "keterangan" => $request->keterangan ?? '',
                            "detail_keterangan" => $request->detail_keterangan[$i] ?? '',
                            "statusformat" => $request->statusformat_id,
                        ];

                        $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                        $pengeluaranStokDetailFifo = app(PengeluaranStokDetailFifoController::class)->store($datafifo);
                        // return response([$pengeluaranStokDetailFifo], 422);

                        if ($pengeluaranStokDetailFifo['error']) {
                            return response($pengeluaranStokDetailFifo, 422);
                        }
                        // dd('test');
                    }
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY PENGELUARAN STOK DETAIL',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $pengeluaranStokHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);
                }

                

                $rbt = Parameter::where('grp', 'PENGELUARAN STOK')->where('subgrp', 'RETUR BELI BUKTI')->first();
                $pengeluaranstok = PengeluaranStok::where('id',$request->pengeluaranstok_id)->first();
                $statusformat = Parameter::where('id', $pengeluaranstok->format)->first();

                if ($statusformat->id == $rbt->id) {

                    $potongKas = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
                    $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();
                    
                    if ($pengeluaranStokHeader->statuspotongretur == $potongKas->id) {

                        $statusApproval = DB::table('parameter')
                            ->where('grp', 'STATUS APPROVAL')
                            ->where('text', 'NON APPROVAL')
                            ->first();

                        $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL RETUR STOK')->where('subgrp', 'KREDIT')->first();
                        // return response([$detaillog[0]['harga']],422);
                        $memo = json_decode($coaKasMasuk->memo, true);
                        $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengeluaranStokHeader->bank_id)->first();
                        $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
                        if ($bank->tipe == 'KAS') {
                            $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
                        }
                        if ($bank->tipe == 'BANK') {
                            $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN STATUS KAS')->first();
                        }
                        $bankid = $request->bank_id;
                        $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                            ->select(
                                'parameter.grp',
                                'parameter.subgrp',
                                'bank.formatpenerimaan',
                                'bank.coa'
                            )
                            ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                            ->whereRaw("bank.id = $bankid")
                            ->first();
                        $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
                        $group = $parameter->grp;
                        $subgroup = $parameter->subgrp;
                        $format = DB::table('parameter')
                            ->where('grp', $group)
                            ->where('subgrp', $subgroup)
                            ->first();

                        $penerimaanRequest = new Request();
                        $penerimaanRequest['group'] = $group;
                        $penerimaanRequest['subgroup'] = $subgroup;
                        $penerimaanRequest['table'] = 'penerimaanheader';
                        $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                        $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];
                        $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;

                        $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

                        $penerimaanHeader = [
                            'tanpagetposition' => 1,
                            'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                            'pelanggan_id' => "0",
                            'bank_id' => $pengeluaranStokHeader->bank_id,
                            'postingdari' => 'PENGELUARAN STOK HEADER',
                            'diterimadari' => 'PENGELUARAN STOK HEADER',
                            'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                            'cabang_id' => '',
                            'statusKas' => $statusKas->id,
                            'statusapproval' => $statusApproval->id,
                            'userapproval' => '',
                            'tglapproval' => '',
                            'noresi' => '',
                            'statuserkas' => $statusBerkas->id,
                            'statusformat' => $format->id,
                            'modifiedby' => auth('api')->user()->name
                        ];

                        $penerimaanDetail = [];

                        for ($i = 0; $i < count($request->detail_stok); $i++) {

                            $penerimaanHeader['entriluar'][] = 0;
                            $penerimaanHeader['nobukti'][] = $nobuktiPenerimaan;
                            $penerimaanHeader['nowarkat'][] = '';
                            $penerimaanHeader['tgljatuhtempo'][] = date('Y-m-d', strtotime($request->tglbukti));
                            $penerimaanHeader['coadebet'][] = $bank->coa;
                            $penerimaanHeader['coakredit'][] = $memo['JURNAL'];
                            $penerimaanHeader['keterangan_detail'][] = $request->detail_keterangan[$i];
                            $penerimaanHeader["nominal_detail"][] = $detaillog[$i]['harga'];
                            $penerimaanHeader['invoice_nobukti'][] = '';
                            $penerimaanHeader['bankpelanggan_id'][] = 0;
                            $penerimaanHeader['jenisbiaya'][] = '';
                            $penerimaanHeader['pelunasanpiutang_nobukti'][] = '';
                            $penerimaanHeader['bulanbeban'][] = date('Y-m-d', strtotime($request->tglbukti));
                        }
                        $penerimaan = $this->storePenerimaan($penerimaanHeader);
                        // return response($penerimaan,422);
                        $pengeluaranStokHeader->penerimaan_nobukti = $penerimaan['data']['nobukti'];
                        $pengeluaranStokHeader->save();
                    } else if ($pengeluaranStokHeader->statuspotongretur == $potongHutang->id) {
                        $hutangHeader = [
                            "tglbukti" => $request->tglbukti,
                            "bank_id" => $request->bank_id,
                            "supplier_id" => $request->supplier_id,
                            "alatbayar_id" => 0,
                            "tglcair" => $request->tglcair,
                        ];
                        // return response([$potongHutang],422);
                        // for ($i = 0; $i < count($request->detail_stok); $i++) {
                        //     $hutangHeader['keterangandetail'][] = $request->detail_keterangan[$i];
                        //     $hutangHeader['bayar'][] = $request->bayar[$i];
                        //     $hutangHeader['hutang_id'][] = $request->hutang_id[$i];
                        //     $hutangHeader['total'][] = $request->total[$i];
                        //     $hutangHeader['potongan'][] = $request->potongan[$i];
                        // }
                    }
                }

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable());
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    public function show(PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {
        return response([
            'status' => true,
            'data' => $pengeluaranStokHeader->find($id),
            'detail' => PengeluaranStokDetail::getAll($id),
        ]);
    }
    /**
     * @ClassName 
     */
    public function update(UpdatePengeluaranStokHeaderRequest $request, PengeluaranStokHeader $pengeluaranStokHeader, $id)
    {



        DB::beginTransaction();

        try {


            $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $id)->first();

            $idpenerimaan = $request->pengeluaranstok_id;
            $fetchFormat =  Pengeluaranstok::where('id', $idpenerimaan)->first();
            // dd($fetchFormat);
            $statusformat = $fetchFormat->format;

            $fetchGrp = Parameter::where('id', $statusformat)->first();


            $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
            if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {

                $querypengeluaranstokdetail = PengeluaranStokDetail::from(
                    "pengeluaranstokdetail as i"
                )
                    ->select(
                        'i.stok_id',

                    )
                    ->where('i.pengeluaranstokheader_id', '=', $id)
                    ->orderBy('i.id', 'Asc')
                    ->get();


                $datastokdetail = json_decode($querypengeluaranstokdetail, true);
                foreach ($datastokdetail as $item) {

                    $reset = $this->resethpp($pengeluaranStokHeader->id, $item['stok_id'], false);


                    if (!$reset['status']) {
                        throw new \Throwable($reset['message']);
                    }
                }
            }
            // dd('test');

            /* Store header */
            $pengeluaranStokHeader = PengeluaranStokHeader::lockForUpdate()->findOrFail($id);

            $pengeluaranStokHeader->tglbukti          = date('Y-m-d', strtotime($request->tglbukti));
            $pengeluaranStokHeader->pengeluaranstok_id = ($request->pengeluaranstok_id == null) ? "" : $request->pengeluaranstok_id;
            $pengeluaranStokHeader->trado_id          = ($request->trado_id == null) ? "" : $request->trado_id;
            $pengeluaranStokHeader->gandengan_id          = ($request->gandengan_id == null) ? "" : $request->gandengan_id;
            $pengeluaranStokHeader->gudang_id         = ($request->gudang_id == null) ? "" : $request->gudang_id;
            $pengeluaranStokHeader->supir_id         = ($request->supir_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->supplier_id         = ($request->supplier_id == null) ? "" : $request->supplier_id;
            $pengeluaranStokHeader->pengeluaranstok_nobukti = ($request->pengeluaranstok_nobukti == null) ? "" : $request->pengeluaranstok_nobukti;
            $pengeluaranStokHeader->penerimaanstok_nobukti  = ($request->penerimaanstok_nobukti == null) ? "" : $request->penerimaanstok_nobukti;
            $pengeluaranStokHeader->servicein_nobukti    = ($request->servicein_nobukti == null) ? "" : $request->servicein_nobukti;
            $pengeluaranStokHeader->kerusakan_id         = ($request->kerusakan_id == null) ? "" : $request->supir_id;
            $pengeluaranStokHeader->statusformat      = ($statusformat == null) ? "" : $statusformat;
            $pengeluaranStokHeader->statuspotongretur      = ($request->statuspotongretur == null) ? "" : $request->statuspotongretur;
            $pengeluaranStokHeader->bank_id      = ($request->bank_id == null) ? "" : $request->bank_id;
            $pengeluaranStokHeader->tglkasmasuk      = date('Y-m-d', strtotime($request->tglkasmasuk));

            $pengeluaranStokHeader->modifiedby        = auth('api')->user()->name;
            $request->sortname                 = $request->sortname ?? 'id';
            $request->sortorder                = $request->sortorder ?? 'asc';
            if ($pengeluaranStokHeader->save()) {
                $logTrail = [
                    'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                    'postingdari' => 'EDIT PENGELUARAN STOK HEADER',
                    'idtrans' => $pengeluaranStokHeader->id,
                    'nobuktitrans' => $pengeluaranStokHeader->id,
                    'aksi' => 'EDIT',
                    'datajson' => $pengeluaranStokHeader->toArray(),
                    'modifiedby' => $pengeluaranStokHeader->modifiedby
                ];

                $validatedLogTrail = new StoreLogTrailRequest($logTrail);
                $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

                if ($request->detail_harga) {


                    /*Update  di stok persediaan*/

                    $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')
                        ->where('text', '=', $request->pengeluaranstok_id)
                        ->first();

                    if (isset($spk)) {
                        goto stokpersediaan;
                    }

                    $spk = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')
                        ->where('text', '=', $request->pengeluaranstok_id)
                        ->first();

                    if (isset($spk)) {
                        goto stokpersediaan;
                    }

                    $spk = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')
                        ->where('text', '=', $request->pengeluaranstok_id)
                        ->first();

                    if (isset($spk)) {
                        goto stokpersediaan;
                    }

                    stokpersediaan:;
                    if ($request->pengeluaranstok_id == $spk->text) {

                        $datadetail = PengeluaranStokDetail::select('stok_id', 'qty')
                            ->where('pengeluaranstokheader_id', '=', $id)
                            ->get();

                        $datadetail = json_decode($datadetail, true);

                        foreach ($datadetail as $item) {
                            $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $item['stok_id'])
                                ->where("gudang_id", ($request->gudang_id))->firstorFail();
                            $stokpersediaan->qty += $item['qty'];
                            $stokpersediaan->save();
                        }
                    }


                    /* Delete existing detail */
                    $pengeluaranStokDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->lockForUpdate()->delete();
                    /* Store detail */
                    $detaillog = [];

                    for ($i = 0; $i < count($request->detail_harga); $i++) {
                        $datadetail = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "qty" => $request->detail_qty[$i],
                            "harga" => $request->detail_harga[$i],
                            "persentasediscount" => $request->detail_persentasediscount[$i],
                            "vulkanisirke" => $request->detail_vulkanisirke[$i],
                            "detail_keterangan" => $request->detail_keterangan[$i],
                            "trado_id" => ($request->trado_id == null) ? "" : $request->trado_id,
                            "gandengan_id" => ($request->gandengan_id == null) ? "" : $request->gandengan_id,
                            "gudang_id" => ($request->gudang_id == null) ? "" : $request->gudang_id,
                        ];

                        $data = new StorePengeluaranStokDetailRequest($datadetail);
                        $pengeluaranStokDetail = app(PengeluaranStokDetailController::class)->store($data);

                        if ($pengeluaranStokDetail['error']) {
                            return response($pengeluaranStokDetail, 422);
                        } else {
                            $iddetail = $pengeluaranStokDetail['id'];
                            $tabeldetail = $pengeluaranStokDetail['tabel'];
                        }
                        $detaillog[] = $pengeluaranStokDetail['detail']->toArray();

                        $datadetailfifo = [
                            "pengeluaranstokheader_id" => $pengeluaranStokHeader->id,
                            "nobukti" => $pengeluaranStokHeader->nobukti,
                            "stok_id" => $request->detail_stok_id[$i],
                            "gudang_id" => $request->gudang_id,
                            "tglbukti" => $request->tglbukti,
                            "qty" => $request->detail_qty[$i],
                            "modifiedby" => auth('api')->user()->name,
                        ];

                        $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                        $pengeluaranStokDetailFifo = app(PengeluaranStokDetailFifoController::class)->store($datafifo);
                        if ($pengeluaranStokDetailFifo['error']) {
                            return response($pengeluaranStokDetailFifo, 422);
                        }
                        
                        $reset = $this->resethppedit($pengeluaranStokHeader->id, $request->detail_stok_id[$i]);
                        // return response($reset['status'], 422);


                        if (!$reset['status']) {
                            throw new \Throwable($reset['message']);
                        }
                    }
                    $datalogtrail = [
                        'namatabel' => strtoupper($tabeldetail),
                        'postingdari' => 'ENTRY PENGELUARAN STOK HEADER',
                        'idtrans' =>  $storedLogTrail['id'],
                        'nobuktitrans' => $pengeluaranStokHeader->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $detaillog,
                        'modifiedby' => auth('api')->user()->name,
                    ];

                    $data = new StoreLogTrailRequest($datalogtrail);
                    app(LogTrailController::class)->store($data);

                    /* Delete existing Jurnal */
                    PenerimaanDetail::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->delete();
                    PenerimaanHeader::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->delete();
                    JurnalUmumDetail::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->delete();
                    JurnalUmumHeader::where('nobukti', $pengeluaranStokHeader->penerimaan_nobukti)->delete();

                     $rbt = Parameter::where('grp', 'PENGELUARAN STOK')->where('subgrp', 'RETUR BELI BUKTI')->first();
                     $pengeluaranstok = PengeluaranStok::where('id',$request->pengeluaranstok_id)->first();
                     $statusformat = Parameter::where('id', $pengeluaranstok->format)->first();
                    //  return response($statusformat,422);
                if ($statusformat->id == $rbt->id) {

                        $potongKas = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POSTING KE KAS/BANK')->first();
                        $potongHutang = Parameter::where('grp', 'STATUS POTONG RETUR')->where('text', 'POTONG HUTANG')->first();

                        if ($pengeluaranStokHeader->statuspotongretur == $potongKas->id) {

                            $statusApproval = DB::table('parameter')
                                ->where('grp', 'STATUS APPROVAL')
                                ->where('text', 'NON APPROVAL')
                                ->first();

                            $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL RETUR STOK')->where('subgrp', 'KREDIT')->first();
                            // return response([$detaillog[0]['harga']],422);
                            $memo = json_decode($coaKasMasuk->memo, true);
                            $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengeluaranStokHeader->bank_id)->first();
                            $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
                            if ($bank->tipe == 'KAS') {
                                $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'KAS')->first();
                            }
                            if ($bank->tipe == 'BANK') {
                                $statusKas = Parameter::where('grp', 'STATUS KAS')->where('text', 'BUKAN STATUS KAS')->first();
                            }
                            $bankid = $request->bank_id;
                            $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))
                                ->select(
                                    'parameter.grp',
                                    'parameter.subgrp',
                                    'bank.formatpenerimaan',
                                    'bank.coa'
                                )
                                ->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')
                                ->whereRaw("bank.id = $bankid")
                                ->first();
                            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;
                            $group = $parameter->grp;
                            $subgroup = $parameter->subgrp;
                            $format = DB::table('parameter')
                                ->where('grp', $group)
                                ->where('subgrp', $subgroup)
                                ->first();

                            $penerimaanRequest = new Request();
                            $penerimaanRequest['group'] = $group;
                            $penerimaanRequest['subgroup'] = $subgroup;
                            $penerimaanRequest['table'] = 'penerimaanheader';
                            $penerimaanRequest['tgl'] = date('Y-m-d', strtotime($request->tglbukti));

                            $nobuktiPenerimaan = app(Controller::class)->getRunningNumber($penerimaanRequest)->original['data'];
                            $pengeluaranStokHeader->coa = $querysubgrppenerimaan->coa;

                            $statusBerkas = Parameter::where('grp', 'STATUS BERKAS')->where('text', 'TIDAK ADA BERKAS')->first();

                            $penerimaanHeader = [
                                'tanpagetposition' => 1,
                                'tglbukti' => date('Y-m-d', strtotime($request->tglbukti)),
                                'pelanggan_id' => "0",
                                'bank_id' => $pengeluaranStokHeader->bank_id,
                                'postingdari' => 'PENGELUARAN STOK HEADER',
                                'diterimadari' => 'PENGELUARAN STOK HEADER',
                                'tgllunas' => date('Y-m-d', strtotime($request->tglbukti)),
                                'cabang_id' => '',
                                'statusKas' => $statusKas->id,
                                'statusapproval' => $statusApproval->id,
                                'userapproval' => '',
                                'tglapproval' => '',
                                'noresi' => '',
                                'statuserkas' => $statusBerkas->id,
                                'statusformat' => $format->id,
                                'modifiedby' => auth('api')->user()->name
                            ];

                            $penerimaanDetail = [];

                            for ($i = 0; $i < count($request->detail_stok); $i++) {

                                $penerimaanHeader['entriluar'][] = 0;
                                $penerimaanHeader['nobukti'][] = $nobuktiPenerimaan;
                                $penerimaanHeader['nowarkat'][] = '';
                                $penerimaanHeader['tgljatuhtempo'][] = date('Y-m-d', strtotime($request->tglbukti));
                                $penerimaanHeader['coadebet'][] = $bank->coa;
                                $penerimaanHeader['coakredit'][] = $memo['JURNAL'];
                                $penerimaanHeader['keterangan_detail'][] = $request->detail_keterangan[$i];
                                $penerimaanHeader["nominal_detail"][] = $detaillog[$i]['harga'];
                                $penerimaanHeader['invoice_nobukti'][] = '';
                                $penerimaanHeader['bankpelanggan_id'][] = 0;
                                $penerimaanHeader['jenisbiaya'][] = '';
                                $penerimaanHeader['pelunasanpiutang_nobukti'][] = '';
                                $penerimaanHeader['bulanbeban'][] = date('Y-m-d', strtotime($request->tglbukti));
                            }
                            $penerimaan = $this->storePenerimaan($penerimaanHeader);
                            // return response($penerimaan,422);
                            $pengeluaranStokHeader->penerimaan_nobukti = $penerimaan['data']['nobukti'];
                            $pengeluaranStokHeader->save();
                        } else if ($pengeluaranStokHeader->statuspotongretur == $potongHutang->id) {
                            $hutangHeader = [
                                "tglbukti" => $request->tglbukti,
                                "bank_id" => $request->bank_id,
                                "supplier_id" => $request->supplier_id,
                                "alatbayar_id" => 0,
                                "tglcair" => $request->tglcair,
                            ];
                            // for ($i = 0; $i < count($request->detail_stok); $i++) {
                            //     $hutangHeader['keterangandetail'][] = $request->keterangandetail[$i];
                            //     $hutangHeader['bayar'][] = $request->bayar[$i];
                            //     $hutangHeader['hutang_id'][] = $request->hutang_id[$i];
                            //     $hutangHeader['total'][] = $request->total[$i];
                            //     $hutangHeader['potongan'][] = $request->potongan[$i];
                            // }
                        }
                    }
                }

                DB::commit();
            }

            /* Set position and page */
            $selected = $this->getPosition($pengeluaranStokHeader, $pengeluaranStokHeader->getTable());
            $pengeluaranStokHeader->position = $selected->position;
            $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / ($request->limit ?? 10));

            if (isset($request->limit)) {
                $pengeluaranStokHeader->page = ceil($pengeluaranStokHeader->position / $request->limit);
            }

            return response([
                'message' => 'Berhasil disimpan',
                'data' => $pengeluaranStokHeader
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
        $pengeluaranStokHeader = PengeluaranStokHeader::where('id', $id)->first();

        /*Update  di stok persediaan*/

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')->first();
        if ($pengeluaranStokHeader->pengeluaranstok_id == $spk->text) {

            $querypengeluaranstokdetail = PengeluaranStokDetail::from(
                "pengeluaranstokdetail as i"
            )
                ->select(
                    'i.stok_id',

                )
                ->where('i.pengeluaranstokheader_id', '=', $id)
                ->orderBy('i.id', 'Asc')
                ->get();


            $datastokdetail = json_decode($querypengeluaranstokdetail, true);
            foreach ($datastokdetail as $item) {

                $reset = $this->resethpp($pengeluaranStokHeader->id, $item['stok_id'], true);
                // return  $reset;

                if (!$reset['status']) {
                    throw new \Throwable($reset['message']);
                }
            }
        }

        DB::beginTransaction();
        $getDetail = PengeluaranStokDetail::where('pengeluaranstokheader_id', $id)->get();
        $pengeluaranStok = new PengeluaranStokHeader();
        $pengeluaranStok = $pengeluaranStok->lockAndDestroy($id);


        /*Update  di stok persediaan*/

        $spk = Parameter::where('grp', 'SPK STOK')->where('subgrp', 'SPK STOK')
            ->where('text', '=', $request->pengeluaranstok_id)
            ->first();

        if (isset($spk)) {
            goto stokpersediaan;
        }

        $spk = Parameter::where('grp', 'KOR MINUS STOK')->where('subgrp', 'KOR MINUS STOK')
            ->where('text', '=', $request->pengeluaranstok_id)
            ->first();

        if (isset($spk)) {
            goto stokpersediaan;
        }

        $spk = Parameter::where('grp', 'RETUR STOK')->where('subgrp', 'RETUR STOK')
            ->where('text', '=', $request->pengeluaranstok_id)
            ->first();

        if (isset($spk)) {
            goto stokpersediaan;
        }

        stokpersediaan:;
        if ($request->pengeluaranstok_id == $spk->text) {

            $datadetail = PengeluaranStokDetail::select('stok_id', 'qty')
                ->where('pengeluaranstokheader_id', '=', $id)
                ->get();

            $datadetail = json_decode($getDetail, true);

            foreach ($datadetail as $item) {
                $stokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $item['stok_id'])
                    ->where("gudang_id", ($request->gudang_id))->firstorFail();
                $stokpersediaan->qty += $item['qty'];
                $stokpersediaan->save();
            }
        }

        if ($pengeluaranStok) {
            $logTrail = [
                'namatabel' => strtoupper($pengeluaranStok->getTable()),
                'postingdari' => 'DELETE PENGELUARAN STOK',
                'idtrans' => $pengeluaranStok->id,
                'nobuktitrans' => $pengeluaranStok->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $pengeluaranStok->toArray(),
                'modifiedby' => $pengeluaranStok->modifiedby
            ];

            $validatedLogTrail = new StoreLogTrailRequest($logTrail);
            $storedLogTrail = app(LogTrailController::class)->store($validatedLogTrail);

            // DELETE PENGELUARAN STOK DETAIL
            $logTrailPengeluaranStokDetail = [
                'namatabel' => 'PENGELUARANSTOKDETAIL',
                'postingdari' => 'DELETE PENGELUARAN STOK DETAIL',
                'idtrans' => $storedLogTrail['id'],
                'nobuktitrans' => $pengeluaranStok->nobukti,
                'aksi' => 'DELETE',
                'datajson' => $getDetail->toArray(),
                'modifiedby' => auth('api')->user()->name
            ];

            $validatedLogTrailPengeluaranStokDetail = new StoreLogTrailRequest($logTrailPengeluaranStokDetail);
            app(LogTrailController::class)->store($validatedLogTrailPengeluaranStokDetail);
            DB::commit();

            $selected = $this->getPosition($pengeluaranStok, $pengeluaranStok->getTable(), true);
            $pengeluaranStok->position = $selected->position;
            $pengeluaranStok->id = $selected->id;
            $pengeluaranStok->page = ceil($pengeluaranStok->position / ($request->limit ?? 10));

            return response([
                'status' => true,
                'message' => 'Berhasil dihapus',
                'data' => $pengeluaranStok
            ]);
        } else {
            DB::rollBack();

            return response([
                'status' => false,
                'message' => 'Gagal dihapus'
            ]);
        }
    }

    public function storePenerimaan($penerimaanHeader)
    {
        try {


            $penerimaan = new StorePenerimaanHeaderRequest($penerimaanHeader);
            $header = app(PenerimaanHeaderController::class)->store($penerimaan);

            // $nobukti = $penerimaanHeader['nobukti'];
            // $fetchPenerimaan = PenerimaanHeader::where("nobukti",$nobukti)->first();

            // $parameterController = new ParameterController;
            // $statusApp = $parameterController->getparameterid('STATUS APPROVAL', 'STATUS APPROVAL', 'NON APPROVAL');

            // $id = $fetchPenerimaan->id;

            // foreach ($penerimaanDetail as $value) {

            //     $value['penerimaan_id'] = $id;
            //     $penerimaanDetail = new StorePenerimaanDetailRequest($value);
            //     $tes = app(PenerimaanDetailController::class)->store($penerimaanDetail);

            // }


            return [
                'status' => true,
                'data' => $header->original['data'],
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    private function resethppedit($id, $stok_id)
    {
        try {

            $temphpp = '##temphppedit' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphpp, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('nobukti', 100)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->unsignedBigInteger('pengeluaranstokheader_id')->nullable();
            });


            $querytemphpp = PengeluaranStokDetail::from(
                "pengeluaranstokdetail as i"
            )
                ->select(
                    'i.id',
                    'i.nobukti',
                    'i.qty',
                    'i.pengeluaranstokheader_id'
                )
                ->whereRaw("i.pengeluaranstokheader_id>" . $id);



            DB::table($temphpp)->insertUsing([
                'id',
                'nobukti',
                'qty',
                'pengeluaranstokheader_id'
            ], $querytemphpp);

            $querytemphpp = DB::table($temphpp)->from(
                $temphpp . " as i"
            )
                ->select(
                    'i.id',
                    'i.nobukti',
                    'i.qty',
                    'a.id as pengeluaranstokheader_id',
                    'a.tglbukti',
                    'a.modifiedby',

                )
                ->join('pengeluaranstokheader as a', 'i.nobukti', 'a.nobukti')
                ->orderBy('i.pengeluaranstokheader_id', 'Asc')
                ->orderBy('i.id', 'Asc')
                ->get();


            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();

            $datafifo = json_decode($querytemphpp, true);
            foreach ($datafifo as $item) {


                $datadetailfifo = [
                    "pengeluaranstokheader_id" => $item['pengeluaranstokheader_id'],
                    "nobukti" => $item['nobukti'],
                    "stok_id" => $stok_id,
                    "gudang_id" => $gudangkantor->text,
                    "tglbukti" => $item['tglbukti'],
                    "qty" => $item['qty'],
                    "modifiedby" => $item['modifiedby'],
                ];
                $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                app(PengeluaranStokDetailFifoController::class)->store($datafifo);
            }
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            // DB::rollBack();

            throw $th;
        }
    }

    public function cekvalidasi($id)
    {
        $pengeluaran = PengeluaranStokHeader::findOrFail($id);
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
    private function resethpp($id, $stok_id, $hapus)
    {
        // DB::beginTransaction();

        try {
            $temphpp = '##temphpp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphpp, function ($table) {
                $table->unsignedBigInteger('id')->nullable();
                $table->string('nobukti', 100)->nullable();
                $table->double('qty', 15, 2)->nullable();
                $table->unsignedBigInteger('pengeluaranstokheader_id')->nullable();
            });


            $querytemphpp = PengeluaranStokDetail::from(
                "pengeluaranstokdetail as i"
            )
                ->select(
                    'i.id',
                    'i.nobukti',
                    'i.qty',
                    'i.pengeluaranstokheader_id'
                )
                ->whereRaw("i.pengeluaranstokheader_id>" . $id);



            DB::table($temphpp)->insertUsing([
                'id',
                'nobukti',
                'qty',
                'pengeluaranstokheader_id'
            ], $querytemphpp);

            $querydetailfifo = PengeluaranStokDetailFifo::from(
                "pengeluaranstokdetailfifo as i"
            )
                ->select(
                    'i.id',
                    'i.penerimaanstok_qty',
                    'i.penerimaanstokheader_nobukti',
                    'i.pengeluaranstokheader_id',
                )
                ->whereRaw("i.pengeluaranstokheader_id>=" . $id . " and i.stok_id=" . $stok_id)
                ->orderBy('i.id', 'Asc')
                ->get();


            $gudangkantor = Parameter::where('grp', 'GUDANG KANTOR')->where('subgrp', 'GUDANG KANTOR')->first();


            $datadetail = json_decode($querydetailfifo, true);
            //  dd($datadetail);
            foreach ($datadetail as $item) {


                $datapenerimaanstokdetail  = PenerimaanStokDetail::lockForUpdate()->where("stok_id", $stok_id)
                    ->where("nobukti", $item['penerimaanstokheader_nobukti'])
                    ->firstorFail();

                // dump( $stok_id);
                // dd($item['penerimaanstokheader_nobukti']);
                $datapenerimaanstokdetail->qtykeluar -= $item['penerimaanstok_qty'];
                $datapenerimaanstokdetail->save();

                if ($hapus == true) {
                    $datastokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $stok_id)
                        ->where("gudang_id", $gudangkantor->text)
                        ->firstorFail();
                    $datastokpersediaan->qty += $item['penerimaanstok_qty'];
                    $datastokpersediaan->save();
                }

                $datapengeluaranstokdetailfifo = PengeluaranStokDetailFifo::lockForUpdate()->where("stok_id", $stok_id)
                    ->where("id", $item['id'])
                    ->firstorFail();
                // dd($datapengeluaranstokdetailfifo);
                $datapengeluaranstokdetailfifo->delete();
            }
            // dd('test');

            if ($hapus == true) {
                $querytemphpp = DB::table($temphpp)->from(
                    $temphpp . " as i"
                )
                    ->select(
                        'i.id',
                        'i.nobukti',
                        'i.qty',
                        'a.id as pengeluaranstokheader_id',
                        'a.tglbukti',
                        'a.modifiedby',

                    )
                    ->join('pengeluaranstokheader as a', 'i.nobukti', 'a.nobukti')
                    ->orderBy('i.pengeluaranstokheader_id', 'Asc')
                    ->orderBy('i.id', 'Asc')
                    ->get();




                $datafifo = json_decode($querytemphpp, true);
                foreach ($datafifo as $item) {


                    $datadetailfifo = [
                        "pengeluaranstokheader_id" => $item['pengeluaranstokheader_id'],
                        "nobukti" => $item['nobukti'],
                        "stok_id" => $stok_id,
                        "gudang_id" => $gudangkantor->text,
                        "tglbukti" => $item['tglbukti'],
                        "qty" => $item['qty'],
                        "modifiedby" => $item['modifiedby'],
                    ];
                    $datafifo = new StorePengeluaranStokDetailFifoRequest($datadetailfifo);
                    $as = app(PengeluaranStokDetailFifoController::class)->store($datafifo);
                    // return $as;
                    $datastokpersediaan  = StokPersediaan::lockForUpdate()->where("stok_id", $stok_id)
                        ->where("gudang_id", $gudangkantor->text)
                        ->firstorFail();
                    $datastokpersediaan->qty -= $item['qty'];
                    $datastokpersediaan->save();
                }
            }


            // DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $th) {
            // DB::rollBack();

            throw $th;
        }
    }

    public function printReport($id)
    {
        DB::beginTransaction();

        try {
            $pengeluaranStokHeader = PengeluaranStokheader::findOrFail($id);
            $statusSudahCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'CETAK')->first();
            $statusBelumCetak = Parameter::where('grp', '=', 'STATUSCETAK')->where('text', '=', 'BELUM CETAK')->first();

            if ($pengeluaranStokHeader->statuscetak != $statusSudahCetak->id) {
                $pengeluaranStokHeader->statuscetak = $statusSudahCetak->id;
                $pengeluaranStokHeader->tglbukacetak = date('Y-m-d H:i:s');
                $pengeluaranStokHeader->userbukacetak = auth('api')->user()->name;
                $pengeluaranStokHeader->jumlahcetak = $pengeluaranStokHeader->jumlahcetak + 1;
                if ($pengeluaranStokHeader->save()) {
                    $logTrail = [
                        'namatabel' => strtoupper($pengeluaranStokHeader->getTable()),
                        'postingdari' => 'PRINT INVOICE EXTRA',
                        'idtrans' => $pengeluaranStokHeader->id,
                        'nobuktitrans' => $pengeluaranStokHeader->id,
                        'aksi' => 'PRINT',
                        'datajson' => $pengeluaranStokHeader->toArray(),
                        'modifiedby' => $pengeluaranStokHeader->modifiedby
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
}
