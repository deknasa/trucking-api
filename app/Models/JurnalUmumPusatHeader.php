<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class JurnalUmumPusatHeader extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumpusatheader';
    protected $anothertable = 'jurnalumumheader';

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
        $approve = request()->approve ?? 3;
        $approval = 0;
        if ($approve == 3) {
            $approval = 4;
        }
        if ($approve == 4) {
            $approval = 3;
        }
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);

        $tempsummary = '##tempsummary' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempsummary, function ($table) {
            $table->id();
            $table->string('nobukti', 1000)->nullable();
            $table->double('nominaldebet', 15, 2)->nullable();
            $table->double('nominalkredit', 15, 2)->nullable();
        });

        $querysummary = JurnalUmumHeader::from(
            DB::raw("jurnalumumheader as a with (readuncommitted)")
        )
            ->select(
                'a.nobukti as nobukti',
                DB::raw("sum((case when b.nominal<=0 then 0 else b.nominal end)) as nominaldebet"),
                DB::raw("sum((case when b.nominal>=0 then 0 else abs(b.nominal) end)) as nominalkredit"),
            )
            ->join(DB::raw("jurnalumumdetail as b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->where('a.statusapproval', $approval)
            ->whereRaw("MONTH(a.tglbukti) = $month")
            ->whereRaw("YEAR(a.tglbukti) = $year")
            ->groupBy('a.nobukti');


        DB::table($tempsummary)->insertUsing([
            'nobukti',
            'nominaldebet',
            'nominalkredit',
        ], $querysummary);

        $query = DB::table('jurnalumumheader')->from(
            DB::raw("jurnalumumheader with (readuncommitted)")
        )
            ->select(

                'jurnalumumheader.id',
                'jurnalumumheader.nobukti',
                'jurnalumumheader.tglbukti',
                'jurnalumumheader.postingdari',
                'jurnalumumheader.userapproval',
                'statusapproval.memo as statusapproval',
                DB::raw('(case when (year(jurnalumumheader.tglapproval) <= 2000) then null else jurnalumumheader.tglapproval end ) as tglapproval'),
                'jurnalumumheader.modifiedby',
                'jurnalumumheader.created_at',
                'jurnalumumheader.updated_at',
                'c.nominaldebet as nominaldebet',
                'c.nominalkredit as nominalkredit',


            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'jurnalumumheader.statusapproval', 'statusapproval.id')
            ->leftjoin(DB::raw($tempsummary . " as c"), 'jurnalumumheader.nobukti', 'c.nobukti')
            ->where('jurnalumumheader.statusapproval', $approval)
            ->whereRaw("MONTH(jurnalumumheader.tglbukti) = $month")
            ->whereRaw("YEAR(jurnalumumheader.tglbukti) = $year");



        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);


        $data = $query->get();

        return $data;
    }

    public function getimportdatacabang()
    {
        // dd(request()->periode);
        $this->setRequestParameters();
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);


        $query = db::table("jurnalumumpusatheader")->from(db::raw("jurnalumumpusatheader a with (readuncommitted)"))
            ->select(
                'a.id as header_id',
                'a.nobukti as header_nobukti',
                'a.tglbukti as header_tglbukti',
                'a.keterangan as header_keterangan',
                'a.postingdari as header_postingdari',
                'a.statusapproval as header_statusapproval',
                'a.userapproval as header_userapproval',
                'a.tglapproval as header_tglapproval',
                'a.statusformat as header_statusformat',
                'a.info as header_info',
                'a.modifiedby as header_modifiedby',
                'a.created_at as header_created_at',
                'a.updated_at as header_updated_at',
                'a.cabang as header_cabang',
                'a.cabang_id as header_cabang_id',
                'b.id as detail_id',
                'b.jurnalumumpusat_id as detail_jurnalumumpusat_id',
                'b.nobukti as detail_nobukti',
                'b.tglbukti as detail_tglbukti',
                'b.coa as detail_coa',
                'b.coamain as detail_coamain',
                'b.nominal as detail_nominal',
                'b.keterangan as detail_keterangan',
                'b.baris as detail_baris',
                'b.info as detail_info',
                'b.modifiedby as detail_modifiedby',
                'b.created_at as detail_created_at',
                'b.updated_at as detail_updated_at',
            )
            ->join(db::raw("jurnalumumpusatdetail b with (readuncommitted)"), 'a.nobukti', 'b.nobukti')
            ->whereRaw("MONTH(a.tglbukti) = " . $month)
            ->whereRaw("YEAR(a.tglbukti) = " . $year)
            ->orderby('a.id', 'asc')
            ->orderby('b.id', 'asc');

            // dd($query->get());


        $data = $query->get();

        return $data;
    }


    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->anothertable.id,
            $this->anothertable.nobukti,
            $this->anothertable.tglbukti,
            $this->anothertable.postingdari,
            'statusapproval.text as statusapproval',
            $this->anothertable.userapproval,
            $this->anothertable.tglapproval,
            $this->anothertable.modifiedby,
            $this->anothertable.created_at,
            $this->anothertable.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'jurnalumumpusatheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('postingdari', 1000)->nullable();
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
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'postingdari', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'nominaldebet') {
            return $query->orderBy('c.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominalkredit') {
            return $query->orderBy('c.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->anothertable . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'nominaldebet') {
                            $query = $query->where('c.nominaldebet', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominalkredit') {
                            $query = $query->where('c.nominalkredit', 'LIKE', "%$filters[data]%");
                        } else {
                            // $query = $query->where($this->anothertable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->anothertable . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                        }
                    }

                    break;
                case "OR":

                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'nominaldebet') {
                                $query = $query->orWhere('c.nominaldebet', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominalkredit') {
                                $query = $query->orWhere('c.nominalkredit', 'LIKE', "%$filters[data]%");
                            } else {
                                // $query = $query->orWhere($this->anothertable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->anothertable . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function processStore(array $data): JurnalUmumPusatHeader
    {
        $jurnalUmumPusatHeader = new JurnalUmumPusatHeader();
        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();

        $cabang_id = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'ID CABANG')->where('subgrp', 'ID CABANG')->first()->text;
        $querycabang = db::table("cabang")->from(db::raw("cabang a with (readcommitted)"))
            ->select(
                'a.namacabang'
            )
            ->where('a.id', $cabang_id)
            ->first();
        $jurnalUmumPusatHeader->nobukti = $data['nobukti'];
        $jurnalUmumPusatHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $jurnalUmumPusatHeader->postingdari = $data['postingdari'];
        $jurnalUmumPusatHeader->statusapproval = $data['statusapproval'];
        $jurnalUmumPusatHeader->userapproval = auth('api')->user()->name;
        $jurnalUmumPusatHeader->tglapproval = date('Y-m-d H:i:s');
        $jurnalUmumPusatHeader->statusformat = $data['statusformat'];
        $jurnalUmumPusatHeader->modifiedby = auth('api')->user()->name;
        $jurnalUmumPusatHeader->cabang_id = $cabang_id ?? 0;
        // $jurnalUmumPusatHeader->cabang = $querycabang->namacabang ?? '';
        $jurnalUmumPusatHeader->info = html_entity_decode(request()->info);


        if (!$jurnalUmumPusatHeader->save()) {
            throw new \Exception("Error storing jurnal umum pusat header.");
        }

        $jurnalUmumPusatHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumPusatHeader->getTable()),
            'postingdari' => 'ENTRY JURNAL UMUM PUSAT HEADER',
            'idtrans' => $jurnalUmumPusatHeader->id,
            'nobuktitrans' => $jurnalUmumPusatHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $jurnalUmumPusatHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $jurnalUmumPusatDetails = [];

        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $akunPusat = DB::table("akunpusat")->from(DB::raw("akunpusat"))->where('coa', $data['coa_detail'][$i])->first();
            $jurnalUmumPusatDetail = (new JurnalUmumPusatDetail())->processStore($jurnalUmumPusatHeader, [
                'coa' => $data['coa_detail'][$i],
                'nominal' => $data['nominal_detail'][$i],
                'coamain' => $akunPusat->coamain,
                'keterangan' => $data['keterangan_detail'][$i],
                'tglbukti' => $data['tglbuktidetail'][$i],
                'baris' => $data['baris'][$i],
            ]);

            $jurnalUmumPusatDetails[] = $jurnalUmumPusatDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumPusatDetail->getTable()),
            'postingdari' => 'ENTRY JURNAL UMUM PUSAT DETAIL',
            'idtrans' =>  $jurnalUmumPusatHeaderLogTrail->id,
            'nobuktitrans' => $jurnalUmumPusatHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $jurnalUmumPusatDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $jurnalUmumPusatHeader;
    }

    public function processDestroy($id, $postingDari = ''): JurnalUmumPusatHeader
    {
        $jurnalUmumDetails = JurnalUmumPusatDetail::lockForUpdate()->where('jurnalumumpusat_id', $id)->get();

        $jurnalUmumHeader = new JurnalUmumPusatHeader();
        $jurnalUmumHeader = $jurnalUmumHeader->lockAndDestroy($id);

        $jurnalUmumHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $jurnalUmumHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $jurnalUmumHeader->id,
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $jurnalUmumHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'JURNALUMUMDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $jurnalUmumHeaderLogTrail['id'],
            'nobuktitrans' => $jurnalUmumHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $jurnalUmumDetails->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        return $jurnalUmumHeader;
    }

    public function processStoreimportdatacabang(array $data): JurnalUmumPusatHeader
    {
        $jurnalUmumPusatHeader = new JurnalUmumPusatHeader();
       
        $jurnalUmumPusatHeader->nobukti = $data['nobukti'] ?? '';
        $jurnalUmumPusatHeader->tglbukti = $data['tglbukti'] ?? '1900/1/1';
        $jurnalUmumPusatHeader->postingdari = $data['postingdari'] ?? '';
        $jurnalUmumPusatHeader->statusapproval = $data['statusapproval'] ?? '0';
        $jurnalUmumPusatHeader->userapproval = $data['userapproval'] ?? '';
        $jurnalUmumPusatHeader->tglapproval = $data['tglapproval'] ?? '1900/1/1';
        $jurnalUmumPusatHeader->statusformat = $data['statusformat'] ?? 0;
        $jurnalUmumPusatHeader->modifiedby = $data['modifiedby'] ?? '';
        $jurnalUmumPusatHeader->cabang_id = $data['cabang_id'] ?? 0;
        $jurnalUmumPusatHeader->cabang = $data['cabang'] ?? '' ;
        $jurnalUmumPusatHeader->info = $data['info'] ?? '';


        if (!$jurnalUmumPusatHeader->save()) {
            throw new \Exception("Error storing jurnal umum pusat header.");
        }

        $jurnalUmumPusatHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumPusatHeader->getTable()),
            'postingdari' => 'IMPORT DATA CABANG JURNAL UMUM PUSAT HEADER',
            'idtrans' => $jurnalUmumPusatHeader->id,
            'nobuktitrans' => $jurnalUmumPusatHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $jurnalUmumPusatHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $jurnalUmumPusatDetails = [];

        for ($i = 0; $i < count($data['nominal_detail']); $i++) {
            $akunPusat = DB::table("akunpusat")->from(DB::raw("akunpusat"))->where('coa', $data['coa_detail'][$i])->first();
            $jurnalUmumPusatDetail = (new JurnalUmumPusatDetail())->processStore($jurnalUmumPusatHeader, [
                'coa' => $data['coa_detail'][$i],
                'nominal' => $data['nominal_detail'][$i],
                'coamain' => $akunPusat->coamain,
                'keterangan' => $data['keterangan_detail'][$i],
                'baris' => $data['baris'][$i],
            ]);

            $jurnalUmumPusatDetails[] = $jurnalUmumPusatDetail->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($jurnalUmumPusatDetail->getTable()),
            'postingdari' => 'ENTRY JURNAL UMUM PUSAT DETAIL',
            'idtrans' =>  $jurnalUmumPusatHeaderLogTrail->id,
            'nobuktitrans' => $jurnalUmumPusatHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $jurnalUmumPusatDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);

        return $jurnalUmumPusatHeader;
    }

}
