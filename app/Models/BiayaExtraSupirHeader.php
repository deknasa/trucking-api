<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BiayaExtraSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'biayaextrasupirheader';

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
        $proses = request()->proses ?? 'reload';
        $user = auth('api')->user()->name;
        $class = 'PenerimaanHeaderController';
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
                DB::table('listtemporarytabel')->where('id', $querydata->id)->delete();
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
                $table->integer('id')->nullable();
                $table->string('nobukti', 50)->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('suratpengantar_nobukti', 50)->nullable();
                $table->date('tgldariheadertrip')->nullable();
                $table->date('tglsampaiheadertrip')->nullable();
                $table->double('nominal', 15, 2)->nullable();
                $table->double('nominaltagih', 15, 2)->nullable();
                $table->string('modifiedby', 200)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
            $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempNominal, function ($table) {
                $table->string('nobukti')->nullable();
                $table->double('nominal', 15, 2)->nullable();
                $table->double('nominaltagih', 15, 2)->nullable();
            });
            $getNominal = DB::table("biayaextrasupirdetail")->from(DB::raw("biayaextrasupirdetail with (readuncommitted)"))
                ->select(DB::raw("biayaextrasupirheader.nobukti,SUM(biayaextrasupirdetail.nominal) AS nominal, SUM(biayaextrasupirdetail.nominaltagih) AS nominaltagih"))
                ->join(DB::raw("biayaextrasupirheader with (readuncommitted)"), 'biayaextrasupirheader.id', 'biayaextrasupirdetail.biayaextrasupir_id')
                ->groupBy("biayaextrasupirheader.nobukti");
            if (request()->tgldari && request()->tglsampai) {
                $getNominal->whereBetween('biayaextrasupirheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }

            DB::table($tempNominal)->insertUsing(['nobukti', 'nominal', 'nominaltagih'], $getNominal);
            $query = DB::table("biayaextrasupirheader")->from(DB::raw("biayaextrasupirheader as a with (readuncommitted)"))
                ->select(
                    'a.id',
                    'a.nobukti',
                    'a.tglbukti',
                    'a.suratpengantar_nobukti',
                    db::raw("cast((format(sp.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadertrip"),
                    db::raw("cast(cast(format((cast((format(sp.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadertrip"),
                    'nominal.nominal',
                    'nominal.nominaltagih',
                    'a.modifiedby',
                    'a.created_at',
                    'a.updated_at'
                )
                ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'a.nobukti', 'nominal.nobukti')
                ->leftJoin(DB::raw("suratpengantar as sp with (readuncommitted)"), 'a.suratpengantar_nobukti', 'sp.nobukti');

            if (request()->tgldari && request()->tglsampai) {
                $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
            }
            DB::table($temtabel)->insertUsing([
                'id',
                'nobukti',
                'tglbukti',
                'suratpengantar_nobukti',
                'tgldariheadertrip',
                'tglsampaiheadertrip',
                'nominal',
                'nominaltagih',
                'modifiedby',
                'created_at',
                'updated_at',
            ], $query);
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
        $query = DB::table($temtabel)->from(DB::raw($temtabel . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.suratpengantar_nobukti',
                'a.tgldariheadertrip',
                'a.tglsampaiheadertrip',
                'a.nominal',
                'a.nominaltagih',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();


        return $data;
    }


    public function selectColumns()
    {
        $temp = '##tempselect' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('suratpengantar_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('nominaltagih', 15, 2)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        $tempNominal = '##tempNominal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempNominal, function ($table) {
            $table->string('nobukti')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('nominaltagih', 15, 2)->nullable();
        });
        $getNominal = DB::table("biayaextrasupirdetail")->from(DB::raw("biayaextrasupirdetail with (readuncommitted)"))
            ->select(DB::raw("biayaextrasupirheader.nobukti,SUM(biayaextrasupirdetail.nominal) AS nominal, SUM(biayaextrasupirdetail.nominaltagih) AS nominaltagih"))
            ->join(DB::raw("biayaextrasupirheader with (readuncommitted)"), 'biayaextrasupirheader.id', 'biayaextrasupirdetail.biayaextrasupir_id')
            ->groupBy("biayaextrasupirheader.nobukti");
        if (request()->tgldari && request()->tglsampai) {
            $getNominal->whereBetween('biayaextrasupirheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }

        DB::table($tempNominal)->insertUsing(['nobukti', 'nominal', 'nominaltagih'], $getNominal);

        $query = DB::table("biayaextrasupirheader")->from(DB::raw("biayaextrasupirheader as a with (readuncommitted)"))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.suratpengantar_nobukti',
                'nominal.nominal',
                'nominal.nominaltagih',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at'
            )
            ->leftJoin(DB::raw("$tempNominal as nominal with (readuncommitted)"), 'a.nobukti', 'nominal.nobukti');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'suratpengantar_nobukti',
            'nominal',
            'nominaltagih',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $query);
        $query = DB::table($temp)->from(DB::raw($temp . " a "))
            ->select(
                'a.id',
                'a.nobukti',
                'a.tglbukti',
                'a.suratpengantar_nobukti',
                'a.nominal',
                'a.nominaltagih',
                'a.modifiedby',
                'a.created_at',
                'a.updated_at',
            );

        return $query;
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('suratpengantar_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->double('nominaltagih', 15, 2)->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });
        if ((date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tgldariheader))) || (date('Y-m', strtotime(request()->tglbukti)) != date('Y-m', strtotime(request()->tglsampaiheader)))) {
            request()->tgldariheader = date('Y-m-01', strtotime(request()->tglbukti));
            request()->tglsampaiheader = date('Y-m-t', strtotime(request()->tglbukti));
        }
        $this->setRequestParameters();
        $query = $this->selectColumns();
        $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween('a.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'suratpengantar_nobukti',
            'nominal',
            'nominaltagih',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return  $temp;
    }

    public function sort($query)
    {
        return $query->orderBy('a.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominaltagih') {
                                $query = $query->whereRaw("format(a.nominaltagih, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'tglbukti') {
                                    $query = $query->orWhereRaw("format(a.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format(a.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'nominaltagih') {
                                    $query = $query->orWhereRaw("format(a.nominaltagih, '#,#0.00') LIKE '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function cekvalidasiaksi($id)
    {
        $error = new Error();
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $query = db::table("biayaextrasupirheader")->from(db::raw("biayaextrasupirheader with (readuncommitted)"))
            ->where('id', $id)
            ->first();

        $getJobtrucking = db::table("suratpengantar")->from(db::raw("suratpengantar with (readuncommitted)"))
        ->select('jobtrucking')
        ->where('nobukti', $query->suratpengantar_nobukti)
        ->first();

        $keteranganerror = $error->cekKeteranganError('SATL2');
        $invoicedetail = DB::table('invoicedetail')
            ->from(
                DB::raw("invoicedetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
            )
            ->where('a.orderantrucking_nobukti', '=', $getJobtrucking->jobtrucking)
            ->first();
        if (isset($invoicedetail)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Trip <b>' . $query->suratpengantar_nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti invoice <b>' . $invoicedetail->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TDT',
                'editcoa' => false
            ];
            goto selesai;
        }

        $gajisupirdetail = DB::table('gajisupirdetail')
            ->from(
                DB::raw("gajisupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
            )
            ->where('a.biayaextrasupir_nobukti', '=', $query->nobukti)
            ->first();
        if (isset($gajisupirdetail)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'No bukti <b>' . $query->nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Gaji Supir <b>' . $gajisupirdetail->nobukti . '</b> <br> ' . $keterangantambahanerror,
                'kodeerror' => 'TDT',
                'editcoa' => false
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

        $query = DB::table('biayaextrasupirheader')->from(DB::raw("biayaextrasupirheader with (readuncommitted)"))
            ->select(
                'id',
                'nobukti',
                'tglbukti',
                'suratpengantar_nobukti',
            )
            ->where('id', $id);


        $data = $query->first();

        return $data;
    }

    public function processStore(array $data): BiayaExtraSupirHeader
    {
        $group = 'BIAYA EXTRA SUPIR BUKTI';
        $subGroup = 'BIAYA EXTRA SUPIR BUKTI';

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $biayaExtraSupirHeader = new BiayaExtraSupirHeader();
        $biayaExtraSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $biayaExtraSupirHeader->suratpengantar_nobukti =  $data['suratpengantar_nobukti'];
        $biayaExtraSupirHeader->statusformat =  $format->id;
        $biayaExtraSupirHeader->modifiedby = auth('api')->user()->name;
        $biayaExtraSupirHeader->info = html_entity_decode(request()->info);
        $biayaExtraSupirHeader->nobukti = (new RunningNumberService)->get($group, $subGroup, $biayaExtraSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$biayaExtraSupirHeader->save()) {
            throw new \Exception("Error storing biaya extra supir header.");
        }

        $biayaExtraSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaExtraSupirHeader->getTable()),
            'postingdari' => 'ENTRY BIAYA EXTRA SUPIR HEADER',
            'idtrans' => $biayaExtraSupirHeader->id,
            'nobuktitrans' => $biayaExtraSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $biayaExtraSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $biayaExtraSupirDetails = [];

        for ($i = 0; $i < count($data['keteranganbiaya']); $i++) {
            $biayaExtraSupirDetail = (new BiayaExtraSupirDetail())->processStore($biayaExtraSupirHeader, [
                'keteranganbiaya' => $data['keteranganbiaya'][$i],
                'nominal' => $data['nominal'][$i],
                'nominaltagih' => $data['nominaltagih'][$i]
            ]);

            $biayaExtraSupirDetails[] = $biayaExtraSupirDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaExtraSupirDetail->getTable()),
            'postingdari' => 'ENTRY BIAYA EXTRA SUPIR DETAIL',
            'idtrans' =>  $biayaExtraSupirHeaderLogTrail->id,
            'nobuktitrans' => $biayaExtraSupirHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $biayaExtraSupirDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $biayaExtraSupirHeader;
    }

    public function processUpdate(BiayaExtraSupirHeader $biayaExtraSupirHeader, array $data): BiayaExtraSupirHeader
    {

        $biayaExtraSupirHeader->suratpengantar_nobukti =  $data['suratpengantar_nobukti'];
        $biayaExtraSupirHeader->modifiedby = auth('api')->user()->name;
        $biayaExtraSupirHeader->info = html_entity_decode(request()->info);


        if (!$biayaExtraSupirHeader->save()) {
            throw new \Exception("Error updating biaya extra supir header.");
        }

        $biayaExtraSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaExtraSupirHeader->getTable()),
            'postingdari' => 'EDIT BIAYA EXTRA SUPIR HEADER',
            'idtrans' => $biayaExtraSupirHeader->id,
            'nobuktitrans' => $biayaExtraSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $biayaExtraSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        BiayaExtraSupirDetail::where('biayaextrasupir_id', $biayaExtraSupirHeader->id)->delete();

        $biayaExtraSupirDetails = [];

        for ($i = 0; $i < count($data['keteranganbiaya']); $i++) {
            $biayaExtraSupirDetail = (new BiayaExtraSupirDetail())->processStore($biayaExtraSupirHeader, [
                'keteranganbiaya' => $data['keteranganbiaya'][$i],
                'nominal' => $data['nominal'][$i],
                'nominaltagih' => $data['nominaltagih'][$i]
            ]);

            $biayaExtraSupirDetails[] = $biayaExtraSupirDetail->toArray();
        }


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($biayaExtraSupirDetail->getTable()),
            'postingdari' => 'EDIT BIAYA EXTRA SUPIR DETAIL',
            'idtrans' =>  $biayaExtraSupirHeaderLogTrail->id,
            'nobuktitrans' => $biayaExtraSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $biayaExtraSupirDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $biayaExtraSupirHeader;
    }

    public function processDestroy($id): BiayaExtraSupirHeader
    {
        $biayaExtraSupirDetails = BiayaExtraSupirDetail::lockForUpdate()->where('biayaextrasupir_id', $id)->get();

        $biayaExtraSupirHeader = new BiayaExtraSupirHeader();
        $biayaExtraSupirHeader = $biayaExtraSupirHeader->lockAndDestroy($id);

        $biayaExtraSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $biayaExtraSupirHeader->getTable(),
            'postingdari' => 'DELETE BIAYA EXTRA SUPIR HEADER',
            'idtrans' => $biayaExtraSupirHeader->id,
            'nobuktitrans' => $biayaExtraSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $biayaExtraSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'BIAYAEXTRASUPIRHEADER',
            'postingdari' => 'DELETE BIAYA EXTRA SUPIR DETAIL',
            'idtrans' => $biayaExtraSupirHeaderLogTrail['id'],
            'nobuktitrans' => $biayaExtraSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $biayaExtraSupirDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $biayaExtraSupirHeader;
    }
}
