<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaDebetHeader extends MyModel
{
    use HasFactory;

    protected $table = 'notadebetheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $panjar = request()->panjar ?? '';
        $agen_id= request()->agen_id ?? 0;

        if ($panjar=='PANJAR') {
            $tglnow = date('Y-m-d');

            $temppanjar = '##temppanjar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppanjar, function ($table) {
                $table->string('nobukti', 50)->nullable();
                $table->double('nominal')->nullable();                
            });

            DB::table($temppanjar)->insertUsing([
                'nobukti',
                'nominal',
            ], (new LaporanKartuPanjar())->getSisapanjar($tglnow, $tglnow, 0, 0, 1,$agen_id,$tglnow));

            // dd(db::table($temppanjar)->get());
        }

        if ($panjar=='PANJAR') {
                        // dd(db::table($temppanjar)->get());

            $query = DB::table($this->table)->from(
                DB::raw($this->table . " with (readuncommitted)")
            )->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                "$this->table.postingdari",
                "$this->table.statusapproval",
                "$this->table.tgllunas",
                "$this->table.userapproval",
                DB::raw('(case when (year(notadebetheader.tglapproval) <= 2000) then null else notadebetheader.tglapproval end ) as tglapproval'),
                "$this->table.userbukacetak",
                DB::raw('(case when (year(notadebetheader.tglbukacetak) <= 2000) then null else notadebetheader.tglbukacetak end ) as tglbukacetak'),
                "$this->table.statusformat",
                "$this->table.statuscetak",
                "$this->table.created_at",
                "$this->table.updated_at",
                "statuscetak.memo as statuscetak_memo",
                "$this->table.modifiedby",
                "parameter.memo as  statusapproval_memo",
                "$this->table.penerimaan_nobukti",
                "agen.namaagen as agen",
                "bank.namabank as bank",
                "alatbayar.namaalatbayar as alatbayar",
                db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
                db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
                db::raw("cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpelunasanpiutangheader"),
                db::raw("cast(cast(format((cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpelunasanpiutangheader"),
                db::raw("isnull(panjar.nominal,0) as nominal")
            )
    
                ->leftJoin(DB::raw("pelunasanpiutangheader with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', '=', 'pelunasanpiutangheader.nobukti')
                ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'notadebetheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
                ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notadebetheader.bank_id', 'bank.id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notadebetheader.agen_id', 'agen.id')
                ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notadebetheader.alatbayar_id', 'alatbayar.id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
                ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id')
                ->Join(DB::raw($temppanjar . " panjar"), 'notadebetheader.nobukti', 'panjar.nobukti');

        } else {
        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.pelunasanpiutang_nobukti",
            "$this->table.tglbukti",
            "$this->table.postingdari",
            "$this->table.statusapproval",
            "$this->table.tgllunas",
            "$this->table.userapproval",
            DB::raw('(case when (year(notadebetheader.tglapproval) <= 2000) then null else notadebetheader.tglapproval end ) as tglapproval'),
            "$this->table.userbukacetak",
            DB::raw('(case when (year(notadebetheader.tglbukacetak) <= 2000) then null else notadebetheader.tglbukacetak end ) as tglbukacetak'),
            "$this->table.statusformat",
            "$this->table.statuscetak",
            "$this->table.created_at",
            "$this->table.updated_at",
            "statuscetak.memo as statuscetak_memo",
            "$this->table.modifiedby",
            "parameter.memo as  statusapproval_memo",
            "$this->table.penerimaan_nobukti",
            "agen.namaagen as agen",
            "bank.namabank as bank",
            "alatbayar.namaalatbayar as alatbayar",
            db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
            db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),
            db::raw("cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpelunasanpiutangheader"),
            db::raw("cast(cast(format((cast((format(pelunasanpiutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpelunasanpiutangheader"),
            db::raw("0 as nominal")

        )

            ->leftJoin(DB::raw("pelunasanpiutangheader with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', '=', 'pelunasanpiutangheader.nobukti')
            ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'notadebetheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notadebetheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notadebetheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notadebetheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id');
        }
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(notadebetheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(notadebetheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("notadebetheader.statuscetak", $statusCetak);
        }


        // dd('test');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function cekvalidasiaksi($id)
    {
        $notaDebet = DB::table("notadebetheader")->from(DB::raw("notadebetheader with (readuncommitted)"))->where('id', $id)->first();
        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.notadebet_nobukti'
            )
            ->where('a.notadebet_nobukti', '=', $notaDebet->nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang ' . $pelunasanPiutang->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        if ($notaDebet->penerimaan_nobukti != '') {
            $jurnal = DB::table('penerimaanheader')
                ->from(
                    DB::raw("penerimaanheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.nobukti', '=', $notaDebet->penerimaan_nobukti)
                ->first();

            if (isset($jurnal)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'Approval Jurnal ' . $jurnal->nobukti,
                    'kodeerror' => 'SAP'
                ];
                goto selesai;
            }
        } else {

            $jurnal = DB::table('notadebetheader')
                ->from(
                    DB::raw("notadebetheader as a with (readuncommitted)")
                )
                ->select(
                    'a.nobukti'
                )
                ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
                ->where('a.nobukti', '=', $notaDebet->nobukti)
                ->first();
            if (isset($jurnal)) {
                $data = [
                    'kondisi' => true,
                    'keterangan' => 'Approval Jurnal ' . $jurnal->nobukti,
                    'kodeerror' => 'SAP'
                ];
                goto selesai;
            }
        }

        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('alatbayar', 255)->nullable();
        });


        $bank = DB::table('bank')->from(
            DB::raw('bank with (readuncommitted)')
        )
            ->select(
                'id as bank_id',
                'namabank as bank',

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
            ->first();


        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
                'alatbayar_id',
                'alatbayar',
            );

        $data = $query->first();

        return $data;
    }


    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->date('tgllunas')->nullable();
            $table->string('agen', 200)->nullable();
            $table->string('pelunasanpiutang_nobukti', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->string('alatbayar', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->string('statusapproval_memo')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak_memo')->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }

        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $query = $this->sort($query);
        $models = $this->filter($query);
        $models = $query
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);


        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "tgllunas",
            "agen",
            "pelunasanpiutang_nobukti",
            "bank",
            "alatbayar",
            "penerimaan_nobukti",
            "postingdari",
            "statusapproval_memo",
            "userapproval",
            "tglapproval",
            "statuscetak_memo",
            "userbukacetak",
            "tglbukacetak",
            "modifiedby",
            "created_at",
            "updated_at",
        ], $models);
        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.tgllunas",
                'agen.namaagen as agen',
                "$this->table.pelunasanpiutang_nobukti",
                'bank.namabank as bank',
                'alatbayar.namaalatbayar as alatbayar',
                "$this->table.penerimaan_nobukti",
                "$this->table.postingdari",
                "parameter.text as  statusapproval_memo",
                "$this->table.userapproval",
                DB::raw('(case when (year(notadebetheader.tglapproval) <= 2000) then null else notadebetheader.tglapproval end ) as tglapproval'),
                "statuscetak.text as statuscetak_memo",
                "$this->table.userbukacetak",
                DB::raw('(case when (year(notadebetheader.tglbukacetak) <= 2000) then null else notadebetheader.tglbukacetak end ) as tglbukacetak'),
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
            )

            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notadebetheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notadebetheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notadebetheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id');
        
    }


    public function getNotaDebet($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail with (readuncommitted)")
        )
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        notadebetheader.keterangan,
        pelunasanpiutangdetail.coalebihbayar,
        COALESCE (pelunasanpiutangdetail.nominallebihbayar, 0) as lebihbayar '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("notadebetheader with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutangdetail.nobukti')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" EXISTS (
            SELECT notadebetheader.pelunasanpiutang_nobukti
            FROM notadebetdetail with (readuncommitted) 
			left join notadebetheader  with (readuncommitted) on notadebetdetail.notadebet_id = notadebetheader.id
            WHERE notadebetheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.nominallebihbayar', '>', 0)
            ->where('notadebetheader.id', $id);

        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'agen') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar') {
            return $query->orderBy('alatbayar.namaalatbayar', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        $panjar = request()->panjar ?? '';

        if ($panjar=='PANJAR') {
            if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
                switch ($this->params['filters']['groupOp']) {
                    case "AND":
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval_memo') {
                                    $query = $query->where('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak_memo') {
                                    $query = $query->where('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'agen') {
                                    $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->where('panjar.nominal', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank') {
                                    $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar') {
                                    $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
                                    if ($filters['field'] == 'statusapproval_memo') {
                                        $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                    } else if ($filters['field'] == 'statuscetak_memo') {
                                        $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                    } else if ($filters['field'] == 'agen') {
                                        $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                    } else if ($filters['field'] == 'nominal') {
                                        $query = $query->orwhere('panjar.nominal', 'LIKE', "%$filters[data]%");
    
                                    } else if ($filters['field'] == 'bank') {
                                        $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                    } else if ($filters['field'] == 'alatbayar') {
                                        $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                    } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
        } else {
            if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
                switch ($this->params['filters']['groupOp']) {
                    case "AND":
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval_memo') {
                                    $query = $query->where('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak_memo') {
                                    $query = $query->where('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'agen') {
                                    $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank') {
                                    $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'alatbayar') {
                                    $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
                                    if ($filters['field'] == 'statusapproval_memo') {
                                        $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                    } else if ($filters['field'] == 'statuscetak_memo') {
                                        $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                    } else if ($filters['field'] == 'agen') {
                                        $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                    } else if ($filters['field'] == 'bank') {
                                        $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                    } else if ($filters['field'] == 'alatbayar') {
                                        $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                                    } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgllunas' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
        }
     
        if (request()->cetak && request()->periode) {
            $query->where('notadebetheader.statuscetak', '<>', request()->cetak)
                ->whereYear('notadebetheader.tglbukti', '=', request()->year)
                ->whereMonth('notadebetheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = NotaDebetHeader::from(DB::raw("notadebetheader with (readuncommitted)"))
            ->select('notadebetheader.id', 'notadebetheader.nobukti', 'notadebetheader.tglbukti', 'notadebetheader.tgllunas', 'notadebetheader.agen_id', 'agen.namaagen as agen', 'notadebetheader.bank_id', 'bank.namabank as bank', 'notadebetheader.alatbayar_id', 'alatbayar.namaalatbayar as alatbayar', 'notadebetheader.nowarkat', 'notadebetheader.penerimaan_nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'notadebetheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'notadebetheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'notadebetheader.alatbayar_id', 'alatbayar.id');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function processStore(array $data): NotaDebetHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? '';
        $group = 'NOTA DEBET BUKTI';
        $subGroup = 'NOTA DEBET BUKTI';
        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subGroup)->first();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $notaDebetHeader = new NotaDebetHeader();

        $notaDebetHeader->pelunasanpiutang_nobukti = $data['pelunasanpiutang_nobukti'] ?? '';
        $notaDebetHeader->agen_id = $data['agen_id'];
        $notaDebetHeader->bank_id = $data['bank_id'];
        $notaDebetHeader->alatbayar_id = $data['alatbayar_id'];
        $notaDebetHeader->nowarkat = $data['nowarkat'] ?? '';
        $notaDebetHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $notaDebetHeader->statusapproval = $statusApproval->id;
        $notaDebetHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $notaDebetHeader->statusformat = $format->id;
        $notaDebetHeader->statuscetak = $statusCetak->id;
        $notaDebetHeader->postingdari = $data['postingdari'] ?? 'ENTRY NOTA DEBET HEADER';
        $notaDebetHeader->modifiedby = auth('api')->user()->name;
        $notaDebetHeader->info = html_entity_decode(request()->info);
        $notaDebetHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $notaDebetHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$notaDebetHeader->save()) {
            throw new \Exception("Error storing nota debet header.");
        }

        $notaDebetHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY NOTA DEBET HEADER',
            'idtrans' => $notaDebetHeader->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaDebetHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $notaDebetDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        $getCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'TIPENOTADEBET')->where('text', 'UANG DITERIMA DIMUKA')->first();

        $memoNotaDebetCoa = json_decode($getCoa->memo, true);
        if ($tanpaprosesnobukti != 1) {
            $data['cekcoakredit'] = $memoNotaDebetCoa['JURNAL'];
        }
        $nominal = 0;
        for ($i = 0; $i < count($data['nominallebihbayar']); $i++) {
            $notaDebetDetail = (new NotaDebetDetail())->processStore($notaDebetHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i] ?? '',
                "nominal" => $data['nominalpiutang'][$i] ?? 0,
                "nominalbayar" => $data['nominal'][$i] ?? 0,
                "lebihbayar" => $data['nominallebihbayar'][$i],
                "keterangandetail" => $data['keterangan_detail'][$i] ?? '-',
                "coalebihbayar" => $data['coakredit'][$i] ?? $memoNotaDebetCoa['JURNAL']
            ]);
            $notaDebetDetails[] = $notaDebetDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i] ?? $memoNotaDebetCoa['JURNAL'];
            $coadebet_detail[] = $data['coadebet'][$i] ?? '';
            $nominal_detail[] = $data['nominallebihbayar'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i] ?? '-';
            $tgljatuhtempo[] = $notaDebetHeader->tglbukti;
            $invoice[] = $data['invoice_nobukti'][$i] ?? '';
            $pelunasanPiutang[] = $data['pelunasanpiutang_nobukti'] ?? '';
            $noWarkat[] = $data['nowarkat'] ?? '';
            $bankId[] = $data['bank_id'];
            $nominal = $nominal + $data['nominallebihbayar'][$i];
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'ENTRY NOTA DEBET DETAIL',
            'idtrans' => $notaDebetHeaderLogTrail->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $notaDebetDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        if ($data['cekcoakredit'] == $memoNotaDebetCoa['JURNAL']) {
            $alatbayarGiro = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
            if ($data['alatbayar_id'] != $alatbayarGiro->id) {
                $penerimaanRequest = [
                    'tglbukti' => $data['tglbukti'],
                    'pelanggan_id' => 0,
                    'agen_id' => $data['agen_id'],
                    'postingdari' => $data['postingdari'] ?? 'ENTRY NOTA DEBET',
                    'diterimadari' => $data['agen'],
                    'tgllunas' => $data['tglbukti'],
                    'bank_id' => $data['bank_id'],
                    'nowarkat' => null,
                    'tgljatuhtempo' => $tgljatuhtempo,
                    'nominal_detail' => $nominal_detail,
                    'coakredit' => $coakredit_detail,
                    'keterangan_detail' => $keterangan_detail,
                    'invoice_nobukti' => $invoice,
                    'bankpelanggan_id' => null,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutang,
                    'bulanbeban' => null,

                ];
                $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);
                $notaDebetHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
                $notaDebetHeader->save();
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
                    'tgljatuhtempo' => $tgljatuhtempo,
                    'nominal' => $nominal_detail,
                    'coakredit' => $coakredit_detail,
                    'keterangan_detail' => $keterangan_detail,
                    'invoice_nobukti' => $invoice,
                    'pelunasanpiutang_nobukti' => $pelunasanPiutang,
                    'bank_id' => $bankId
                ];
                $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processStore($penerimaanGiroRequest);
                $notaDebetHeader->penerimaan_nobukti = $penerimaanGiroHeader->nobukti;
                $notaDebetHeader->save();
            }
            $notaDebetRincian = (new NotaDebetRincian())->processStore($notaDebetHeader, [ 
                "agen_id" => $data['agen_id'],
                "nominal" => $nominal,
            ]);
        } else {

            /*STORE JURNAL*/
            $jurnalRequest = [
                'tanpaprosesnobukti' => 1,
                'nobukti' => $notaDebetHeader->nobukti,
                'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                'postingdari' => $data['postingdari'],
                'statusapproval' => $statusApproval->id,
                'userapproval' => "",
                'tglapproval' => "",
                'modifiedby' => auth('api')->user()->name,
                'statusformat' => "0",
                'coakredit_detail' => $coakredit_detail,
                'coadebet_detail' => $coadebet_detail,
                'nominal_detail' => $nominal_detail,
                'keterangan_detail' => $keterangan_detail
            ];
            $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
        }

        return $notaDebetHeader;
    }

    public function processUpdate(NotaDebetHeader $notaDebetHeader, array $data): NotaDebetHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? '';
        $nobuktiOld = $notaDebetHeader->nobukti;
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'NOTA DEBET')->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'NOTA DEBET BUKTI';
            $subGroup = 'NOTA DEBET BUKTI';
            $querycek = DB::table('notadebetheader')->from(
                DB::raw("notadebetheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $notaDebetHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $notaDebetHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $notaDebetHeader->nobukti = $nobukti;
            $notaDebetHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $notaDebetHeader->tgllunas = date('Y-m-d', strtotime($data['tgllunas']));
        $notaDebetHeader->nowarkat = $data['nowarkat'] ?? '';
        $notaDebetHeader->agen_id = $data['agen_id'] ?? '';
        $notaDebetHeader->bank_id = $data['bank_id'];
        $notaDebetHeader->alatbayar_id = $data['alatbayar_id'];
        $notaDebetHeader->modifiedby = auth('api')->user()->name;
        $notaDebetHeader->info = html_entity_decode(request()->info);

        if (!$notaDebetHeader->save()) {
            throw new \Exception("Error Update nota debet header.");
        }

        $notaDebetHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetHeader->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT NOTA DEBET HEADER',
            'idtrans' => $notaDebetHeader->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaDebetHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $getPreviousCoa = DB::table("notadebetdetail")->from(DB::raw("notadebetdetail with (readuncommitted)"))->select('coalebihbayar')->where('notadebet_id', $notaDebetHeader->id)->first();

        $notaDebetDetail = NotaDebetDetail::where('notadebet_id', $notaDebetHeader->id)->lockForUpdate()->delete();
        NotaDebetRincian::where('notadebet_id', $notaDebetHeader->id)->lockForUpdate()->delete();

        $notaDebetDetails = [];
        $coakredit_detail = [];
        $coadebet_detail = [];
        $nominal_detail = [];
        $keterangan_detail = [];
        $getCoa = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'TIPENOTADEBET')->where('text', 'UANG DITERIMA DIMUKA')->first();

        $memoNotaDebetCoa = json_decode($getCoa->memo, true);
        if ($tanpaprosesnobukti != 1) {
            $data['cekcoakredit'] = $memoNotaDebetCoa['JURNAL'];
        }
        $nominal = 0;
        for ($i = 0; $i < count($data['nominallebihbayar']); $i++) {
            $notaDebetDetail = (new NotaDebetDetail())->processStore($notaDebetHeader, [
                "invoice_nobukti" => $data['invoice_nobukti'][$i] ?? '',
                "nominal" => $data['nominalpiutang'][$i] ?? 0,
                "nominalbayar" => $data['nominal'][$i] ?? 0,
                "lebihbayar" => $data['nominallebihbayar'][$i],
                "keterangandetail" => $data['keterangan_detail'][$i] ?? '-',
                "coalebihbayar" => $data['coakredit'][$i] ?? $memoNotaDebetCoa['JURNAL']
            ]);
            $notaDebetDetails[] = $notaDebetDetail->toArray();
            $coakredit_detail[] = $data['coakredit'][$i] ?? $memoNotaDebetCoa['JURNAL'];
            $coadebet_detail[] = $data['coadebet'][$i] ?? '';
            $nominal_detail[] = $data['nominallebihbayar'][$i];
            $keterangan_detail[] = $data['keterangan_detail'][$i] ?? '-';
            $tgljatuhtempo[] = $notaDebetHeader->tglbukti;
            $invoice[] = $data['invoice_nobukti'][$i] ?? '';
            $pelunasanPiutang[] = $data['pelunasanpiutang_nobukti'] ?? '';
            $noWarkat[] = $data['nowarkat'] ?? '';
            $bankId[] = $data['bank_id'];
            $nominal = $nominal + $data['nominallebihbayar'][$i];
        }

        if ($data['cekcoakredit'] == $memoNotaDebetCoa['JURNAL']) {
            $notaDebetRincian = (new NotaDebetRincian())->processStore($notaDebetHeader, [
                "agen_id" => $data['agen_id'],
                "nominal" => $nominal,
            ]);
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($notaDebetDetail->getTable()),
            'postingdari' => $data['postingdari'] ?? 'EDIT NOTA DEBET DETAIL',
            'idtrans' => $notaDebetHeaderLogTrail->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $notaDebetDetails,
            'modifiedby' => auth('api')->user()->user
        ]);

        if ($tanpaprosesnobukti == 1) {
            // CEK JIKA COA BERGANTI
            if ($data['cekcoakredit'] != $getPreviousCoa->coalebihbayar) {
                // DELETE EXISTED NO BUKTI
                if ($getPreviousCoa->coalebihbayar == $memoNotaDebetCoa['JURNAL']) {

                    if ($notaDebetHeader->penerimaan_nobukti != '') {
                        if ($notaDebetHeader->alatbayar_id == 3) {
                            $getPenerimaan = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->penerimaan_nobukti)->first();
                            if ($getPenerimaan != null) {
                                (new PenerimaanGiroHeader())->processDestroy($getPenerimaan->id, $data['postingdari']);
                            }
                        } else {
                            $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->penerimaan_nobukti)->first();
                            if ($getPenerimaan != null) {
                                (new PenerimaanHeader())->processDestroy($getPenerimaan->id, $data['postingdari']);
                            }
                        }

                        $notaDebetHeader->penerimaan_nobukti = '';
                        $notaDebetHeader->save();
                    }
                } else {
                    $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->nobukti)->first();
                    (new JurnalUmumHeader())->processDestroy($getJurnal->id, $data['postingdari']);
                }

                // STORE
                if ($data['cekcoakredit'] == $memoNotaDebetCoa['JURNAL']) {
                    $alatbayarGiro = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
                    if ($data['alatbayar_id'] != $alatbayarGiro->id) {
                        $penerimaanRequest = [
                            'tglbukti' => $data['tglbukti'],
                            'pelanggan_id' => 0,
                            'agen_id' => $data['agen_id'],
                            'postingdari' => $data['postingdari'] ?? 'ENTRY NOTA DEBET',
                            'diterimadari' => $data['agen'],
                            'tgllunas' => $data['tglbukti'],
                            'bank_id' => $data['bank_id'],
                            'nowarkat' => null,
                            'tgljatuhtempo' => $tgljatuhtempo,
                            'nominal_detail' => $nominal_detail,
                            'coakredit' => $coakredit_detail,
                            'keterangan_detail' => $keterangan_detail,
                            'invoice_nobukti' => $invoice,
                            'bankpelanggan_id' => null,
                            'pelunasanpiutang_nobukti' => $pelunasanPiutang,
                            'bulanbeban' => null,

                        ];
                        $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);
                        $notaDebetHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
                        $notaDebetHeader->save();
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
                            'tgljatuhtempo' => $tgljatuhtempo,
                            'nominal' => $nominal_detail,
                            'coakredit' => $coakredit_detail,
                            'keterangan_detail' => $keterangan_detail,
                            'invoice_nobukti' => $invoice,
                            'pelunasanpiutang_nobukti' => $pelunasanPiutang,
                            'bank_id' => $bankId
                        ];
                        $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processStore($penerimaanGiroRequest);
                        $notaDebetHeader->penerimaan_nobukti = $penerimaanGiroHeader->nobukti;
                        $notaDebetHeader->save();
                    }
                } else {

                    /*STORE JURNAL*/
                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $notaDebetHeader->nobukti,
                        'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                        'postingdari' => $data['postingdari'],
                        'userapproval' => "",
                        'tglapproval' => "",
                        'modifiedby' => auth('api')->user()->name,
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'nominal_detail' => $nominal_detail,
                        'keterangan_detail' => $keterangan_detail
                    ];
                    $jurnalUmumHeader = (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            }
        }

        if ($data['cekcoakredit'] == $getPreviousCoa->coalebihbayar) {
            if ($data['cekcoakredit'] == $memoNotaDebetCoa['JURNAL']) {
                $alatbayarGiro = DB::table("alatbayar")->from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
                if ($data['alatbayar_id'] != $alatbayarGiro->id) {
                    $penerimaanRequest = [
                        'tglbukti' => $notaDebetHeader->tglbukti,
                        'pelanggan_id' => 0,
                        'agen_id' => $data['agen_id'],
                        'postingdari' => 'EDIT NOTA DEBET',
                        'diterimadari' => $data['agen'],
                        'tgllunas' => $notaDebetHeader->tglbukti,
                        'bank_id' => $notaDebetHeader->bank_id,
                        'nowarkat' => null,
                        'tgljatuhtempo' => $tgljatuhtempo,
                        'nominal_detail' => $nominal_detail,
                        'coakredit' => $coakredit_detail,
                        'keterangan_detail' => $keterangan_detail,
                        'invoice_nobukti' => null,
                        'bankpelanggan_id' => null,
                        'pelunasanpiutang_nobukti' => null,
                        'bulanbeban' => null,

                    ];
                    $penerimaanHeader = PenerimaanHeader::where('nobukti', $notaDebetHeader->penerimaan_nobukti)->first();
                    $dataPenerimaan = (new PenerimaanHeader())->processUpdate($penerimaanHeader, $penerimaanRequest);
                    $notaDebetHeader->penerimaan_nobukti = $dataPenerimaan->nobukti;
                    $notaDebetHeader->save();
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
                        'tgljatuhtempo' => $tgljatuhtempo,
                        'nominal' => $nominal_detail,
                        'coakredit' => $coakredit_detail,
                        'keterangan_detail' => $keterangan_detail,
                        'invoice_nobukti' => $invoice,
                        'pelunasanpiutang_nobukti' => $pelunasanPiutang,
                        'bank_id' => $bankId
                    ];
                    $penerimaanHeader = PenerimaanGiroHeader::where('nobukti', $notaDebetHeader->penerimaan_nobukti)->first();
                    $penerimaanGiroHeader = (new PenerimaanGiroHeader())->processUpdate($penerimaanHeader, $penerimaanGiroRequest);
                    $notaDebetHeader->penerimaan_nobukti = $penerimaanGiroHeader->nobukti;
                    $notaDebetHeader->save();
                }
            } else {

                /*STORE JURNAL*/
                $jurnalRequest = [                    
                    'tanpaprosesnobukti' => 1,
                    'nobukti' => $notaDebetHeader->nobukti,
                    'tglbukti' => $notaDebetHeader->tglbukti,
                    'postingdari' => $data['postingdari'],
                    'coakredit_detail' => $coakredit_detail,
                    'coadebet_detail' => $coadebet_detail,
                    'nominal_detail' => $nominal_detail,
                    'keterangan_detail' => $keterangan_detail
                ];
                $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $nobuktiOld)->first();
                $newJurnal = new JurnalUmumHeader();
                $newJurnal = $newJurnal->find($getJurnal->id);
                (new JurnalUmumHeader())->processUpdate($newJurnal, $jurnalRequest);
            }
        }
        return $notaDebetHeader;
    }

    public function processDestroy($id, $postingDari = ''): NotaDebetHeader
    {
        $notaDebetDetails = NotaDebetDetail::lockForUpdate()->where('notadebet_id', $id)->get();

        $notaDebetHeader = new NotaDebetHeader();
        $notaDebetHeader = $notaDebetHeader->lockAndDestroy($id);

        $notaDebetHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $notaDebetHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $notaDebetHeader->id,
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaDebetHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'NOTADEBETDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $notaDebetHeaderLogTrail['id'],
            'nobuktitrans' => $notaDebetHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $notaDebetDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);
        if ($notaDebetHeader->penerimaan_nobukti != '') {
            if ($notaDebetHeader->alatbayar_id == 3) {
                $getPenerimaan = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->penerimaan_nobukti)->first();
                if ($getPenerimaan != null) {
                    (new PenerimaanGiroHeader())->processDestroy($getPenerimaan->id, $postingDari);
                }
            } else {
                $getPenerimaan = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->penerimaan_nobukti)->first();
                if ($getPenerimaan != null) {
                    (new PenerimaanHeader())->processDestroy($getPenerimaan->id, $postingDari);
                }
            }
        } else {
            $getJurnal = JurnalUmumHeader::from(DB::raw("jurnalumumheader with (readuncommitted)"))->where('nobukti', $notaDebetHeader->nobukti)->first();
            (new JurnalUmumHeader())->processDestroy($getJurnal->id, $postingDari);
        }
        return $notaDebetHeader;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("notadebetheader with (readuncommitted)"))
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                "$this->table.postingdari",
                "$this->table.tgllunas",
                "$this->table.jumlahcetak",
                'pelunasanpiutang.penerimaan_nobukti',
                'statuscetak.memo as statuscetak',
                'statuscetak.id as  statuscetak_id',
                DB::raw("'Nota Debet' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("pelunasanpiutangheader as pelunasanpiutang with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id');

        $data = $query->first();
        return $data;
    }
}
