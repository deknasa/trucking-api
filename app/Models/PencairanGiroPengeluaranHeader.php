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
        $query = DB::table($this->anotherTable)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
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

        $this->sort($query, 'pengeluaranheader');
        $this->filter($query, 'pengeluaranheader');
        $this->paginate($query);


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $data = $query->get();

        return $data;
    }


    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " as pgp with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "pgp.id,
            pgp.nobukti,
            pgp.tglbukti,
            pgp.keterangan,
            pgp.pengeluaran_nobukti,
            statusapproval.text as statusapproval,
            pgp.userapproval,
            pgp.tglapproval,
            pgp.modifiedby,
            pgp.created_at,
            pgp.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pgp.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query, 'pencairangiropengeluaranheader');
        $models = $this->filter($query, 'pencairangiropengeluaranheader');
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'keterangan', 'pengeluaran_nobukti', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query, $table)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar_id') {
            return $query->orderBy('alatbayar.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nobukti') {
            return $query->orderBy('pgp.nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti') {
            return $query->orderBy('pgp.tglbukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pengeluaran_nobukti') {
            return $query->orderBy('pengeluaranheader.nobukti', $this->params['sortOrder']);
        } else {
            return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'alatbayar_id') {
                            $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nobukti') {
                            $query = $query->where('pgp.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query->whereRaw("format(pgp.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'pengeluaran_nobukti') {
                            $query = $query->where('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format((SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
                            WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti), '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":

                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti') {
                                $query = $query->orWhere('pgp.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query->orWhereRaw("format(pgp.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'pengeluaran_nobukti') {
                                $query = $query->orWhere('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format((SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
                            WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->anotherTable . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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
}
