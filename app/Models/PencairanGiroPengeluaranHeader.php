<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PencairanGiroPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PencairanGiroPengeluaranHeader';
    protected $anotherTable = 'pengeluaranheader';
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
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);

        $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $query1 = DB::table($this->anotherTable)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, 
                pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, 
                pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);

        $query2 = DB::table($this->anotherTable)->from(DB::raw("saldopengeluaranheader as pengeluaranheader with (readuncommitted) "))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM saldopengeluarandetail as pengeluarandetail
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("saldopengeluarandetail as pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);



        $query3 = DB::table(DB::raw("({$query1->toSql()} UNION ALL {$query2->toSql()}) as V"))
            ->mergeBindings($query1)
            ->mergeBindings($query2);



        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templist, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });



        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'id',
            'dibayarke',
            'bank_id',
            'transferkeac',
            'modifiedby',
            'created_at',
            'updated_at',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'nominal',

        ], $query3);

        $query = DB::table($templist)->from(DB::raw($templist . " AS pengeluaranheader "))
            ->select([
                'pengeluaranheader.pengeluaran_nobukti',
                'pengeluaranheader.id',
                'pengeluaranheader.dibayarke',
                'pengeluaranheader.bank_id',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.alatbayar_id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',
                'pengeluaranheader.statusapproval',
                'pengeluaranheader.nominal',
                'pengeluaranheader.modifiedby',
                'pengeluaranheader.created_at',
                'pengeluaranheader.updated_at',
                db::raw("cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpengeluaranheader"),
                db::raw("cast(cast(format((cast((format(pengeluaran.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpengeluaranheader"),
            ])
            ->leftJoin(DB::raw("pengeluaranheader as pengeluaran with (readuncommitted)"), 'pengeluaranheader.pengeluaran_nobukti', '=', 'pengeluaran.nobukti');



        // dd( $query->get());

        $this->sort($query, 'pengeluaranheader');

        $this->filter($query, 'pengeluaranheader');

        $this->paginate($query);


        $this->totalRows = $query->count();

        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $data = $query->get();
        // dd($data);
        return $data;
    }


    public function selectColumns()
    {
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);

        $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $query1 = DB::table($this->anotherTable)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, 
                pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, 
                pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);

        $query2 = DB::table($this->anotherTable)->from(DB::raw("saldopengeluaranheader as pengeluaranheader with (readuncommitted) "))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM saldopengeluarandetail as pengeluarandetail
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("saldopengeluarandetail as pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);



        $query3 = DB::table(DB::raw("({$query1->toSql()} UNION ALL {$query2->toSql()}) as V"))
            ->mergeBindings($query1)
            ->mergeBindings($query2);



        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templist, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });



        DB::table($templist)->insertUsing([
            'pengeluaran_nobukti',
            'id',
            'dibayarke',
            'bank_id',
            'transferkeac',
            'modifiedby',
            'created_at',
            'updated_at',
            'alatbayar_id',
            'nobukti',
            'tglbukti',
            'statusapproval',
            'nominal',

        ], $query3);

        $query = DB::table($templist)->from(DB::raw($templist . " AS pengeluaranheader "))
            ->select([
                'pengeluaranheader.pengeluaran_nobukti',
                'pengeluaranheader.id',
                'pengeluaranheader.dibayarke',
                'pengeluaranheader.bank_id',
                'pengeluaranheader.transferkeac',
                'pengeluaranheader.alatbayar_id',
                'pengeluaranheader.nobukti',
                'pengeluaranheader.tglbukti',
                'pengeluaranheader.statusapproval',
                'pengeluaranheader.nominal',
                'pengeluaranheader.modifiedby',
                'pengeluaranheader.created_at',
                'pengeluaranheader.updated_at',
            ]);

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->string('pengeluaran_nobukti', 300)->nullable();
            $table->integer('id')->nullable();
            $table->longText('dibayarke')->nullable();
            $table->longText('bank_id')->nullable();
            $table->longText('transferkeac')->nullable();
            $table->longText('alatbayar_id')->nullable();
            $table->longText('nobukti')->nullable();
            $table->date('tglbukti')->nullable();
            $table->longText('statusapproval')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('modifiedby')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query, 'pengeluaranheader');
        $models = $this->filter($query, 'pengeluaranheader');
        DB::table($temp)->insertUsing([
            'pengeluaran_nobukti','id','dibayarke', 'bank_id', 'transferkeac', 'alatbayar_id', 'nobukti', 'tglbukti', 'statusapproval','nominal', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }

    // 'a.pengeluaran_nobukti',
    // 'a.id',
    // 'a.dibayarke',
    // 'a.bank_id',
    // 'a.transferkeac',
    // 'a.alatbayar_id',
    // 'a.nobukti',
    // 'a.tglbukti',
    // 'a.statusapproval',
    // 'a.nominal',
    // 'a.modifiedby',
    // 'a.created_at',
    // 'a.updated_at',
    public function sort($query, $table)
    {
        // if ($this->params['sortIndex'] == 'bank_id') {
        //     return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'alatbayar_id') {
        //     return $query->orderBy('alatbayar.keterangan', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'nobukti') {
        //     return $query->orderBy('pgp.nobukti', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'tglbukti') {
        //     return $query->orderBy('pgp.tglbukti', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'pengeluaran_nobukti') {
        //     return $query->orderBy('pengeluaranheader.nobukti', $this->params['sortOrder']);
        // } else {
        return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":

                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'statusapproval') {
                        //     $query = $query->where('parameter.text', '=', "$filters[data]");
                        // } else if ($filters['field'] == 'bank_id') {
                        //     $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'alatbayar_id') {
                        //     $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'nobukti') {
                        //     $query = $query->where('pgp.nobukti', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'tglbukti') {
                        //     $query->whereRaw("format(pgp.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        // } else if ($filters['field'] == 'pengeluaran_nobukti') {
                        //     $query = $query->where('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                        if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format($table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query->whereRaw("format($table.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":

                    // $query = $query->where(function ($query, $table) {
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'statusapproval') {
                        //     $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                        // } else if ($filters['field'] == 'bank_id') {
                        //     $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'alatbayar_id') {
                        //     $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'nobukti') {
                        //     $query = $query->orWhere('pgp.nobukti', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'tglbukti') {
                        //     $query->orWhereRaw("format(pgp.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        // } else if ($filters['field'] == 'pengeluaran_nobukti') {
                        //     $query = $query->orWhere('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                        if ($filters['field'] == 'nominal') {
                            $query = $query->orWhereRaw("format($table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query->orWhereRaw("format($table.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->orWhere($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }
                    // });

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

    public function processStore(array $data): PencairanGiroPengeluaranHeader
    {

        $group = 'PENCAIRAN GIRO BUKTI';
        $subGroup = 'PENCAIRAN GIRO BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $pencairanGiro = new PencairanGiroPengeluaranHeader();

        $statusApproval = Parameter::where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['pengeluaranId']); $i++) {

            $pengeluaran = PengeluaranHeader::from(DB::raw("pengeluaranheader with (readuncommitted)"))
                ->select('nobukti', 'alatbayar_id')->where('id', $data['pengeluaranId'][$i])->first();
            if ($pengeluaran == null) {
                $saldoPengeluaran = SaldoPengeluaranHeader::from(DB::raw("saldopengeluaranheader with (readuncommitted)"))
                    ->select('nobukti', 'alatbayar_id')->where('id', $data['pengeluaranId'][$i])->first();

                $cekPencairanSaldo = PencairanGiroPengeluaranHeader::from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))->where('pengeluaran_nobukti', $saldoPengeluaran->nobukti)->first();

                if ($cekPencairanSaldo != null) {
                    $getJurnalHeader = JurnalUmumHeader::where('nobukti', $cekPencairanSaldo->nobukti)->first();
                    $this->processDestroy($cekPencairanSaldo->id);

                    (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PENCAIRAN GIRO PENGELUARAN DETAIL');
                } else {
                    $pencairanGiro->nobukti = (new RunningNumberService)->get($group, $subGroup, $pencairanGiro->getTable(), date('Y-m-d'));
                    $pencairanGiro->tglbukti = date('Y-m-d');
                    $pencairanGiro->pengeluaran_nobukti = $saldoPengeluaran->nobukti;
                    $pencairanGiro->statusapproval = $statusApproval->id;
                    $pencairanGiro->userapproval = '';
                    $pencairanGiro->tglapproval = '';
                    $pencairanGiro->modifiedby = auth('api')->user()->name;
                    $pencairanGiro->info = html_entity_decode(request()->info);
                    $pencairanGiro->statusformat = $format->id;

                    if (!$pencairanGiro->save()) {
                        throw new \Exception("Error storing pencairan giro pengeluaran header.");
                    }
                    $pencairanGiroLogTrail = (new LogTrail())->processStore([
                        'namatabel' => strtoupper($pencairanGiro->getTable()),
                        'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                        'idtrans' => $pencairanGiro->id,
                        'nobuktitrans' => $pencairanGiro->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $pencairanGiro->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);



                    // STORE DETAIL

                    $saldoPengeluaranDetail = saldopengeluarandetail::from(DB::raw("saldopengeluarandetail with (readuncommitted)"))->where('saldopengeluaran_id', $data['pengeluaranId'][$i])->get();

                    $pencairanGiroDetails = [];
                    $coadebet_detail = [];
                    $coakredit_detail = [];
                    $keterangan_detail = [];
                    $nominal_detail = [];

                    foreach ($saldoPengeluaranDetail as $index => $value) {

                        $pencairanGiroDetail = (new PencairanGiroPengeluaranDetail())->processStore($pencairanGiro, [
                            'alatbayar_id' => $saldoPengeluaran->alatbayar_id,
                            'nowarkat' => $value->nowarkat,
                            'tgljatuhtempo' => $value->tgljatuhtempo,
                            'nominal' => $value->nominal,
                            'coadebet' => $value->coadebet,
                            'coakredit' => $value->coakredit,
                            'keterangan' => $value->keterangan,
                            'bulanbeban' => $value->bulanbeban,
                        ]);

                        $coadebet_detail[$index] = $value->coadebet;
                        $coakredit_detail[$index] = $value->coakredit;
                        $keterangan_detail[$index] = $value->keterangan;
                        $nominal_detail[$index] = $value->nominal;

                        $pencairanGiroDetails[] = $pencairanGiroDetail->toArray();
                    }


                    (new LogTrail())->processStore([
                        'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
                        'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                        'idtrans' => $pencairanGiroLogTrail['id'],
                        'nobuktitrans' => $pencairanGiro->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $pencairanGiroDetails,
                        'modifiedby' => auth('api')->user()->name
                    ]);

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pencairanGiro->nobukti,
                        'tglbukti' => date('Y-m-d'),
                        'postingdari' => "ENTRY PENCAIRAN GIRO PENGELUARAN",
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'nominal_detail' => $nominal_detail,
                        'keterangan_detail' => $keterangan_detail
                    ];
                    (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            } else {

                $cekPencairan = PencairanGiroPengeluaranHeader::from(DB::raw("pencairangiropengeluaranheader with (readuncommitted)"))->where('pengeluaran_nobukti', $pengeluaran->nobukti)->first();

                if ($cekPencairan != null) {
                    $getJurnalHeader = JurnalUmumHeader::where('nobukti', $cekPencairan->nobukti)->first();
                    $this->processDestroy($cekPencairan->id);

                    (new JurnalUmumHeader())->processDestroy($getJurnalHeader->id, 'PENCAIRAN GIRO PENGELUARAN DETAIL');
                } else {
                    $pencairanGiro->nobukti = (new RunningNumberService)->get($group, $subGroup, $pencairanGiro->getTable(), date('Y-m-d'));
                    $pencairanGiro->tglbukti = date('Y-m-d');
                    $pencairanGiro->pengeluaran_nobukti = $pengeluaran->nobukti;
                    $pencairanGiro->statusapproval = $statusApproval->id;
                    $pencairanGiro->userapproval = '';
                    $pencairanGiro->tglapproval = '';
                    $pencairanGiro->modifiedby = auth('api')->user()->name;
                    $pencairanGiro->info = html_entity_decode(request()->info);
                    $pencairanGiro->statusformat = $format->id;

                    if (!$pencairanGiro->save()) {
                        throw new \Exception("Error storing pencairan giro pengeluaran header.");
                    }
                    $pencairanGiroLogTrail = (new LogTrail())->processStore([
                        'namatabel' => strtoupper($pencairanGiro->getTable()),
                        'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN HEADER',
                        'idtrans' => $pencairanGiro->id,
                        'nobuktitrans' => $pencairanGiro->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $pencairanGiro->toArray(),
                        'modifiedby' => auth('api')->user()->user
                    ]);



                    // STORE DETAIL

                    $pengeluaranDetail = PengeluaranDetail::from(DB::raw("pengeluarandetail with (readuncommitted)"))->where('pengeluaran_id', $data['pengeluaranId'][$i])->get();

                    $pencairanGiroDetails = [];
                    $coadebet_detail = [];
                    $coakredit_detail = [];
                    $keterangan_detail = [];
                    $nominal_detail = [];

                    foreach ($pengeluaranDetail as $index => $value) {

                        $pencairanGiroDetail = (new PencairanGiroPengeluaranDetail())->processStore($pencairanGiro, [
                            'alatbayar_id' => $pengeluaran->alatbayar_id,
                            'nowarkat' => $value->nowarkat,
                            'tgljatuhtempo' => $value->tgljatuhtempo,
                            'nominal' => $value->nominal,
                            'coadebet' => $value->coadebet,
                            'coakredit' => $value->coakredit,
                            'keterangan' => $value->keterangan,
                            'bulanbeban' => $value->bulanbeban,
                        ]);

                        $coadebet_detail[$index] = $value->coadebet;
                        $coakredit_detail[$index] = $value->coakredit;
                        $keterangan_detail[$index] = $value->keterangan;
                        $nominal_detail[$index] = $value->nominal;

                        $pencairanGiroDetails[] = $pencairanGiroDetail->toArray();
                    }


                    (new LogTrail())->processStore([
                        'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
                        'postingdari' => 'ENTRY PENCAIRAN GIRO PENGELUARAN DETAIL',
                        'idtrans' => $pencairanGiroLogTrail['id'],
                        'nobuktitrans' => $pencairanGiro->nobukti,
                        'aksi' => 'ENTRY',
                        'datajson' => $pencairanGiroDetails,
                        'modifiedby' => auth('api')->user()->name
                    ]);

                    $jurnalRequest = [
                        'tanpaprosesnobukti' => 1,
                        'nobukti' => $pencairanGiro->nobukti,
                        'tglbukti' => date('Y-m-d'),
                        'postingdari' => "ENTRY PENCAIRAN GIRO PENGELUARAN",
                        'statusformat' => "0",
                        'coakredit_detail' => $coakredit_detail,
                        'coadebet_detail' => $coadebet_detail,
                        'nominal_detail' => $nominal_detail,
                        'keterangan_detail' => $keterangan_detail
                    ];
                    (new JurnalUmumHeader())->processStore($jurnalRequest);
                }
            }
        }
        return $pencairanGiro;
    }

    public function processDestroy($id, $postingDari = ''): PencairanGiroPengeluaranHeader
    {
        $getDetail = PencairanGiroPengeluaranDetail::lockForUpdate()->where('pencairangiropengeluaran_id', $id)->get();

        $pencairanGiro = new PencairanGiroPengeluaranHeader();
        $pencairanGiro = $pencairanGiro->lockAndDestroy($id);

        $pencairanGiroLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pencairanGiro->getTable()),
            'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN HEADER',
            'idtrans' => $pencairanGiro->id,
            'nobuktitrans' => $pencairanGiro->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $pencairanGiro->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        (new LogTrail())->processStore([
            'namatabel' => 'PENCAIRANGIROPENGELUARANDETAIL',
            'postingdari' => 'DELETE PENCAIRAN GIRO PENGELUARAN DETAIL',
            'idtrans' => $pencairanGiroLogTrail['id'],
            'nobuktitrans' => $pencairanGiro->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pencairanGiro;
    }

    public function cekValidasi($nobukti)
    {
        
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
}
