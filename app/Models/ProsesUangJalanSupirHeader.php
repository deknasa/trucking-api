<?php

namespace App\Models;

use App\Services\RunningNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesUangJalanSupirHeader extends MyModel
{
    use HasFactory;
    protected $table = 'prosesuangjalansupirheader';

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


        $query = DB::table($this->table)->from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti',
                'prosesuangjalansupirheader.nominaluangjalan',
                'prosesuangjalansupirheader.userapproval',
                DB::raw('(case when (year(prosesuangjalansupirheader.tglapproval) <= 2000) then null else prosesuangjalansupirheader.tglapproval end ) as tglapproval'),
                'statusapproval.memo as statusapproval',
                'trado.kodetrado as trado_id',
                'statuscetak.memo as statuscetak',
                'prosesuangjalansupirheader.userbukacetak',
                DB::raw('(case when (year(prosesuangjalansupirheader.tglbukacetak) <= 2000) then null else prosesuangjalansupirheader.tglbukacetak end ) as tglbukacetak'),
                'supir.namasupir as supir_id',
                'prosesuangjalansupirheader.modifiedby',
                'prosesuangjalansupirheader.created_at',
                'prosesuangjalansupirheader.updated_at',
                db::raw("cast((format(absensisupir.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderabsensisupirheader"),
                db::raw("cast(cast(format((cast((format(absensisupir.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderabsensisupirheader"),
            )
            ->leftJoin(DB::raw("absensisupirheader as absensisupir with (readuncommitted)"), 'prosesuangjalansupirheader.absensisupir_nobukti', '=', 'absensisupir.nobukti')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesuangjalansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesuangjalansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');

        if (request()->tgldari) {
            $query->whereBetween('prosesuangjalansupirheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
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
        $keteranganerror = $error->cekKeteranganError('SAPP') ?? '';
        $keterangantambahanerror = $error->cekKeteranganError('PTBL') ?? '';
        $getDetail = DB::table("prosesuangjalansupirdetail")->from(DB::raw("prosesuangjalansupirdetail with (readuncommitted)"))
            ->where('nobukti', $nobukti)
            ->get();

        foreach ($getDetail as $row => $val) {
            if ($val->penerimaantrucking_nobukti != '') {
                $cekPenerimaan = DB::table("penerimaantruckingheader")->from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $val->penerimaantrucking_nobukti)
                    ->first();

                if ($cekPenerimaan != '') {
                    $penerimaan = $cekPenerimaan->penerimaan_nobukti ?? '';
                    // dd($penerimaan);
                    $idpenerimaan = db::table('penerimaanheader')->from(db::raw("penerimaanheader a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.nobukti'
                        )
                        ->where('a.nobukti', $penerimaan)
                        ->first();
                    if ($idpenerimaan != '') {
                        $cekJurnal = DB::table('jurnalumumpusatheader')
                            ->from(
                                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
                            )
                            ->select(
                                'a.nobukti'
                            )
                            ->where('a.nobukti', '=', $idpenerimaan->nobukti)
                            ->first();
                        if (isset($cekJurnal)) {
                            $data = [
                                'kondisi' => true,
                                'keterangan' =>  'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Approval Jurnal <b>' . $cekJurnal->nobukti . '</b> <br> ' . $keterangantambahanerror,
                                'kodeerror' => 'SAPP'
                            ];
                            goto selesai;
                        }
                    }
                }
            }


            if ($val->pengeluarantrucking_nobukti != '') {
                $cekPengeluaran = DB::table("pengeluarantruckingheader")->from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                    ->where('nobukti', $val->pengeluarantrucking_nobukti)
                    ->first();

                if ($cekPengeluaran != '') {
                    $pengeluaran = $cekPengeluaran->pengeluaran_nobukti ?? '';
                    // dd($pengeluaran);
                    $idpengeluaran = db::table('pengeluaranheader')->from(db::raw("pengeluaranheader a with (readuncommitted)"))
                        ->select(
                            'a.id',
                            'a.nobukti'
                        )
                        ->where('a.nobukti', $pengeluaran)
                        ->first();
                    if ($idpengeluaran != '') {
                        $cekJurnal = DB::table('jurnalumumpusatheader')
                            ->from(
                                DB::raw("jurnalumumpusatheader as a with (readuncommitted)")
                            )
                            ->select(
                                'a.nobukti'
                            )
                            ->where('a.nobukti', '=', $idpengeluaran->nobukti)
                            ->first();
                        if (isset($cekJurnal)) {
                            $data = [
                                'kondisi' => true,
                                'keterangan' =>  'No Bukti <b>' . $nobukti . '</b><br>' . $keteranganerror . '<br> No Bukti Approval Jurnal <b>' . $cekJurnal->nobukti . '</b> <br> ' . $keterangantambahanerror,
                                'kodeerror' => 'SAPP'
                            ];
                            goto selesai;
                        }
                    }
                }
            }
        }

        $data = [
            'kondisi' => false,
            'keterangan' => '',
        ];
        selesai:
        return $data;
    }

    public function getPinjaman($supirId)
    {
        $nobukti = request()->nobukti;
        $tempPribadi = $this->createTempPinjaman($supirId);

        $tglBukti = date('Y-m-d', strtotime(request()->tglbukti));
        if ($nobukti != '') {
        } else {
            $query = PengeluaranTruckingDetail::from(DB::raw("pengeluarantruckingdetail with (readuncommitted)"))
                ->select(DB::raw("row_number() Over(Order By pengeluarantruckingdetail.nobukti) as id,pengeluarantruckingheader.tglbukti as pinj_tglbukti,pengeluarantruckingdetail.nobukti as pinj_nobukti,pengeluarantruckingdetail.keterangan as keteranganpinjaman," . $tempPribadi . ".sisa, $tempPribadi.jlhpinjaman,$tempPribadi.totalbayar"))
                ->leftJoin(DB::raw("$tempPribadi with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', $tempPribadi . ".nobukti")
                ->leftJoin(DB::raw("pengeluarantruckingheader with (readuncommitted)"), 'pengeluarantruckingdetail.nobukti', "pengeluarantruckingheader.nobukti")
                ->whereRaw("pengeluarantruckingdetail.supir_id = $supirId")
                ->whereRaw("pengeluarantruckingdetail.nobukti = $tempPribadi.nobukti")
                ->where("pengeluarantruckingheader.tglbukti", "<=", $tglBukti)
                ->where(function ($query) use ($tempPribadi) {
                    $query->whereRaw("$tempPribadi.sisa != 0")
                        ->orWhereRaw("$tempPribadi.sisa is null");
                })
                ->orderBy('pengeluarantruckingheader.tglbukti', 'asc')
                ->orderBy('pengeluarantruckingdetail.nobukti', 'asc');
        }
        return $query->get();
    }

    public function createTempPinjaman($supirId)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti,SUM(pengeluarantruckingdetail.nominal) AS jlhpinjaman,
            (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail
            WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS totalbayar, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))
            ->whereRaw("(pengeluarantruckingdetail.supir_id=" . $supirId . " or " . $supirId . "=0)")
            ->where("pengeluarantruckingdetail.nobukti",  'LIKE', "%PJT%")
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('jlhpinjaman')->nullable();
            $table->bigInteger('totalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'jlhpinjaman', 'totalbayar', 'sisa'], $fetch);


        return $temp;
    }

    public function createTempPengembalianPinjaman($nobukti)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table("penerimaantruckingdetail")->from(DB::raw("penerimaantruckingdetail as a with (readuncommitted)"))
            ->select(DB::raw("a.id as penerimaantruckingheader_id, c.nobukti,c.tglbukti,a.supir_id, a.nominal, a.keterangan,d.nominal AS jlhpinjaman, 
             (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= d.nobukti and penerimaantruckingdetail.penerimaantruckingheader_id != b.id) AS totalbayar,
             (select d.nominal - sum(isnull(penerimaantruckingdetail.nominal,0)) from penerimaantruckingdetail where d.nobukti=penerimaantruckingdetail.pengeluarantruckingheader_nobukti) as sisa"))
            ->leftJoin(DB::raw("penerimaantruckingheader as b"), 'b.id', 'a.penerimaantruckingheader_id')
            ->leftJoin(DB::raw("pengeluarantruckingheader as c"), 'c.nobukti', 'a.pengeluarantruckingheader_nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail as d"), 'c.nobukti', 'd.nobukti')
            ->where('b.penerimaantrucking_id', "2")
            ->where('b.pendapatansupir_bukti', $nobukti);
        Schema::create($temp, function ($table) {
            $table->bigInteger('penerimaantruckingheader_id')->nullable();
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('supir_id')->nullable();
            $table->float('nominal')->nullable();
            $table->longText('keterangan')->nullable();
            $table->float('jlhpinjaman')->nullable();
            $table->float('totalbayar')->nullable();
            $table->float('sisa')->nullable();
        });
        DB::table($temp)->insertUsing(['penerimaantruckingheader_id', 'nobukti', 'tglbukti', 'supir_id', 'nominal', 'keterangan', 'jlhpinjaman', 'totalbayar', 'sisa'], $fetch);

        return $temp;
    }
    public function getPengembalian($id)
    {
        $query = DB::table("prosesuangjalansupirdetail")->from(DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)"))
            ->select(DB::raw("b.penerimaantruckingheader_id as id, c.nobukti as pinj_nobukti, d.tglbukti as pinj_tglbukti, c.nominal as jlhpinjaman,
        (SELECT isnull(SUM(penerimaantruckingdetail.nominal),0) FROM penerimaantruckingdetail 
        WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= c.nobukti and penerimaantruckingdetail.penerimaantruckingheader_id != b.id) AS totalbayar,
        (select c.nominal - sum(isnull(penerimaantruckingdetail.nominal,0)) from penerimaantruckingdetail where c.nobukti=penerimaantruckingdetail.pengeluarantruckingheader_nobukti) as sisa,
        b.nominal as nombayar, c.keterangan as keteranganpinjaman"))
            ->leftJoin(DB::raw("penerimaantruckingdetail as b with (readuncommitted)"), 'a.penerimaantrucking_nobukti', 'b.nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingdetail as c with (readuncommitted)"), 'c.nobukti', 'b.pengeluarantruckingheader_nobukti')
            ->leftJoin(DB::raw("pengeluarantruckingheader as d with (readuncommitted)"), 'd.nobukti', 'c.nobukti')
            ->where('a.prosesuangjalansupir_id', $id)
            ->where('a.statusprosesuangjalan', 230);

        return $query->get();
    }

    public function findAll($id)
    {
        $query = ProsesUangJalanSupirHeader::from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti as absensisupir',
                'prosesuangjalansupirheader.supir_id',
                'supir.namasupir as supir',
                'prosesuangjalansupirheader.nominaluangjalan as uangjalan',
                'prosesuangjalansupirheader.trado_id',
                'trado.kodetrado as trado'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->where('prosesuangjalansupirheader.id', $id);

        return $query->first();
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.absensisupir_nobukti,
                 'trado.kodetrado as trado_id',
                 'supir.namasupir as supir_id',
                 $this->table.nominaluangjalan,
                 statusapproval.text as statusapproval,
                 prosesuangjalansupirheader.userapproval,
                 (case when (year(prosesuangjalansupirheader.tglapproval) <= 2000) then null else prosesuangjalansupirheader.tglapproval end ) as tglapproval,
                 statuscetak.text as statuscetak,
                 prosesuangjalansupirheader.userbukacetak,
                 (case when (year(prosesuangjalansupirheader.tglbukacetak) <= 2000) then null else prosesuangjalansupirheader.tglbukacetak end ) as tglbukacetak,
                 $this->table.modifiedby,
                 $this->table.created_at,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesuangjalansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesuangjalansupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('absensisupir_nobukti', 1000)->nullable();
            $table->string('trado_id')->nullable();
            $table->string('supir_id')->nullable();
            $table->float('nominaluangjalan')->nullable();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
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
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'absensisupir_nobukti', 'trado_id', 'supir_id', 'nominaluangjalan', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'trado_id') {
            return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'supir_id') {
            return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
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
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'trado_id') {
                                $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir_id') {
                                $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominaluangjalan') {
                                $query = $query->whereRaw("format($this->table.nominaluangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'trado_id') {
                                    $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'supir_id') {
                                    $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominaluangjalan') {
                                    $query = $query->orWhereRaw("format($this->table.nominaluangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
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

    public function getNominalAbsensi($nobukti, $supir_id, $trado_id)
    {
        $query = DB::table("absensisupirdetail")->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->where('nobukti', $nobukti)
            ->where('supir_id', $supir_id)
            ->where('trado_id', $trado_id)
            ->first();
        return $query;
    }

    public function getSisaPinjamanForValidation($nobukti)
    {
        $fetch = DB::table('pengeluarantruckingdetail')
            ->from(
                DB::raw("pengeluarantruckingdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengeluarantruckingdetail.nobukti, (SELECT (pengeluarantruckingdetail.nominal - coalesce(SUM(penerimaantruckingdetail.nominal),0)) FROM penerimaantruckingdetail WHERE penerimaantruckingdetail.pengeluarantruckingheader_nobukti= pengeluarantruckingdetail.nobukti) AS sisa"))

            ->where("pengeluarantruckingdetail.nobukti", $nobukti)
            ->groupBy('pengeluarantruckingdetail.nobukti', 'pengeluarantruckingdetail.nominal');

        return $fetch->first();
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(DB::raw("prosesuangjalansupirheader with (readuncommitted)"))
            ->select(
                'prosesuangjalansupirheader.id',
                'prosesuangjalansupirheader.nobukti',
                'prosesuangjalansupirheader.tglbukti',
                'prosesuangjalansupirheader.absensisupir_nobukti',
                'prosesuangjalansupirheader.nominaluangjalan',
                'trado.kodetrado as trado_id',
                'supir.namasupir as supir_id',
                'statuscetak.memo as statuscetak',
                "statuscetak.id as  statuscetak_id",
                'prosesuangjalansupirheader.jumlahcetak',
                DB::raw("'Laporan Proses Uang Jalan Supir' as judulLaporan"),
                DB::raw("'" . $getJudul->text . "' as judul"),
                DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
                DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
            )
            ->where("$this->table.id", $id)
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesuangjalansupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'prosesuangjalansupirheader.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'prosesuangjalansupirheader.supir_id', 'supir.id');

        $data = $query->first();
        return $data;
    }
    public function processStore(array $data): ProsesUangJalanSupirHeader
    {


        $group = 'PROSES UANG JALAN BUKTI';
        $subGroup = 'PROSES UANG JALAN BUKTI';
        $statusCetak = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $format = DB::table('parameter')
            ->where('grp', $group)
            ->where('subgrp', $subGroup)
            ->first();

        $prosesUangJalanSupir = new ProsesUangJalanSupirHeader();

        $statusApproval = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $prosesUangJalanSupir->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $prosesUangJalanSupir->absensisupir_nobukti = $data['absensisupir'];
        $prosesUangJalanSupir->trado_id = $data['trado_id'];
        $prosesUangJalanSupir->supir_id = $data['supir_id'];
        $prosesUangJalanSupir->statuscetak = $statusCetak->id ?? 0;
        $prosesUangJalanSupir->nominaluangjalan = $data['uangjalan'];
        $prosesUangJalanSupir->statusapproval = $statusApproval->id;
        $prosesUangJalanSupir->statusformat = $format->id;
        $prosesUangJalanSupir->modifiedby = auth('api')->user()->name;
        $prosesUangJalanSupir->info = html_entity_decode(request()->info);

        $prosesUangJalanSupir->nobukti = (new RunningNumberService)->get($group, $subGroup, $prosesUangJalanSupir->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        if (!$prosesUangJalanSupir->save()) {
            throw new \Exception("Error storing proses uang jalan supir.");
        }

        $prosesUangJalanSupirLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
            'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
            'idtrans' => $prosesUangJalanSupir->id,
            'nobuktitrans' => $prosesUangJalanSupir->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $prosesUangJalanSupir->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        $statusTransfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
        $statusAdjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
        $statusPengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        $statusDeposit = Parameter::from(DB::raw("parameter with (readuncommitted)"))
            ->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();
        $statusPosting = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS POSTING')->where('text', 'POSTING')->first();
        $detailLog = [];
        //INSERT PENGELUARAN DARI LIST TRANSFER            
        $detaillogTransfer = [];
        for ($i = 0; $i < count($data['nilaitransfer']); $i++) {
            $bankid = $data['bank_idtransfer'][$i];
            $coatransfer = Bank::from(DB::raw("bank with (readuncommitted)"))->where('id', $bankid)->first();

            // PENGELUARAN TRUCKING HEADER
            $fetchFormatTKS =  DB::table('pengeluarantrucking')
                ->where('kodepengeluaran', 'TKS')
                ->first();
            $supirIdTransfer = [];
            $nominalTransfer = [];
            $keteranganTransfer = [];

            $supirIdTransfer[] = $data['supir_id'];
            $nominalTransfer[] = $data['nilaitransfer'][$i];
            $keteranganTransfer[] = $data['keterangantransfer'][$i];

            $pengeluaranTruckingHeader = [
                'tglbukti' => date('Y-m-d', strtotime($data['tgltransfer'][$i])),
                'pengeluarantrucking_id' => $fetchFormatTKS->id,
                'bank_id' => $bankid,
                'coa' => $coatransfer->coa,
                'pengeluaran_nobukti' => '',
                'statusposting' => $statusPosting->id,
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                'supir_id' => $supirIdTransfer,
                'nominal' => $nominalTransfer,
                'keterangan' => $keteranganTransfer
            ];

            $dataPengeluaran = (new PengeluaranTruckingHeader())->processStore($pengeluaranTruckingHeader);

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
                'nobukti' => $prosesUangJalanSupir->nobukti,
                'penerimaantrucking_bank_id' => '',
                'penerimaantrucking_tglbukti' => '',
                'penerimaantrucking_nobukti' => '',
                'pengeluarantrucking_bank_id' => $bankid,
                'pengeluarantrucking_tglbukti' => date('Y-m-d', strtotime($data['tgltransfer'][$i])),
                'pengeluarantrucking_nobukti' => $dataPengeluaran->nobukti,
                'pengembaliankasgantung_bank_id' => '',
                'pengembaliankasgantung_tglbukti' => '',
                'pengembaliankasgantung_nobukti' => '',
                'statusprosesuangjalan' => $statusTransfer->id,
                'nominal' => $data['nilaitransfer'][$i],
                'keterangan' => $data['keterangantransfer'][$i],
                'modifiedby' => $prosesUangJalanSupir->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

            $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
        }
        // END PENGELUARAN DARI LIST TRANSFER 


        // INSERT ADJUST TRANSFER

        $fetchFormatATS = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
            ->where('kodepenerimaan', 'ATS')
            ->first();

        $supirIdATS[] = $data['supir_id'];
        $nominalATS[] = $data['nilaiadjust'];
        $keteranganATS[] = $data['keteranganadjust'];

        $penerimaanTruckingHeaderATS = [
            'tglbukti' => date('Y-m-d', strtotime($data['tgladjust'])),
            'penerimaantrucking_id' => $fetchFormatATS->id,
            'bank_id' => $data['bank_idadjust'],
            'coa' => $fetchFormatATS->coapostingkredit,
            'penerimaan_nobukti' => '',
            'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
            'supir_id' => $supirIdATS,
            'nominal' => $nominalATS,
            'keterangan' => $keteranganATS
        ];

        $dataPenerimaanDepo = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderATS);
        $datadetail = [
            'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
            'nobukti' => $prosesUangJalanSupir->nobukti,
            'penerimaantrucking_bank_id' => $data['bank_idadjust'],
            'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($data['tgladjust'])),
            'penerimaantrucking_nobukti' => $dataPenerimaanDepo->nobukti,
            'pengeluarantrucking_bank_id' => '',
            'pengeluarantrucking_tglbukti' => '',
            'pengeluarantrucking_nobukti' => '',
            'pengembaliankasgantung_bank_id' => '',
            'pengembaliankasgantung_tglbukti' => '',
            'pengembaliankasgantung_nobukti' => '',
            'statusprosesuangjalan' => $statusAdjust->id,
            'nominal' => $data['nilaiadjust'],
            'keterangan' => $data['keteranganadjust'],
            'modifiedby' => $prosesUangJalanSupir->modifiedby,

        ];

        //STORE PROSES UANG JALAN DETAIL
        $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

        $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
        // END PENERIMAAN DARI ADJUST TRANSFER / PENGEMBALIAN KAS GANTUNG


        // INSERT PENERIMAAN DARI DEPOSITO
        $bankidDeposit = $data['bank_iddeposit'];
        if ($bankidDeposit != '') {
            // INSERT PENERIMAAN TRUCKING DEPOSITO
            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();

            $supirIdDeposito[] = $data['supir_id'];
            $nominalDeposito[] = $data['nilaideposit'];
            $keteranganDeposito[] = $data['keterangandeposit'];

            $penerimaanTruckingHeaderDPO = [
                'tglbukti' => date('Y-m-d', strtotime($data['tgldeposit'])),
                'penerimaantrucking_id' => $fetchFormatDPO->id,
                'bank_id' => $bankidDeposit,
                'coa' => $fetchFormatDPO->coapostingkredit,
                'penerimaan_nobukti' => '',
                'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                'supir_id' => $supirIdDeposito,
                'nominal' => $nominalDeposito,
                'keterangan' => $keteranganDeposito
            ];

            $dataPenerimaanDepo = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderDPO);

            $datadetail = [
                'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
                'nobukti' => $prosesUangJalanSupir->nobukti,
                'penerimaantrucking_bank_id' => $bankidDeposit,
                'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($data['tgldeposit'])),
                'penerimaantrucking_nobukti' => $dataPenerimaanDepo->nobukti,
                'pengeluarantrucking_bank_id' => '',
                'pengeluarantrucking_tglbukti' => '',
                'pengeluarantrucking_nobukti' => '',
                'pengembaliankasgantung_bank_id' => '',
                'pengembaliankasgantung_tglbukti' => '',
                'pengembaliankasgantung_nobukti' => '',
                'statusprosesuangjalan' => $statusDeposit->id,
                'nominal' => $data['nilaideposit'],
                'keterangan' => $data['keterangandeposit'],
                'modifiedby' => $prosesUangJalanSupir->modifiedby,

            ];

            //STORE PROSES UANG JALAN DETAIL
            $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

            $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
        }
        // END PENERIMAAN DARI DEPOSITO
        // INSERT PENERIMAAN DARI PENGEMBALIAN PINJAMAN
        $detaillogPinjaman = [];

        if ($data['pjt_id']) {

            for ($i = 0; $i < count($data['pjt_id']); $i++) {
                $bankidPengembalian = $data['bank_idpengembalian'];

                // PENERIMAAN TRUCKING HEADER
                $fetchFormatPJP =  DB::table('penerimaantrucking')
                    ->where('kodepenerimaan', 'PJP')
                    ->first();
                $statusformaPJP = $fetchFormatPJP->format;

                $supirPengembalian = [];
                $pengeluaranTruckingPengembalian = [];
                $nominalPengembalian = [];
                $keteranganPengembalian = [];

                $supirPengembalian[] = $data['supir_id'];
                $pengeluaranTruckingPengembalian[] = $data['pengeluarantruckingheader_nobukti'][$i];
                $nominalPengembalian[] = $data['nombayar'][$i];
                $keteranganPengembalian[] = $data['keteranganpinjaman'][$i];

                $penerimaanTruckingHeaderPJP = [
                    'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_id' => $fetchFormatPJP->id,
                    'supirheader_id' => $prosesUangJalanSupir->supir_id,
                    'bank_id' => $bankidPengembalian,
                    'coa' => $fetchFormatPJP->coapostingkredit,
                    'penerimaan_nobukti' => '',
                    'postingdari' => 'ENTRY PROSES UANG JALAN SUPIR',
                    'diterimadari' => $data['supir'],
                    'supir_id' => $supirPengembalian,
                    'pengeluarantruckingheader_nobukti' => $pengeluaranTruckingPengembalian,
                    'keterangan' => $keteranganPengembalian,
                    'nominal' => $nominalPengembalian
                ];

                $dataPenerimaanPinjaman = (new PenerimaanTruckingHeader())->processStore($penerimaanTruckingHeaderPJP);


                $datadetail = [
                    'prosesuangjalansupir_id' => $prosesUangJalanSupir->id,
                    'nobukti' => $prosesUangJalanSupir->nobukti,
                    'penerimaantrucking_bank_id' => $bankidPengembalian,
                    'penerimaantrucking_tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
                    'penerimaantrucking_nobukti' => $dataPenerimaanPinjaman->nobukti,
                    'pengeluarantrucking_bank_id' => '',
                    'pengeluarantrucking_tglbukti' => '',
                    'pengeluarantrucking_nobukti' => '',
                    'pengembaliankasgantung_bank_id' => '',
                    'pengembaliankasgantung_tglbukti' => '',
                    'pengembaliankasgantung_nobukti' => '',
                    'statusprosesuangjalan' => $statusPengembalian->id,
                    'nominal' => $data['nombayar'][$i],
                    'keterangan' => $data['keteranganpinjaman'][$i],
                    'modifiedby' => $prosesUangJalanSupir->modifiedby,

                ];

                //STORE PROSES UANG JALAN DETAIL
                $prosesUangJalanSupirDetail = (new ProsesUangJalanSupirDetail())->processStore($prosesUangJalanSupir, $datadetail);

                $prosesUangJalanSupirDetails[] = $prosesUangJalanSupirDetail->toArray();
            }
        }
        // END PENERIMAAN PENGEMBALIAN PINJAMAN


        (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
            'postingdari' =>  'ENTRY PROSES UANG JALAN SUPIR DETAIL',
            'idtrans' =>  $prosesUangJalanSupirLogTrail->id,
            'nobuktitrans' => $prosesUangJalanSupir->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $prosesUangJalanSupirDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);
        return $prosesUangJalanSupir;
    }

    public function processUpdate(ProsesUangJalanSupirHeader $prosesUangJalanSupirHeader, array $data): ProsesUangJalanSupirHeader
    {
        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PROSES UANG JALAN')->first();

        if (trim($getTgl->text) == 'YA') {
            $group = 'PROSES UANG JALAN BUKTI';
            $subGroup = 'PROSES UANG JALAN BUKTI';
            $querycek = DB::table('prosesuangjalansupirheader')->from(
                DB::raw("prosesuangjalansupirheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $prosesUangJalanSupirHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();

            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subGroup, $prosesUangJalanSupirHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }

            $prosesUangJalanSupirHeader->nobukti = $nobukti;
            $prosesUangJalanSupirHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }

        $prosesUangJalanSupirHeader->modifiedby = auth('api')->user()->name;
        $prosesUangJalanSupirHeader->info = html_entity_decode(request()->info);

        if (!$prosesUangJalanSupirHeader->save()) {
            throw new \Exception("Error updating proses uang jalan supir header.");
        }

        $prosesUangJalanSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($prosesUangJalanSupirHeader->getTable()),
            'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
            'idtrans' => $prosesUangJalanSupirHeader->id,
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $prosesUangJalanSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        $id = $prosesUangJalanSupirHeader->id;
        $detail = new ProsesUangJalanSupirDetail();
        $detailTransfer = $detail->findTransfer($id);


        $detailLog = [];
        foreach ($detailTransfer as $key => $value) {
            $pengeluarantrucking_nobukti = $value['pengeluarantrucking_nobukti'];
            $fetchFormatTKS =  DB::table('pengeluarantrucking')
                ->where('kodepengeluaran', 'TKS')
                ->first();

            $getPengeluaranTrucking = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where("pengeluaran_nobukti", $pengeluarantrucking_nobukti)->first();

            $supirIdTransfer = [];
            $nominalTransfer = [];
            $keteranganTransfer = [];

            $bankid = $data['bank_idtransfer'][$key];
            $supirIdTransfer[] = $prosesUangJalanSupirHeader->supir_id;
            $nominalTransfer[] = $value['nominal'];
            $keteranganTransfer[] = $data['keterangantransfer'][$key];

            $pengeluaranTruckingHeader = [
                'tglbukti' => date('Y-m-d', strtotime($data['tgltransfer'][$key])),
                'pengeluarantrucking_id' => $fetchFormatTKS->id,
                'bank_id' => $bankid,
                'coa' => $fetchFormatTKS->coapostingdebet,
                'pengeluaran_nobukti' => '',
                'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
                'supir_id' => $supirIdTransfer,
                'nominal' => $nominalTransfer,
                'keterangan' => $keteranganTransfer
            ];

            $newPengeluaranTrucking = new PengeluaranTruckingHeader();
            $newPengeluaranTrucking = $newPengeluaranTrucking->findAll($getPengeluaranTrucking->id);
            $pengeluaranTrucking = (new PengeluaranTruckingHeader())->processUpdate($newPengeluaranTrucking, $pengeluaranTruckingHeader);

            $editProsesDetailTransfer = ProsesUangJalanSupirDetail::find($value['idtransfer']);
            $editProsesDetailTransfer->keterangan = $data['keterangantransfer'][$key];
            $editProsesDetailTransfer->update();

            $detailLog[] = $editProsesDetailTransfer->toArray();
        }


        // UPDATE ADJUST 
        $detailAdjust = $detail->adjustTransfer($id);
        $fetchFormatATS = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
            ->where('kodepenerimaan', 'ATS')
            ->first();

        $getPenerimaanTruckingATS = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where("penerimaan_nobukti", $detailAdjust->penerimaan_nobukti)->first();

        $supirIdATS[] = $prosesUangJalanSupirHeader->supir_id;
        $nominalATS[] = $detailAdjust->nilaiadjust;
        $keteranganATS[] = $data['keteranganadjust'];

        $penerimaanTruckingHeaderATS = [
            'tglbukti' => date('Y-m-d', strtotime($detailAdjust->tgldeposit)),
            'penerimaantrucking_id' => $fetchFormatATS->id,
            'bank_id' => $data['bank_idadjust'],
            'coa' => $fetchFormatATS->coapostingkredit,
            'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
            'supir_id' => $supirIdATS,
            'nominal' => $nominalATS,
            'keterangan' => $keteranganATS
        ];

        $newPenerimaanTruckingATS = new PenerimaanTruckingHeader();
        $newPenerimaanTruckingATS = $newPenerimaanTruckingATS->findAll($getPenerimaanTruckingATS->id);
        (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingATS, $penerimaanTruckingHeaderATS);

        $editProsesDetailAdjust = ProsesUangJalanSupirDetail::find($detailAdjust->idadjust);
        $editProsesDetailAdjust->keterangan = $data['keteranganadjust'];
        $editProsesDetailAdjust->update();

        $detailLog[] = $editProsesDetailAdjust->toArray();


        // UPDATE DEPOSITO
        $detailDeposito = $detail->deposito($id);
        if ($detailDeposito != null) {
            $fetchFormatDPO = PenerimaanTrucking::from(DB::raw("penerimaantrucking with (readuncommitted)"))
                ->where('kodepenerimaan', 'DPO')
                ->first();
            $getPenerimaanTruckingDPO = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where("penerimaan_nobukti", $detailDeposito->penerimaandeposit_nobukti)->first();

            $supirIdDeposito[] = $prosesUangJalanSupirHeader->supir_id;
            $nominalDeposito[] = $detailDeposito->nilaideposit;
            $keteranganDeposito[] = $data['keterangandeposit'];

            $penerimaanTruckingHeaderDPO = [
                'tglbukti' => date('Y-m-d', strtotime($detailDeposito->tgldeposit)),
                'penerimaantrucking_id' => $fetchFormatDPO->id,
                'bank_id' => $detailDeposito->bank_iddeposit,
                'coa' => $fetchFormatDPO->coapostingkredit,
                'postingdari' => 'EDIT PROSES UANG JALAN SUPIR',
                'supir_id' => $supirIdDeposito,
                'nominal' => $nominalDeposito,
                'keterangan' => $keteranganDeposito
            ];

            $newPenerimaanTruckingDPO = new PenerimaanTruckingHeader();
            $newPenerimaanTruckingDPO = $newPenerimaanTruckingDPO->findAll($getPenerimaanTruckingDPO->id);
            (new PenerimaanTruckingHeader())->processUpdate($newPenerimaanTruckingDPO, $penerimaanTruckingHeaderDPO);

            $editProsesDetailDeposit = ProsesUangJalanSupirDetail::find($detailDeposito->iddeposit);
            $editProsesDetailDeposit->keterangan = $data['keterangandeposit'];
            $editProsesDetailDeposit->update();

            $detailLog[] = $editProsesDetailDeposit->toArray();
        }

        (new LogTrail())->processStore([
            'namatabel' => 'PROSESUANGJALANSUPIRDETAIL',
            'postingdari' =>  'EDIT PROSES UANG JALAN SUPIR DETAIL',
            'idtrans' =>  $prosesUangJalanSupirHeaderLogTrail->id,
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'EDIT',
            'datajson' => $detailLog,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $prosesUangJalanSupirHeader;
    }


    public function processDestroy($id, $postingDari = ''): ProsesUangJalanSupirHeader
    {
        $getDetail = ProsesUangJalanSupirDetail::lockForUpdate()->where('prosesuangjalansupir_id', $id)->get();


        $prosesUangJalanSupirHeader = new ProsesUangJalanSupirHeader();
        $prosesUangJalanSupirHeader = $prosesUangJalanSupirHeader->lockAndDestroy($id);

        $prosesUangJalanSupirHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $prosesUangJalanSupirHeader->getTable(),
            'postingdari' => $postingDari,
            'idtrans' => $prosesUangJalanSupirHeader->id,
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $prosesUangJalanSupirHeader->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => 'PROSESUANGJALANSUPIRDETAIL',
            'postingdari' => $postingDari,
            'idtrans' => $prosesUangJalanSupirHeaderLogTrail['id'],
            'nobuktitrans' => $prosesUangJalanSupirHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $getDetail->toArray(),
            'modifiedby' => auth('api')->user()->name
        ]);

        $transfer = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'TRANSFER')->first();
        $adjust = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'ADJUST TRANSFER')->first();
        $pengembalian = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'PENGEMBALIAN PINJAMAN')->first();
        $deposito = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS PROSES UANG JALAN')->where('text', 'DEPOSITO SUPIR')->first();
        foreach ($getDetail as $key) {

            if ($key->statusprosesuangjalan == $transfer->id) {

                $getPengeluaranTrucking = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))->where('nobukti', $key->pengeluarantrucking_nobukti)->first();
                if ($getPengeluaranTrucking != null) {
                    (new PengeluaranTruckingHeader())->processDestroy($getPengeluaranTrucking->id, $postingDari);
                }
            } else if ($key->statusprosesuangjalan == $adjust->id) {

                if ($key->penerimaantrucking_nobukti != '') {
                    $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $key->penerimaantrucking_nobukti)->first();
                    if ($getPenerimaanTrucking != null) {
                        (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
                    }
                }
            } else if ($key->statusprosesuangjalan == $pengembalian->id) {

                if ($key->penerimaantrucking_nobukti != '') {
                    $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $key->penerimaantrucking_nobukti)->first();
                    if ($getPenerimaanTrucking != null) {
                        (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
                    }
                }
            } else if ($key->statusprosesuangjalan == $deposito->id) {
                if ($key->penerimaantrucking_nobukti != '') {
                    $getPenerimaanTrucking = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))->where('nobukti', $key->penerimaantrucking_nobukti)->first();
                    if ($getPenerimaanTrucking != null) {
                        (new PenerimaanTruckingHeader())->processDestroy($getPenerimaanTrucking->id, $postingDari);
                    }
                }
            }
        }

        return $prosesUangJalanSupirHeader;
    }

    public function processApproval(array $data)
    {

        $statusApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'APPROVAL')->first();
        $statusNonApproval = Parameter::from(
            DB::raw("parameter with (readuncommitted)")
        )->where('grp', '=', 'STATUS APPROVAL')->where('text', '=', 'NON APPROVAL')->first();

        for ($i = 0; $i < count($data['prosesId']); $i++) {
            $prosesUangJalanSupir = ProsesUangJalanSupirHeader::find($data['prosesId'][$i]);

            if ($prosesUangJalanSupir->statusapproval == $statusApproval->id) {
                $prosesUangJalanSupir->statusapproval = $statusNonApproval->id;
                $prosesUangJalanSupir->tglapproval = date('Y-m-d', strtotime("1900-01-01"));
                $prosesUangJalanSupir->userapproval = '';

                $aksi = $statusNonApproval->text;
            } else {
                $prosesUangJalanSupir->statusapproval = $statusApproval->id;
                $prosesUangJalanSupir->tglapproval = date('Y-m-d', time());
                $prosesUangJalanSupir->userapproval = auth('api')->user()->name;
                $aksi = $statusApproval->text;
            }

            if (!$prosesUangJalanSupir->save()) {
                throw new \Exception("Error approval proses uang jalan supir header.");
            }
            (new LogTrail())->processStore([
                'namatabel' => strtoupper($prosesUangJalanSupir->getTable()),
                'postingdari' => 'APPROVAL PROSES UANG JALAN SUPIR',
                'idtrans' => $prosesUangJalanSupir->id,
                'nobuktitrans' => $prosesUangJalanSupir->nobukti,
                'aksi' => $aksi,
                'datajson' => $prosesUangJalanSupir->toArray(),
                'modifiedby' => auth('api')->user()->user
            ]);
        }
        return $prosesUangJalanSupir;
    }
}
