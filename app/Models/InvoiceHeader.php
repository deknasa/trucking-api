<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class InvoiceHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("invoiceheader with (readuncommitted)"))
            ->select(
                'invoiceheader.id',
                'invoiceheader.nobukti',
                'invoiceheader.tglbukti',
                'invoiceheader.nominal',
                'invoiceheader.tglterima',
                'invoiceheader.tgljatuhtempo',
                'agen.namaagen as agen',
                'jenisorder.keterangan as jenisorder_id',
                'invoiceheader.piutang_nobukti',
                'statusapproval.memo as statusapproval',
                'statuscetak.memo as statuscetak',
                'invoiceheader.userapproval',
                DB::raw('(case when (year(invoiceheader.tglapproval) <= 2000) then null else invoiceheader.tglapproval end ) as tglapproval'),
                'invoiceheader.userbukacetak',
                'invoiceheader.jumlahcetak',
                DB::raw('(case when (year(invoiceheader.tglbukacetak) <= 2000) then null else invoiceheader.tglbukacetak end ) as tglbukacetak'),
                'invoiceheader.modifiedby',
                'invoiceheader.created_at',
                'invoiceheader.updated_at',
                db::raw("cast((format(piutang.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpiutangheader"),
                db::raw("cast(cast(format((cast((format(piutang.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpiutangheader"),
            )
            ->leftJoin(DB::raw("piutangheader as piutang with (readuncommitted)"), 'invoiceheader.piutang_nobukti', '=', 'piutang.nobukti')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(invoiceheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(invoiceheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("invoiceheader.statuscetak", $statusCetak);
        }
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();
        return $data;
    }


    public function cekvalidasiaksi($nobukti)
    {

        $error = new Error();
        $keteranganerror = $error->cekKeteranganError('SATL2') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';


        $pelunasanPiutang = DB::table('pelunasanpiutangdetail')
            ->from(
                DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.invoice_nobukti'
            )
            ->where('a.invoice_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti pelunasan piutang <b>' . $pelunasanPiutang->nobukti . '</b> <br> ' . $keterangantambahanerror,

                // 'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'SATL2'
            ];
            goto selesai;
        }

        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';

        $invoice = DB::table('invoiceheader')
            ->from(
                DB::raw("invoiceheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.piutang_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.piutang_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($invoice)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No Bukti <b>' . $invoice->piutang_nobukti . '</b><br>' . $keteranganerror . ' <br> ' . $keterangantambahanerror,

                // 'keterangan' => 'Approval Jurnal ' . $invoice->piutang_nobukti,
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

    public function findAll($id)
    {
        $query = InvoiceHeader::from(DB::raw("invoiceheader with (readuncommitted)"))
            ->select(
                'invoiceheader.*',
                'agen.namaagen as agen',
                'jenisorder.keterangan as jenisorder'
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id')
            ->where('invoiceheader.id', $id);
        $data = $query->first();
        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                $this->table.nominal,
                $this->table.tglterima,
                $this->table.tgljatuhtempo,
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'cabang.namacabang as cabang_id',
                $this->table.piutang_nobukti,
                'statusapproval.text as statusapproval',
                $this->table.userapproval,
                $this->table.tglapproval,
                'statuscetak.text as statuscetak',
                $this->table.userbukacetak,
                $this->table.tglbukacetak,
                $this->table.jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
                "
                )
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'invoiceheader.cabang_id', 'cabang.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('nominal')->nullable();
            $table->date('tglterima')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->string('agen_id')->default();
            $table->string('jenisorder_id')->default();
            $table->string('cabang_id')->default();
            $table->string('piutang_nobukti')->default();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->default();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'nominal', 'tglterima', 'tgljatuhtempo', 'agen_id', 'jenisorder_id', 'cabang_id', 'piutang_nobukti', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function getSpSearch($request)
    {

        $statusjeniskendaraan = request()->statusjeniskendaraan;
        $jenisTangki = DB::table('parameter')->from(DB::raw("parameter as a with (readuncommitted)"))
            ->select('a.id')
            ->where('a.grp', '=', 'STATUS JENIS KENDARAAN')
            ->where('a.text', '=', 'TANGKI')
            ->first();
        $statusbatalmuat = db::table('parameter')->from(db::raw("parameter with (readuncommitted)"))
            ->select('id')
            ->where('grp', 'STATUS BATAL MUAT')
            ->where('subgrp', 'STATUS BATAL MUAT')
            ->where('text', 'BATAL MUAT')
            ->first()->id ?? 0;


        $kotapelabuhan = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'PELABUHAN CABANG')
            ->where('subgrp', '=', 'PELABUHAN CABANG')
            ->first();

        $pilihanperiodeotobon = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('grp', '=', 'STATUS PILIHAN INVOICE')
            ->where('subgrp', '=', 'STATUS PILIHAN INVOICE')
            ->where('text', '=', 'PERIODE OTOBON')
            ->first();

        $pilihanperiodeotobon = $pilihanperiodeotobon->id ?? 0;


        $pilihanperiodepisahbulan = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('grp', '=', 'STATUS PILIHAN INVOICE')
            ->where('subgrp', '=', 'STATUS PILIHAN INVOICE')
            ->where('text', '=', 'PERIODE PISAH BULAN')
            ->first();
        $pilihanperiodepisahbulan = $pilihanperiodepisahbulan->id ?? 0;


        $statuslongtrip = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('grp', '=', 'STATUS LONGTRIP')
            ->where('subgrp', '=', 'STATUS LONGTRIP')
            ->where('text', '=', 'LONGTRIP')
            ->first();

        $statusperalihan = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('grp', '=', 'STATUS PERALIHAN')
            ->where('subgrp', '=', 'STATUS PERALIHAN')
            ->where('text', '=', 'PERALIHAN')
            ->first();

        $statuslangsir = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.id'
            )
            ->where('grp', '=', 'STATUS LANGSIR')
            ->where('subgrp', '=', 'STATUS LANGSIR')
            ->where('text', '=', 'LANGSIR')
            ->first();

        $statusfull = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'STATUS CONTAINER')
            ->where('subgrp', '=', 'STATUS CONTAINER FULL')
            ->first();

        $statusempty = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'STATUS CONTAINER')
            ->where('subgrp', '=', 'STATUS CONTAINER EMPTY')
            ->first();

        $statusfullempty = DB::table('parameter')->from(
            db::raw("parameter a with (readuncommitted)")
        )
            ->select(
                'a.text'
            )
            ->where('grp', '=', 'STATUS CONTAINER')
            ->where('subgrp', '=', 'STATUS CONTAINER FULL EMPTY')
            ->first();

        $kotapelabuhanid = $kotapelabuhan->text ?? 0;
        $pilihanperiode = $request->pilihanperiode ?? 0;

        $tempdaripelabuhan = '##tempdaripelabuhan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdaripelabuhan, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
        });

        $querydaripelabuhan = DB::table('suratpengantar')->from(
            db::raw("suratpengantar a with (readuncommitted)")
        )
            ->select(
                'a.jobtrucking'
            )
            ->whereRaw("(a.dari_id=" . $kotapelabuhanid . " or a.statuslangsir=" . $statuslangsir->id . ")")
            ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
            ->where('a.agen_id', $request->agen_id)
            ->where('a.jenisorder_id', $request->jenisorder_id)
            // ->where('nobukti', 'TRP 0112/VI/2024')
            ->groupBy('a.jobtrucking');

        DB::table($tempdaripelabuhan)->insertUsing([
            'jobtrucking',
        ], $querydaripelabuhan);
        // dd($querydaripelabuhan->get());


        // dump($pilihanperiodeotobon);
        // dd($pilihanperiode);
        if ($statusjeniskendaraan == $jenisTangki->id) {
            $queryhasil = DB::table('suratpengantar')->from(
                db::raw("suratpengantar a with (readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    db::raw("max(a.totalomset) as nominal"),
                    db::raw("max(a.nobukti) as suratpengantar_nobukti")
                )
                ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                ->where('a.agen_id', $request->agen_id)
                ->where('a.statusjeniskendaraan', $statusjeniskendaraan)
                ->groupBy('a.jobtrucking');
        } else {

            if ($pilihanperiodeotobon == $pilihanperiode) {

                $tempkepelabuhan = '##tempkepelabuhan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkepelabuhan, function ($table) {
                    $table->string('jobtrucking', 1000)->nullable();
                    $table->double('nominal')->nullable();
                    $table->string('suratpengantar_nobukti', 1000)->nullable();
                });


                $fullempty = db::table('parameter')->from(db::raw("parameter with (readuncommitted)"))
                    ->select('text as id')
                    ->where('grp', 'STATUS CONTAINER')
                    ->where('subgrp', 'STATUS CONTAINER FULL EMPTY')
                    ->first()->id ?? 0;


                $querykepelabuhan = DB::table('suratpengantar')->from(
                    db::raw("suratpengantar a with (readuncommitted)")
                )
                    ->select(
                        'a.jobtrucking',
                        db::raw("max(a.totalomset) as nominal"),
                        db::raw("max(a.nobukti) as suratpengantar_nobukti")
                    )
                    ->whereRaw("(a.sampai_id=" . $kotapelabuhanid . " or a.statuslangsir=" . $statuslangsir->id . ")")
                    ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                    ->where('a.agen_id', $request->agen_id)
                    ->where('a.jenisorder_id', $request->jenisorder_id)
                    ->whereRaw("a.statuscontainer_id not in(" . $fullempty . ")")
                    ->where('a.statusjeniskendaraan', $statusjeniskendaraan)
                    ->groupBy('a.jobtrucking');


                DB::table($tempkepelabuhan)->insertUsing([
                    'jobtrucking',
                    'nominal',
                    'suratpengantar_nobukti',
                ], $querykepelabuhan);


                $querykepelabuhan = DB::table('suratpengantar')->from(
                    db::raw("suratpengantar a with (readuncommitted)")
                )
                    ->select(
                        'a.jobtrucking',
                        db::raw("max(a.totalomset) as nominal"),
                        db::raw("max(a.nobukti) as suratpengantar_nobukti")
                    )
                    ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'")
                    ->where('a.agen_id', $request->agen_id)
                    ->where('a.jenisorder_id', $request->jenisorder_id)
                    ->whereRaw("(a.statuscontainer_id in(" . $fullempty . ") or a.statusbatalmuat=" . $statusbatalmuat . ")")
                    ->leftjoin(DB::raw($tempkepelabuhan . " g"), 'a.jobtrucking', 'g.jobtrucking')
                    ->whereRaw("isnull(g.jobtrucking,'')=''")
                    ->where('a.statusjeniskendaraan', $statusjeniskendaraan)
                    ->groupBy('a.jobtrucking');

                // dd($querykepelabuhan->get());

                DB::table($tempkepelabuhan)->insertUsing([
                    'jobtrucking',
                    'nominal',
                    'suratpengantar_nobukti',
                ], $querykepelabuhan);






                $queryhasil = DB::table($tempdaripelabuhan)->from(
                    db::raw($tempdaripelabuhan . " a ")
                )
                    ->select(
                        'a.jobtrucking',
                        'b.nominal',
                        'b.suratpengantar_nobukti'
                    )
                    ->join(DB::raw($tempkepelabuhan) . " as b", 'a.jobtrucking', 'b.jobtrucking');
            } else {


                $tempkepelabuhanbeda = '##tempkepelabuhanbeda' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
                Schema::create($tempkepelabuhanbeda, function ($table) {
                    $table->string('jobtrucking', 1000)->nullable();
                    $table->double('nominal')->nullable();
                    $table->string('suratpengantar_nobukti', 1000)->nullable();
                });


                $querykepelabuhanbeda = DB::table('suratpengantar')->from(
                    db::raw("suratpengantar a with (readuncommitted)")
                )
                    ->select(
                        'a.jobtrucking',
                        db::raw("max(a.totalomset) as nominal"),
                        db::raw("max(a.nobukti) as suratpengantar_nobukti")
                    )
                    ->whereRaw("(a.sampai_id=" . $kotapelabuhanid . " or a.statuslangsir=" . $statuslangsir->id . "  or a.statusbatalmuat=" . $statusbatalmuat . ")")
                    ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "'")
                    ->where('a.agen_id', $request->agen_id)
                    ->where('a.jenisorder_id', $request->jenisorder_id)
                    ->where('a.statusjeniskendaraan', $statusjeniskendaraan)
                    ->groupBy('a.jobtrucking');

                // dd($querykepelabuhanbeda->toSql());

                DB::table($tempkepelabuhanbeda)->insertUsing([
                    'jobtrucking',
                    'nominal',
                    'suratpengantar_nobukti',
                ], $querykepelabuhanbeda);

                $queryhasil = DB::table($tempdaripelabuhan)->from(
                    db::raw($tempdaripelabuhan . " a ")
                )
                    ->select(
                        'a.jobtrucking',
                        'b.nominal',
                        'b.suratpengantar_nobukti'
                    )
                    ->join(DB::raw($tempkepelabuhanbeda) . " as b", 'a.jobtrucking', 'b.jobtrucking');
            }
        }
        $temphasil = '##temphasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temphasil, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->double('nominal')->nullable();
            $table->string('suratpengantar_nobukti', 1000)->nullable();
        });

        DB::table($temphasil)->insertUsing([
            'jobtrucking',
            'nominal',
            'suratpengantar_nobukti',
        ], $queryhasil);

        // dd('test');
        // dd(db::table($temphasil)->get());

        // GET NOBUKTI TRIP ASAL LONGTRIP
        $temptripasal = '##temptripasal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temptripasal, function ($table) {
            $table->string('nobukti', 1000)->nullable();
        });
        $querygetTripasal = DB::table("suratpengantar")->from(DB::raw("suratpengantar with (readuncommitted)"))
            ->select(DB::raw("nobukti_tripasal as nobukti"))
            ->whereRaw("isnull(nobukti_tripasal,'') != ''")
            ->where('agen_id', $request->agen_id)
            ->where('statusjeniskendaraan', $statusjeniskendaraan)
            ->whereRaw("tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'");
        DB::table($temptripasal)->insertUsing([
            'nobukti',
        ], $querygetTripasal);

        $queryTripAwalLongtrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
            ->select(
                'a.jobtrucking',
                db::raw("a.totalomset as nominal"),
                db::raw("a.nobukti as suratpengantar_nobukti")
            )
            ->where('a.agen_id', $request->agen_id)
            ->where('a.statusjeniskendaraan', $statusjeniskendaraan)
            ->join(DB::raw("$temptripasal as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti');

        DB::table($temphasil)->insertUsing([
            'jobtrucking',
            'nominal',
            'suratpengantar_nobukti',
        ], $queryTripAwalLongtrip);

        // GET LONGTRIP
        $getLongTrip = DB::table("suratpengantar")->from(DB::raw("suratpengantar as a with (readuncommitted)"))
            ->select(
                'a.jobtrucking',
                db::raw("a.totalomset as nominal"),
                db::raw("a.nobukti as suratpengantar_nobukti")
            )
            ->where('a.statuslongtrip', $statuslongtrip->id)
            ->where('a.agen_id', $request->agen_id)
            ->where('a.statusjeniskendaraan', $statusjeniskendaraan)
            ->whereRaw("a.tglbukti>='" . date('Y-m-d', strtotime($request->tgldari)) . "' and  a.tglbukti<='" . date('Y-m-d', strtotime($request->tglsampai)) . "'");
        DB::table($temphasil)->insertUsing([
            'jobtrucking',
            'nominal',
            'suratpengantar_nobukti',
        ], $getLongTrip);



        $tempomsettambahan = '##tempomsettambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        // $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR BIAYA TAMBAHAN')->first();

        $fetch = DB::table("suratpengantar")->from(DB::raw("suratpengantar"))
            ->select(
                'c.jobtrucking',
                DB::raw("STRING_AGG(suratpengantarbiayatambahan.keteranganbiaya, ', ') AS keterangan"),
                DB::raw("sum(suratpengantarbiayatambahan.nominaltagih) as nominal")
            )
            ->join(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->join(DB::raw($temphasil . " c"), 'suratpengantar.jobtrucking', 'c.jobtrucking')
            ->groupby('c.jobtrucking');
        // if ($cekStatus->text == 'YA') {
        //     $fetch->where('suratpengantarbiayatambahan.statusapproval', 3);
        // }
        // dd($fetch->get());
        Schema::create($tempomsettambahan, function ($table) {
            $table->string('jobtrucking');
            $table->LongText('keterangan')->nullable();
            $table->double('nominal')->nullable();
        });

        DB::table($tempomsettambahan)->insertUsing(['jobtrucking', 'keterangan', 'nominal'], $fetch);

        $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsp, function ($table) {
            $table->string('jobtrucking', 1000)->nullable();
            $table->LongText('nospfull')->nullable();
            $table->LongText('nospempty')->nullable();
            $table->LongText('nospfullempty')->nullable();
        });

        if ($statusjeniskendaraan != $jenisTangki->id) {

            $querystatuscontainer = DB::table('statuscontainer')->from(
                db::raw("statuscontainer a with (readuncommitted)")
            )
                ->select(
                    'a.id',
                )
                ->orderBy('a.id');

            //    dd('test')            ;

            $datadetail = json_decode($querystatuscontainer->get(), true);
            foreach ($datadetail as $item) {
                $nosp = '';
                $hit = 0;
                $querystatusfilter = DB::table($temphasil)->from(
                    db::raw($temphasil . " a with (readuncommitted)")
                )
                    ->select(
                        'a.jobtrucking',
                    )
                    ->join(DB::raw("suratpengantar sp with (readuncommitted)"), 'a.jobtrucking', 'sp.jobtrucking')
                    ->where('sp.statuscontainer_id', $item['id'])
                    ->OrderBy('sp.tglbukti');

                $datadetailsp = json_decode($querystatusfilter->get(), true);
                foreach ($datadetailsp as $itemsp) {


                    $querystatusfilter2 = DB::table('suratpengantar')->from(
                        db::raw("suratpengantar a with (readuncommitted)")
                    )
                        ->select(
                            'a.nosp',
                        )
                        ->where('a.jobtrucking', $itemsp['jobtrucking'])
                        ->where('a.statuscontainer_id', $item['id'])
                        ->groupby('a.nosp');
                    $nosp = '';
                    $hit = 0;
                    $datadetailsp2 = json_decode($querystatusfilter2->get(), true);
                    foreach ($datadetailsp2 as $itemsp2) {
                        if ($hit == 0) {
                            $nosp = $nosp . $itemsp2['nosp'];
                        } else {
                            $nosp = $nosp . ', ' . $itemsp2['nosp'];
                        }
                        $hit = $hit + 1;
                    }

                    $queryinstemp = DB::table($tempsp)->from(
                        DB::raw($tempsp . " a")
                    )
                        ->select(
                            'a.jobtrucking'
                        )
                        ->where('a.jobtrucking', $itemsp['jobtrucking'])
                        ->first();
                    if (!isset($queryinstemp)) {
                        DB::table($tempsp)->insert([
                            'jobtrucking' => $itemsp['jobtrucking'],
                            'nospfull' => '',
                            'nospempty' => '',
                            'nospfullempty' => '',
                        ]);
                    }


                    // dd($statusfull);
                    // dd($nosp);
                    if ($statusfull->text == $item['id']) {
                        DB::table($tempsp)
                            ->where('jobtrucking', $itemsp['jobtrucking'])
                            ->update(['nospfull' => $nosp]);
                    }

                    if ($statusempty->text == $item['id']) {
                        DB::table($tempsp)
                            ->where('jobtrucking', $itemsp['jobtrucking'])
                            ->update(['nospempty' => $nosp]);
                    }

                    if ($statusfullempty->text == $item['id']) {
                        DB::table($tempsp)
                            ->where('jobtrucking', $itemsp['jobtrucking'])
                            ->update(['nospfullempty' => $nosp]);
                    }
                }
            }
        } else {

            $querystatusfilter = DB::table($temphasil)->from(
                db::raw($temphasil . " a with (readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    'sp.nosp'
                )
                ->join(DB::raw("suratpengantar sp with (readuncommitted)"), 'a.jobtrucking', 'sp.jobtrucking')
                ->where('sp.statusjeniskendaraan', $statusjeniskendaraan)
                ->OrderBy('sp.tglbukti');
            $datadetailsp = json_decode($querystatusfilter->get(), true);


            foreach ($datadetailsp as $itemsp) {
                $queryinstemp = DB::table($tempsp)->from(
                    DB::raw($tempsp . " a")
                )
                    ->select(
                        'a.jobtrucking'
                    )
                    ->where('a.jobtrucking', $itemsp['jobtrucking'])
                    ->first();
                if (!isset($queryinstemp)) {
                    DB::table($tempsp)->insert([
                        'jobtrucking' => $itemsp['jobtrucking'],
                        'nospfull' => '',
                        'nospempty' => '',
                        'nospfullempty' => $itemsp['nosp'],
                    ]);
                }
            }
        }
        $tempdatahasil = '##tempdatahasil' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdatahasil, function ($table) {
            $table->Integer('id')->nullable();
            $table->Integer('idinvoice')->nullable();
            $table->longText('jobtrucking')->nullable();
            $table->date('tglsp')->nullable();
            $table->LongText('keterangan')->nullable();
            $table->LongText('jenisorder_id')->nullable();
            $table->LongText('agen_id')->nullable();
            $table->LongText('statuslongtrip')->nullable();
            $table->LongText('statusperalihan')->nullable();
            $table->LongText('nocont')->nullable();
            $table->LongText('tarif_id')->nullable();
            $table->Double('omset', 15, 2)->nullable();
            $table->Double('nominalextra', 15, 2)->nullable();
            $table->Double('nominalretribusi', 15, 2)->nullable();
            $table->Double('total', 15, 2)->nullable();
            $table->LongText('nospfull')->nullable();
            $table->LongText('nospempty')->nullable();
            $table->LongText('nospfullempty')->nullable();
            $table->LongText('keteranganbiaya')->nullable();
        });


        $query2 = DB::table('invoicedetail')->from(
            DB::raw("invoicedetail as a")
        )
            ->select(
                'sp.id',
                'a.invoice_id as idinvoice',
                'a.orderantrucking_nobukti as jobtrucking',
                'sp.tglbukti as tglsp',
                'a.keterangan as keterangan',
                'jenisorder.keterangan as jenisorder_id',
                'agen.namaagen as agen_id',
                DB::raw("(case when sp.statuslongtrip=" . $statuslongtrip->id . " then 'true' else 'false' end) as statuslongtrip"),
                DB::raw("(case when sp.statusperalihan=" . $statusperalihan->id . " then 'true' else 'false' end) as statusperalihan"),
                'sp.nocont as nocont',
                DB::raw("isnull(tarif.tujuan,'') as tarif_id"),
                DB::raw("isnull(a.nominal,0) as omset"),
                DB::raw("isnull(a.nominalextra,0) as nominalextra"),
                DB::raw("isnull(a.nominalretribusi,0) as nominalretribusi"),
                DB::raw("(isnull(a.nominal,0)+isnull(a.nominalextra,0)+isnull(a.nominalretribusi,0)) as total"),
                DB::raw("isnull(e.nospfull,'') as nospfull"),
                DB::raw("isnull(e.nospempty,'') as nospempty"),
                DB::raw("isnull(e.nospfullempty,'') as nospfullempty"),
                'c.keterangan as keteranganbiaya',

            )
            ->leftjoin(DB::raw($temphasil . " a1"), 'a.orderantrucking_nobukti', 'a1.jobtrucking')
            ->leftjoin(DB::raw($tempomsettambahan . " c"), 'a.orderantrucking_nobukti', 'c.jobtrucking')
            ->join(DB::raw("suratpengantar sp with (readuncommitted)"), 'a1.suratpengantar_nobukti', 'sp.nobukti')
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'a.orderantrucking_nobukti', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'sp.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->leftjoin(DB::raw($tempsp . " e"), 'a.orderantrucking_nobukti', 'e.jobtrucking')
            ->where('a.invoice_id', $request->id)

            ->orderBy("sp.tglbukti");


        DB::table($tempdatahasil)->insertUsing([
            'id',
            'idinvoice',
            'jobtrucking',
            'tglsp',
            'keterangan',
            'jenisorder_id',
            'agen_id',
            'statuslongtrip',
            'statusperalihan',
            'nocont',
            'tarif_id',
            'omset',
            'nominalextra',
            'nominalretribusi',
            'total',
            'nospfull',
            'nospempty',
            'nospfullempty',
            'keteranganbiaya',
        ], $query2);

        // dd($query2->get());

        $query2 = DB::table($temphasil)->from(
            DB::raw($temphasil . " as a")
        )
            ->select(
                'sp.id',
                DB::raw("null as idinvoice"),
                'a.jobtrucking',
                'sp.tglbukti as tglsp',
                'sp.keterangan as keterangan',
                'jenisorder.keterangan as jenisorder_id',
                'agen.namaagen as agen_id',
                DB::raw("(case when sp.statuslongtrip=" . $statuslongtrip->id . " then 'true' else 'false' end) as statuslongtrip"),
                DB::raw("(case when sp.statusperalihan=" . $statusperalihan->id . " then 'true' else 'false' end) as statusperalihan"),
                // 'sp.nocont as nocont',
                DB::raw("(CASE WHEN sp.container_id = 3 THEN sp.nocont + (CASE WHEN sp.nocont2 != '' then ' / ' else '' end) + sp.nocont2 
                ELSE sp.nocont end) as nocont"),
                DB::raw("
                (CASE WHEN sp.statusjeniskendaraan=645 then
                isnull(tarif.tujuan,'')+(case when isnull(sp.penyesuaian,'')='' then '' else ' ( '+isnull(sp.penyesuaian,'')+' ) '  end)
                else
                isnull(tariftangki.tujuan,'')+(case when isnull(sp.penyesuaian,'')='' then '' else ' ( '+isnull(sp.penyesuaian,'')+' ) '  end) end)
                 as tarif_id"),
                DB::raw("isnull(a.nominal,0) as omset"),
                DB::raw("isnull(c.nominal,0) as nominalextra"),
                DB::raw("0 as nominalretribusi"),
                DB::raw("(isnull(a.nominal,0)+isnull(c.nominal,0)) as total"),
                DB::raw("isnull(e.nospfull,'') as nospfull"),
                DB::raw("isnull(e.nospempty,'') as nospempty"),
                DB::raw("isnull(e.nospfullempty,'') as nospfullempty"),
                'c.keterangan as keteranganbiaya',

            )
            ->join(DB::raw("suratpengantar sp with (readuncommitted)"), 'a.suratpengantar_nobukti', 'sp.nobukti')
            ->leftjoin(DB::raw($tempomsettambahan . " c"), 'a.jobtrucking', 'c.jobtrucking')
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'a.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'sp.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("tariftangki with (readuncommitted)"), 'sp.tariftangki_id', 'tariftangki.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->leftjoin(DB::raw($tempsp . " e"), 'a.jobtrucking', 'e.jobtrucking')
            ->leftJoin(DB::raw("invoicedetail f with (readuncommitted)"), 'a.jobtrucking', 'f.orderantrucking_nobukti')
            ->whereRaw("isnull(f.orderantrucking_nobukti,'')=''")

            ->orderBy("sp.tglbukti");

        //   dd($query2->get());

        DB::table($tempdatahasil)->insertUsing([
            'id',
            'idinvoice',
            'jobtrucking',
            'tglsp',
            'keterangan',
            'jenisorder_id',
            'agen_id',
            'statuslongtrip',
            'statusperalihan',
            'nocont',
            'tarif_id',
            'omset',
            'nominalextra',
            'nominalretribusi',
            'total',
            'nospfull',
            'nospempty',
            'nospfullempty',
            'keteranganbiaya',
        ], $query2);


        // dd(db::table($tempdatahasil)->get());


        $query = DB::table($tempdatahasil)->from(
            DB::raw($tempdatahasil . " as a")
        )
            ->select(
                DB::raw("row_number() Over(Order By tglsp) as id"),
                'a.id as sp_id',
                'a.idinvoice',
                'a.jobtrucking',
                'a.tglsp',
                'a.keterangan',
                'a.jenisorder_id as jenisorder_idgrid',
                'a.agen_id as agen_idgrid',
                'a.statuslongtrip',
                'a.statusperalihan',
                'a.nocont',
                'a.tarif_id',
                'a.omset',
                'a.nominalextra',
                'a.nominalretribusi',
                'a.total',
                'a.nospfull',
                'a.nospempty',
                'a.nospfullempty',
                'a.keteranganbiaya',

            )
            // ->where('a.nocont', '!=', '')
            ->orderBy("a.tglsp");


        $data = $query->get();

        // dd($data);
        return $data;
    }

    public function getSP($request)
    {
        $temp = $this->createTempSP($request);
        $biayaTambahan = $this->createBiayaTambahan($request);

        $statusLongtrip = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS LONGTRIP')->where('text', 'LONGTRIP')->first();
        $statusPeralihan = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PERALIHAN')->where('text', 'PERALIHAN')->first();

        // dd(DB::table($temp)->get());
        $query = DB::table('suratpengantar')->from(DB::raw("suratpengantar as sp with (readuncommitted)"))
            ->select(DB::raw("$temp.id,$temp.jobtrucking,sp.tglsp, sp.keterangan,jenisorder.keterangan as jenisorder_id, agen.namaagen as agen_id, 
            (case when sp.statuslongtrip = $statusLongtrip->id then 'true' else 'false' end) as statuslongtrip,(case when ot.statusperalihan = $statusPeralihan->id then 'true' else 'false' end) as statusperalihan, (case when ot.nocont IS NULL then '-' else ot.nocont end) as nocont, 
            (case when tarif.tujuan IS NULL then '-' else tarif.tujuan end) as tarif_id,
            ot.nominal as omset, (case when $biayaTambahan.nominaltagih IS NULL then 0 else $biayaTambahan.nominaltagih end) as nominalextra,
            (case when $biayaTambahan.nominaltagih IS NULL then (ot.nominal + 0) else (ot.nominal + $biayaTambahan.nominaltagih) end) as total"))
            ->Join(DB::raw("$temp with (readuncommitted)"), 'sp.id', "$temp.id")
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'sp.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'ot.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->leftJoin(DB::raw("$biayaTambahan with (readuncommitted)"), "sp.jobtrucking", "$biayaTambahan.jobtrucking")
            ->whereRaw("sp.jobtrucking not in(select orderantrucking_nobukti from invoicedetail)")
            ->orderBy("sp.jobtrucking", 'asc');
        // dd($query->toSql());
        $data = $query->get();
        return $data;
    }

    public function createBiayaTambahan($request)
    {
        $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'SURAT PENGANTAR BIAYA TAMBAHAN')->first();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = SuratPengantar::from(DB::raw("suratpengantar"))
            ->select(DB::raw("suratpengantar.id, suratpengantar.jobtrucking, suratpengantarbiayatambahan.nominaltagih"))
            ->join(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->where('suratpengantar.agen_id', $request->agen_id)
            ->where('suratpengantar.jenisorder_id', $request->jenisorder_id)
            ->where('suratpengantar.tglbukti', '>=', date('Y-m-d', strtotime($request->tgldari)))
            ->where('suratpengantar.tglbukti', '<=', date('Y-m-d', strtotime($request->tglsampai)));
        if ($cekStatus->text == 'YA') {
            $fetch->where('suratpengantarbiayatambahan.statusapproval', 3);
        }

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('jobtrucking');
            $table->bigInteger('nominaltagih')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'jobtrucking', 'nominaltagih'], $fetch);

        // $data = DB::table($temp)->get();
        return $temp;
    }

    public function createTempSP($request)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = SuratPengantar::from(DB::raw("suratpengantar"))
            ->select(DB::raw("min(id) as id, jobtrucking"))
            ->where('agen_id', $request->agen_id)
            ->where('jenisorder_id', $request->jenisorder_id)
            ->where('tglbukti', '>=', date('Y-m-d', strtotime($request->tgldari)))
            ->where('tglbukti', '<=', date('Y-m-d', strtotime($request->tglsampai)))
            ->whereRaw("nocont != ''")
            ->whereRaw("noseal != ''")
            ->groupBy('jobtrucking');

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('jobtrucking')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['id', 'jobtrucking'], $fetch);
        return $temp;
    }

    public function getInvoicePengeluaran($tgldari, $tglsampai)
    {

        // 
        $datafilter = request()->filter ?? 0;
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'SumbanganSosialController';


        $parameter = new Parameter();

        $sumbanganton = $parameter->cekText('SUMBANGAN TON', 'SUMBANGAN TON') ?? '0';

        if ($proses == 'reload') {
            $temtabel = 'tempgetinv' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                $queryid = db::table('listtemporarytabel')->from(db::raw("listtemporarytabel a with (readuncommitted)"))
                    ->select('id')->where('id', $querydata->id)->first();
                if (isset($queryid)) {
                    DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
                }
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );


            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_detail')->nullable();
                $table->string('noinvoice_detail', 50)->nullable();
                $table->string('nojobtrucking_detail', 50)->nullable();
                $table->string('container_detail', 1000)->nullable();
                $table->date('tglbukti')->nullable();
                $table->double('nominal_detail', 15, 2)->nullable();
            });

            $queryget = db::table("saldosumbangansosial")->from(db::raw("saldosumbangansosial a with (readuncommitted)"))
                ->select(
                    'a.id_detail',
                    'a.noinvoice_detail',
                    'a.nojobtrucking_detail',
                    db::raw("isnull(container.keterangan,'TON') as container_detail"),
                    'a.tgl_bukti as tglbukti',
                    db::raw("(case when container.nominalsumbangan IS NULL then " . $sumbanganton . " else container.nominalsumbangan end) as nominal_detail")
                )
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'a.container_id', 'container.id')
                ->whereRaw("a.nojobtrucking_detail not in (select orderantrucking_nobukti from pengeluarantruckingdetail where orderantrucking_nobukti != '')")
                ->whereBetween('a.tgl_bukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->orderby('a.id', 'asc');

            DB::table($temtabel)->insertUsing([
                'id_detail',
                'noinvoice_detail',
                'nojobtrucking_detail',
                'container_detail',
                'tglbukti',
                'nominal_detail',
            ], $queryget);




            $query1 = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
                ->select(DB::raw("
            invoicedetail.id as id_detail,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail,
            isnull(container.keterangan,'TON') as container_detail,
            invoiceheader.tglbukti,
            (case when container.nominalsumbangan IS NULL then " . $sumbanganton . " else container.nominalsumbangan end) as nominal_detail

            "))

                ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
                ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', 'ot.nobukti')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'ot.container_id', 'container.id')
                ->whereRaw("invoicedetail.orderantrucking_nobukti not in (select orderantrucking_nobukti from pengeluarantruckingdetail where orderantrucking_nobukti != '')")
                ->whereBetween('invoiceheader.tglbukti', [date('Y-m-d', strtotime($tgldari)), date('Y-m-d', strtotime($tglsampai))])
                ->whereRaw("isnull(invoicedetail.orderantrucking_nobukti, '') != ''");


            DB::table($temtabel)->insertUsing([
                'id_detail',
                'noinvoice_detail',
                'nojobtrucking_detail',
                'container_detail',
                'tglbukti',
                'nominal_detail',
            ], $query1);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }

        // 



        // ->where('invoiceheader.tsglbukti', '<=', date('Y-m-d', strtotime($tglsampai)));

        $this->setRequestParameters();
        $query = db::table($temtabel)->from(db::raw("$temtabel as a"))
            ->select(
                'a.id as id_detail',
                'a.noinvoice_detail',
                'a.nojobtrucking_detail',
                'a.container_detail',
                'a.tglbukti',
                'a.nominal_detail',
                DB::raw("'$temtabel' as namatabel")
            );



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('a.nominal_detail');
        // dd($query->sum('a.nominal_detail'));

        $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterGetInvoice($query);
        if (request()->limit != 0) {
            $query->skip($this->params['offset'])->take($this->params['limit']);
        }
        $data = $query->get();


        return $data;
    }

    public function filterGetInvoice($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {

                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {

                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function getEdit($id, $request)
    {
        $temp = $this->createTempSP($request);

        $query = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
            ->select(DB::raw("$temp.id,$temp.jobtrucking,sp.tglsp, sp.keterangan,jenisorder.keterangan as jenisorder_id, agen.namaagen as agen_id, sp.statuslongtrip, ot.statusperalihan, ot.nocont, (case when tarif.tujuan IS NULL then '-' else tarif.tujuan end) as tarif_id, ot.nominal as omset, invoicedetail.nominalretribusi, invoicedetail.nominalextra, (ot.nominal + invoicedetail.nominalretribusi + invoicedetail.nominalextra) as total"))

            ->leftJoin(DB::raw("suratpengantar as sp with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', 'sp.jobtrucking')
            ->Join(DB::raw("$temp with (readuncommitted)"), 'sp.id', "$temp.id")
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'sp.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'ot.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->whereRaw("invoicedetail.invoice_id = $id");

        $data = $query->get();
        return $data;
    }

    public function getAllEdit($id, $request)
    {
        $tempAll = $this->createTempAllEdit($id, $request);
        $data = DB::table($tempAll)->get();
        return $data;
    }

    public function createTempAllEdit($id, $request)
    {

        $tempSP = $this->createTempSP($request);
        $biayaTambahan = $this->createBiayaTambahan($request);
        $temp = '##tempAll' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
            ->select(DB::raw("$tempSP.id,$tempSP.jobtrucking,sp.tglsp, sp.keterangan,jenisorder.keterangan as jenisorder_id, agen.namaagen as agen_id, sp.statuslongtrip, ot.statusperalihan, ot.nocont, (case when tarif.tujuan IS NULL then '-' else tarif.tujuan end) as tarif_id, ot.nominal as omset, invoicedetail.nominalretribusi,invoicedetail.nominalextra,(ot.nominal + invoicedetail.nominalretribusi + invoicedetail.nominalextra) as total"))

            ->leftJoin(DB::raw("suratpengantar as sp with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', 'sp.jobtrucking')
            ->Join(DB::raw("$tempSP with (readuncommitted)"), 'sp.id', "$tempSP.id")
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'sp.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'ot.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->leftJoin(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"), 'sp.id', 'suratpengantarbiayatambahan.suratpengantar_id')
            ->whereRaw("invoicedetail.invoice_id = $id");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('jobtrucking')->nullable();
            $table->date('tglsp')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('jenisorder_id')->nullable();
            $table->string('agen_id')->nullable();
            $table->bigInteger('statuslongtrip')->nullable();
            $table->bigInteger('statusperalihan')->nullable();
            $table->string('nocont')->nullable();
            $table->string('tarif_id')->nullable();
            $table->bigInteger('omset')->nullable();
            $table->bigInteger('nominalretribusi')->nullable();
            $table->bigInteger('nominalextra')->nullable();
            $table->bigInteger('total')->nullable();
        });

        DB::table($temp)->insertUsing(['id', 'jobtrucking', 'tglsp', 'keterangan', 'jenisorder_id', 'agen_id', 'statuslongtrip', 'statusperalihan', 'nocont', 'tarif_id', 'omset', 'nominalretribusi', 'nominalextra', 'total'], $fetch);

        $fetch2 = SuratPengantar::from(DB::raw("suratpengantar as sp with (readuncommitted)"))
            ->select(DB::raw("$tempSP.id,$tempSP.jobtrucking,sp.tglsp, sp.keterangan,jenisorder.keterangan as jenisorder_id, agen.namaagen as agen_id, sp.statuslongtrip, ot.statusperalihan, (case when ot.nocont IS NULL then '-' else ot.nocont end) as nocont, 
            (case when tarif.tujuan IS NULL then '-' else tarif.tujuan end) as tarif_id,
            ot.nominal as omset, (case when $biayaTambahan.nominaltagih IS NULL then 0 else $biayaTambahan.nominaltagih end) as nominalextra,
            (case when $biayaTambahan.nominaltagih IS NULL then (ot.nominal + 0) else (ot.nominal + $biayaTambahan.nominaltagih) end) as total"))
            ->Join(DB::raw("$tempSP with (readuncommitted)"), 'sp.id', "$tempSP.id")
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'sp.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'ot.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->leftJoin(DB::raw("$biayaTambahan with (readuncommitted)"), "sp.jobtrucking", "$biayaTambahan.jobtrucking")
            ->whereRaw("sp.jobtrucking not in(select orderantrucking_nobukti from invoicedetail)")
            ->orderBy("sp.jobtrucking", 'asc');

        DB::table($temp)->insertUsing(['id', 'jobtrucking', 'tglsp', 'keterangan', 'jenisorder_id', 'agen_id', 'statuslongtrip', 'statusperalihan', 'nocont', 'tarif_id', 'omset', 'nominalextra', 'total'], $fetch2);

        return $temp;
    }

    public function getInvoiceOtok($tgldari, $tglsampai, $agen_id, $container_id)
    {
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'OtobonKantorController';
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                $queryid = db::table('listtemporarytabel')->from(db::raw("listtemporarytabel a with (readuncommitted)"))
                    ->select('id')->where('id', $querydata->id)->first();
                if (isset($queryid)) {
                    DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
                }
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );


            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_detail')->nullable();
                $table->string('noinvoice_detail', 50)->nullable();
                $table->string('nojobtrucking_detail', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->double('nominal_detail', 15, 2)->nullable();
            });

            $query1 = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
                ->select(DB::raw("
            invoicedetail.id as id_detail,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail,
            invoiceheader.tglbukti,
            (case when otobon.nominal IS NULL then 0 else otobon.nominal end) as nominal_detail

            "))

                ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
                ->leftJoin(DB::raw("otobon with (readuncommitted)"), 'invoiceheader.agen_id', 'otobon.agen_id')
                ->whereBetween('invoiceheader.tglbukti', [$tgldari, $tglsampai])
                ->where('otobon.agen_id', $agen_id)
                ->where('otobon.container_id', $container_id)
                ->whereRaw("isnull(invoicedetail.orderantrucking_nobukti, '') != ''");


            DB::table($temtabel)->insertUsing([
                'id_detail',
                'noinvoice_detail',
                'nojobtrucking_detail',
                'tglbukti',
                'nominal_detail',
            ], $query1);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }
        $query = db::table($temtabel)->from(db::raw($temtabel))
            ->select(
                'id as id_detail',
                'noinvoice_detail',
                'nojobtrucking_detail',
                'tglbukti',
                'nominal_detail',
            )->orderBY('id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');
        $data = $query->get();


        return $data;
    }
    public function getInvoiceOtol($tgldari, $tglsampai, $agen_id, $container_id)
    {
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'OtobonLapanganController';
        if ($proses == 'reload') {
            $temtabel = 'temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true)) . request()->nd ?? 0;

            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel a with (readuncommitted)")
            )
                ->select(
                    'id',
                    'class',
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            if (isset($querydata)) {
                Schema::dropIfExists($querydata->namatabel);
                $queryid = db::table('listtemporarytabel')->from(db::raw("listtemporarytabel a with (readuncommitted)"))
                    ->select('id')->where('id', $querydata->id)->first();
                if (isset($queryid)) {
                    DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
                }
            }

            DB::table('listtemporarytabel')->insert(
                [
                    'class' => $class,
                    'namatabel' => $temtabel,
                    'modifiedby' => $user,
                    'created_at' => date('Y/m/d H:i:s'),
                    'updated_at' => date('Y/m/d H:i:s'),
                ]
            );


            Schema::create($temtabel, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_detail')->nullable();
                $table->string('noinvoice_detail', 50)->nullable();
                $table->string('nojobtrucking_detail', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->double('nominal_detail', 15, 2)->nullable();
            });

            $query1 = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
                ->select(DB::raw("
            invoicedetail.id as id_detail,
            invoicedetail.nobukti as noinvoice_detail,
            invoicedetail.orderantrucking_nobukti as nojobtrucking_detail,
            invoiceheader.tglbukti,
            (case when lapangan.nominal IS NULL then 0 else lapangan.nominal end) as nominal_detail

            "))

                ->leftJoin(DB::raw("invoiceheader with (readuncommitted)"), 'invoicedetail.invoice_id', 'invoiceheader.id')
                ->leftJoin(DB::raw("lapangan with (readuncommitted)"), 'invoiceheader.agen_id', 'lapangan.agen_id')
                ->whereBetween('invoiceheader.tglbukti', [$tgldari, $tglsampai])
                ->where('lapangan.agen_id', $agen_id)
                ->where('lapangan.container_id', $container_id)
                ->whereRaw("isnull(invoicedetail.orderantrucking_nobukti, '') != ''");


            DB::table($temtabel)->insertUsing([
                'id_detail',
                'noinvoice_detail',
                'nojobtrucking_detail',
                'tglbukti',
                'nominal_detail',
            ], $query1);
        } else {
            $querydata = DB::table('listtemporarytabel')->from(
                DB::raw("listtemporarytabel with (readuncommitted)")
            )
                ->select(
                    'namatabel',
                )
                ->where('class', '=', $class)
                ->where('modifiedby', '=', $user)
                ->first();

            // dd($querydata);
            $temtabel = $querydata->namatabel;
        }
        $query = db::table($temtabel)->from(db::raw($temtabel))
            ->select(
                'id as id_detail',
                'noinvoice_detail',
                'nojobtrucking_detail',
                'tglbukti',
                'nominal_detail',
            )->orderBY('id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->totalNominal = $query->sum('nominal_detail');
        $data = $query->get();


        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'agen') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'jenisorder_id') {
            return $query->orderBy('jenisorder.keterangan', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'agen') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'jenisorder_id') {
                                $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo' || $filters['field'] == 'tglterima' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
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
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'agen') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'jenisorder_id') {
                                    $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo' || $filters['field'] == 'tglterima' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
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

        if (request()->approve && request()->periode) {
            $query->where('invoiceheader.statusapproval', request()->approve)
                ->whereYear('invoiceheader.tglbukti', '=', request()->year)
                ->whereMonth('invoiceheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('invoiceheader.statuscetak', '<>', request()->cetak)
                ->whereYear('invoiceheader.tglbukti', '=', request()->year)
                ->whereMonth('invoiceheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function jenisorder()
    {
        return $this->belongsTo(JenisOrder::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id');
    }

    public function processStore(array $data): InvoiceHeader
    {
        $group = 'INVOICE BUKTI';
        $subGroup = 'INVOICE BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $invoiceHeader = new InvoiceHeader();
        $invoiceHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $invoiceHeader->nominal = '';
        $invoiceHeader->tglterima = date('Y-m-d', strtotime($data['tglterima']));
        $invoiceHeader->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $invoiceHeader->agen_id = $data['agen_id'];
        $invoiceHeader->statusjeniskendaraan = $data['statusjeniskendaraan'];
        $invoiceHeader->jenisorder_id = $data['jenisorder_id'];
        $invoiceHeader->piutang_nobukti = $data['piutang_nobukti'] ?? '';
        $invoiceHeader->statusapproval = $statusApproval->id;
        $invoiceHeader->noinvoicepajak = $data['noinvoicepajak'] ?? '';
        $invoiceHeader->userapproval = '';
        $invoiceHeader->tglapproval = '';
        $invoiceHeader->statuscetak = $statusCetak->id;
        $invoiceHeader->statuspilihaninvoice = $data['statuspilihaninvoice'] ?? '';
        $invoiceHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $invoiceHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $invoiceHeader->modifiedby = auth('api')->user()->name;
        $invoiceHeader->info = html_entity_decode(request()->info);
        $invoiceHeader->statusformat = $format->id;
        $invoiceHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$invoiceHeader->save()) {
            throw new \Exception("Error storing invoice header.");
        }

        $invoiceDetails = [];

        $keteranganDetail = [];
        $nominalDetail = [];
        $invoiceNobukti = [];

        $total = 0;
        for ($i = 0; $i < count($data['sp_id']); $i++) {

            $SP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('id', $data['sp_id'][$i])->first();
            $total = $total + $data['omset'][$i] + $data['nominalretribusi'][$i] + $data['nominalextra'][$i];

            $getSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('jobtrucking', $SP->jobtrucking)->get();

            $allSP = "";
            foreach ($getSP as $key => $value) {
                if ($key == 0) {
                    $allSP = $allSP . $value->nobukti;
                } else {
                    $allSP = $allSP . ',' . $value->nobukti;
                }
            }

            $invoiceDetail = (new InvoiceDetail())->processStore($invoiceHeader, [
                'nominal' => $data['omset'][$i],
                'nominalextra' => $data['nominalextra'][$i],
                'nominalretribusi' => $data['nominalretribusi'][$i],
                'total' => $data['omset'][$i] + $data['nominalretribusi'][$i] + $data['nominalextra'][$i],
                'keterangan' => $data['keterangan'][$i] ?? '',
                'orderantrucking_nobukti' => $SP->jobtrucking,
                'suratpengantar_nobukti' => $allSP
            ]);
            // STORE 
            $invoiceDetails[] = $invoiceDetail->toArray();
        }

        $keteranganDetail[] = "TAGIHAN INVOICE " . $data['jenisorder'] . " " . $data['agen'] . " periode " . $data['tgldari'] . " s/d " . $data['tglsampai'];
        $nominalDetail[] = $total;
        $invoiceNobukti[] =  $invoiceHeader->nobukti;

        $invoiceRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
            'postingdari' => 'ENTRY INVOICE',
            'invoice' => $invoiceHeader->nobukti,
            'agen_id' => $data['agen_id'],
            'invoice_nobukti' => $invoiceNobukti,
            'nominal_detail' => $nominalDetail,
            'keterangan_detail' => $keteranganDetail,
            'jenis' => 'utama'
        ];

        $piutangHeader = (new PiutangHeader())->processStore($invoiceRequest);
        $invoiceHeader->piutang_nobukti = $piutangHeader->nobukti;
        $invoiceHeader->nominal = $total;
        $invoiceHeader->save();

        $invoiceHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceHeader->getTable()),
            'postingdari' => 'ENTRY INVOICE HEADER',
            'idtrans' => $invoiceHeader->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $invoiceHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceDetail->getTable()),
            'postingdari' => 'ENTRY INVOICE DETAIL',
            'idtrans' =>  $invoiceHeaderLogTrail->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $invoiceDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $invoiceHeader;
    }

    public function processUpdate(InvoiceHeader $invoiceHeader, array $data): InvoiceHeader
    {

        $nobuktiOld = $invoiceHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'INVOICE')->first();
        if (trim($getTgl->text) == 'YA') {
            $group = 'INVOICE BUKTI';
            $subGroup = 'INVOICE BUKTI';
            $querycek = DB::table('invoiceheader')->from(
                DB::raw("invoiceheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $invoiceHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $invoiceHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }
            $invoiceHeader->nobukti = $nobukti;
            $invoiceHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $invoiceHeader->noinvoicepajak = $data['noinvoicepajak'] ?? '';
        $invoiceHeader->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $invoiceHeader->nominal = '';
        $invoiceHeader->tglterima = date('Y-m-d', strtotime($data['tglterima']));
        $invoiceHeader->tgldari = date('Y-m-d', strtotime($data['tgldari']));
        $invoiceHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai']));
        $invoiceHeader->modifiedby = auth('api')->user()->name;
        $invoiceHeader->info = html_entity_decode(request()->info);
        $invoiceHeader->editing_by = '';
        $invoiceHeader->editing_at = null;
        $invoiceHeader->statuspilihaninvoice = $data['statuspilihaninvoice'] ?? '';


        if (!$invoiceHeader->save()) {
            throw new \Exception("Error updating invoice header.");
        }



        InvoiceDetail::where('invoice_id', $invoiceHeader->id)->delete();

        $keteranganDetail = [];
        $nominalDetail = [];
        $invoiceNobukti = [];

        $invoiceDetails = [];

        $keteranganDetail = [];
        $nominalDetail = [];
        $invoiceNobukti = [];

        $total = 0;
        for ($i = 0; $i < count($data['sp_id']); $i++) {

            $SP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('id', $data['sp_id'][$i])->first();

            $total = $total + $data['omset'][$i] + $data['nominalretribusi'][$i] + $data['nominalextra'][$i];

            $getSP = SuratPengantar::from(DB::raw("suratpengantar with (readuncommitted)"))
                ->where('jobtrucking', $SP->jobtrucking)->get();

            $allSP = "";
            foreach ($getSP as $key => $value) {
                if ($key == 0) {
                    $allSP = $allSP . $value->nobukti;
                } else {
                    $allSP = $allSP . ',' . $value->nobukti;
                }
            }

            $invoiceDetail = (new InvoiceDetail())->processStore($invoiceHeader, [
                'nominal' => $data['omset'][$i],
                'nominalextra' => $data['nominalextra'][$i],
                'nominalretribusi' => $data['nominalretribusi'][$i],
                'total' => $data['omset'][$i] + $data['nominalretribusi'][$i] + $data['nominalextra'][$i],
                'keterangan' => $data['keterangan'][$i] ?? '',
                'orderantrucking_nobukti' => $SP->jobtrucking,
                'suratpengantar_nobukti' => $allSP
            ]);
            // STORE 
            $invoiceDetails[] = $invoiceDetail->toArray();
        }

        $keteranganDetail[] = "TAGIHAN INVOICE " . $data['jenisorder'] . " " . $data['agen'] . " periode " . $data['tgldari'] . " s/d " . $data['tglsampai'];
        $nominalDetail[] = $total;
        $invoiceNobukti[] =  $invoiceHeader->nobukti;

        $invoiceHeader->nominal = $total;
        $invoiceHeader->save();

        $invoiceRequest = [
            'tgljatuhtempo' => date('Y-m-d', strtotime($data['tgljatuhtempo'])),
            'postingdari' => 'EDIT INVOICE',
            'tglbukti' => $invoiceHeader->tglbukti,
            'invoice' => $invoiceHeader->nobukti,
            'agen_id' => $invoiceHeader->agen_id,
            'invoice_nobukti' => $invoiceNobukti,
            'nominal_detail' => $nominalDetail,
            'keterangan_detail' => $keteranganDetail,
            'jenis' => 'utama'
        ];

        $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $nobuktiOld)->first();
        $newPiutang = new PiutangHeader();
        $newPiutang = $newPiutang->findUpdate($getPiutang->id);
        $piutangHeader = (new PiutangHeader())->processUpdate($newPiutang, $invoiceRequest);

        $invoiceHeader->piutang_nobukti = $piutangHeader->nobukti;
        $invoiceHeader->save();

        $invoiceHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceHeader->getTable()),
            'postingdari' => 'EDIT INVOICE HEADER',
            'idtrans' => $invoiceHeader->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $invoiceHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($invoiceDetail->getTable()),
            'postingdari' => 'EDIT INVOICE DETAIL',
            'idtrans' =>  $invoiceHeaderLogTrail->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $invoiceDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $invoiceHeader;
    }
    public function processDestroy($id, $postingDari = ''): InvoiceHeader
    {
        $invoiceDetails = InvoiceDetail::lockForUpdate()->where('invoice_id', $id)->get();

        $invoiceHeader = new InvoiceHeader();
        $invoiceHeader = $invoiceHeader->lockAndDestroy($id);

        $invoiceHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $invoiceHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $invoiceHeader->id,
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $invoiceHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'INVOICEDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $invoiceHeaderLogTrail['id'],
            'nobuktitrans' => $invoiceHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $invoiceDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $getPiutang = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->where('invoice_nobukti', $invoiceHeader->nobukti)->first();
        (new PiutangHeader())->processDestroy($getPiutang->id, $postingDari);
        return $invoiceHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("invoiceheader with (readuncommitted)"))
            ->select(
                'invoiceheader.id',
                'invoiceheader.nobukti',
                'invoiceheader.tglbukti',
                'invoiceheader.nominal',
                'invoiceheader.tglterima',
                'invoiceheader.tgljatuhtempo',
                'agen.namaagen as agen',
                'jenisorder.keterangan as jenisorder_id',
                'invoiceheader.piutang_nobukti',
                'statusapproval.memo as statusapproval',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Laporan Invoice' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(invoiceheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(invoiceheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("invoiceheader.statuscetak", $statusCetak);
        }

        $data = $query->first();
        return $data;
    }
}
