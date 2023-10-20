<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class ProsesGajiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirdetail';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {

            $query->select(
                $this->table . '.nominal',
                $this->table . '.keterangan as keterangan_detail',
                'prosesgajisupirheader.keterangan',
            )
                ->leftJoin(DB::raw("prosesgajisupirheader with (readuncommitted)"), $this->table . '.prosesgajisupir_id', 'prosesgajisupirheader.id');
            $query->where($this->table . '.prosesgajisupir_id', '=', request()->prosesgajisupir_id);
        } else {
            $temp = '##tempebs' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            Schema::create($temp, function ($table) {
                $table->unsignedBigInteger('prosesgajisupir_id')->nullable();
                $table->string('nobukti',50)->nullable();
                $table->string('gajisupir_nobukti',50)->nullable();
                $table->string('supir_id',200)->nullable();
                $table->string('trado_id',200)->nullable();
                $table->double('total', 15, 2)->nullable();
                $table->double('uangjalan', 15, 2)->nullable();
                $table->double('bbm', 15, 2)->nullable();
                $table->double('uangmakanharian', 15, 2)->nullable();
                $table->double('potonganpinjaman', 15, 2)->nullable();
                $table->double('potonganpinjamansemua', 15, 2)->nullable();
                $table->double('deposito', 15, 2)->nullable();
                $table->double('komisisupir', 15, 2)->nullable();
                $table->double('tolsupir', 15, 2)->nullable();
                $table->double('uangmakanberjenjang', 15, 2)->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->double('gajikenek', 15, 2)->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
                $table->dateTime('tgldariheadergajisupirheaderheader')->nullable();
                $table->dateTime('tglsampaiheadergajisupirheaderheader')->nullable();
            });

            $tempric1 = '##tempricebs' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

            Schema::create($tempric1, function ($table) {
                $table->string('gajisupir_nobukti',50)->nullable();
                $table->double('gajisupir', 15, 2)->nullable();
                $table->double('gajikenek', 15, 2)->nullable();
                $table->double('biayaextra', 15, 2)->nullable();
            });

            $queryric=db::table("gajisupirdetail")->from(db::raw("gajisupirdetail a with (readuncommitted)"))
            ->select(
                'b.gajisupir_nobukti',
                db::raw("sum(a.gajisupir) as gajisupir"),
                db::raw("sum(a.gajikenek) as gajikenek"),
                db::raw("sum(a.biayatambahan) as biayaextra"),
            )
            ->join(db::raw("prosesgajisupirdetail b with (readuncommitted)"),'a.nobukti','b.gajisupir_nobukti')
            ->where('b.prosesgajisupir_id',request()->prosesgajisupir_id)
            ->groupby("b.gajisupir_nobukti");

            DB::table($tempric1)->insertUsing([
                'gajisupir_nobukti',
                'gajisupir',
                'gajikenek',
                'biayaextra',
            ], $queryric);


            $queryebs=db::table("prosesgajisupirdetail")->from(db::raw("prosesgajisupirdetail a with (readuncommitted)"))
            ->select(
                'a.prosesgajisupir_id',
                'a.nobukti',
                'a.gajisupir_nobukti',
                'supir.namasupir as supir_id',
                'trado.kodetrado as trado_id',
                DB::RAW("(gajisupirheader.total+gajisupirheader.komisisupir+isnull(d.gajikenek,0)) as total"),
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.deposito',
                'gajisupirheader.komisisupir',
                'gajisupirheader.tolsupir',
                DB::raw("(case when gajisupirheader.uangmakanberjenjang IS NULL then 0 else gajisupirheader.uangmakanberjenjang end) as uangmakanberjenjang"),
                db::raw("isnull(d.gajisupir,0) as gajisupir"),
                db::raw("isnull(d.gajikenek,0) as gajikenek"),
                db::raw("isnull(d.biayaextra,0) as biayaextra"),
                db::raw("cast((format(gajisupirheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheadergajisupirheaderheader"),
                db::raw("cast(cast(format((cast((format(gajisupirheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheadergajisupirheaderheader"), 
            )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'a.supir_id', 'supir.id')
                ->leftJoin(DB::raw("trado with (readuncommitted)"), 'a.trado_id', 'trado.id')
                ->leftJoin(DB::raw("prosesgajisupirheader with (readuncommitted)"), 'a.nobukti', 'prosesgajisupirheader.nobukti')
                ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'a.gajisupir_nobukti', 'gajisupirheader.nobukti')
                ->leftJoin(DB::raw( $tempric1." d"), 'a.gajisupir_nobukti', 'd.gajisupir_nobukti');

         

                DB::table($temp)->insertUsing([
                    'prosesgajisupir_id',
                    'nobukti',
                    'gajisupir_nobukti',
                    'supir_id',
                    'trado_id',
                    'total',
                    'uangjalan',
                    'bbm',
                    'uangmakanharian',
                    'potonganpinjaman',
                    'potonganpinjamansemua',
                    'deposito',
                    'komisisupir',
                    'tolsupir',
                    'uangmakanberjenjang',
                    'gajisupir',
                    'gajikenek',
                    'biayaextra',
                    'tgldariheadergajisupirheaderheader',
                    'tglsampaiheadergajisupirheaderheader',
                ], $queryebs);
    

       
            $tempQuery = DB::table($temp)->from(DB::raw($temp  ." a "));
            $tempQuery->select(
                'a.gajisupir_nobukti',
                'a.supir_id',
                'a.trado_id',
                'a.total',
                'a.uangjalan',
                'a.bbm',
                'a.uangmakanharian',
                'a.potonganpinjaman',
                'a.potonganpinjamansemua',
                'a.deposito',
                'a.komisisupir',
                'a.gajisupir',
                'a.gajikenek',
                'a.biayaextra',
                'a.tolsupir',
                'a.uangmakanberjenjang',
                'a.tgldariheadergajisupirheaderheader',
                'a.tglsampaiheadergajisupirheaderheader', 
            );
   
            
            $this->sort($tempQuery);
            $tempQuery->where('a.prosesgajisupir_id', '=', request()->prosesgajisupir_id);
            $this->filter($tempQuery);


            $this->totalRows = $tempQuery->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
     
            $tempbuktisum = '##tempbuktisum' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($tempbuktisum, function ($table) {
                $table->string('nobukti', 100)->nullable();
            });
            $databukti = json_decode($query->get(), true);
            foreach ($databukti as $item) {

                DB::table($tempbuktisum)->insert([
                    'nobukti' => $item['gajisupir_nobukti'],
                ]);
            }

 
            $querytotal = DB::table($temp)->from(DB::raw($temp . " a "))
                ->select(
                    db::raw("sum(a.total) as total"),
                    db::raw("sum(a.uangjalan) as uangjalan"),
                    db::raw("sum(a.bbm) as bbm"),
                    db::raw("sum(a.uangmakanharian) as uangmakanharian"),
                    db::raw("sum(a.potonganpinjaman) as potonganpinjaman"),
                    db::raw("sum(a.potonganpinjamansemua) as potonganpinjamansemua"),
                    db::raw("sum(a.deposito) as deposito"),
                    db::raw("sum(a.gajisupir) as gajisupir"),
                    db::raw("sum(a.gajikenek) as gajikenek"),
                    db::raw("sum(a.komisisupir) as komisisupir"),
                    db::raw("sum(a.biayaextra) as biayaextra"),
                    db::raw("sum(a.tolsupir) as tolsupir"),
                    db::raw("sum(a.uangmakanberjenjang) as uangmakanberjenjang"),
                )
                ->join(db::raw($tempbuktisum . " b "), 'a.gajisupir_nobukti', 'b.nobukti')
                ->first();
                // dd('test');
            $this->totalNominal = $querytotal->total ?? 0;
            $this->totalUangJalan = $querytotal->uangjalan ?? 0;
            $this->totalBBM = $querytotal->bbm ?? 0;
            $this->totalUangMakan = $querytotal->uangmakanharian ?? 0;
            $this->totalPinjaman = $querytotal->potonganpinjaman ?? 0;
            $this->totalPinjamanSemua = $querytotal->potonganpinjamansemua ?? 0;
            $this->totalDeposito = $querytotal->deposito ?? 0;
            $this->totalKomisi = $querytotal->komisisupir ?? 0;
            $this->totalBiayaExtra = $querytotal->biayaextra ?? 0;
            $this->totalGajiSupir = $querytotal->gajisupir ?? 0;
            $this->totalGajiKenek = $querytotal->gajikenek ?? 0;
            $this->totalTol = $querytotal->tolsupir ?? 0;
            $this->totalUangMakanBerjenjang = $querytotal->uangmakanberjenjang ?? 0;
            
            return $tempQuery->get();
        }

        return $query->get();
    }

    public function sortposition($query)
    {
        if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'total') {
            return $query->orderBy('gajisupirheader.total', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'uangjalan') {
            return $query->orderBy('gajisupirheader.uangjalan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'bbm') {
            return $query->orderBy('gajisupirheader.bbm', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'uangmakanharian') {
            return $query->orderBy('gajisupirheader.uangmakanharian', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'potonganpinjaman') {
            return $query->orderBy('gajisupirheader.potonganpinjaman', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'potonganpinjamansemua') {
            return $query->orderBy('gajisupirheader.potonganpinjamansemua', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'deposito') {
            return $query->orderBy('gajisupirheader.deposito', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'komisisupir') {
            return $query->orderBy('gajisupirheader.komisisupir', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tolsupir') {
            return $query->orderBy('gajisupirheader.tolsupir', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
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
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                                $query = $query->where('a.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                                $query = $query->orWhere( 'a.' . $filters['field'], 'LIKE', "%$filters[data]%");
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


    
    public function filterposition($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->whereRaw("format(gajisupirheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->whereRaw("format(gajisupirheader.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->whereRaw("format(gajisupirheader.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->whereRaw("format(gajisupirheader.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->whereRaw("format(gajisupirheader.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->whereRaw("format(gajisupirheader.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->whereRaw("format(gajisupirheader.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'komisisupir') {
                                $query = $query->whereRaw("format(gajisupirheader.komisisupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tolsupir') {
                                $query = $query->whereRaw("format(gajisupirheader.tolsupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'supir_id') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->orWhere('trado.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format(gajisupirheader.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhereRaw("format(gajisupirheader.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->orWhereRaw("format(gajisupirheader.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhereRaw("format(gajisupirheader.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->orWhereRaw("format(gajisupirheader.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->orWhereRaw("format(gajisupirheader.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->orWhereRaw("format(gajisupirheader.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'komisisupir') {
                                $query = $query->orWhereRaw("format(gajisupirheader.komisisupir, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tolsupir') {
                                $query = $query->orWhereRaw("format(gajisupirheader.tolsupir, '#,#0.00') LIKE '%$filters[data]%'");
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

    public function processStore(ProsesGajiSupirHeader $prosesGajiSupirHeader, array $data): ProsesGajiSupirDetail
    {
        $prosesGajiSupirDetail = new ProsesGajiSupirDetail();
        $prosesGajiSupirDetail->prosesgajisupir_id = $prosesGajiSupirHeader->id;
        $prosesGajiSupirDetail->nobukti = $prosesGajiSupirHeader->nobukti;
        $prosesGajiSupirDetail->gajisupir_nobukti = $data['gajisupir_nobukti'];
        $prosesGajiSupirDetail->supir_id = $data['supir_id'];
        $prosesGajiSupirDetail->trado_id = $data['trado_id'];
        $prosesGajiSupirDetail->nominal = $data['nominal'];
        $prosesGajiSupirDetail->keterangan = $data['keterangan'];
        $prosesGajiSupirDetail->modifiedby = auth('api')->user()->name;
        $prosesGajiSupirDetail->info = html_entity_decode(request()->info);
        
        if (!$prosesGajiSupirDetail->save()) {
            throw new \Exception("Error storing Proses Gaji Supir Detail.");
        }

        return $prosesGajiSupirDetail;
    }
}
