<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceDetail extends MyModel
{
    use HasFactory;

    protected $table = 'invoicedetail';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $tempsp = '##tempsp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsp, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nocont', 1000)->nullable();
                $table->string('spfull', 1000)->nullable();
                $table->string('spempty', 1000)->nullable();
                $table->string('spfullempty', 1000)->nullable();
                $table->integer('pelanggan_id')->nullable();
                $table->integer('container_id')->nullable();
                $table->date('tglsp')->nullable();
                $table->integer('sampai_id')->nullable();
            });

            $tempinvoice = '##tempinvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempinvoice, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
            });

            $querydata = DB::table('invoicedetail')->from(
                DB::raw("invoicedetail a with (readuncommitted)")
            )
                ->select(
                    'orderantrucking_nobukti as jobtrucking',
                )
                ->where('invoice_id', '=', request()->invoice_id);

            DB::table($tempinvoice)->insertUsing([
                'jobtrucking',
            ], $querydata);

            $querysp = DB::table('suratpengantar')->from(
                DB::raw("suratpengantar a with (readuncommitted)")
            )
                ->select(
                    'a.jobtrucking',
                    DB::raw("(case when isnull(a.container_id,0)=3 then a.nocont+'/'+ a.nocont2 else a.nocont end) as nocont"),
                    DB::raw("(case when isnull(c.kodestatuscontainer,'')='FULL' then a.nosp else '' end) as spfull"),
                    DB::raw("(case when isnull(c.kodestatuscontainer,'')='EMPTY' then a.nosp else '' end) as spempty"),
                    DB::raw("(case when isnull(c.kodestatuscontainer,'')='FULL EMPTY' then a.nosp else '' end) as spfullempty"),
                    'a.pelanggan_id',
                    'a.container_id',
                    'a.tglsp',
                    'a.sampai_id',
                )
                ->join(DB::raw($tempinvoice . " b "), 'a.jobtrucking', 'b.jobtrucking')
                ->leftjoin(DB::raw("statuscontainer as c with (readuncommitted)"), 'a.statuscontainer_id', 'c.id');

            DB::table($tempsp)->insertUsing([
                'jobtrucking',
                'nocont',
                'spfull',
                'spempty',
                'spfullempty',
                'pelanggan_id',
                'container_id',
                'tglsp',
                'sampai_id'
            ], $querysp);

            $tempsprekap = '##tempsprekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempsprekap, function ($table) {
                $table->string('jobtrucking', 1000)->nullable();
                $table->string('nocont', 1000)->nullable();
                $table->string('spfull', 1000)->nullable();
                $table->string('spempty', 1000)->nullable();
                $table->string('spfullempty', 1000)->nullable();
                $table->integer('pelanggan_id')->nullable();
                $table->integer('container_id')->nullable();
                $table->date('tglsp')->nullable();
                $table->integer('sampai_id')->nullable();
            });

            $querysprekap = DB::table($tempsp)->from(
                DB::raw($tempsp . " a ")
            )
                ->select(
                    'a.jobtrucking',
                    DB::Raw("max(a.nocont) as nocont"),
                    DB::Raw("max(a.spfull) as spfull"),
                    DB::Raw("max(a.spempty) as spempty"),
                    DB::Raw("max(a.spfullempty) as spfullempty"),
                    DB::Raw("max(a.pelanggan_id) as pelanggan_id"),
                    DB::Raw("max(a.container_id) as container_id"),
                    DB::Raw("max(a.tglsp) as tglsp"),
                    DB::Raw("max(a.sampai_id) as sampai_id"),
                )
                ->groupby('a.jobtrucking');

            DB::table($tempsprekap)->insertUsing([
                'jobtrucking',
                'nocont',
                'spfull',
                'spempty',
                'spfullempty',
                'pelanggan_id',
                'container_id',
                'tglsp',
                'sampai_id'
            ], $querysprekap);

            $tempomsettambahan = '##tempomsettambahan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            $tempomsettambahanrinci = '##tempomsettambahanrinci' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            $cekStatus = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS CETAKAN')->where('subgrp', 'INVOICE')->first();
            if ($cekStatus->text == 'FORMAT 2') {

                Schema::create($tempomsettambahanrinci, function ($table) {
                    $table->string('jobtrucking');
                    $table->LongText('keterangan')->nullable();
                    $table->double('nominal')->nullable();
                });

                $fetch = DB::table("suratpengantar")->from(DB::raw("suratpengantar"))
                    ->select(
                        'c.jobtrucking',
                        DB::raw("STRING_AGG(cast(suratpengantarbiayatambahan.keteranganbiaya as nvarchar(max)), ', ') AS keterangan"),
                        DB::raw("sum(suratpengantarbiayatambahan.nominaltagih) as nominal")
                    )
                    ->join(DB::raw("suratpengantarbiayatambahan with (readuncommitted)"), 'suratpengantar.id', 'suratpengantarbiayatambahan.suratpengantar_id')
                    ->join(DB::raw($tempsprekap . " c"), 'suratpengantar.jobtrucking', 'c.jobtrucking')
                    ->whereRaw("isnull(suratpengantarbiayatambahan.nominaltagih,0)!=0")
                    ->groupby('c.jobtrucking');

                DB::table($tempomsettambahanrinci)->insertUsing(['jobtrucking', 'keterangan', 'nominal'], $fetch);
                
                $fetch = DB::table("suratpengantar")->from(DB::raw("suratpengantar"))
                ->select(
                    'c.jobtrucking',
                    DB::raw("STRING_AGG(b.keteranganbiaya, ', ') AS keterangan"),
                    DB::raw("sum(b.nominaltagih) as nominal")
                )
                ->join(DB::raw("biayaextrasupirheader as a with (readuncommitted)"), 'suratpengantar.nobukti', 'a.suratpengantar_nobukti')
                ->join(DB::raw("biayaextrasupirdetail as b with (readuncommitted)"), 'b.nobukti', 'a.nobukti')
                ->join(DB::raw($tempsprekap . " c"), 'suratpengantar.jobtrucking', 'c.jobtrucking')
                ->whereRaw("isnull(b.nominaltagih,0)!=0")
                ->groupby('c.jobtrucking');
                DB::table($tempomsettambahanrinci)->insertUsing(['jobtrucking', 'keterangan', 'nominal'], $fetch);

                $fetch = DB::table($tempomsettambahanrinci)->from(DB::raw("$tempomsettambahanrinci as a"))
                ->select(
                    'a.jobtrucking',
                    DB::raw("STRING_AGG(a.keterangan, ', ') AS keterangan"),
                    DB::raw("sum(a.nominal) as nominal")
                )
                ->groupby('a.jobtrucking');
                Schema::create($tempomsettambahan, function ($table) {
                    $table->string('jobtrucking');
                    $table->LongText('keterangan')->nullable();
                    $table->double('nominal')->nullable();
                });


                DB::table($tempomsettambahan)->insertUsing(['jobtrucking', 'keterangan', 'nominal'], $fetch);
            }
            // else{
            //     $fetch = DB::table("suratpengantar")->from(DB::raw("suratpengantar"))
            //         ->select(
            //             'c.jobtrucking',
            //             DB::raw("STRING_AGG(suratpengantar.keterangan, ', ') AS keterangan"),
            //         )
            //         ->join(DB::raw($tempsprekap . " c"), 'suratpengantar.jobtrucking', 'c.jobtrucking')
            //         ->groupby('c.jobtrucking');
            //     Schema::create($tempomsettambahan, function ($table) {
            //         $table->string('jobtrucking');
            //         $table->LongText('keterangan')->nullable();
            //     });

            //     DB::table($tempomsettambahan)->insertUsing(['jobtrucking', 'keterangan'], $fetch);
            // }


            $query->select(
                'suratpengantar.tglsp',
                'pelanggan.namapelanggan as shipper',
                'kota.keterangan as tujuan',
                'suratpengantar.nocont',
                'container.kodecontainer as ukcont',
                // DB::raw("(case when isnull(suratpengantar.spfullempty, '')='' then CONCAT(ISNULL(suratpengantar.spfull, ''), ' / ', ISNULL(suratpengantar.spempty, '')) else suratpengantar.spfullempty end) as fullEmpty"),
                DB::raw("suratpengantar.spfull as [full]"),
                DB::raw("suratpengantar.spempty as empty"),
                DB::raw("suratpengantar.spfullempty as fullEmpty"),
                $this->table . '.nominal as omset',
                DB::raw("({$this->table}.nominalextra + {$this->table}.nominalretribusi) as extra"),
                $this->table . '.total as jumlah',
                $this->table . '.kapal',
            )
                ->where($this->table . '.invoice_id', '=', request()->invoice_id)
                ->leftJoin(DB::raw($tempsprekap . " as suratpengantar"), $this->table . '.orderantrucking_nobukti', 'suratpengantar.jobtrucking')
                ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'suratpengantar.pelanggan_id', 'pelanggan.id')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
                ->leftJoin(DB::raw("kota with (readuncommitted)"), 'suratpengantar.sampai_id', 'kota.id');

            if ($cekStatus->text == 'FORMAT 2') {
                $query->addSelect(
                    DB::raw("(CASE WHEN isnull(invoicedetail.nominalextra, 0)=0 then (case when isnull(invoicedetail.nominalretribusi, 0)!=0 then {$this->table}.keterangan else '' end) 
                            ELSE 
                            ({$this->table}.keterangan + (CASE WHEN isnull({$this->table}.keterangan, '')='' then '' else '. ' end)  + c.keterangan) end) as keterangan")
                )

                    ->leftjoin(DB::raw($tempomsettambahan . " c"), $this->table . '.orderantrucking_nobukti', 'c.jobtrucking');
            } else {
                $query->addSelect(
                    DB::raw("isnull({$this->table}.keterangan,'') as keterangan")
                );
            }
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.keterangan',
                $this->table . '.nominal',
                $this->table . '.total',
                $this->table . '.nominalextra',
                $this->table . '.nominalretribusi',
                $this->table . '.orderantrucking_nobukti',
                $this->table . '.suratpengantar_nobukti',
                db::raw("cast((format(orderantrucking.tglbukti,'yyyy/MM')+'/1') as date) as tgldariorderantrucking"),
                db::raw("cast(cast(format((cast((format(orderantrucking.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiorderantrucking"),
                db::raw("cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as date) as tgldarisuratpengantar"),
                db::raw("cast(cast(format((cast((format(suratpengantar.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaisuratpengantar"),
            )
                ->leftJoin(DB::raw("orderantrucking with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', '=', 'orderantrucking.nobukti')
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'invoicedetail.suratpengantar_nobukti', '=', 'suratpengantar.nobukti');

            $this->sort($query);
            $query->where($this->table . '.invoice_id', '=', request()->invoice_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('invoicedetail.nominal');
            $this->totalTotal = $query->sum('invoicedetail.total');
            $this->totalExtra = $query->sum('invoicedetail.nominalextra');
            $this->totalRetribusi = $query->sum('invoicedetail.nominalretribusi');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }
        return $query->get();
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalextra' || $filters['field'] == 'nominalretribusi' || $filters['field'] == 'total') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'nominal' || $filters['field'] == 'nominalextra' || $filters['field'] == 'nominalretribusi' || $filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(InvoiceHeader $invoiceHeader, array $data): InvoiceDetail
    {
        $invoiceDetail = new InvoiceDetail();
        $invoiceDetail->invoice_id = $invoiceHeader->id;
        $invoiceDetail->nobukti = $invoiceHeader->nobukti;
        $invoiceDetail->nominal = $data['nominal'];
        $invoiceDetail->nominalextra = $data['nominalextra'];
        $invoiceDetail->nominalretribusi = $data['nominalretribusi'];
        $invoiceDetail->total = $data['total'];
        $invoiceDetail->keterangan = $data['keterangan'];
        $invoiceDetail->kapal = $data['kapal'];
        $invoiceDetail->orderantrucking_nobukti = $data['orderantrucking_nobukti'];
        $invoiceDetail->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $invoiceDetail->modifiedby = auth('api')->user()->name;
        $invoiceDetail->info = html_entity_decode(request()->info);

        if (!$invoiceDetail->save()) {
            throw new \Exception("Error storing invoice detail.");
        }

        return $invoiceDetail;
    }
}
