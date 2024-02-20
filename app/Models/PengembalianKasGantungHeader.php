<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\RunningNumberService;

class PengembalianKasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pengembaliankasgantungheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function cekvalidasiaksi($nobukti)
    {

        $prosesUangJalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.pengembaliankasgantung_nobukti'
            )
            ->where('a.pengembaliankasgantung_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangJalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Proses Uang Jalan Supir ' . $prosesUangJalan->nobukti,
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $jurnal = DB::table('pengembaliankasgantungheader')
            ->from(
                DB::raw("pengembaliankasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti',
                'a.penerimaan_nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"), 'a.penerimaan_nobukti', 'b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($jurnal)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal ' . $jurnal->penerimaan_nobukti,
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

    public function default()
    {

        $tempdefault = '##tempdefault' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempdefault, function ($table) {
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank', 255)->nullable();
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

        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
            );

        $data = $query->first();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';

        $query = DB::table($this->table)->select(
            'pengembaliankasgantungheader.id',
            'pengembaliankasgantungheader.nobukti',
            'pengembaliankasgantungheader.tglbukti',
            'pengembaliankasgantungheader.keterangan',
            'bank.namabank as bank',
            DB::raw('(case when (year(pengembaliankasgantungheader.tgldari) <= 2000) then null else pengembaliankasgantungheader.tgldari end ) as tgldari'),
            DB::raw('(case when (year(pengembaliankasgantungheader.tglsampai) <= 2000) then null else pengembaliankasgantungheader.tglsampai end ) as tglsampai'),
            'pengembaliankasgantungheader.penerimaan_nobukti',
            'akunpusat.keterangancoa as coa',
            'pengembaliankasgantungheader.postingdari',
            DB::raw('(case when (year(pengembaliankasgantungheader.tglkasmasuk) <= 2000) then null else pengembaliankasgantungheader.tglkasmasuk end ) as tglkasmasuk'),
            DB::raw('(case when (year(pengembaliankasgantungheader.tglbukacetak) <= 2000) then null else pengembaliankasgantungheader.tglbukacetak end ) as tglbukacetak'),
            'statuscetak.memo as statuscetak',
            'pengembaliankasgantungheader.userbukacetak',
            'pengembaliankasgantungheader.jumlahcetak',
            'pengembaliankasgantungheader.modifiedby',
            'pengembaliankasgantungheader.created_at',
            'pengembaliankasgantungheader.updated_at',
            db::raw("cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpenerimaanheader"),
            db::raw("cast(cast(format((cast((format(penerimaanheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpenerimaanheader"),

        )

            ->leftJoin(DB::raw("penerimaanheader with (readuncommitted)"), 'pengembaliankasgantungheader.penerimaan_nobukti', '=', 'penerimaanheader.nobukti')
            ->leftJoin('akunpusat', 'pengembaliankasgantungheader.coakasmasuk', 'akunpusat.coa')
            ->leftJoin('bank', 'pengembaliankasgantungheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statuscetak', 'pengembaliankasgantungheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween('pengembaliankasgantungheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(pengembaliankasgantungheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(pengembaliankasgantungheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("pengembaliankasgantungheader.statuscetak", $statusCetak);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

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
            $table->longText('keterangan')->nullable();
            $table->string('bank', 1000)->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('postingdari', 50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
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
        if (request()->tgldariheader) {
            $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);
        }
        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "keterangan",
            "bank",
            "tgldari",
            "tglsampai",
            "penerimaan_nobukti",
            "coa",
            "postingdari",
            "tglkasmasuk",
            "statuscetak",
            "userbukacetak",
            "tglbukacetak",
            "jumlahcetak",
            "modifiedby",
            "created_at",
            "updated_at",
        ], $models);
        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.keterangan",
            'bank.namabank as bank',
            "$this->table.tgldari",
            "$this->table.tglsampai",
            "$this->table.penerimaan_nobukti",
            'akunpusat.keterangancoa as coa',
            "$this->table.postingdari",
            "$this->table.tglkasmasuk",
            'statuscetak.memo as statuscetak',
            "$this->table.userbukacetak",
            "$this->table.tglbukacetak",
            "$this->table.jumlahcetak",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
        )->leftJoin('akunpusat', 'pengembaliankasgantungheader.coakasmasuk', 'akunpusat.coa')
            ->leftJoin('bank', 'pengembaliankasgantungheader.bank_id', 'bank.id')
            ->leftJoin('parameter as statuscetak', 'pengembaliankasgantungheader.statuscetak', 'statuscetak.id');
    }

    public function getPengembalian($id, $dari, $sampai)
    {
        $tempPribadi = $this->createTempPengembalianKasGantung($id, $dari, $sampai);
        $tempAll = $this->createTempPengembalian($id, $dari, $sampai);
        $temp = '##tempGet' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $pengembalian = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("pengembaliankasgantungheader_id,nobukti,tglbukti,keterangan,coa,sisa,bayar"));

        Schema::create($temp, function ($table) {
            $table->bigInteger('pengembaliankasgantungheader_id')->nullable();
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->string('keterangan')->nullable();
            $table->string('coa')->nullable();
            $table->bigInteger('sisa')->nullable();
            $table->bigInteger('bayar')->nullable();
        });

        DB::table($temp)->insertUsing(['pengembaliankasgantungheader_id', 'nobukti', 'tglbukti', 'keterangan', 'coa', 'sisa', 'bayar'], $pengembalian);

        $pinjaman = DB::table($tempAll)->from(DB::raw("$tempAll with (readuncommitted)"))
            ->select(DB::raw("null as pengembaliankasgantungheader_id,nobukti,tglbukti,keterangan, null as coa,sisa, 0 as bayar"))
            ->where(function ($query) use ($tempAll) {
                $query->whereRaw("$tempAll.sisa != 0")
                    ->orWhereRaw("$tempAll.sisa is null");
            });
        DB::table($temp)->insertUsing(['pengembaliankasgantungheader_id', 'nobukti', 'tglbukti', 'keterangan', 'coa', 'sisa', 'bayar'], $pinjaman);

        $data = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $temp.nobukti) as id,pengembaliankasgantungheader_id,nobukti,tglbukti,keterangan as keterangandetail,coa as coadetail,sisa,bayar as nominal"))
            ->get();

        return $data;
    }

    public function createTempPengembalianKasGantung($id, $dari, $sampai)
    {
        $temp = '##tempPengembalian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("pengembaliankasgantungdetail.pengembaliankasgantung_id,kasgantungdetail.nobukti,kasgantungheader.tglbukti,
            pengembaliankasgantungdetail.nominal as bayar,pengembaliankasgantungdetail.keterangan, pengembaliankasgantungdetail.coa,
            (SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) 
            FROM pengembaliankasgantungdetail WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa"))
            ->leftJoin(DB::raw("kasgantungheader with (readuncommitted)"), 'kasgantungheader.nobukti', 'kasgantungdetail.nobukti')
            ->leftJoin(DB::raw("pengembaliankasgantungdetail with (readuncommitted)"), 'pengembaliankasgantungdetail.kasgantung_nobukti', 'kasgantungdetail.nobukti')
            ->where("pengembaliankasgantungdetail.pengembaliankasgantung_id", $id)
            ->groupBy('pengembaliankasgantungdetail.pengembaliankasgantung_id', 'kasgantungdetail.nobukti', 'kasgantungheader.tglbukti', 'pengembaliankasgantungdetail.nominal', 'pengembaliankasgantungdetail.keterangan', 'pengembaliankasgantungdetail.coa');


        Schema::create($temp, function ($table) {
            $table->bigInteger('pengembaliankasgantungheader_id')->nullable();
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('bayar')->nullable();
            $table->string('keterangan');
            $table->string('coa');
            $table->bigInteger('sisa')->nullable();
        });
        $tes = DB::table($temp)->insertUsing(['pengembaliankasgantungheader_id', 'nobukti', 'tglbukti', 'bayar', 'keterangan', 'coa', 'sisa'], $fetch);

        return $temp;
    }

    public function createTempPengembalian($id, $dari, $sampai)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("kasgantungdetail.nobukti,kasgantungheader.tglbukti,(SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) FROM pengembaliankasgantungdetail WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa, MAX(kasgantungdetail.keterangan)"))
            ->leftJoin(DB::raw("kasgantungheader with (readuncommitted)"), 'kasgantungheader.nobukti', 'kasgantungdetail.nobukti')
            ->whereRaw("kasgantungheader.nobukti not in (select kasgantung_nobukti from pengembaliankasgantungdetail where pengembaliankasgantung_id=$id)")
            ->whereBetween('kasgantungheader.tglbukti', [$dari, $sampai])
            ->groupBy('kasgantungdetail.nobukti', 'kasgantungheader.tglbukti');
        //dd($fetch->toSQL());

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('sisa')->nullable();
            $table->longText('keterangan')->nullabble();
        });
        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'sisa', 'keterangan'], $fetch);
        return $temp;
    }

    public function getDeletePengembalian($id, $dari, $sampai)
    {
        $tempPribadi = $this->createTempPengembalianKasGantung($id, $dari, $sampai);

        $data = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
            ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,pengembaliankasgantungheader_id,nobukti,sisa,bayar as nominal,keterangan as keterangandetail,coa as coadetail, tglbukti"))
            ->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }
        if ($this->params['sortIndex'] == 'bank') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'coa') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        }
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'bank') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coa') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkasmasuk' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
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
                                    $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'bank') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'coa') {
                                    $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglkasmasuk' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglbukacetak') {
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
        if (request()->cetak && request()->periode) {
            $query->where('pengembaliankasgantungheader.statuscetak', '<>', request()->cetak)
                ->whereYear('pengembaliankasgantungheader.tglbukti', '=', request()->year)
                ->whereMonth('pengembaliankasgantungheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.keterangan",
                "$this->table.bank_id",
                'bank.namabank as bank',
                "$this->table.tgldari",
                "$this->table.tglsampai",
                "$this->table.penerimaan_nobukti",
                'akunpusat.keterangancoa as coa',
                "$this->table.postingdari",
                "$this->table.tglkasmasuk",
                "$this->table.statuscetak",
                "$this->table.userbukacetak",
                "$this->table.tglbukacetak",
                "$this->table.jumlahcetak",
                "$this->table.modifiedby",
                "$this->table.created_at",
                "$this->table.updated_at",
            )
            ->leftJoin('bank', 'pengembaliankasgantungheader.bank_id', 'bank.id')
            ->leftJoin('penerimaanheader', 'pengembaliankasgantungheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
            ->leftJoin('akunpusat', 'pengembaliankasgantungheader.coakasmasuk', 'akunpusat.coa');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getSisaEditPengembalianKasGantung($id, $nobukti)
    {
        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("kasgantungdetail.nobukti,
        (SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) 
        FROM pengembaliankasgantungdetail
        WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa"))
            ->where("kasgantungdetail.nobukti", $nobukti)
            ->groupBy('kasgantungdetail.nobukti');

        return $fetch->first();
    }

    public function getMinusSisaPengembalian($nobukti)
    {
        $query = DB::table("kasgantungdetail")->from(DB::raw("kasgantungdetail with (readuncommitted)"))
            ->select(DB::raw("SUM(nominal) as nominal"))
            ->where('nobukti', $nobukti)
            ->first($nobukti);

        return $query;
    }

    public function getExport($id)
    {
        $this->setRequestParameters();

        $getJudul = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))
            ->select('text')
            ->where('grp', 'JUDULAN LAPORAN')
            ->where('subgrp', 'JUDULAN LAPORAN')
            ->first();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "akunpusat.keterangancoa as coakasmasuk",
            "$this->table.tgldari",
            "$this->table.tglsampai",
            "$this->table.penerimaan_nobukti",
            "$this->table.postingdari",
            "$this->table.tglkasmasuk",
            "$this->table.jumlahcetak",
            "bank.namabank as bank",
            'statuscetak.memo as statuscetak',
            'statuscetak.id as  statuscetak_id',
            DB::raw("'Laporan Pengembalian Kas Gantung' as judulLaporan"),
            DB::raw("'" . $getJudul->text . "' as judul"),
            DB::raw("'Tgl Cetak:'+format(getdate(),'dd-MM-yyyy HH:mm:ss')as tglcetak"),
            DB::raw(" 'User :" . auth('api')->user()->name . "' as usercetak")
        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'pengembaliankasgantungheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), "$this->table.bank_id", "bank.id")
            ->leftJoin("akunpusat", "$this->table.coakasmasuk", "akunpusat.coa")
            ->where("$this->table.id", $id);

        $data = $query->first();
        return $data;
    }


    public function processStore(array $data): PengembalianKasGantungHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;
        $group = 'PENGEMBALIAN KAS GANTUNG BUKTI';
        $subgroup = 'PENGEMBALIAN KAS GANTUNG BUKTI';

        $format = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();
        $bankid = $data['bank_id'];
        $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select('parameter.grp', 'parameter.subgrp', 'bank.formatpenerimaan', 'bank.coa')->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')->whereRaw("bank.id = $bankid")->first();
        $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENGEMBALIAN KAS GANTUNG')->where('subgrp', 'KREDIT')->first();
        $coaKasGantung = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
        $memoKasGantung = json_decode($coaKasGantung->memo, true);
        $memo = json_decode($coaKasMasuk->memo, true);
        $statusApproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();


        $pengembalianKasGantungHeader = new PengembalianKasGantungHeader();

        $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->bank_id = $data['bank_id'];
        $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($data['tgldari'])) ?? date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai'])) ?? date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->coakasmasuk = $querysubgrppenerimaan->coa;
        $pengembalianKasGantungHeader->postingdari = $data['postingdari'] ?? "Pengembalian Kas Gantung";
        $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->statusformat = $data['statusformat'] ?? $format->id;
        $pengembalianKasGantungHeader->statuscetak = $statusCetak->id ?? 0;
        $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;
        $pengembalianKasGantungHeader->info = html_entity_decode(request()->info);
        $pengembalianKasGantungHeader->nobukti = (new RunningNumberService)->get($group, $subgroup, $pengembalianKasGantungHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));

        $pengembalianKasGantungHeader->save();

        if (!$pengembalianKasGantungHeader->save()) {
            throw new \Exception("Error storing pengembalian Kas Gantung Header");
        }


        $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengembalianKasGantungHeader->bank_id)->first();
        $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
        $group = $parameter->grp;
        $subgroup = $parameter->subgrp;
        $formatPenerimaan = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();

        for ($i = 0; $i < count($data['kasgantungdetail_id']); $i++) {
            // if ($data['datadetail'] != '') {
            //     $kasgantungnobukti = $data['datadetail'][$i]['kasgantung_nobukti'];
            //     $coakredit = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
            //     $coakreditmemo = json_decode($coakredit->memo, true);
            // }

            $pengembalianKasGantungDetail = (new PengembalianKasGantungDetail())->processStore($pengembalianKasGantungHeader, [
                "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                "nobukti" => $pengembalianKasGantungHeader->nobukti,
                "nominal" => $data['nominal'][$i],
                "coadetail" => $data['coadetail'][$i] ?? $memoKasGantung['JURNAL'],
                "keterangandetail" => $data['keterangandetail'][$i] ?? '',
                "kasgantung_nobukti" => $data['kasgantung_nobukti'][$i],
            ]);
            $pengembalianKasGantungDetails[] = $pengembalianKasGantungDetail->toArray();
            $tglJatuhTempo[] = $data['tglbukti'];
            $nominal_detail[] = $data['nominal'][$i];
            $coadebet_detail[] = $bank->coa;
            $coakredit_detail[] = $memoKasGantung['JURNAL'];
            $keterangan_detail[] = $data['keterangandetail'][$i];
        }


        $penerimaanRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' =>  $data['postingdari'] ?? "ENTRY PENGEMBALIAN KAS GANTUNG",
            'statusapproval' => $statusApproval->id,
            'pelanggan_id' => 0,
            'agen_id' => 0,
            'diterimadari' => "PENGEMBALIAN KAS GANTUNG",
            'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
            'statusformat' => $formatPenerimaan->id,
            'bank_id' => $pengembalianKasGantungHeader->bank_id,

            'nowarkat' => null,
            'tgljatuhtempo' => $tglJatuhTempo,
            'nominal_detail' => $nominal_detail,
            'coadebet' => $coadebet_detail,
            'coakredit' => $coakredit_detail,
            'keterangan_detail' => $keterangan_detail,
            'invoice_nobukti' => null,
            'bankpelanggan_id' => null,
            'pelunasanpiutang_nobukti' => null,
            'bulanbeban' => $tglJatuhTempo,
        ];
        $penerimaanHeader = (new PenerimaanHeader())->processStore($penerimaanRequest);
        $pengembalianKasGantungHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
        $pengembalianKasGantungHeader->save();

        $pengembalianKasGantungHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
            'postingdari' =>  $data['postingdari'] ?? strtoupper('ENTRY PENGEMBALIAN KAS GANTUNG'),
            'idtrans' => $pengembalianKasGantungHeader->id,
            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengembalianKasGantungHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        //store logtrail detail
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengembalianKasGantungDetail->getTable()),
            'postingdari' =>  $data['postingdari'] ?? strtoupper('ENTRY PENGEMBALIAN KAS GANTUNG'),
            'idtrans' =>  $pengembalianKasGantungHeaderLogTrail->id,
            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengembalianKasGantungDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $pengembalianKasGantungHeader;
    }

    public function processUpdate(PengembalianKasGantungHeader $pengembalianKasGantungHeader, array $data): PengembalianKasGantungHeader
    {
        $tanpaprosesnobukti = $data['tanpaprosesnobukti'] ?? 0;

        $group = 'PENGEMBALIAN KAS GANTUNG BUKTI';
        $subgroup = 'PENGEMBALIAN KAS GANTUNG BUKTI';

        $bankid = $data['bank_id'];
        $querysubgrppenerimaan = DB::table('bank')->from(DB::raw("bank with (readuncommitted)"))->select('parameter.grp', 'parameter.subgrp', 'bank.formatpenerimaan', 'bank.coa')->join(DB::raw("parameter with (readuncommitted)"), 'bank.formatpenerimaan', 'parameter.id')->whereRaw("bank.id = $bankid")->first();
        $coaKasMasuk = DB::table('parameter')->from(DB::raw("parameter with (readuncommitted)"))->select('memo')->where('grp', 'JURNAL PENGEMBALIAN KAS GANTUNG')->where('subgrp', 'KREDIT')->first();
        $coaKasGantung = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JURNAL KAS GANTUNG')->where('subgrp', 'DEBET')->first();
        $memoKasGantung = json_decode($coaKasGantung->memo, true);
        $memo = json_decode($coaKasMasuk->memo, true);
        $statusApproval = DB::table('parameter')->where('grp', 'STATUS APPROVAL')->where('text', 'NON APPROVAL')->first();
        $statusCetak = Parameter::where('grp', 'STATUSCETAK')->where('text', 'BELUM CETAK')->first();

        $getTgl = DB::table("parameter")->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'EDIT TANGGAL BUKTI')->where('subgrp', 'PENGEMBALIAN KAS GANTUNG')->first();
        if (trim($getTgl->text) == 'YA') {
            $querycek = DB::table('penerimaanheader')->from(
                DB::raw("penerimaanheader a with (readuncommitted)")
            )
                ->select(
                    'a.nobukti'
                )
                ->where('a.id', $pengembalianKasGantungHeader->id)
                ->whereRAw("format(a.tglbukti,'MM-yyyy')='" . date('m-Y', strtotime($data['tglbukti'])) . "'")
                ->first();


            if (isset($querycek)) {
                $nobukti = $querycek->nobukti;
            } else {
                $nobukti = (new RunningNumberService)->get($group, $subgroup, $pengembalianKasGantungHeader->getTable(), date('Y-m-d', strtotime($data['tglbukti'])));
            }
            $pengembalianKasGantungHeader->nobukti = $nobukti;
            $pengembalianKasGantungHeader->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        }
        // $pengembalianKasGantungHeader->bank_id = $data['bank_id'];
        $pengembalianKasGantungHeader->tgldari = date('Y-m-d', strtotime($data['tgldari'])) ?? date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->tglsampai = date('Y-m-d', strtotime($data['tglsampai'])) ?? date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->coakasmasuk = $querysubgrppenerimaan->coa;
        $pengembalianKasGantungHeader->postingdari = $data['postingdari'] ?? "Pengembalian Kas Gantung";
        // $pengembalianKasGantungHeader->tglkasmasuk = date('Y-m-d', strtotime($data['tglbukti']));
        $pengembalianKasGantungHeader->modifiedby = auth('api')->user()->name;
        $pengembalianKasGantungHeader->info = html_entity_decode(request()->info);

        $pengembalianKasGantungHeader->save();

        if (!$pengembalianKasGantungHeader->save()) {
            throw new \Exception("Error update pengembalian Kas Gantung Header");
        }


        $bank = Bank::select('coa', 'formatpenerimaan', 'tipe')->where('id', $pengembalianKasGantungHeader->bank_id)->first();
        $parameter = Parameter::where('id', $bank->formatpenerimaan)->first();
        $group = $parameter->grp;
        $subgroup = $parameter->subgrp;
        $formatPenerimaan = DB::table('parameter')->where('grp', $group)->where('subgrp', $subgroup)->first();


        PengembalianKasGantungDetail::where('pengembaliankasgantung_id', $pengembalianKasGantungHeader->id)->lockForUpdate()->delete();
        for ($i = 0; $i < count($data['kasgantungdetail_id']); $i++) {

            $pengembalianKasGantungDetail = (new PengembalianKasGantungDetail())->processStore($pengembalianKasGantungHeader, [
                "pengembaliankasgantung_id" => $pengembalianKasGantungHeader->id,
                "nobukti" => $pengembalianKasGantungHeader->nobukti,
                "nominal" => $data['nominal'][$i],
                "coadetail" => $data['coadetail'][$i] ?? $memoKasGantung['JURNAL'],
                "keterangandetail" => $data['keterangandetail'][$i],
                "kasgantung_nobukti" => $data['kasgantung_nobukti'][$i],
            ]);
            $pengembalianKasGantungDetails[] = $pengembalianKasGantungDetail->toArray();
            $tglJatuhTempo[] = $data['tglbukti'];
            $nominal_detail[] = $data['nominal'][$i];
            $coadebet_detail[] = $bank->coa;
            $coakredit_detail[] = $memoKasGantung['JURNAL'];
            $keterangan_detail[] = $data['keterangandetail'][$i];
        }


        $penerimaanRequest = [
            'tglbukti' => date('Y-m-d', strtotime($data['tglbukti'])),
            'postingdari' =>  $data['postingdari'] ?? "ENTRY PENGEMBALIAN KAS GANTUNG",
            'statusapproval' => $statusApproval->id,
            'pelanggan_id' => 0,
            'agen_id' => 0,
            'diterimadari' => "PENGEMBALIAN KAS GANTUNG",
            'tgllunas' => date('Y-m-d', strtotime($data['tglbukti'])),
            'statusformat' => $formatPenerimaan->id,
            'bank_id' => $pengembalianKasGantungHeader->bank_id,

            'nowarkat' => null,
            'tgljatuhtempo' => $tglJatuhTempo,
            'nominal_detail' => $nominal_detail,
            'coadebet' => $coadebet_detail,
            'coakredit' => $coakredit_detail,
            'keterangan_detail' => $keterangan_detail,
            'invoice_nobukti' => null,
            'bankpelanggan_id' => null,
            'pelunasanpiutang_nobukti' => null,
            'bulanbeban' => $tglJatuhTempo,
        ];
        $penerimaan = PenerimaanHeader::where('nobukti', $pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->first();
        $penerimaanHeader = (new PenerimaanHeader())->processUpdate($penerimaan, $penerimaanRequest);

        $pengembalianKasGantungHeader->penerimaan_nobukti = $penerimaanHeader->nobukti;
        $pengembalianKasGantungHeader->save();
        $pengembalianKasGantungHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengembalianKasGantungHeader->getTable()),
            'postingdari' =>  $data['postingdari'] ?? strtoupper('ENTRY PENGEMBALIAN KAS GANTUNG'),
            'idtrans' => $pengembalianKasGantungHeader->id,
            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengembalianKasGantungHeader->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);

        //store logtrail detail
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($pengembalianKasGantungDetail->getTable()),
            'postingdari' =>  $data['postingdari'] ?? strtoupper('ENTRY PENGEMBALIAN KAS GANTUNG'),
            'idtrans' =>  $pengembalianKasGantungHeaderLogTrail->id,
            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
            'aksi' => 'ENTRY',
            'datajson' => $pengembalianKasGantungDetails,
            'modifiedby' => auth('api')->user()->user,
        ]);


        return $pengembalianKasGantungHeader;
    }

    public function processDestroy($id, $postingdari = null): PengembalianKasGantungHeader
    {
        $pengembalianKasGantungHeader = PengembalianKasGantungHeader::findOrFail($id);
        $dataHeader =  $pengembalianKasGantungHeader->toArray();

        $pengembalianKasGantungDetail = PengembalianKasGantungDetail::where('pengembaliankasgantung_id', '=', $pengembalianKasGantungHeader->id)->get();
        $dataDetail = $pengembalianKasGantungDetail->toArray();

        $penerimaan = PenerimaanHeader::where('nobukti', $pengembalianKasGantungHeader->penerimaan_nobukti)->lockForUpdate()->first();
        $penerimaanHeader = (new PenerimaanHeader())->processDestroy($penerimaan->id, $postingdari ?? strtoupper('PENGEMBALIAN KAS GANTUNG'));

        $pengembalianKasGantungHeader = $pengembalianKasGantungHeader->lockAndDestroy($id);

        $pengembalianKasGantungHeaderLogTrail = (new LogTrail())->processStore([
            'namatabel' => $pengembalianKasGantungHeader->getTable(),
            'postingdari' => $postingdari ?? strtoupper('DELETE PENGEMBALIAN KAS GANTUNG'),
            'idtrans' => $pengembalianKasGantungHeader->id,
            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataHeader,
            'modifiedby' => auth('api')->user()->name
        ]);

        (new LogTrail())->processStore([
            'namatabel' => "penerimaanstokdetail",
            'postingdari' => $postingdari ?? strtoupper('DELETE PENGEMBALIAN KAS GANTUNG'),
            'idtrans' => $pengembalianKasGantungHeaderLogTrail['id'],
            'nobuktitrans' => $pengembalianKasGantungHeader->nobukti,
            'aksi' => 'DELETE',
            'datajson' => $dataDetail,
            'modifiedby' => auth('api')->user()->name
        ]);

        return $pengembalianKasGantungHeader;
    }
}
