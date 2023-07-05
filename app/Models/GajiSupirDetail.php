<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GajiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'gajisupirdetail';
    protected $tempTable = '';
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

            $query->select(
                $this->table .  '.nobukti',
                $this->table . '.suratpengantar_nobukti',
                'suratpengantar.nosp',
                'statuscontainer.kodestatuscontainer',
                'dari.keterangan as dari',
                'sampai.keterangan as sampai',
                'container.kodecontainer',
                'suratpengantar.liter',
                'suratpengantar.nocont',
                'suratpengantar.tglsp',
                'agen.namaagen as agen',
                'parameter.text as statusritasi',
                $this->table . '.gajisupir',
                $this->table . '.gajikenek',
                DB::raw("({$this->table}.gajisupir + {$this->table}.gajikenek) as borongan"),
                $this->table . '.gajiritasi as upahritasi',
                $this->table . '.biayatambahan as biayaextra'
            )
                ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), $this->table . '.suratpengantar_nobukti', 'suratpengantar.nobukti')
                ->leftJoin(DB::raw("statuscontainer with (readuncommitted)"), 'suratpengantar.statuscontainer_id', 'statuscontainer.id')
                ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
                ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id')
                ->leftJoin(DB::raw("ritasi with (readuncommitted)"), $this->table . '.ritasi_nobukti', 'ritasi.nobukti')
                ->leftJoin(DB::raw("container with (readuncommitted)"), 'suratpengantar.container_id', 'container.id')
                ->leftJoin(DB::raw("agen with (readuncommitted)"), 'suratpengantar.agen_id', 'agen.id')
                ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', 'parameter.id')
                ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-');

            $query->where($this->table . '.gajisupir_id', '=', request()->gajisupir_id);
            return $query->get();
        } else {
            $tempDetail = $this->createTemp();
            $this->tempTable = $tempDetail;
            $tempQuery = DB::table($tempDetail)->from(DB::raw("$tempDetail with (readuncommitted)"));

            $tempQuery->orderBy($tempDetail . '.' . $this->params['sortIndex'], $this->params['sortOrder']);

            $this->filter($tempQuery, $tempDetail);
            $this->totalGajiSupir = $tempQuery->sum('gajisupir');
            $this->totalGajiKenek = $tempQuery->sum('gajikenek');
            $this->totalKomisiSupir = $tempQuery->sum('komisisupir');
            $this->totalUpahRitasi = $tempQuery->sum('upahritasi');
            $this->totalBiayaExtra = $tempQuery->sum('biayaextra');
            $this->totalTolSupir = $tempQuery->sum('tolsupir');

            $this->totalRows = $tempQuery->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($tempQuery);
            return $tempQuery->get();
        }
    }


    public function createTemp()
    {

        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti as nobukti',
                'gajisupirdetail.suratpengantar_nobukti',
                'suratpengantar.tglbukti as tglsp',
                'dari.keterangan as dari',
                'sampai.keterangan as sampai',
                'suratpengantar.nocont',
                'suratpengantar.nosp',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'parameter.text as statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan'
            )
            ->leftJoin(DB::raw("suratpengantar with (readuncommitted)"), 'gajisupirdetail.suratpengantar_nobukti', 'suratpengantar.nobukti')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'suratpengantar.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'suratpengantar.sampai_id', 'sampai.id')
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', 'parameter.id')

            ->where('gajisupirdetail.suratpengantar_nobukti', '!=', '-')
            ->where('gajisupirdetail.gajisupir_id', request()->gajisupir_id);


        Schema::create($temp, function ($table) {
            $table->string('nobukti')->nullable();
            $table->string('suratpengantar_nobukti')->nullable();
            $table->date('tglsp')->nullable()->nullable();
            $table->string('dari')->nullable();
            $table->string('sampai')->nullable();
            $table->string('nocont')->nullable();
            $table->string('nosp')->nullable();
            $table->bigInteger('gajisupir')->nullable();
            $table->bigInteger('gajikenek')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('upahritasi')->nullable();
            $table->string('ritasi_nobukti')->nullable();
            $table->string('statusritasi')->nullable();
            $table->bigInteger('biayaextra')->nullable();
            $table->string('keteranganbiayatambahan')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'tglsp', 'dari', 'sampai', 'nocont', 'nosp', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiayatambahan'], $fetch);

        $fetch = DB::table('gajisupirdetail')->from(DB::raw("gajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirdetail.nobukti as nobukti',
                'gajisupirdetail.suratpengantar_nobukti',
                'ritasi.tglbukti as tglsp',
                'dari.keterangan as dari',
                'sampai.keterangan as sampai',
                'gajisupirdetail.gajisupir',
                'gajisupirdetail.gajikenek',
                'gajisupirdetail.komisisupir',
                'gajisupirdetail.tolsupir',
                'gajisupirdetail.gajiritasi as upahritasi',
                'ritasi.nobukti as ritasi_nobukti',
                'parameter.text as statusritasi',
                'gajisupirdetail.biayatambahan as biayaextra',
                'gajisupirdetail.keteranganbiayatambahan',
                

            )
            ->leftJoin(DB::raw("ritasi with (readuncommitted)"), 'gajisupirdetail.ritasi_nobukti', 'ritasi.nobukti')
            ->leftJoin(DB::raw("kota as dari with (readuncommitted)"), 'ritasi.dari_id', 'dari.id')
            ->leftJoin(DB::raw("kota as sampai with (readuncommitted)"), 'ritasi.sampai_id', 'sampai.id')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'ritasi.statusritasi', 'parameter.id')

            ->where('gajisupirdetail.suratpengantar_nobukti', '-')
            ->where('gajisupirdetail.gajisupir_id', request()->gajisupir_id);

        $tes = DB::table($temp)->insertUsing(['nobukti', 'suratpengantar_nobukti', 'tglsp', 'dari', 'sampai', 'gajisupir', 'gajikenek', 'komisisupir', 'tolsupir', 'upahritasi', 'ritasi_nobukti', 'statusritasi', 'biayaextra', 'keteranganbiayatambahan'], $fetch);

        return $temp;
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($tempQuery, $tempDetail, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $tempQuery->where(function ($tempQuery) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra') {
                                $query = $tempQuery->whereRaw("format(".$this->tempTable . "." . $filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglsp') {
                                $query = $tempQuery->whereRaw("format(".$this->tempTable . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else{
                                $tempQuery = $tempQuery->where($this->tempTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":

                    $tempQuery->where(function ($tempQuery) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'gajisupir' || $filters['field'] == 'gajikenek' || $filters['field'] == 'komisisupir' || $filters['field'] == 'tolsupir' || $filters['field'] == 'upahritasi' || $filters['field'] == 'biayaextra') {
                                $query = $tempQuery->orWhereRaw("format(".$this->tempTable . "." . $filters['field'].", '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglsp') {
                                $query = $tempQuery->orWhereRaw("format(".$this->tempTable . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else{
                                $tempQuery = $tempQuery->orWhere($this->tempTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                default:

                    break;
            }

            $this->totalRows = $tempQuery->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $tempQuery;
    }

    public function paginate($tempQuery)
    {
        return $tempQuery->skip($this->params['offset'])->take($this->params['limit']);
    }
    public function processStore(GajiSupirHeader $gajiSupirHeader, array $data): GajiSupirDetail
    {
        $gajiSupirDetail = new GajiSupirDetail();
        $gajiSupirDetail->gajisupir_id = $gajiSupirHeader->id;
        $gajiSupirDetail->nobukti = $gajiSupirHeader->nobukti;
        $gajiSupirDetail->nominaldeposito = $data['nominaldeposito'];
        $gajiSupirDetail->nourut = $data['nourut'];
        $gajiSupirDetail->suratpengantar_nobukti = $data['suratpengantar_nobukti'];
        $gajiSupirDetail->ritasi_nobukti = $data['ritasi_nobukti'];
        $gajiSupirDetail->komisisupir = $data['komisisupir'];
        $gajiSupirDetail->tolsupir = $data['tolsupir'];
        $gajiSupirDetail->voucher = $data['voucher'];
        $gajiSupirDetail->novoucher = $data['novoucher'];
        $gajiSupirDetail->gajisupir = $data['gajisupir'];
        $gajiSupirDetail->gajikenek = $data['gajikenek'];
        $gajiSupirDetail->gajiritasi = $data['gajiritasi'];
        $gajiSupirDetail->biayatambahan = $data['biayatambahan'];
        $gajiSupirDetail->keteranganbiayatambahan = $data['keteranganbiayatambahan'];
        $gajiSupirDetail->nominalpengembalianpinjaman = $data['nominalpengembalianpinjaman'];
        
        $gajiSupirDetail->modifiedby = auth('api')->user()->name;
        
        if (!$gajiSupirDetail->save()) {
            throw new \Exception("Error storing gaji supir detail.");
        }

        return $gajiSupirDetail;
    }
}
