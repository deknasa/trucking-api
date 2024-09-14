<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class PelunasanPiutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pelunasanpiutangheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('alatbayar', 255)->nullable();
            $table->unsignedBigInteger('statuspelunasan')->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',
                'tipe',

            )
            ->where('tipe', '=', 'KAS')
            ->first();

        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $alatbayardefault = $statusdefault->id ?? 0;

        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $alatbayardefault)
            ->where('tipe', '=', $bank->tipe)
            ->first();

        $statuspelunasan = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'PELUNASAN')
            ->where('subgrp', '=', 'PELUNASAN')
            ->where('default', '=', 'YA')
            ->first();

        $statuspelunasanid = $statuspelunasan->id ?? 0;

        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar, "statuspelunasan" => $statuspelunasanid]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
                'alatbayar_id',
                'alatbayar',
                'statuspelunasan'
            );

        $data = $query->first();

        return $data;
    }

    public function cekvalidasiaksi($id)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';

        $pelunasan = DB::table("pelunasanpiutangheader")->from(DB::raw("pelunasanpiutangheader"))->where('id', $id)->first();

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.penerimaan_nobukti', 'b.nobukti')
            ->where('a.penerimaan_nobukti', '=', $pelunasan->penerimaan_nobukti)
            ->first();

        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasanPiutang->penerimaan_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->penerimaan_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaangiro_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.penerimaangiro_nobukti', 'b.nobukti')
            ->where('a.penerimaangiro_nobukti', '=', $pelunasan->penerimaangiro_nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasanPiutang->penerimaangiro_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->penerimaangiro_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }


        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaangiro_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("penerimaanheader b with (readuncommitted)"), 'a.penerimaangiro_nobukti', 'b.penerimaangiro_nobukti')
            ->where('a.penerimaangiro_nobukti', '=', $pelunasan->penerimaangiro_nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {

            $keteranganerror = $error->cekKeteranganError('SCG') ?? '';
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasanPiutang->penerimaangiro_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->penerimaangiro_nobukti,
                'kodeerror' => 'SCG'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.notadebet_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.notadebet_nobukti', 'b.nobukti')
            ->where('a.notadebet_nobukti', '=', $pelunasan->notadebet_nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasanPiutang->notadebet_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->notadebet_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.notakredit_nobukti',
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.notakredit_nobukti', 'b.nobukti')
            ->where('a.notakredit_nobukti', '=', $pelunasan->notakredit_nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $pelunasanPiutang->notakredit_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,
                // 'keterangan' => 'Approval Jurnal ' . $pelunasanPiutang->notakredit_nobukti,
                'kodeerror' => 'SAPP'
            ];
            goto selesai;
        }



        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $query = DB::table($this->table)->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(
                'pelunasanpiutangheader.id',
                'pelunasanpiutangheader.nobukti',
                'pelunasanpiutangheader.tglbukti',
                'pelunasanpiutangheader.modifiedby',
                'pelunasanpiutangheader.updated_at',
                'pelunasanpiutangheader.created_at',
                'pelunasanpiutangheader.pengeluaran_nobukti',
                'pelunasanpiutangheader.penerimaan_nobukti',
                'pelunasanpiutangheader.penerimaangiro_nobukti',
                db::raw("pelunasanpiutangheader.penerimaan_nobukti as penerimaan_nobuktihidden"),
                db::raw("pelunasanpiutangheader.penerimaangiro_nobukti as penerimaangiro_nobuktihidden"),
                'pelunasanpiutangheader.notadebet_nobukti',
                'pelunasanpiutangheader.notakredit_nobukti',
                'pelunasanpiutangheader.notakreditpph_nobukti',
                'statuscetak.memo as statuscetak',
                'bank.namabank as bank_id',
                'agen.namaagen as agen_id',
                'alatbayar.namaalatbayar as alatbayar_id',
                db::raw("cast((format(penerimaangiroheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaangiroheader"),
                db::raw("cast(cast(format((cast((format(penerimaangiroheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaangiroheader"),
                db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
                'penerimaanheader.bank_id as penerimaanbank_id',
                db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
                db::raw("cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                'pengeluaranheader.bank_id as pengeluaranbank_id',
                db::raw("cast(cast(format((cast((format(pengeluaranheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
                db::raw("cast((format(notadebetheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernotadebetheader"),
                db::raw("cast(cast(format((cast((format(notadebetheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernotadebetheader"),
                db::raw("cast((format(notakreditheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadernotakreditheader"),
                db::raw("cast(cast(format((cast((format(notakreditheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadernotakreditheader"),
            )

            ->leftJoin(DB::raw("penerimaangiroheader with (readuncommitted)"), 'pelunasanpiutangheader.penerimaangiro_nobukti', '=', 'penerimaangiroheader.nobukti')
            ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'pelunasanpiutangheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
            ->leftJoin(DB::raw("pengeluaranheader with (readuncommitted)"), 'pelunasanpiutangheader.pengeluaran_nobukti', '=', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("notadebetheader with (readuncommitted)"), 'pelunasanpiutangheader.notadebet_nobukti', '=', 'notadebetheader.nobukti')
            ->leftJoin(DB::raw("notakreditheader with (readuncommitted)"), 'pelunasanpiutangheader.notakredit_nobukti', '=', 'notakreditheader.nobukti')

            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pelunasanpiutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pelunasanpiutangheader.alatbayar_id', 'alatbayar.id');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pelunasanpiutangheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pelunasanpiutangheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pelunasanpiutangheader.statuscetak", $statusCetak);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getPelunasanPiutang($id, $agenid, $pilihan)
    {
        $this->setRequestParameters();

        $tempPiutang = $this->createTempPiutang($id, $agenid, $pilihan);
        $tempPelunasan = $this->createTempPelunasan($id, $agenid);

        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table($tempPelunasan)->from(DB::raw("$tempPelunasan with (readuncommitted)"))
            ->select(DB::raw("pelunasanpiutang_id,piutang_nobukti,tglbukti,nominal,keterangan,potongan,potonganpph, coapotongan,keteranganpotongan,keteranganpotonganpph,nominallebihbayar,nominalpiutang,invoice_nobukti,sisa,statusnotadebet,statusnotakredit"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('pelunasanpiutang_id')->nullable();
            $table->string('piutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
            $table->float('potongan')->nullable();
            $table->float('potonganpph')->nullable();
            $table->string('coapotongan')->nullable();
            $table->longText('keteranganpotongan')->nullable();
            $table->longText('keteranganpotonganpph')->nullable();
            $table->float('nominallebihbayar')->nullable();
            $table->float('nominalpiutang')->nullable();
            $table->string('invoice_nobukti')->nullable();
            $table->float('sisa')->nullable();
            $table->bigInteger('statusnotadebet')->nullable();
            $table->bigInteger('statusnotakredit')->nullable();
        });

        DB::table($temp)->insertUsing(['pelunasanpiutang_id', 'piutang_nobukti', 'tglbukti', 'nominal', 'keterangan', 'potongan', 'potonganpph', 'coapotongan', 'keteranganpotongan', 'keteranganpotonganpph', 'nominallebihbayar', 'nominalpiutang', 'invoice_nobukti', 'sisa', 'statusnotadebet', 'statusnotakredit'], $fetch);

        $piutang = DB::table("$tempPiutang as A")->from(DB::raw("$tempPiutang as A with (readuncommitted)"))
            ->select(DB::raw("null as pelunasanpiutang_id,A.nobukti as piutang_nobukti, A.tglbukti as tglbukti, 0 as nominal, null as keterangan, 0 as potongan,0 as potonganpph, null as coapotongan, null as keteranganpotongan,null as keteranganpotonganpph, 0 as nominallebihbayar, A.nominalpiutang,A.invoice_nobukti as invoice_nobukti, A.sisa as sisa, 0 as statusnotadebet, 0 as statusnotakredit"))
            ->distinct("A.nobukti")
            ->leftJoin(DB::raw("$tempPelunasan as B with (readuncommitted)"), "A.nobukti", "B.piutang_nobukti")
            ->whereRaw("isnull(b.piutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");

        DB::table($temp)->insertUsing(['pelunasanpiutang_id', 'piutang_nobukti', 'tglbukti', 'nominal', 'keterangan', 'potongan', 'potonganpph', 'coapotongan', 'keteranganpotongan', 'keteranganpotonganpph', 'nominallebihbayar', 'nominalpiutang', 'invoice_nobukti', 'sisa', 'statusnotadebet', 'statusnotakredit'], $piutang);

        $data = DB::table($temp)
            ->select(DB::raw("row_number() Over(Order By $temp.piutang_nobukti) as id,$temp.pelunasanpiutang_id,$temp.piutang_nobukti as nobukti,$temp.tglbukti as tglbukti_piutang,$temp.invoice_nobukti,$temp.nominal as bayar,$temp.keterangan,potongan, $temp.potonganpph, $temp.coapotongan,$temp.keteranganpotongan,$temp.keteranganpotonganpph,$temp.nominallebihbayar,$temp.nominalpiutang as nominal,$temp.sisa, $temp.statusnotadebet, $temp.statusnotakredit,
            (case when isnull(c.nobukti,'')<>'' or isnull(b.postingdari,'')='INVOICE' then 'UTAMA' else 'TAMBAHAN' end) as jenisinvoice"))
            ->join(db::raw("piutangheader b with (readuncommitted)"), 'b.nobukti', $temp . ".piutang_nobukti")
            ->leftjoin(db::raw("invoiceheader c with (readuncommitted)"), 'b.invoice_nobukti', "c.nobukti")
            ->get();

        return $data;
    }

    public function createTempPiutang($id, $agenid, $pilihan)
    {
        $temp = '##tempPiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('piutangheader')->from(DB::raw("piutangheader with (readuncommitted)"))
            ->select(DB::raw("piutangheader.nobukti,piutangheader.tglbukti,piutangheader.nominal as nominalpiutang,piutangheader.invoice_nobukti, (SELECT (piutangheader.nominal - COALESCE(SUM(pelunasanpiutangdetail.nominal),0) - COALESCE(SUM(pelunasanpiutangdetail.potongan),0) - COALESCE(SUM(pelunasanpiutangdetail.potonganpph),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"));
        if ($pilihan == 'agen') {
            $fetch->whereRaw("piutangheader.agen_id = $agenid")
                ->groupBy('piutangheader.id', 'piutangheader.nobukti', 'piutangheader.agen_id', 'piutangheader.nominal', 'piutangheader.tglbukti', 'piutangheader.invoice_nobukti');
        } else {
            $fetch->whereRaw("piutangheader.pelanggan_id = $agenid")
                ->groupBy('piutangheader.id', 'piutangheader.nobukti', 'piutangheader.pelanggan_id', 'piutangheader.nominal', 'piutangheader.tglbukti', 'piutangheader.invoice_nobukti');
        }
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti')->nullable();
            $table->bigInteger('nominalpiutang');
            $table->string('invoice_nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'nominalpiutang', 'invoice_nobukti', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPelunasan($id, $agenid)
    {
        $tempo = '##tempPelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('pelunasanpiutangdetail as ppd')->from(DB::raw("pelunasanpiutangdetail as ppd with (readuncommitted)"))
            ->select(DB::raw("ppd.pelunasanpiutang_id,ppd.piutang_nobukti,piutangheader.tglbukti,ppd.nominal,ppd.keterangan,ppd.potongan,ppd.potonganpph,ppd.coapotongan,ppd.keteranganpotongan,ppd.keteranganpotonganpph,ppd.nominallebihbayar, piutangheader.nominal as nominalpiutang,ppd.invoice_nobukti, (SELECT (piutangheader.nominal - SUM(isnull(pelunasanpiutangdetail.nominal,0)) - SUM(isnull(pelunasanpiutangdetail.potongan,0)) - SUM(isnull(pelunasanpiutangdetail.potonganpph,0))) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa, ppd.statusnotadebet, ppd.statusnotakredit"))
            ->join(DB::raw("piutangheader with (readuncommitted)"), 'ppd.piutang_nobukti', 'piutangheader.nobukti')
            ->whereRaw("ppd.pelunasanpiutang_id = $id");
        Schema::create($tempo, function ($table) {
            $table->bigInteger('pelunasanpiutang_id')->nullable();
            $table->string('piutang_nobukti');
            $table->date('tglbukti')->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
            $table->float('potongan')->nullable();
            $table->float('potonganpph')->nullable();
            $table->string('coapotongan')->nullable();
            $table->longText('keteranganpotongan')->nullable();
            $table->longText('keteranganpotonganpph')->nullable();
            $table->float('nominallebihbayar')->nullable();
            $table->float('nominalpiutang')->nullable();
            $table->string('invoice_nobukti')->nullable();
            $table->float('sisa')->nullable();
            $table->bigInteger('statusnotadebet')->nullable();
            $table->bigInteger('statusnotakredit')->nullable();
        });

        $tes = DB::table($tempo)->insertUsing(['pelunasanpiutang_id', 'piutang_nobukti', 'tglbukti', 'nominal', 'keterangan', 'potongan', 'potonganpph', 'coapotongan', 'keteranganpotongan', 'keteranganpotonganpph', 'nominallebihbayar', 'nominalpiutang', 'invoice_nobukti', 'sisa', 'statusnotadebet', 'statusnotakredit'], $fetch);

        return $tempo;
    }

    public function getDeletePelunasanPiutang($id, $agenId)
    {


        $tempPelunasan = $this->createTempPelunasan($id, $agenId);

        $data = DB::table($tempPelunasan)
            ->select(DB::raw("row_number() Over(Order By $tempPelunasan.piutang_nobukti) as id,pelunasanpiutang_id,piutang_nobukti as nobukti,tglbukti as tglbukti_piutang,invoice_nobukti,nominal as bayar,keterangan,potongan,potonganpph, coapotongan,keteranganpotongan,keteranganpotonganpph,nominallebihbayar,nominalpiutang as nominal,sisa,statusnotadebet,statusnotakredit"))
            ->get();
        return $data;
    }

    public function getPelunasanNotaKredit($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"))
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        pelunasanpiutangdetail.keterangan,
        pelunasanpiutangdetail.coapotongan,
        COALESCE (pelunasanpiutangdetail.potongan, 0) as potongan '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" NOT EXISTS (
            SELECT notakreditheader.pelunasanpiutang_nobukti
            FROM notakreditdetail with (readuncommitted)
			left join notakreditheader with (readuncommitted) on notakreditdetail.notakredit_id = notakreditheader.id
            WHERE notakreditheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.potongan', '>', 0)
            ->where('pelunasanpiutangdetail.pelunasanpiutang_id', $id);




        $data = $query->get();

        return $data;
    }

    public function getPelunasanNotaDebet($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"))
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        pelunasanpiutangdetail.keterangan,
        pelunasanpiutangdetail.coalebihbayar,
        COALESCE (pelunasanpiutangdetail.nominallebihbayar, 0) as lebihbayar '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" NOT EXISTS (
            SELECT notadebetheader.pelunasanpiutang_nobukti
            FROM notadebetdetail with (readuncommitted)
			left join notadebetheader with (readuncommitted) on notadebetdetail.notadebet_id = notadebetheader.id
            WHERE notadebetheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.nominallebihbayar', '>', 0)
            ->where('pelunasanpiutangdetail.pelunasanpiutang_id', $id);




        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {

        $query = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(
                'pelunasanpiutangheader.id',
                'pelunasanpiutangheader.nobukti',
                'pelunasanpiutangheader.tglbukti',
                'pelunasanpiutangheader.bank_id',
                'pelunasanpiutangheader.alatbayar_id',
                'pelunasanpiutangheader.agen_id',
                'pelunasanpiutangheader.pelanggan_id',
                'pelunasanpiutangheader.statuspelunasan',
                'pelunasanpiutangheader.penerimaan_nobukti',
                'pelunasanpiutangheader.pengeluaran_nobukti',
                'pelunasanpiutangheader.penerimaangiro_nobukti',
                'pelunasanpiutangheader.notakredit_nobukti',
                'pelunasanpiutangheader.notakreditpph_nobukti',
                db::raw("(case when isnull(c.notadebet_nobukti,'')='' then pelunasanpiutangheader.notadebet_nobukti else isnull(c.notadebet_nobukti,'')  end) as notadebet_nobukti "),
                'pelunasanpiutangheader.nowarkat',
                'pelunasanpiutangheader.tglcair as tgljatuhtempo',

                'bank.namabank as bank',
                'alatbayar.namaalatbayar as alatbayar',
                'agen.namaagen as agen',
                'pelanggan.namapelanggan as pelanggan',
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pelunasanpiutangheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("notadebetfifo c with (readuncommitted)"), 'pelunasanpiutangheader.nobukti', 'c.pelunasanpiutang_nobukti')
            ->where('pelunasanpiutangheader.id', $id);

        $data = $query->first();

        return $data;
    }


    public function pelunasanpiutangdetail()
    {
        return $this->hasMany(PelunasanPiutangDetail::class, 'pelunasanpiutang_id');
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(

                'pelunasanpiutangheader.id',
                'pelunasanpiutangheader.nobukti',
                'pelunasanpiutangheader.tglbukti',
                'pelunasanpiutangheader.pengeluaran_nobukti',
                'pelunasanpiutangheader.penerimaan_nobukti',
                'pelunasanpiutangheader.penerimaangiro_nobukti',
                'pelunasanpiutangheader.notadebet_nobukti',
                'pelunasanpiutangheader.notakredit_nobukti',
                'pelunasanpiutangheader.notakreditpph_nobukti',
                'statuscetak.text as statuscetak',
                'bank.namabank as bank_id',
                'agen.namaagen as agen_id',
                'alatbayar.namaalatbayar as alatbayar_id',
                'pelunasanpiutangheader.modifiedby',
                'pelunasanpiutangheader.created_at',
                'pelunasanpiutangheader.updated_at',
            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pelunasanpiutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pelunasanpiutangheader.alatbayar_id', 'alatbayar.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('penerimaan_nobukti', 1000)->nullable();
            $table->string('penerimaangiro_nobukti', 1000)->nullable();
            $table->string('notadebet_nobukti', 1000)->nullable();
            $table->string('notakredit_nobukti', 1000)->nullable();
            $table->string('notakreditpph_nobukti', 1000)->nullable();
            $table->string('statuscetak')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('agen_id')->nullable();
            $table->string('alatbayar_id')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pengeluaran_nobukti', 'penerimaan_nobukti', 'penerimaangiro_nobukti', 'notadebet_nobukti', 'notakredit_nobukti', 'notakreditpph_nobukti', 'statuscetak', 'bank_id', 'agen_id', 'alatbayar_id', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar_id') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'agen_id') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar_id') {
                                    $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                                }
                            }
                        }
                    });
                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getSisaEditPelunasanValidasi($id, $nobukti)
    {
        $fetch = DB::table('pelunasanpiutangdetail as ppd')->from(DB::raw("pelunasanpiutangdetail as ppd with (readuncommitted)"))
            ->select(DB::raw("ppd.pelunasanpiutang_id,ppd.piutang_nobukti,piutangheader.tglbukti,ppd.nominal,ppd.keterangan,ppd.potongan,ppd.coapotongan,ppd.keteranganpotongan,ppd.nominallebihbayar, piutangheader.nominal as nominalpiutang,ppd.invoice_nobukti, (SELECT (piutangheader.nominal - SUM(pelunasanpiutangdetail.nominal) - SUM(pelunasanpiutangdetail.potongan)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->join(DB::raw("piutangheader with (readuncommitted)"), 'ppd.piutang_nobukti', 'piutangheader.nobukti')
            ->whereRaw("ppd.pelunasanpiutang_id = $id")
            ->whereRaw("ppd.piutang_nobukti = '$nobukti'");

        return $fetch->first();
    }
    // 
    public function getEditPelunasan($nobukti, $agenId)
    {
        $query = DB::table('piutangheader')->from(DB::raw("piutangheader with (readuncommitted)"))
            ->select(DB::raw("piutangheader.nobukti,piutangheader.tglbukti,piutangheader.nominal as nominalpiutang,piutangheader.invoice_nobukti, (SELECT (piutangheader.nominal - COALESCE(SUM(pelunasanpiutangdetail.nominal),0) - COALESCE(SUM(pelunasanpiutangdetail.potongan),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->whereRaw("piutangheader.agen_id = $agenId")
            ->whereRaw("piutangheader.nobukti = '$nobukti'")
            ->groupBy('piutangheader.id', 'piutangheader.nobukti', 'piutangheader.agen_id', 'piutangheader.nominal', 'piutangheader.tglbukti', 'piutangheader.invoice_nobukti');
        return $query->first();
    }
    public function getMinusSisaPelunasan($nobukti)
    {
        $query = DB::table("piutangheader")->from(DB::raw("piutangheader with (readuncommitted)"))
            ->select('nominal')
            ->where('nobukti', $nobukti)
            ->first($nobukti);

        return $query;
    }
    public function processStore(array $data): PelunasanPiutangHeader
    {
        $group = 'PELUNASAN PIUTANG BUKTI';
        $subGroup = 'PELUNASAN PIUTANG BUKTI';
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();
        $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $notakredit = false;
        foreach ($data['potongan'] as $value) {
            if ($value != '0') {
                $notakredit = true;
                break;
            }
        }
        $notakreditpph = false;
        foreach ($data['potonganpph'] as $value) {
            if ($value != '0') {
                $notakreditpph = true;
                break;
            }
        }

        $notadebet = false;
        foreach ($data['nominallebihbayar'] as $value) {
            if ($value != '0') {
                $notadebet = true;
                break;
            }
        }
        $nominallunas = 0;
        for ($i = 0; $i < count($data['piutang_id']); $i++) {
            $nominallunas = $nominallunas + $data['bayar'][$i];
        }

        $pelunasanPiutangHeader = new PelunasanPiutangHeader();
        $pelunasanPiutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pelunasanPiutangHeader->statuspelunasan = $data['statuspelunasan'];
        $pelunasanPiutangHeader->bank_id = $data['bank_id'];
        $pelunasanPiutangHeader->alatbayar_id = $data['alatbayar_id'];
        $pelunasanPiutangHeader->pengeluaran_nobukti = '-';
        $pelunasanPiutangHeader->penerimaan_nobukti = '-';
        $pelunasanPiutangHeader->penerimaangiro_nobukti = '-';
        $pelunasanPiutangHeader->statuscetak = $statusCetak->id ?? 0;
        $pelunasanPiutangHeader->notakredit_nobukti = '-';
        $pelunasanPiutangHeader->notakreditpph_nobukti = '-';
        $pelunasanPiutangHeader->notadebet_nobukti =  '-';
        $pelunasanPiutangHeader->agen_id = $data['agen_id'];
        $pelunasanPiutangHeader->pelanggan_id = $data['pelanggan_id'];
        $pelunasanPiutangHeader->nowarkat = $data['nowarkat'] ?? '';
        $pelunasanPiutangHeader->tglcair = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $pelunasanPiutangHeader->statusformat = $format->id;
        $pelunasanPiutangHeader->modifiedby = auth('api')->user()->name;
        $pelunasanPiutangHeader->info = html_entity_decode(request()->info);

        $pelunasanPiutangHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $pelunasanPiutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$pelunasanPiutangHeader->save()) {
            throw new \Exception("Error storing pelunasan piutang header.");
        }

        $pelunasanPiutangDetails = [];

        $noWarkat = [];
        $tglJatuhTempo = [];
        $nominalDetail = [];
        $coaKredit = [];
        $keteranganDetail = [];
        $invoiceNobukti = [];
        $pelunasanNobukti = [];
        $bankId = [];

        $nominalPiutang = [];
        $nominalBayar = [];
        $nominalPotongan = [];
        $coaPotongan = [];
        $nominalLebihBayar = [];
        $coaDebetNotaKredit = [];
        $coaDebetNotaDebet = [];
        $coaKreditNotaDebet = [];

        $getCoa = db::table("agen")->from(db::raw("agen a with (readuncommitted)"))->where('id', $data['agen_id'])->first();
        $getJurnalPengeluaran = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'TIPENOTADEBET')->where('text', 'UANG DITERIMA DIMUKA')->first();
        $memoJurnalPengeluaran = json_decode($getJurnalPengeluaran->memo, true);

        if ($notadebet ==  true) {
            $notadebetCoaMemo = [];
            for ($i = 0; $i < count($data['statusnotadebet']); $i++) {
                if ($data['statusnotadebet'][$i] != '') {

                    $getNotaDebetCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->select('memo')
                        ->where('id', $data['statusnotadebet'][$i])->first();
                    $notadebetCoaMemo = json_decode($getNotaDebetCoa->memo, true);
                    break;
                }
            }
            $memoNotaDebetCoa = $notadebetCoaMemo;
        }

        if ($notakredit ==  true) {
            $getNotaKreditCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->select('memo')
                ->where('grp', 'JURNAL POTONGAN')->where('subgrp', 'DEBET')->first();
            $memoNotaKreditCoa = json_decode($getNotaKreditCoa->memo, true);
        }

        if ($notakreditpph ==  true) {

            $getNotaKreditCoaPPH = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->select('memo')
                ->where('grp', 'JURNAL POTONGAN PPH')->where('subgrp', 'DEBET')->first();
            $memoNotaKreditCoaPPH = json_decode($getNotaKreditCoaPPH->memo, true);
        }

        $nominal = 0;
        $nominalemkl_jurnal = [];
        $coakreditemkl_jurnal = [];
        $keteranganemkl_jurnal = [];

        for ($i = 0; $i < count($data['piutang_id']); $i++) {
            $piutang = PiutangHeader::where('nobukti', $data['piutang_nobukti'][$i])->first();
            $potongan = $data['potongan'][$i] ?? 0;
            $potonganpph = $data['potonganpph'][$i] ?? 0;
            if ($data['nominallebihbayar'][$i] > 0) {

                $getNominalLebih = $memoNotaDebetCoa['JURNAL'];
                $invoiceNobuktiND[] = $piutang->invoice_nobukti ?? '';
                $nominalPiutangND[] = $piutang->nominal;
                $nominalBayarND[] = $data['bayar'][$i];
                $nominalLebihBayar[] = $data['nominallebihbayar'][$i] ?? 0;
                $coaDebetNotaDebet[] = $getCoa->coa;
                $coaKreditNotaDebet[] = $memoNotaDebetCoa['JURNAL'];
            }

            if ($data['potongan'][$i] > 0) {
                $getCoaPotongan = $memoNotaKreditCoa['JURNAL'];
                $nominalPiutangNK[] = $piutang->nominal;
                $invoiceNobuktiNK[] = $piutang->invoice_nobukti ?? '';
                $nominalBayarNK[] = $data['bayar'][$i];
                $keteranganPotongan[] = $data['keteranganpotongan'][$i];
                $nominalPotongan[] = $potongan;
                $coaPotongan[] = $memoNotaKreditCoa['JURNAL'] ?? '';
                $coaKreditNotaKredit[] = $getCoa->coa; //$memoNotaKreditCoa['JURNAL'];
            }

            if ($data['potonganpph'][$i] > 0) {
                $getCoaPotonganpph = $memoNotaKreditCoaPPH['JURNAL'];
                $nominalPiutangNKpph[] = $piutang->nominal;
                $invoiceNobuktiNKpph[] = $piutang->invoice_nobukti ?? '';
                $nominalBayarNKpph[] = $data['bayar'][$i];
                $nominalPotonganpph[] = $potonganpph;
                $keteranganPotonganPPH[] = $data['keteranganpotonganpph'][$i];
                $coaPotonganpph[] = $memoNotaKreditCoaPPH['JURNAL'] ?? '';
                $coaKreditNotaKreditpph[] = $getCoa->coa; //$memoNotaKreditCoa['JURNAL'];
            }



            $pelunasanPiutangDetail = (new PelunasanPiutangDetail())->processStore($pelunasanPiutangHeader, [
                'nominal' => $data['bayar'][$i],
                'piutang_nobukti' => $piutang->nobukti,
                'keterangan' => $data['keterangan'][$i] ?? '',
                'potongan' => $data['potongan'][$i] ?? '',
                'potonganpph' => $data['potonganpph'][$i] ?? '',
                'coapotongan' => $getCoaPotongan ?? '',
                'coapotonganpph' => $getCoaPotonganpph ?? '',
                'invoice_nobukti' => $piutang->invoice_nobukti ?? '',
                'keteranganpotongan' => $data['keteranganpotongan'][$i] ?? '',
                'keteranganpotonganpph' => $data['keteranganpotonganpph'][$i] ?? '',
                'nominallebihbayar' => $data['nominallebihbayar'][$i] ?? '',
                'coalebihbayar' => $getNominalLebih ?? '',
                'statusnotadebet' => $data['statusnotadebet'][$i] ?? '',
                'statusnotakredit' => $data['statusnotakredit'][$i] ?? '',
            ]);

            $pelunasanPiutangDetails[] = $pelunasanPiutangDetail->toArray();

            $noWarkat[] = $data['nowarkat'] ?? '';
            if ($data['alatbayar_id'] != $alatbayarGiro->id) {
                $tglJatuhTempo[] = $data['tglbukti'];
            } else {
                $top = intval($getCoa->top);
                $dateNow = date('Y-m-d');
                $nextDay = date('d-m-Y', strtotime($dateNow . " +$top day"));
                $tglJatuhTempo[] = $data['tgljatuhtempo'];
            }

            $nominalDetail[] = $data['bayar'][$i];
            if ($data['agen_id'] != 0) {
                $coaKredit[] =  $piutang->coadebet;
            } else {
                // START INVOICE EMKL FIFO UNTUK KE JURNAL
                $kondisi = true;
                $nominalbayarfifo = $data['bayar'][$i] + $potongan;
                while ($kondisi == true) {

                    // CEK FIFO
                    $cekfifo = db::table("invoiceemklfifo")->from(db::raw("invoiceemklfifo with (readuncommitted)"))
                        ->where('nobukti', $piutang->invoice_nobukti)
                        ->whereRaw("isnull(nominal,0) != isnull(nominalpelunasan,0)")
                        ->whereRaw("isnull(nominal,0) > 0")
                        ->first();

                    if ($cekfifo != '') {
                        $nominalsisa = $cekfifo->nominal - $cekfifo->nominalpelunasan;
                        if ($nominalbayarfifo <= $nominalsisa) {

                            DB::update(DB::raw("UPDATE invoiceemklfifo SET nominalpelunasan=$nominalbayarfifo where id=$cekfifo->id"));
                            $nominalemkl_jurnal[] = $nominalbayarfifo;
                            $coakreditemkl_jurnal[] = $cekfifo->coadebet;
                            $keteranganemkl_jurnal[] = $data['keterangan'][$i];

                            $kondisi = false;
                        } else {
                            $nominalbayarfifo = $nominalbayarfifo - $nominalsisa;
                            DB::update(DB::raw("UPDATE invoiceemklfifo SET nominalpelunasan=$nominalsisa where id=$cekfifo->id"));

                            $nominalemkl_jurnal[] = $nominalsisa;
                            $coakreditemkl_jurnal[] = $cekfifo->coadebet;
                            $keteranganemkl_jurnal[] = $data['keterangan'][$i];

                            $cekfifoselisihkurang = db::table("invoiceemklfifo")->from(db::raw("invoiceemklfifo with (readuncommitted)"))
                                ->where('jobemkl_nobukti', $cekfifo->jobemkl_nobukti)
                                ->where('status', 'selisih')
                                ->whereRaw("isnull(nominal,0) != isnull(nominalpelunasan,0)")
                                ->first();

                            if ($cekfifoselisihkurang != '') {
                                DB::update(DB::raw("UPDATE invoiceemklfifo SET nominalpelunasan=$cekfifoselisihkurang->nominal where id=$cekfifoselisihkurang->id"));

                                $nominalemkl_jurnal[] = $cekfifoselisihkurang->nominal;
                                $coakreditemkl_jurnal[] = $cekfifoselisihkurang->coadebet;
                                $keteranganemkl_jurnal[] = $data['keterangan'][$i];
                            }

                        }
                    }
                }
                // END INVOICE EMKL FIFO UNTUK KE JURNAL
                $getcoainvoice = db::table('invoiceemkldetail')->from(db::raw("invoiceemkldetail as a with (readuncommitted)"))
                    ->select('a.coadebet')
                    ->join(db::raw("invoiceemklheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                    ->where('b.piutang_nobukti', $piutang->nobukti)
                    ->first();
                $coaKredit[] = $getcoainvoice->coadebet;
            }
            // dd($piutang->coadebet,$piutang->invoice_nobukti);
            $keteranganDetail[] = $data['keterangan'][$i];
            $invoiceNobukti[] = $piutang->invoice_nobukti ?? '';
            $pelunasanNobukti[] = $pelunasanPiutangHeader->nobukti;
            $bankId[] = $pelunasanPiutangHeader->bank_id;
            $coaDebetPengeluaran[] = $memoJurnalPengeluaran['JURNAL'];
            $nominal = $nominal + $data['bayar'][$i];

            // if ($notakreditpph) {

            //     if ($potonganpph > 0) {
            //         $noWarkat[] = $data['nowarkat'] ?? '';
            //         $nominalDetail[] = -$potonganpph;
            //         $coaKredit[] = $memoNotaKreditCoaPPH['JURNAL'] ?? '';
            //         $keteranganDetail[] = $data['keteranganpotonganpph'][$i];
            //         $invoiceNobukti[] = $piutang->invoice_nobukti ?? '';
            //         $pelunasanNobukti[] = $pelunasanPiutangHeader->nobukti;
            //         if ($data['alatbayar_id'] != $alatbayarGiro->id) {
            //             $tglJatuhTempo[] = $data['tglbukti'];
            //         } else {
            //             $top = intval($getCoa->top);
            //             $dateNow = date('Y-m-d');
            //             $nextDay = date('d-m-Y', strtotime($dateNow . " +$top day"));
            //             $tglJatuhTempo[] = $data['tgljatuhtempo'];
            //         }
            //     }
            // }
        }


        if ($notakreditpph) {
            $notaKreditRequest = [
                'tanpaprosesnobukti' => 1,
                'tglbukti' => $data['tglbukti'],
                'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                'agen_id' => $data['agen_id'],
                'agen' => $data['agen'],
                'bank_id' => $data['bank_id'],
                'alatbayar_id' => $data['alatbayar_id'],
                'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                'tgllunas' => $data['tglbukti'],
                'nowarkat' => $data['nowarkat'] ?? '',
                'invoice_nobukti' => $invoiceNobuktiNKpph,
                'nominalpiutang' => $nominalPiutangNKpph,
                'nominal' => $nominalBayarNKpph,
                'potongan' => $nominalPotonganpph,
                'coakredit' => $coaKreditNotaKreditpph,
                'coadebet' => $coaPotonganpph,
                'keteranganpotongan' => $keteranganPotonganPPH,
                'cekcoadebet' => $memoNotaKreditCoaPPH['JURNAL']
            ];
            $notaKreditHeader = (new NotaKreditHeader())->processStore($notaKreditRequest);
            $pelunasanPiutangHeader->notakreditpph_nobukti = $notaKreditHeader->nobukti;
        }


        if ($data['alatbayar_id'] != $alatbayarGiro->id) {
            // SAVE TO PENERIMAAN
            $penerimaanRequest = [
                'tglbukti' => $data['tglbukti'],
                'pelanggan_id' => 0,
                'agen_id' => $data['agen_id'],
                'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                'diterimadari' => $data['agen'],
                'tgllunas' => $data['tglbukti'],
                'bank_id' => $data['bank_id'],
                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                'nominal_detail' => $nominalDetail,
                'coakredit' => $coaKredit,
                'keterangan_detail' => $keteranganDetail,
                'invoice_nobukti' => $invoiceNobukti,
                'pelunasanpiutang_nobukti' => $pelunasanNobukti

            ];
            if($data['pelanggan_id'] != 0){
                // JURNAL UNTUK PENERIMAAN KAS/BANK INVOICE EMKL
                $penerimaanRequest['nominalemkl_jurnal'] = $nominalemkl_jurnal;
                $penerimaanRequest['coakreditemkl_jurnal'] = $coakreditemkl_jurnal;
                $penerimaanRequest['keteranganemkl_jurnal'] = $keteranganemkl_jurnal;
                $penerimaanRequest['pelunasanemkl'] = 1;
            }
            $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);
            $pelunasanPiutangHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
        } else {
            $penerimaanGiroRequest = [
                'tglbukti' => $data['tglbukti'],
                'pelanggan_id' => 0,
                'agen_id' => $data['agen_id'],
                'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                'diterimadari' => $data['agen'],
                'tgllunas' => $data['tglbukti'],
                'bank_id' => $data['bank_id'],
                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                'nominal' => $nominalDetail,
                'coakredit' => $coaKredit,
                'keterangan_detail' => $keteranganDetail,
                'invoice_nobukti' => $invoiceNobukti,
                'pelunasanpiutang_nobukti' => $pelunasanNobukti,
                'bank_id' => $bankId
            ];
            $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processStore($penerimaanGiroRequest);
            $pelunasanPiutangHeader->penerimaangiro_nobukti = $penerimaanGiroHeader->nobukti;
        }

        if ($notakredit) {
            $notaKreditRequest = [
                'tanpaprosesnobukti' => 1,
                'tglbukti' => $data['tglbukti'],
                'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                'agen_id' => $data['agen_id'],
                'agen' => $data['agen'],
                'bank_id' => $data['bank_id'],
                'alatbayar_id' => $data['alatbayar_id'],
                'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                'tgllunas' => $data['tglbukti'],
                'nowarkat' => $data['nowarkat'] ?? '',
                'invoice_nobukti' => $invoiceNobuktiNK,
                'nominalpiutang' => $nominalPiutangNK,
                'nominal' => $nominalBayarNK,
                'potongan' => $nominalPotongan,
                'coakredit' => $coaKreditNotaKredit,
                'coadebet' => $coaPotongan,
                'keteranganpotongan' => $keteranganPotongan,
                'cekcoadebet' => $memoNotaKreditCoa['JURNAL']
            ];
            $notaKreditHeader = (new NotaKreditHeader())->processStore($notaKreditRequest);
            $pelunasanPiutangHeader->notakredit_nobukti = $notaKreditHeader->nobukti;
        }

        if ($notadebet) {
            $notaDebetRequest = [
                'tanpaprosesnobukti' => 1,
                'tglbukti' => $data['tglbukti'],
                'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                'agen_id' => $data['agen_id'],
                'bank_id' => $data['bank_id'],
                'alatbayar_id' => $data['alatbayar_id'],
                'agen' => $data['agen'],
                'postingdari' => 'ENTRY PELUNASAN PIUTANG',
                'tgllunas' => $data['tglbukti'],
                'nowarkat' => $data['nowarkat'] ?? '',
                'invoice_nobukti' => $invoiceNobuktiND,
                'nominalpiutang' => $nominalPiutangND,
                'nominal' => $nominalBayarND,
                'nominallebihbayar' => $nominalLebihBayar,
                'coadebet' => $coaDebetNotaDebet,
                'coakredit' => $coaKreditNotaDebet,
                'cekcoakredit' => $memoNotaDebetCoa['JURNAL']
            ];
            $notaDebetheader = (new NotaDebetHeader())->processStore($notaDebetRequest);
            $pelunasanPiutangHeader->notadebet_nobukti = $notaDebetheader->nobukti;
        }
        $cekStatusPelunasan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PELUNASAN')->where('text', 'NOTA DEBET')->first();
        if ($data['statuspelunasan'] == $cekStatusPelunasan->id) {
            $pengeluaranRequest = [
                'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                'pelanggan_id' => 0,
                'postingdari' => "ENTRY PELUNASAN PIUTANG",
                'dibayarke' => $data['agen'],
                'alatbayar_id' => $data['alatbayar_id'],
                'bank_id' => $data['bank_id'],
                'transferkeac' => "",
                'transferkean' => "",
                'transferkebank' => "",
                'userapproval' => "",
                'tglapproval' => "",

                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                "nominal_detail" => $nominalDetail,
                'coadebet' => $coaDebetPengeluaran,
                "keterangan_detail" => $keteranganDetail,
                'noinvoice' => $invoiceNobukti
            ];

            $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);

            $pelunasanPiutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;



            if ($data['notadebet_nobukti']) {
                $detailFifo = [
                    'nominal' => $nominal,
                    'agen_id' => $data['agen_id'],
                    'pelunasanpiutang_id' => $pelunasanPiutangHeader->id,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                    'notadebet_nobukti' => $data['notadebet_nobukti'],
                ];
                // dd($detailFifo);
                (new NotaDebetFifo())->processStoreNotFifo($detailFifo);
            }
        }
        $pelunasanPiutangHeader->save();

        $pelunasanPiutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelunasanPiutangHeader->getTable()),
            'postingdari' => 'ENTRY PELUNASAN PIUTANG HEADER',
            'idtrans' => $pelunasanPiutangHeader->id,
            'nobuktitrans' => $pelunasanPiutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pelunasanPiutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelunasanPiutangDetail->getTable()),
            'postingdari' => 'ENTRY PELUNASAN PIUTANG DETAIL',
            'idtrans' =>  $pelunasanPiutangHeaderLogTrail->id,
            'nobuktitrans' => $pelunasanPiutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pelunasanPiutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $pelunasanPiutangHeader;
    }


    public function processUpdate(PelunasanPiutangHeader $pelunasanPiutangHeader, array $data): PelunasanPiutangHeader
    {
        $nobuktiOld = $pelunasanPiutangHeader->nobukti;
        $previousStatusPelunasan = $pelunasanPiutangHeader->statuspelunasan;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PELUNASAN PIUTANG')->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'PELUNASAN PIUTANG BUKTI';
            $subGroup = 'PELUNASAN PIUTANG BUKTI';
            $querycek = DB::table('pelunasanpiutangheader')->from(
                DB::raw("pelunasanpiutangheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $pelunasanPiutangHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $pelunasanPiutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $pelunasanPiutangHeader->nobukti = $nobukti;
            $pelunasanPiutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $pelunasanPiutangHeader->nowarkat = $data['nowarkat'] ?? '';
        $pelunasanPiutangHeader->tglcair = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $pelunasanPiutangHeader->modifiedby = auth('api')->user()->name;
        $pelunasanPiutangHeader->info = html_entity_decode(request()->info);

        if (!$pelunasanPiutangHeader->save()) {
            throw new \Exception("Error Update pelunasan piutang header.");
        }

        $alatbayarGiro = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        PelunasanPiutangDetail::where('pelunasanpiutang_id', $pelunasanPiutangHeader->id)->lockForUpdate()->delete();

        $pelunasanPiutangDetails = [];

        $noWarkat = [];
        $tglJatuhTempo = [];
        $nominalDetail = [];
        $coaKredit = [];
        $keteranganDetail = [];
        $invoiceNobukti = [];
        $pelunasanNobukti = [];
        $bankId = [];

        $nominalPiutang = [];
        $nominalBayar = [];
        $nominalPotongan = [];
        $coaPotongan = [];
        $coaLebihBayar = [];
        $nominalLebihBayar = [];
        $coaDebetNotaKredit = [];
        $coaKreditNotaDebet = [];
        $coaDebetNotaDebet = [];
        $coaKreditNotaDebet = [];

        $notakredit = false;
        foreach ($data['potongan'] as $value) {
            if ($value != '0') {
                $notakredit = true;
                break;
            }
        }
        $notakreditpph = false;
        foreach ($data['potonganpph'] as $value) {
            if ($value != '0') {
                $notakreditpph = true;
                break;
            }
        }

        $notadebet = false;
        foreach ($data['nominallebihbayar'] as $value) {
            if ($value != '0') {
                $notadebet = true;
                break;
            }
        }

        $getJurnalPengeluaran = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'TIPENOTADEBET')->where('text', 'UANG DITERIMA DIMUKA')->first();
        $memoJurnalPengeluaran = json_decode($getJurnalPengeluaran->memo, true);

        $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $data['agen_id'])->first();
        if ($notadebet ==  true) {
            $notadebetCoaMemo = [];
            for ($i = 0; $i < count($data['statusnotadebet']); $i++) {
                if ($data['statusnotadebet'][$i] != '' && $data['statusnotadebet'][$i] != 0) {
                    $getNotaDebetCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->select('memo')
                        ->where('id', $data['statusnotadebet'][$i])->first();
                    $notadebetCoaMemo = json_decode($getNotaDebetCoa->memo, true);
                    break;
                }
            }
            $memoNotaDebetCoa = $notadebetCoaMemo;
        }

        if ($notakredit ==  true) {
            $getNotaKreditCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->select('memo')
                ->where('grp', 'JURNAL POTONGAN')->where('subgrp', 'DEBET')->first();
            $memoNotaKreditCoa = json_decode($getNotaKreditCoa->memo, true);
        }

        if ($notakreditpph ==  true) {

            $getNotaKreditCoaPPH = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->select('memo')
                ->where('grp', 'JURNAL POTONGAN PPH')->where('subgrp', 'DEBET')->first();
            $memoNotaKreditCoaPPH = json_decode($getNotaKreditCoaPPH->memo, true);
        }

        // dd($getcoadebetnk);
        $nominal = 0;
        for ($i = 0; $i < count($data['piutang_id']); $i++) {
            $piutang = PiutangHeader::where('nobukti', $data['piutang_nobukti'][$i])->first();

            $potongan = $data['potongan'][$i] ?? 0;
            $potonganpph = $data['potonganpph'][$i] ?? 0;
            if ($data['nominallebihbayar'][$i] > 0) {
                $getNominalLebih = $memoNotaDebetCoa['JURNAL'];
                $invoiceNobuktiND[] = $piutang->invoice_nobukti ?? '';
                $nominalPiutangND[] = $piutang->nominal;
                $nominalBayarND[] = $data['bayar'][$i];
                $nominalLebihBayar[] = $data['nominallebihbayar'][$i] ?? 0;
                $coaDebetNotaDebet[] = $getCoa->coa;
                $coaKreditNotaDebet[] = $memoNotaDebetCoa['JURNAL'];
            }

            if ($data['potongan'][$i] > 0) {
                $getCoaPotongan = $memoNotaKreditCoa['JURNAL'];
                $nominalPiutangNK[] = $piutang->nominal;
                $invoiceNobuktiNK[] = $piutang->invoice_nobukti ?? '';
                $keteranganPotongan[] = $data['keteranganpotongan'][$i];
                $nominalBayarNK[] = $data['bayar'][$i];
                $nominalPotongan[] = $potongan;
                $coaPotongan[] = $memoNotaKreditCoa['JURNAL'] ?? '';
                $coaKreditNotaKredit[] = $getCoa->coa; //$memoNotaKreditCoa['JURNAL'];
            }
            if ($data['potonganpph'][$i] > 0) {
                $getCoaPotonganpph = $memoNotaKreditCoaPPH['JURNAL'];
                $nominalPiutangNKpph[] = $piutang->nominal;
                $invoiceNobuktiNKpph[] = $piutang->invoice_nobukti ?? '';
                $nominalBayarNKpph[] = $data['bayar'][$i];
                $nominalPotonganpph[] = $potonganpph;
                $keteranganPotonganPPH[] = $data['keteranganpotonganpph'][$i];
                $coaPotonganpph[] = $memoNotaKreditCoaPPH['JURNAL'] ?? '';
                $coaKreditNotaKreditpph[] = $getCoa->coa; //$memoNotaKreditCoa['JURNAL'];
            }

            $pelunasanPiutangDetail = (new PelunasanPiutangDetail())->processStore($pelunasanPiutangHeader, [
                'nominal' => $data['bayar'][$i],
                'piutang_nobukti' => $piutang->nobukti,
                'keterangan' => $data['keterangan'][$i] ?? '',
                'potongan' => $data['potongan'][$i] ?? '',
                'potonganpph' => $data['potonganpph'][$i] ?? '',
                'coapotongan' => $getCoaPotongan ?? '',
                'coapotonganpph' => $getCoaPotonganpph ?? '',
                'invoice_nobukti' => $piutang->invoice_nobukti ?? '',
                'keteranganpotongan' => $data['keteranganpotongan'][$i] ?? '',
                'keteranganpotonganpph' => $data['keteranganpotonganpph'][$i] ?? '',
                'nominallebihbayar' => $data['nominallebihbayar'][$i] ?? '',
                'coalebihbayar' => $getNominalLebih ?? '',
                'statusnotadebet' => $data['statusnotadebet'][$i] ?? '',
                'statusnotakredit' => $data['statusnotakredit'][$i] ?? '',
            ]);

            $pelunasanPiutangDetails[] = $pelunasanPiutangDetail->toArray();

            $noWarkat[] = $data['nowarkat'] ?? '';
            if ($pelunasanPiutangHeader->alatbayar_id != $alatbayarGiro->id) {
                $tglJatuhTempo[] = $pelunasanPiutangHeader->tglbukti;
            } else {
                $top = intval($getCoa->top);
                $dateNow = date('Y-m-d');
                $nextDay = date('d-m-Y', strtotime($dateNow . " +$top day"));
                $tglJatuhTempo[] = $data['tgljatuhtempo'];
            }
            // $tglJatuhTempo[] = $pelunasanPiutangHeader->tglbukti;
            $nominalDetail[] = $data['bayar'][$i];

            if ($pelunasanPiutangHeader->agen_id != 0) {
                $coaKredit[] =  $piutang->coadebet;
            } else {
                $getcoainvoice = db::table('invoiceemkldetail')->from(db::raw("invoiceemkldetail as a with (readuncommitted)"))
                    ->select('a.coadebet')
                    ->join(db::raw("invoiceemklheader as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                    ->where('b.piutang_nobukti', $piutang->nobukti)
                    ->first();
                $coaKredit[] = $getcoainvoice->coadebet;
            }
            $keteranganDetail[] = $data['keterangan'][$i];
            $invoiceNobukti[] = $piutang->invoice_nobukti ?? '';
            $pelunasanNobukti[] = $pelunasanPiutangHeader->nobukti;
            $bankId[] = $pelunasanPiutangHeader->bank_id;
            $coaDebetPengeluaran[] = $memoJurnalPengeluaran['JURNAL'];
            $nominal = $nominal + $data['bayar'][$i];

            // if ($notakreditpph) {
            //     if ($potonganpph > 0) {

            //         $noWarkat[] = $data['nowarkat'] ?? '';
            //         $nominalDetail[] = -$potonganpph;
            //         $coaKredit[] = $memoNotaKreditCoaPPH['JURNAL'] ?? '';
            //         $keteranganDetail[] = $data['keteranganpotonganpph'][$i];
            //         $invoiceNobukti[] = $piutang->invoice_nobukti ?? '';
            //         $pelunasanNobukti[] = $pelunasanPiutangHeader->nobukti;
            //         if ($data['alatbayar_id'] != $alatbayarGiro->id) {
            //             $tglJatuhTempo[] = $data['tglbukti'];
            //         } else {
            //             $top = intval($getCoa->top);
            //             $dateNow = date('Y-m-d');
            //             $nextDay = date('d-m-Y', strtotime($dateNow . " +$top day"));
            //             $tglJatuhTempo[] = $data['tgljatuhtempo'];
            //         }
            //     }
            // }
        }

        if ($pelunasanPiutangHeader->notakreditpph_nobukti != '-') {
            if ($notakreditpph) {
                $get = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))
                    ->select('id')
                    ->where('nobukti', $pelunasanPiutangHeader->notakreditpph_nobukti)->first();
                $notaKreditRequest = [
                    'tanpaprosesnobukti' => 1,
                    'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                    'agen_id' => $data['agen_id'],
                    'agen' => $data['agen'],
                    'bank_id' => $pelunasanPiutangHeader->bank_id,
                    'alatbayar_id' => $pelunasanPiutangHeader->alatbayar_id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                    'nowarkat' => $data['nowarkat'] ?? '',
                    'invoice_nobukti' => $invoiceNobuktiNKpph,
                    'nominalpiutang' => $nominalPiutangNKpph,
                    'nominal' => $nominalBayarNKpph,
                    'potongan' => $nominalPotonganpph,
                    'coakredit' => $coaKreditNotaKreditpph,
                    'coadebet' => $coaPotonganpph,
                    'keteranganpotongan' => $keteranganPotonganPPH,
                    'cekcoadebet' => $memoNotaKreditCoaPPH['JURNAL']
                ];
                $newNotaKredit = new NotaKreditHeader();
                $newNotaKredit = $newNotaKredit->findAll($get->id);
                $getNotaKredit = (new NotaKreditHeader())->processUpdate($newNotaKredit, $notaKreditRequest);
                $pelunasanPiutangHeader->notakreditpph_nobukti = $getNotaKredit->nobukti;
            } else {
                $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->notakreditpph_nobukti)->first();
                if (isset($getNotaKredit)) {
                    (new NotaKreditHeader())->processDestroy($getNotaKredit->id, 'DELETE PELUNASAN PIUTANG');
                    $pelunasanPiutangHeader->notakreditpph_nobukti = '-';
                }
            }
        } else {
            if ($notakreditpph) {
                $notaKreditRequest = [
                    'tanpaprosesnobukti' => 1,
                    'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                    'agen_id' => $data['agen_id'],
                    'agen' => $data['agen'],
                    'bank_id' => $pelunasanPiutangHeader->bank_id,
                    'alatbayar_id' => $pelunasanPiutangHeader->alatbayar_id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                    'nowarkat' => $data['nowarkat'] ?? '',
                    'invoice_nobukti' => $invoiceNobuktiNKpph,
                    'nominalpiutang' => $nominalPiutangNKpph,
                    'nominal' => $nominalBayarNKpph,
                    'potongan' => $nominalPotonganpph,
                    'coakredit' => $coaKreditNotaKreditpph,
                    'coadebet' => $coaPotonganpph,
                    'keteranganpotongan' => $keteranganPotonganPPH,
                    'cekcoadebet' => $memoNotaKreditCoaPPH['JURNAL']
                ];
                $notaKreditHeader = (new NotaKreditHeader())->processStore($notaKreditRequest);
                $pelunasanPiutangHeader->notakreditpph_nobukti = $notaKreditHeader->nobukti;
            }
        }

        if ($pelunasanPiutangHeader->penerimaan_nobukti != '-') {
            $get = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->where('nobukti', $pelunasanPiutangHeader->penerimaan_nobukti)->first();
            $penerimaanRequest = [
                'pelanggan_id' => 0,
                'agen_id' => $pelunasanPiutangHeader->agen_id,
                'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                'postingdari' => 'EDIT PELUNASAN PIUTANG',
                'diterimadari' => $data['agen'],
                'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                'bank_id' => $data['bank_id'],
                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                'nominal_detail' => $nominalDetail,
                'coakredit' => $coaKredit,
                'keterangan_detail' => $keteranganDetail,
                'invoice_nobukti' => $invoiceNobukti,
                'pelunasanpiutang_nobukti' => $pelunasanNobukti

            ];
            $newPenerimaan = new PenerimaanHeader();
            $newPenerimaan = $newPenerimaan->findAll($get->id);
            $getPenerimaan = (new PenerimaanHeader())->processUpdate($newPenerimaan, $penerimaanRequest);
            $pelunasanPiutangHeader->penerimaan_nobukti = $getPenerimaan->nobukti;
        }

        if ($pelunasanPiutangHeader->penerimaangiro_nobukti != '-') {

            $get = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                ->select('id')
                ->where('nobukti', $pelunasanPiutangHeader->penerimaangiro_nobukti)->first();
            $penerimaanGiroRequest = [
                'isUpdate' => 1,
                'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                'agen_id' => $pelunasanPiutangHeader->agen_id,
                'postingdari' => 'EDIT PELUNASAN PIUTANG',
                'diterimadari' => $data['agen'],
                'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                'nominal' => $nominalDetail,
                'coakredit' => $coaKredit,
                'keterangan_detail' => $keteranganDetail,
                'invoice_nobukti' => $invoiceNobukti,
                'pelunasanpiutang_nobukti' => $pelunasanNobukti,
                'bank_id' => $bankId

            ];

            $newPenerimaanGiro = new PenerimaanGiroHeader();
            $newPenerimaanGiro = $newPenerimaanGiro->findAll($get->id);
            $getPenerimaanGiro = (new PenerimaanGiroHeader())->processUpdate($newPenerimaanGiro, $penerimaanGiroRequest);
            $pelunasanPiutangHeader->penerimaangiro_nobukti = $getPenerimaanGiro->nobukti;
        }


        if ($pelunasanPiutangHeader->notakredit_nobukti != '-') {

            if ($notakredit) {

                $get = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))
                    ->select('id')
                    ->where('nobukti', $pelunasanPiutangHeader->notakredit_nobukti)->first();
                $notaKreditRequest = [
                    'tanpaprosesnobukti' => 1,
                    'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                    'agen_id' => $data['agen_id'],
                    'agen' => $data['agen'],
                    'bank_id' => $pelunasanPiutangHeader->bank_id,
                    'alatbayar_id' => $pelunasanPiutangHeader->alatbayar_id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                    'nowarkat' => $data['nowarkat'] ?? '',
                    'invoice_nobukti' => $invoiceNobuktiNK,
                    'nominalpiutang' => $nominalPiutangNK,
                    'nominal' => $nominalBayarNK,
                    'potongan' => $nominalPotongan,
                    'coakredit' => $coaKreditNotaKredit,
                    'coadebet' => $coaPotongan,
                    'keteranganpotongan' => $keteranganPotongan,
                    'cekcoadebet' => $memoNotaKreditCoa['JURNAL']
                ];

                // dd($notaKreditRequest);

                $newNotaKredit = new NotaKreditHeader();
                $newNotaKredit = $newNotaKredit->findAll($get->id);
                $getNotaKredit = (new NotaKreditHeader())->processUpdate($newNotaKredit, $notaKreditRequest);
                $pelunasanPiutangHeader->notakredit_nobukti = $getNotaKredit->nobukti;
            } else {
                $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->notakredit_nobukti)->first();

                (new NotaKreditHeader())->processDestroy($getNotaKredit->id, 'DELETE PELUNASAN PIUTANG');
                $pelunasanPiutangHeader->notakredit_nobukti = '-';
            }
        } else {
            if ($notakredit) {
                $notaKreditRequest = [
                    'tanpaprosesnobukti' => 1,
                    'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                    'agen_id' => $data['agen_id'],
                    'agen' => $data['agen'],
                    'bank_id' => $pelunasanPiutangHeader->bank_id,
                    'alatbayar_id' => $pelunasanPiutangHeader->alatbayar_id,
                    'postingdari' => 'EDIT PELUNASAN PIUTANG',
                    'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                    'nowarkat' => $data['nowarkat'] ?? '',
                    'invoice_nobukti' => $invoiceNobuktiNK,
                    'nominalpiutang' => $nominalPiutangNK,
                    'nominal' => $nominalBayarNK,
                    'potongan' => $nominalPotongan,
                    'coakredit' => $coaKreditNotaKredit,
                    'coadebet' => $coaPotongan,
                    'keteranganpotongan' => $keteranganDetail,
                    'cekcoadebet' => $memoNotaKreditCoa['JURNAL']
                ];
                $notaKreditHeader = (new NotaKreditHeader())->processStore($notaKreditRequest);
                $pelunasanPiutangHeader->notakredit_nobukti = $notaKreditHeader->nobukti;
            }
        }

        $statuspelunasan = $data['statuspelunasan'] ?? 0;
        $parameter = new Parameter();
        $idstatuspelunasan = $parameter->cekId('PELUNASAN', 'PELUNASAN', 'NOTA DEBET') ?? 0;

        if ($idstatuspelunasan != $statuspelunasan) {
            if ($pelunasanPiutangHeader->notadebet_nobukti != '-') {
                if ($notadebet) {
                    $get = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))
                        ->select('id')
                        ->where('nobukti', $pelunasanPiutangHeader->notadebet_nobukti)->first();
                    $notaDebetRequest = [
                        'tanpaprosesnobukti' => 1,
                        'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                        'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                        'agen_id' => $data['agen_id'],
                        'bank_id' => $pelunasanPiutangHeader->bank_id,
                        'alatbayar_id' => $pelunasanPiutangHeader->alatbayar_id,
                        'agen' => $data['agen'],
                        'postingdari' => 'EDIT PELUNASAN PIUTANG',
                        'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                        'nowarkat' => $data['nowarkat'] ?? '',
                        'invoice_nobukti' => $invoiceNobuktiND,
                        'nominalpiutang' => $nominalPiutangND,
                        'nominal' => $nominalBayarND,
                        'nominallebihbayar' => $nominalLebihBayar,
                        'coadebet' => $coaDebetNotaDebet,
                        'coakredit' => $coaKreditNotaDebet,
                        'cekcoakredit' => $memoNotaDebetCoa['JURNAL']

                    ];

                    $newNotaDebet = new NotaDebetHeader();
                    $newNotaDebet = $newNotaDebet->findAll($get->id);
                    $getNotaDebet = (new NotaDebetHeader())->processUpdate($newNotaDebet, $notaDebetRequest);
                    $pelunasanPiutangHeader->notadebet_nobukti = $getNotaDebet->nobukti;
                } else {
                    $getNotaDebet = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->notadebet_nobukti)->first();
                    (new NotaDebetHeader())->processDestroy($getNotaDebet->id, 'DELETE PELUNASAN PIUTANG');
                    $pelunasanPiutangHeader->notadebet_nobukti = '-';
                }
            } else {
                if ($notadebet) {
                    $notaDebetRequest = [
                        'tanpaprosesnobukti' => 1,
                        'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                        'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                        'agen_id' => $data['agen_id'],
                        'bank_id' => $pelunasanPiutangHeader->bank_id,
                        'alatbayar_id' => $pelunasanPiutangHeader->alatbayar_id,
                        'agen' => $data['agen'],
                        'postingdari' => 'EDIT PELUNASAN PIUTANG',
                        'tgllunas' => $pelunasanPiutangHeader->tglbukti,
                        'nowarkat' => $data['nowarkat'] ?? '',
                        'invoice_nobukti' => $invoiceNobuktiND,
                        'nominalpiutang' => $nominalPiutangND,
                        'nominal' => $nominalBayarND,
                        'nominallebihbayar' => $nominalLebihBayar,
                        'coadebet' => $coaDebetNotaDebet,
                        'coakredit' => $coaKreditNotaDebet,
                        'cekcoakredit' => $memoNotaDebetCoa['JURNAL']
                    ];
                    $notaDebetheader = (new NotaDebetHeader())->processStore($notaDebetRequest);
                    $pelunasanPiutangHeader->notadebet_nobukti = $notaDebetheader->nobukti;
                }
            }
        }

        if ($pelunasanPiutangHeader->pengeluaran_nobukti != '-' && $pelunasanPiutangHeader->pengeluaran_nobukti != null) {
            $get = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
                ->where('nobukti', $pelunasanPiutangHeader->penerimaan_nobukti)->first();
            $pengeluaranRequest = [
                'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                'pelanggan_id' => 0,
                'postingdari' => "EDIT PELUNASAN PIUTANG",
                'dibayarke' => $data['agen'],
                'alatbayar_id' => $data['alatbayar_id'],
                'bank_id' => $data['bank_id'],
                'transferkeac' => "",
                'transferkean' => "",
                'transferkebank' => "",
                'userapproval' => "",
                'tglapproval' => "",

                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                "nominal_detail" => $nominalDetail,
                'coadebet' => $coaDebetPengeluaran,
                "keterangan_detail" => $keteranganDetail,
                'noinvoice' => $invoiceNobukti
            ];

            $pengeluaranHeader = PengeluaranHeader::where('nobukti', $pelunasanPiutangHeader->pengeluaran_nobukti)->first();
            $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader, $pengeluaranRequest);

            $pelunasanPiutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;
            $pelunasanPiutangHeader->save();
        }
        $cekStatusPelunasan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PELUNASAN')->where('text', 'NOTA DEBET')->first();

        // if ($data['statuspelunasan'] == $cekStatusPelunasan->id) {
        //     $pengeluaranRequest = [
        //         'tglbukti' => $pelunasanPiutangHeader->tglbukti,
        //         'pelanggan_id' => 0,
        //         'postingdari' => "EDIT PELUNASAN PIUTANG",
        //         'dibayarke' => $data['agen'],
        //         'alatbayar_id' => $data['alatbayar_id'],
        //         'bank_id' => $data['bank_id'],
        //         'transferkeac' => "",
        //         'transferkean' => "",
        //         'transferkebank' => "",
        //         'userapproval' => "",
        //         'tglapproval' => "",

        //         'nowarkat' => $noWarkat,
        //         'tgljatuhtempo' => $tglJatuhTempo,
        //         "nominal_detail" => $nominalDetail,
        //         'coadebet' => $coaDebetPengeluaran,
        //         "keterangan_detail" => $keteranganDetail,
        //         'noinvoice' => $invoiceNobukti
        //     ];

        //     $pengeluaranHeader = PengeluaranHeader::where('nobukti', $pelunasanPiutangHeader->pengeluaran_nobukti)->first();
        //     $pengeluaranHeader = (new PengeluaranHeader())->processUpdate($pengeluaranHeader, $pengeluaranRequest);

        //     $pelunasanPiutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;

        //     $pelunasannotadebet = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
        //         ->select('a.id')
        //         ->where('grp', 'PELUNASAN')
        //         ->where('subgrp', 'PELUNASAN')
        //         ->where('text', 'NOTA DEBET')
        //         ->first()->id ?? 0;




        //     $querypelunasannotadebet = db::table("pelunasanpiutangheader")->from(db::raw("pelunasanpiutangheader a with (readuncommitted)"))
        //         ->select(
        //             'a.nobukti',
        //             db::raw("sum(b.nominal) as nominal"),
        //             db::raw("max(a.id) as id"),
        //             db::raw("max(a.agen_id) as agen_id")
        //         )
        //         ->join(db::raw("pelunasanpiutangdetail b with(readuncommitted)"), 'a.nobukti', 'b.nobukti')
        //         ->where('a.statuspelunasan', $pelunasannotadebet)
        //         ->whereRaw("a.id>=" . $pelunasanPiutangHeader->id)
        //         ->groupby('a.nobukti');

        //     DB::delete(DB::raw("delete notadebetfifo from notadebetfifo a 
        //                         inner join pelunasanpiutangheader b on a.pelunasanpiutang_nobukti=b.nobukti
        //                         where b.statuspelunasan=" . $pelunasannotadebet . "
        //                         and b.id>=" . $pelunasanPiutangHeader->id));


        //     $datadetail = json_decode($querypelunasannotadebet->get(), true);
        //     foreach ($datadetail as $item) {

        //         $detailFifo = [
        //             'nominal' => $item['nominal'] ?? 0,
        //             'agen_id' => $item['agen_id'] ?? 0,
        //             'pelunasanpiutang_id' => $item['id'] ?? 0,
        //             'pelunasanpiutang_nobukti' => $item['nobukti'] ?? '',
        //             'notadebet_nobukti' => $pelunasanPiutangHeader->notadebet_nobukti ?? '',
        //         ];
        //         (new NotaDebetFifo())->processStoreNotFifo($detailFifo);
        //     }
        // }

        $cekStatusPelunasan = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'PELUNASAN')->where('text', 'NOTA DEBET')->first();
        if ($data['statuspelunasan'] == $cekStatusPelunasan->id) {
            $pengeluaranRequest = [
                'tglbukti' => $pelunasanPiutangHeader->tglbukti,
                'pelanggan_id' => 0,
                'postingdari' => "ENTRY PELUNASAN PIUTANG",
                'dibayarke' => $data['agen'],
                'alatbayar_id' => $data['alatbayar_id'],
                'bank_id' => $data['bank_id'],
                'transferkeac' => "",
                'transferkean' => "",
                'transferkebank' => "",
                'userapproval' => "",
                'tglapproval' => "",

                'nowarkat' => $noWarkat,
                'tgljatuhtempo' => $tglJatuhTempo,
                "nominal_detail" => $nominalDetail,
                'coadebet' => $coaDebetPengeluaran,
                "keterangan_detail" => $keteranganDetail,
                'noinvoice' => $invoiceNobukti
            ];

            $pengeluaranHeader = (new PengeluaranHeader())->processStore($pengeluaranRequest);

            $pelunasanPiutangHeader->pengeluaran_nobukti = $pengeluaranHeader->nobukti;



            if ($data['notadebet_nobukti']) {
                db::table('notadebetfifo')->where('pelunasanpiutang_nobukti', $pelunasanPiutangHeader->nobukti)->delete();
                $detailFifo = [
                    'nominal' => $nominal,
                    'agen_id' => $data['agen_id'],
                    'pelunasanpiutang_id' => $pelunasanPiutangHeader->id,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutangHeader->nobukti,
                    'notadebet_nobukti' => $data['notadebet_nobukti'],
                ];
                (new NotaDebetFifo())->processStoreNotFifo($detailFifo);
            }
        }


        $pelunasanPiutangHeader->save();

        $pelunasanPiutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelunasanPiutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY PELUNASAN PIUTANG HEADER',
            'idtrans' => $pelunasanPiutangHeader->id,
            'nobuktitrans' => $pelunasanPiutangHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pelunasanPiutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pelunasanPiutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT PELUNASAN PIUTANG DETAIL',
            'idtrans' => $pelunasanPiutangHeaderLogTrail->id,
            'nobuktitrans' => $pelunasanPiutangHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $pelunasanPiutangDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        return $pelunasanPiutangHeader;
    }


    public function processDestroy($id, $postingDari = ''): PelunasanPiutangHeader
    {
        $pelunasannotadebet = db::table("parameter")->from(db::raw("parameter a with (readuncommitted)"))
            ->select('a.id')
            ->where('grp', 'PELUNASAN')
            ->where('subgrp', 'PELUNASAN')
            ->where('text', 'NOTA DEBET')
            ->first()->id ?? 0;

        $querypelunasanheader = db::table("pelunasanpiutangheader")->from(db::raw("pelunasanpiutangheader a with (readuncommitted)"))
            ->select(
                'a.nobukti',
            )
            ->where('a.statuspelunasan', $pelunasannotadebet)
            ->whereRaw("a.id=" . $id)
            ->first();

        $nobuktipelunasan = $querypelunasanheader->nobukti ?? '';

        db::table('notadebetfifo')->where('pelunasanpiutang_nobukti', $nobuktipelunasan)->delete();

        $pelunasanPiutangDetails = PelunasanPiutangDetail::lockForUpdate()->where('pelunasanpiutang_id', $id)->get();

        $pelunasanPiutangHeader = new PelunasanPiutangHeader();
        $pelunasanPiutangHeader = $pelunasanPiutangHeader->lockAndDestroy($id);


        // if (isset($querypelunasanheader)) {


        //     $querypelunasannotadebet = db::table("pelunasanpiutangheader")->from(db::raw("pelunasanpiutangheader a with (readuncommitted)"))
        //         ->select(
        //             'a.nobukti',
        //             db::raw("sum(b.nominal) as nominal"),
        //             db::raw("max(a.id) as id"),
        //             db::raw("max(a.agen_id) as agen_id")
        //         )
        //         ->join(db::raw("pelunasanpiutangdetail b with(readuncommitted)"), 'a.nobukti', 'b.nobukti')
        //         ->where('a.statuspelunasan', $pelunasannotadebet)
        //         ->whereRaw("a.id>" . $id)
        //         ->groupby('a.nobukti');

        //     DB::delete(DB::raw("delete notadebetfifo from notadebetfifo a 
        //                             inner join pelunasanpiutangheader b on a.pelunasanpiutang_nobukti=b.nobukti
        //                             where b.statuspelunasan=" . $pelunasannotadebet . "
        //                             and b.id>" . $id));


        //     $datadetail = json_decode($querypelunasannotadebet->get(), true);
        //     foreach ($datadetail as $item) {

        //         $detailFifo = [
        //             'nominal' => $item['nominal'] ?? 0,
        //             'agen_id' => $item['agen_id'] ?? 0,
        //             'pelunasanpiutang_id' => $item['id'] ?? 0,
        //             'pelunasanpiutang_nobukti' => $item['nobukti'] ?? '',
        //         ];
        //         (new NotaDebetFifo())->processStore($detailFifo);
        //     }
        // }



        $pelunasanPiutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $pelunasanPiutangHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $pelunasanPiutangHeader->id,
            'nobuktitrans' => $pelunasanPiutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pelunasanPiutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PELUNASANPIUTANGDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $pelunasanPiutangHeaderLogTrail['id'],
            'nobuktitrans' => $pelunasanPiutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pelunasanPiutangDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        if ($pelunasanPiutangHeader->penerimaan_nobukti != '-') {
            $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->penerimaan_nobukti)->first();
            if (isset($getPenerimaan)) {
                if ($getPenerimaan != null) {
                    (new PenerimaanHeader())->processDestroy($getPenerimaan->id, $postingDari);
                }
            }
        }
        if ($pelunasanPiutangHeader->penerimaangiro_nobukti != '-') {
            $getGiro = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->penerimaangiro_nobukti)->first();
            if (isset($getGiro)) {
                if ($getGiro != null) {
                    (new PenerimaanGiroHeader())->processDestroy($getGiro->id, $postingDari);
                }
            }
        }

        if ($pelunasanPiutangHeader->notakredit_nobukti != '-') {
            $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->notakredit_nobukti)->first();
            if (isset($getNotaKredit)) {

                if ($getNotaKredit != null) {
                    (new NotaKreditHeader())->processDestroy($getNotaKredit->id, $postingDari);
                }
            }
        }

        if ($pelunasanPiutangHeader->notakreditpph_nobukti != '-') {
            $getNotaKredit = NotaKreditHeader::from(DB::raw("notakreditheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->notakreditpph_nobukti)->first();
            if (isset($getNotaKredit)) {

                if ($getNotaKredit != null) {
                    (new NotaKreditHeader())->processDestroy($getNotaKredit->id, $postingDari);
                }
            }
        }

        $statuspelunasan = $data['statuspelunasan'] ?? 0;
        $parameter = new Parameter();
        $idstatuspelunasan = $parameter->cekId('PELUNASAN', 'PELUNASAN', 'NOTA DEBET') ?? 0;

        if ($idstatuspelunasan != $statuspelunasan) {
            if ($pelunasanPiutangHeader->notadebet_nobukti != '-') {
                $getNotaDebet = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->notadebet_nobukti)->first();
                if (isset($getNotaDebet)) {
                    if ($getNotaDebet != null) {
                        (new NotaDebetHeader())->processDestroy($getNotaDebet->id, $postingDari);
                    }
                }
            }
        }
        if ($pelunasanPiutangHeader->pengeluaran_nobukti != '-' && $pelunasanPiutangHeader->pengeluaran_nobukti != null) {
            $getPengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))->where('nobukti', $pelunasanPiutangHeader->pengeluaran_nobukti)->first();
            if ($getPengeluaran != null) {
                (new PengeluaranHeader())->processDestroy($getPengeluaran->id, $postingDari);
            }
        }

        return $pelunasanPiutangHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(
                'pelunasanpiutangheader.id',
                'pelunasanpiutangheader.nobukti',
                'pelunasanpiutangheader.tglbukti',
                'pelunasanpiutangheader.penerimaan_nobukti',
                'pelunasanpiutangheader.penerimaangiro_nobukti',
                'pelunasanpiutangheader.notadebet_nobukti',
                'pelunasanpiutangheader.notakredit_nobukti',
                'pelunasanpiutangheader.notakreditpph_nobukti',
                'pelunasanpiutangheader.jumlahcetak',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'bank.namabank as bank_id',
                'agen.namaagen as agen_id',
                'alatbayar.namaalatbayar as alatbayar_id',
                DB::raw("'Bukti Pelunasan Piutang' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pelunasanpiutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pelunasanpiutangheader.alatbayar_id', 'alatbayar.id')
            ->where("$this->table.id", $id);

        $data = $query->first();
        return $data;
    }
}
