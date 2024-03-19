<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PiutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'piutangheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {

        $temppelunasan = '##temppelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasan, function ($table) {
            $table->string('piutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $query = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.piutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('piutang_nobukti');

        DB::table($temppelunasan)->insertUsing([
            'piutang_nobukti',
            'nominal',
        ], $query);

        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $query = DB::table($this->table)->from(
            DB::raw("piutangheader with (readuncommitted)")
        )->select(
            'piutangheader.id',
            'piutangheader.nobukti',
            'piutangheader.tglbukti',
            'piutangheader.tgljatuhtempo',
            'piutangheader.postingdari',
            'piutangheader.nominal',
            DB::raw("isnull(c.nominal,0) as nominalpelunasan"),
            DB::raw("piutangheader.nominal-isnull(c.nominal,0) as sisapiutang"),
            'piutangheader.invoice_nobukti',
            'piutangheader.modifiedby',
            'piutangheader.updated_at',
            'piutangheader.created_at',
            'parameter.memo as statuscetak',
            'debet.keterangancoa as coadebet',
            'kredit.keterangancoa as coakredit',
            DB::raw('(case when (year(piutangheader.tglbukacetak) <= 2000) then null else piutangheader.tglbukacetak end ) as tglbukacetak'),
            'piutangheader.userbukacetak',
            'agen.namaagen as agen_id',
            db::raw("cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceheader"),
            db::raw("cast(cast(format((cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceheader"),
            db::raw("cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceextraheader"),
            db::raw("cast(cast(format((cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceextraheader"),
        )
            ->leftJoin(DB::raw("invoiceheader as invoice with (readuncommitted)"), 'piutangheader.invoice_nobukti', '=', 'invoice.nobukti')
            ->leftJoin(DB::raw("invoiceextraheader as invoiceextra with (readuncommitted)"), 'piutangheader.invoice_nobukti', '=', 'invoiceextra.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'piutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'piutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), 'piutangheader.coadebet', 'debet.coa')
            ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), 'piutangheader.coakredit', 'kredit.coa')
            ->leftJoin(DB::raw($temppelunasan . " as c"), 'piutangheader.nobukti', 'c.piutang_nobukti');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(piutangheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(piutangheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("piutangheader.statuscetak", $statusCetak);
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
        $pelunasanPiutang = DB::table('pelunasanpiutangdetail')
            ->from(
                DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.piutang_nobukti'
            )
            ->where('a.piutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }
        $invoice = DB::table('invoiceheader')
            ->from(
                DB::raw("invoiceheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.piutang_nobukti'
            )
            ->where('a.piutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($invoice)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice ' . $invoice->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $invoiceExtra = DB::table('invoiceextraheader')
            ->from(
                DB::raw("invoiceextraheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.piutang_nobukti'
            )
            ->where('a.piutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($invoiceExtra)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice Extra ' . $invoiceExtra->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $invoiceCharge = DB::table('invoicechargegandenganheader')
            ->from(
                DB::raw("invoicechargegandenganheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.piutang_nobukti'
            )
            ->where('a.piutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($invoiceCharge)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Invoice Charge Gandengan ' . $invoiceCharge->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $jurnalpusat = DB::table('jurnalumumpusatheader')
            ->from(
                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnalpusat)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal ' . $jurnalpusat->nobukti,
                'kodeerror' => 'SAP'
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

    public function getPiutang($id)
    {
        $this->setRequestParameters();

        $temp = $this->createTempPiutang($id);

        $query = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader with (readuncommitted)")
            )
            ->select(DB::raw("row_number() Over(Order By piutangheader.id) as id,piutangheader.nobukti as nobukti,piutangheader.tglbukti as tglbukti_piutang, piutangheader.invoice_nobukti, piutangheader.nominal, piutangheader.agen_id," . $temp . ".sisa, $temp.sisa as sisaawal,
                (case when isnull(c.nobukti,'')<>'' or isnull(piutangheader.postingdari,'')='INVOICE' then 'UTAMA' else 'TAMBAHAN' end) as jenisinvoice"))
            ->leftJoin(DB::raw("$temp with (readuncommitted)"), 'piutangheader.agen_id', $temp . ".agen_id")
            ->leftjoin(DB::raw("invoiceheader c with (readuncommitted)"), 'piutangheader.invoice_nobukti', "c.nobukti")
            ->whereRaw("piutangheader.agen_id = $id")
            ->whereRaw("piutangheader.nobukti = $temp.nobukti")
            ->where(function ($query) use ($temp) {
                $query->whereRaw("$temp.sisa != 0")
                    ->orWhereRaw("$temp.sisa is null");
            });

        // dd($query->toSql());

        $data = $query->get();


        return $data;
    }

    public function createTempPiutang($id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader with (readuncommitted)")
            )
            ->select(DB::raw("piutangheader.nobukti,piutangheader.agen_id, sum(pelunasanpiutangdetail.nominal) as nominalbayar, (SELECT (piutangheader.nominal - coalesce(SUM(pelunasanpiutangdetail.nominal),0) - coalesce(SUM(pelunasanpiutangdetail.potongan),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangdetail.piutang_nobukti', 'piutangheader.nobukti')
            ->whereRaw("piutangheader.agen_id = $id")
            ->groupBy('piutangheader.nobukti', 'piutangheader.agen_id', 'piutangheader.nominal');
        // ->get();
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('agen_id')->nullable();
            $table->bigInteger('nominalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'agen_id', 'nominalbayar', 'sisa'], $fetch);


        return $temp;
    }

    public function findUpdate($id)
    {
        $data = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->select(
            'piutangheader.id',
            'piutangheader.nobukti',
            'piutangheader.tglbukti',
            'piutangheader.tgljatuhtempo',
            'piutangheader.postingdari',
            'piutangheader.nominal',
            'piutangheader.invoice_nobukti',
            'piutangheader.agen_id',
            'piutangheader.statuscetak',
            'piutangheader.modifiedby',
            'piutangheader.updated_at',
            'agen.namaagen as agen'
        )->leftJoin('agen', 'piutangheader.agen_id', 'agen.id')
            ->where('piutangheader.id', $id)->first();

        return $data;
    }

    public function selectColumns($query)
    {
        $temppelunasan = '##temppelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasan, function ($table) {
            $table->string('piutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $tes = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.piutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('piutang_nobukti');

        DB::table($temppelunasan)->insertUsing([
            'piutang_nobukti',
            'nominal',
        ], $tes);

        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.tgljatuhtempo,
                 $this->table.postingdari,
                 $this->table.nominal,
                 isnull(c.nominal,0) as nominalpelunasan,
                 piutangheader.nominal-isnull(c.nominal,0) as sisapiutang,
                 $this->table.invoice_nobukti,
                 'agen.namaagen as agen_id',
                 'parameter.text as statuscetak',
                 $this->table.userbukacetak,
                 $this->table.tglbukacetak,
                 'debet.text as coadebet',
                 'kredit.text as coakredit',
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'piutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'piutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), 'piutangheader.coadebet', 'debet.coa')
            ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), 'piutangheader.coakredit', 'kredit.coa')
            ->leftJoin(DB::raw($temppelunasan . " as c"), 'piutangheader.nobukti', 'c.piutang_nobukti');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->float('nominal')->nullable();
            $table->float('nominalpelunasan')->nullable();
            $table->float('sisapiutang')->nullable();
            $table->string('invoice_nobukti')->nullable();
            $table->string('agen_id')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('coadebet')->default();
            $table->string('coakredit')->default();
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
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'tgljatuhtempo', 'postingdari', 'nominal', 'nominalpelunasan', 'sisapiutang', 'invoice_nobukti', 'agen_id', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'coadebet', 'coakredit', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'nominalpelunasan') {
            return $query->orderBy('c.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'sisapiutang') {
            return $query->orderBy(DB::raw("(piutangheader.nominal - isnull(c.nominal,0))"), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coadebet') {
            return $query->orderBy('debet.keterangancoa', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coakredit') {
            return $query->orderBy('kredit.keterangancoa', $this->params['sortOrder']);
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
                                $query = $query->where('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'agen_id') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(piutangheader.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalpelunasan') {
                                $query = $query->whereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'sisapiutang') {
                                $query = $query->whereRaw("format((piutangheader.nominal - isnull(c.nominal,0)), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'coadebet') {
                                $query = $query->where('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coakredit') {
                                $query = $query->where('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo') {
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
                                    $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(piutangheader.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominalpelunasan') {
                                    $query = $query->orWhereRaw("format(c.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'sisapiutang') {
                                    $query = $query->orWhereRaw("format((piutangheader.nominal - isnull(c.nominal,0)), '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'coadebet') {
                                    $query = $query->orWhere('debet.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coakredit') {
                                    $query = $query->orWhere('kredit.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgljatuhtempo') {
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

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function piutangDetails()
    {
        return $this->hasMany(PiutangDetail::class, 'piutang_id');
    }

    public function getSisaPiutang($nobukti, $agen_id)
    {


        $query = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader with (readuncommitted)")
            )
            ->select(DB::raw("piutangheader.nobukti, (SELECT (piutangheader.nominal - coalesce(SUM(pelunasanpiutangdetail.nominal),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangdetail.piutang_nobukti', 'piutangheader.nobukti')
            ->whereRaw("piutangheader.agen_id = $agen_id")
            ->whereRaw("piutangheader.nobukti = '$nobukti'")
            ->groupBy('piutangheader.nobukti', 'piutangheader.nominal')
            ->first();

        return $query;
    }

    public function processStore(array $data): PiutangHeader
    {

        $group = 'PIUTANG BUKTI';
        $subGroup = 'PIUTANG BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $piutangHeader = new PiutangHeader();
        $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $data['agen_id'])->first();

        $getCoapendapatan = db::table('agen')->from(DB::raw("agen with (readuncommitted)"))->where('id', $data['agen_id'])
            ->whereraw("isnull(agen.coapendapatan,'')<>''")
            ->first();

        $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE TAMBAHAN')
            ->where('subgrp', 'DEBET')
            ->where('text', 'DEBET')
            ->first();
        $memocoa = json_decode($paramcoa->memo, true);
        $coa = $memocoa['JURNAL'];


        /*if (isset($getCoapendapatan)) {
            $coapendapatan=$getCoapendapatan->coapendapatan ?? '';
        } else {*/
        $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE TAMBAHAN')
            ->where('subgrp', 'KREDIT')
            ->where('text', 'KREDIT')
            ->first();
        $memo = json_decode($param->memo, true);
        $coapendapatan = $memo['JURNAL'];
        // }
        // dump($coa);
        // dd($coapendapatan);


        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $piutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $piutangHeader->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $piutangHeader->postingdari = $data['postingdari'] ?? 'ENTRY PIUTANG HEADER';
        $piutangHeader->invoice_nobukti = $data['invoice'] ?? '';
        $piutangHeader->modifiedby = auth('api')->user()->name;
        $piutangHeader->info = html_entity_decode(request()->info);
        $piutangHeader->statusformat = $format->id;
        $piutangHeader->agen_id = $data['agen_id'];
        // $piutangHeader->coadebet = $getCoa->coa;
        $piutangHeader->coadebet = $coa;
        // $piutangHeader->coakredit = $getCoa->coapendapatan;
        $piutangHeader->coakredit = $coapendapatan;
        $piutangHeader->statuscetak = $statusCetak->id;
        $piutangHeader->userbukacetak = '';
        $piutangHeader->tglbukacetak = '';
        $piutangHeader->nominal = array_sum($data['nominal_detail']);

        $piutangHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $piutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$piutangHeader->save()) {
            throw new \Exception("Error storing piutang header.");
        }

        $piutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($piutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY PIUTANG HEADER',
            'idtrans' => $piutangHeader->id,
            'nobuktitrans' => $piutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $piutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $piutangDetails = [];

        for ($i = 0; $i < count($data['nominal_detail']); $i++) {

            $piutangDetail = (new PiutangDetail())->processStore($piutangHeader, [
                'nominal' => $data['nominal_detail'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'invoice_nobukti' => $data['invoice_nobukti'][$i] ?? ''
            ]);

            // $coadebet_detail[] = $getCoa->coa;
            // $coakredit_detail[] = $getCoa->coapendapatan;
            $coadebet_detail[] = $coa;
            $coakredit_detail[] = $coapendapatan;
            $keterangan_detail[] = $data['keterangan_detail'][$i];
            $nominal_detail[] = $data['nominal_detail'][$i];

            $piutangDetails[] = $piutangDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($piutangDetail->getTable()),
            'postingdari' =>  $data['postingdari'] ?? 'ENTRY PIUTANG DETAIL',
            'idtrans' =>  $piutangHeaderLogTrail->id,
            'nobuktitrans' => $piutangHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $piutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $piutangHeader->nobukti,
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' => $data['postingdari'] ?? 'ENTRY PIUTANG HEADER',
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];

        (new JurnalUmumHeader())->processStore($jurnalRequest);
        return $piutangHeader;
    }

    public function processUpdate(PiutangHeader $piutangHeader, array $data): PiutangHeader
    {
        $proseslain = $data['proseslain'] ?? 0;
        $nobuktiOld = $piutangHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PIUTANG')->first();
        $getCoa = Agen::from(DB::raw("agen with (readuncommitted)"))->where('id', $data['agen_id'])->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'PIUTANG BUKTI';
            $subGroup = 'PIUTANG BUKTI';

            $querycek = DB::table('piutangheader')->from(
                DB::raw("piutangheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $piutangHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $piutangHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }
            $piutangHeader->nobukti = $nobukti;
            $piutangHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $paramcoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE TAMBAHAN')
            ->where('subgrp', 'DEBET')
            ->where('text', 'DEBET')
            ->first();
        $memocoa = json_decode($paramcoa->memo, true);
        $coa = $memocoa['JURNAL'];

        $param = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL PIUTANG INVOICE TAMBAHAN')
            ->where('subgrp', 'KREDIT')
            ->where('text', 'KREDIT')
            ->first();
        $memo = json_decode($param->memo, true);
        $coapendapatan = $memo['JURNAL'];

        $piutangHeader->modifiedby = auth('api')->user()->name;
        $piutangHeader->info = html_entity_decode(request()->info);
        $piutangHeader->tgljatuhtempo = date('Y-m-d', strtotime($data['tgljatuhtempo']));
        $piutangHeader->agen_id = $data['agen_id'];
        $piutangHeader->invoice_nobukti = $data['invoice'] ?? '';
        $piutangHeader->coadebet = $coa;
        $piutangHeader->coakredit = $coapendapatan;
        $piutangHeader->postingdari = $data['postingdari'] ?? 'EDIT PIUTANG HEADER';
        $piutangHeader->nominal = ($proseslain != 0) ? $data['nominal'] : array_sum($data['nominal_detail']);


        if (!$piutangHeader->save()) {
            throw new \Exception("Error updating piutang header.");
        }

        $piutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($piutangHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT PIUTANG HEADER',
            'idtrans' => $piutangHeader->id,
            'nobuktitrans' => $piutangHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $piutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        PiutangDetail::where('piutang_id', $piutangHeader->id)->delete();

        $piutangDetails = [];

        /*if (isset($getCoapendapatan)) {
            $coapendapatan=$getCoapendapatan->coapendapatan ?? '';
        } else {*/
        // }
        // dump($coa);
        // dd($coapendapatan);


        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $piutangDetail = (new PiutangDetail())->processStore($piutangHeader, [
                'nominal' => $data['nominal_detail'][$i],
                'keterangan' => $data['keterangan_detail'][$i],
                'invoice_nobukti' => $data['invoice_nobukti'][$i] ?? ''
            ]);

            $coadebet_detail[] = $coa;
            $coakredit_detail[] = $coapendapatan;
            // $coadebet_detail[] = $getCoa->coa;
            // $coakredit_detail[] = $getCoa->coapendapatan;
            $keterangan_detail[] = $data['keterangan_detail'][$i];
            $nominal_detail[] = $data['nominal_detail'][$i];

            $piutangDetails[] = $piutangDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($piutangDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT PIUTANG DETAIL',
            'idtrans' =>  $piutangHeaderLogTrail->id,
            'nobuktitrans' => $piutangHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $piutangDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        $jurnalRequest = [
            'tanpaprosesnobukti' => 1,
            'nobukti' => $piutangHeader->nobukti,
            'tglbukti' => $piutangHeader->tglbukti,
            'postingdari' => $data['postingdari'] ?? 'EDIT PIUTANG HEADER',
            'statusformat' => "0",
            'coakredit_detail' => $coakredit_detail,
            'coadebet_detail' => $coadebet_detail,
            'nominal_detail' => $nominal_detail,
            'keterangan_detail' => $keterangan_detail
        ];
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();

        $newJurnal = new JurnalUmumHeader();
        $newJurnal = $newJurnal->find($getJurnal->id);
        $jurnalumumHeader = (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);

        return $piutangHeader;
    }


    public function processDestroy($id, $postingDari = ''): PiutangHeader
    {
        $piutangDetails = PiutangDetail::lockForUpdate()->where('piutang_id', $id)->get();

        $piutangHeader = new PiutangHeader();
        $piutangHeader = $piutangHeader->lockAndDestroy($id);

        $piutangHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $piutangHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $piutangHeader->id,
            'nobuktitrans' => $piutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $piutangHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PIUTANGDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $piutangHeaderLogTrail['id'],
            'nobuktitrans' => $piutangHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $piutangDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $piutangHeader->nobukti)->first();
        $jurnalumumHeader = (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        return $piutangHeader;
    }

    public function getExport($id)
    {
        $temppelunasan = '##temppelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasan, function ($table) {
            $table->string('piutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $query = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.piutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('piutang_nobukti');

        DB::table($temppelunasan)->insertUsing([
            'piutang_nobukti',
            'nominal',
        ], $query);

        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw("piutangheader with (readuncommitted)")
        )->select(
            'piutangheader.id',
            'piutangheader.nobukti',
            'piutangheader.tglbukti',
            'piutangheader.postingdari',
            'piutangheader.nominal',
            DB::raw("isnull(c.nominal,0) as nominalpelunasan"),
            DB::raw("piutangheader.nominal-isnull(c.nominal,0) as sisapiutang"),
            'piutangheader.invoice_nobukti',
            'debet.keterangancoa as coadebet',
            'kredit.keterangancoa as coakredit',
            'agen.namaagen as agen_id',
            'piutangheader.jumlahcetak',
            'statuscetak.memo as statuscetak',
            'statuscetak.id as  statuscetak_id',
            DB::raw("'Laporan Piutang' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'piutangheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'piutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("akunpusat as debet with (readuncommitted)"), 'piutangheader.coadebet', 'debet.coa')
            ->leftJoin(DB::raw("akunpusat as kredit with (readuncommitted)"), 'piutangheader.coakredit', 'kredit.coa')
            ->leftJoin(DB::raw($temppelunasan . " as c"), 'piutangheader.nobukti', 'c.piutang_nobukti');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(piutangheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(piutangheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("piutangheader.statuscetak", $statusCetak);
        }
        $data = $query->first();
        return $data;
    }
}
