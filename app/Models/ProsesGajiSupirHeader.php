<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProsesGajiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'prosesgajisupirheader';
    protected $tableTotal = '';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

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
                'bank'
            );

        $data = $query->first();

        return $data;
    }

    public function get()
    {
        $this->setRequestParameters();

        $this->tableTotal = $this->createTempTotal();
        $query = DB::table($this->table)->from(DB::raw("prosesgajisupirheader with (readuncommitted)"))

            ->select(

                'prosesgajisupirheader.id',
                'prosesgajisupirheader.nobukti',
                'prosesgajisupirheader.tglbukti',
                'prosesgajisupirheader.tgldari',
                'prosesgajisupirheader.tglsampai',
                'prosesgajisupirheader.periode',
                'prosesgajisupirheader.userapproval',
                'statusapproval.memo as statusapproval',
                'statuscetak.memo as statuscetak',
                'prosesgajisupirheader.userbukacetak',
                'prosesgajisupirheader.jumlahcetak',
                'prosesgajisupirheader.pengeluaran_nobukti',
                'prosesgajisupirheader.modifiedby',
                'prosesgajisupirheader.created_at',
                'prosesgajisupirheader.updated_at',
                DB::raw("(case when (year(prosesgajisupirheader.tglapproval) <= 2000) then null else prosesgajisupirheader.tglapproval end ) as tglapproval"),
                DB::raw("(case when (year(prosesgajisupirheader.tglbukacetak) <= 2000) then null else prosesgajisupirheader.tglbukacetak end ) as tglbukacetak"),
                $this->tableTotal . '.total',
                $this->tableTotal . '.totalposting',
                $this->tableTotal . '.uangjalan',
                $this->tableTotal . '.bbm',
                $this->tableTotal . '.uangmakanharian',
                $this->tableTotal . '.potonganpinjaman',
                $this->tableTotal . '.potonganpinjamansemua',
                $this->tableTotal . '.deposito'
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'prosesgajisupirheader.statusapproval', 'statusapproval.id')
            ->leftJoin($this->tableTotal, $this->tableTotal . '.nobukti', 'prosesgajisupirheader.nobukti');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();

        return $data;
    }

    public function createTempTotal()
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                DB::raw("distinct(prosesgajisupirheader.nobukti),
                (SELECT SUM(isnull(gajisupirheader.total, 0)+isnull(gajisupirheader.uangmakanharian, 0))
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS total,
            (SELECT SUM(gajisupirheader.total)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS totalposting,
                (SELECT SUM(gajisupirheader.uangjalan)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangjalan,
                (SELECT SUM(gajisupirheader.bbm)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS bbm,  
                (SELECT SUM(gajisupirheader.uangmakanharian)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangmakanharian,
                (SELECT SUM(gajisupirheader.potonganpinjaman)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS potonganpinjaman,  
                (SELECT SUM(gajisupirheader.potonganpinjamansemua)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS potonganpinjamansemua,  
                 
                (SELECT SUM(gajisupirheader.deposito)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS deposito
            ")
            )
            ->join(DB::raw("prosesgajisupirdetail with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->join(DB::raw("prosesgajisupirheader with (readuncommitted)"), 'prosesgajisupirheader.id', 'prosesgajisupirdetail.prosesgajisupir_id')
            ->whereRaw("gajisupirheader.nobukti in(select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirdetail.prosesgajisupir_id = prosesgajisupirheader.id)");


        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('total')->nullable();
            $table->bigInteger('totalposting')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'total', 'totalposting', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito'], $fetch);

        return $temp;
    }


    public function getEdit($gajiId, $aksi)
    {
        $this->setRequestParameters();
        $tempRIC = $this->createTempRIC($gajiId, null, null, $aksi);
        $query = DB::table($tempRIC)
            ->select(
                $tempRIC . '.idric',
                $tempRIC . '.nobuktiric',
                $tempRIC . '.tglbuktiric',
                $tempRIC . '.supir_id',
                $tempRIC . '.supir',
                $tempRIC . '.tgldariric',
                $tempRIC . '.tglsampairic',
                $tempRIC . '.borongan',
                $tempRIC . '.uangjalan',
                $tempRIC . '.bbm',
                $tempRIC . '.uangmakanharian',
                $tempRIC . '.potonganpinjaman',
                $tempRIC . '.potonganpinjamansemua',
                $tempRIC . '.deposito',
                $tempRIC . '.komisisupir',
                $tempRIC . '.tolsupir'
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        if ($this->params['sortIndex'] == 'id') {
            $query->orderBy($tempRIC . '.nobuktiric', $this->params['sortOrder']);
        } else {
            $query->orderBy($tempRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
        $this->filterTrip($query, $tempRIC);
        $this->paginate($query);
        $data = $query->get();

        $this->totalBorongan = $query->sum('borongan');
        $this->totalUangJalan = $query->sum('uangjalan');
        $this->totalUangBBM = $query->sum('bbm');
        $this->totalUangMakan = $query->sum('uangmakanharian');
        $this->totalPotPinjaman = $query->sum('potonganpinjaman');
        $this->totalPotPinjSemua = $query->sum('potonganpinjamansemua');
        $this->totalDeposito = $query->sum('deposito');
        $this->totalKomisi = $query->sum('komisisupir');
        $this->totalTol = $query->sum('tolsupir');
        return $data;
    }

    public function getAllEdit($gajiId, $dari, $sampai, $aksi)
    {
        $this->setRequestParameters();
        $tempRIC = $this->createTempRIC($gajiId, $dari, $sampai, $aksi);
        $query = DB::table($tempRIC)
            ->select(
                $tempRIC . '.idric',
                $tempRIC . '.nobuktiric',
                $tempRIC . '.tglbuktiric',
                $tempRIC . '.supir_id',
                $tempRIC . '.supir',
                $tempRIC . '.tgldariric',
                $tempRIC . '.tglsampairic',
                $tempRIC . '.borongan',
                $tempRIC . '.uangjalan',
                $tempRIC . '.bbm',
                $tempRIC . '.uangmakanharian',
                $tempRIC . '.potonganpinjaman',
                $tempRIC . '.potonganpinjamansemua',
                $tempRIC . '.deposito',
                $tempRIC . '.komisisupir',
                $tempRIC . '.tolsupir'
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($tempRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $tempRIC);
        $this->paginate($query);
        $data = $query->get();

        $this->totalBorongan = $query->sum('borongan');
        $this->totalUangJalan = $query->sum('uangjalan');
        $this->totalUangBBM = $query->sum('bbm');
        $this->totalUangMakan = $query->sum('uangmakanharian');
        $this->totalPotPinjaman = $query->sum('potonganpinjaman');
        $this->totalPotPinjSemua = $query->sum('potonganpinjamansemua');
        $this->totalDeposito = $query->sum('deposito');
        $this->totalKomisi = $query->sum('komisisupir');
        $this->totalTol = $query->sum('tolsupir');
        return $data;
    }

    public function createTempRIC($gajiId, $dari = null, $sampai = null, $aksi)
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))
            ->select(
                'gajisupirheader.id as idric',
                'prosesgajisupirdetail.gajisupir_nobukti as nobuktiric',
                'gajisupirheader.tglbukti as tglbuktiric',
                'gajisupirheader.supir_id',
                'supir.namasupir as supir',
                'gajisupirheader.tgldari as tgldariric',
                'gajisupirheader.tglsampai as tglsampairic',
                'gajisupirheader.total as borongan',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.deposito',
                'gajisupirheader.komisisupir',
                'gajisupirheader.tolsupir'
            )
            ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('prosesgajisupirdetail.prosesgajisupir_id', $gajiId);

        Schema::create($temp, function ($table) {
            $table->bigInteger('idric');
            $table->string('nobuktiric');
            $table->date('tglbuktiric')->nullable();
            $table->bigInteger('supir_id');
            $table->string('supir');
            $table->date('tgldariric')->nullable();
            $table->date('tglsampairic')->nullable();
            $table->bigInteger('borongan')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'supir', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir'], $fetch);

        if ($aksi != '') {

            $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select(
                    'gajisupirheader.id as idric',
                    'gajisupirheader.nobukti as nobuktiric',
                    'gajisupirheader.tglbukti as tglbuktiric',
                    'gajisupirheader.supir_id',
                    'supir.namasupir as supir',
                    'gajisupirheader.tgldari as tgldariric',
                    'gajisupirheader.tglsampai as tglsampairic',
                    'gajisupirheader.total as borongan',
                    'gajisupirheader.uangjalan',
                    'gajisupirheader.bbm',
                    'gajisupirheader.uangmakanharian',
                    'gajisupirheader.potonganpinjaman',
                    'gajisupirheader.potonganpinjamansemua',
                    'gajisupirheader.deposito',
                    'gajisupirheader.komisisupir',
                    'gajisupirheader.tolsupir'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
                ->where('gajisupirheader.tglbukti', '>=', $dari)
                ->where('gajisupirheader.tglbukti', '<=', $sampai)
                ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)");

            $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'supir', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir'], $fetch);
        }

        return $temp;
    }
    public function selectColumns($query)
    {
        return $query->select(
            DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.tgldari,
            $this->table.tglsampai,
            'statusapproval.text as statusapproval',
            $this->table.userapproval,
            $this->table.tglapproval,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.periode,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at
            ")
        )
            ->leftJoin('parameter as statuscetak', 'prosesgajisupirheader.statuscetak', 'statuscetak.id')
            ->leftJoin('parameter as statusapproval', 'prosesgajisupirheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->date('periode')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'tgldari', 'tglsampai', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'periode', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }


    public function getRic($dari, $sampai)
    {
        $this->setRequestParameters();
        $getRIC = $this->createTempGetRIC($dari, $sampai);

        $query = DB::table($getRIC)
            ->select(
                $getRIC . '.idric',
                $getRIC . '.nobuktiric',
                $getRIC . '.tglbuktiric',
                $getRIC . '.supir_id',
                $getRIC . '.supir',
                $getRIC . '.tgldariric',
                $getRIC . '.tglsampairic',
                $getRIC . '.borongan',
                $getRIC . '.uangjalan',
                $getRIC . '.bbm',
                $getRIC . '.uangmakanharian',
                $getRIC . '.potonganpinjaman',
                $getRIC . '.potonganpinjamansemua',
                $getRIC . '.deposito',
                $getRIC . '.komisisupir',
                $getRIC . '.tolsupir'
            );
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $query->orderBy($getRIC . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        $this->filterTrip($query, $getRIC);
        $this->paginate($query);
        $data = $query->get();

        $this->totalBorongan = $query->sum('borongan');
        $this->totalUangJalan = $query->sum('uangjalan');
        $this->totalUangBBM = $query->sum('bbm');
        $this->totalUangMakan = $query->sum('uangmakanharian');
        $this->totalPotPinjaman = $query->sum('potonganpinjaman');
        $this->totalPotPinjSemua = $query->sum('potonganpinjamansemua');
        $this->totalDeposito = $query->sum('deposito');
        $this->totalKomisi = $query->sum('komisisupir');
        $this->totalTol = $query->sum('tolsupir');
        return $data;
    }

    public function createTempGetRIC($dari, $sampai)
    {
        $temp = '##tempRIC' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(
                'gajisupirheader.id as idric',
                'gajisupirheader.nobukti as nobuktiric',
                'gajisupirheader.tglbukti as tglbuktiric',
                'gajisupirheader.supir_id',
                'supir.namasupir as supir',
                'gajisupirheader.tgldari as tgldariric',
                'gajisupirheader.tglsampai as tglsampairic',
                'gajisupirheader.total as borongan',
                'gajisupirheader.uangjalan',
                'gajisupirheader.bbm',
                'gajisupirheader.uangmakanharian',
                'gajisupirheader.potonganpinjaman',
                'gajisupirheader.potonganpinjamansemua',
                'gajisupirheader.deposito',
                'gajisupirheader.komisisupir',
                'gajisupirheader.tolsupir'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('gajisupirheader.tglbukti', '>=', $dari)
            ->where('gajisupirheader.tglbukti', '<=', $sampai)
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('idric');
            $table->string('nobuktiric');
            $table->date('tglbuktiric')->nullable();
            $table->bigInteger('supir_id');
            $table->string('supir');
            $table->date('tgldariric')->nullable();
            $table->date('tglsampairic')->nullable();
            $table->bigInteger('borongan')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'supir', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir'], $fetch);

        return $temp;
    }

    public function filterTrip($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            // $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            // $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function getPotSemua($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();

        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->select('nominal')
                ->where('gajisupir_id', $ricId)
                ->where('supir_id', 0)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }
        }
        return $total;
    }

    public function getPotPribadi($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->select('nominal')
                ->where('gajisupir_id', $ricId)
                ->where('supir_id', '!=', 0)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }
        }
        return $total;
    }

    public function getDeposito($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function getBBM($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function getPinjaman($dari, $sampai)
    {
        $gajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))->whereRaw("tglbukti >= '$dari'")->whereRaw("tglbukti <= '$sampai'")
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)")->get();
        $total = 0;
        foreach ($gajiSupir as $key => $value) {
            $ricId = $value->id;
            $potongan = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
                ->where('gajisupir_id', $ricId)
                ->first();

            if ($potongan != null) {
                $total = $total + $potongan->nominal;
            }
        }
        return $total;
    }

    public function findAll($id)
    {
        $parameter = Parameter::from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'JUDULAN LAPORAN')->where('subgrp', 'JUDULAN LAPORAN')->first();
        $query = ProsesGajiSupirHeader::from(DB::raw("prosesgajisupirheader with (readuncommitted)"))
            ->select(
                'prosesgajisupirheader.id',
                'prosesgajisupirheader.nobukti',
                'prosesgajisupirheader.tglbukti',
                'prosesgajisupirheader.periode',
                'prosesgajisupirheader.keterangan',
                'prosesgajisupirheader.tgldari',
                'prosesgajisupirheader.tglsampai',
                'prosesgajisupirheader.statuscetak',
                'prosesgajisupirheader.bank_id as bank_idPR',
                'prosesgajisupirheader.pengeluaran_nobukti as nobuktiPR',
                'bank.namabank as bankPR',
                DB::raw("'$parameter->text' as judul"),
                DB::raw("'Laporan Proses Gaji Supir' as judulLaporan"),
            )->leftJoin(DB::raw("bank with (readuncommitted)"), 'prosesgajisupirheader.bank_id', 'bank.id')
            ->where('prosesgajisupirheader.id', $id)
            ->first();

        return $query;
    }

    public function showPotSemua($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->where('supir_id', '0')
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchPS = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '0')->first();
            if (isset($fetchPS)) {
                $tes = $fetchPS->penerimaantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPS' => $penerimaan->bank_id,
                'bankPS' => $penerimaan->bank,
                'nobuktiPS' => $penerimaan->penerimaan_nobukti,
                'nomPS' => $total
            ];
            return $data;
        }
    }
    public function showPotPribadi($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->where('supir_id', '!=', '0')
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchPP = GajiSupirPelunasanPinjaman::from(DB::raw("gajisupirpelunasanpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->where('supir_id', '!=', '0')->first();
            if (isset($fetchPP)) {
                $tes = $fetchPP->penerimaantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPP' => $penerimaan->bank_id,
                'bankPP' => $penerimaan->bank,
                'nobuktiPP' => $penerimaan->penerimaan_nobukti,
                'nomPP' => $total
            ];
            return $data;
        }
    }
    public function showDeposito($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchDeposito = GajiSupirDeposito::from(DB::raw("gajisupirdeposito with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchDeposito)) {
                $tes = $fetchDeposito->penerimaantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idDeposito' => $penerimaan->bank_id,
                'bankDeposito' => $penerimaan->bank,
                'nobuktiDeposito' => $penerimaan->penerimaan_nobukti,
                'nomDeposito' => $total
            ];
            return $data;
        }
    }
    public function showBBM($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchBBM = GajiSupirBBM::from(DB::raw("gajisupirbbm with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchBBM)) {
                $tes = $fetchBBM->penerimaantrucking_nobukti;
            }
        }

        // dd($tes)
        if ($tes != '') {

            $penerimaan = PenerimaanTruckingHeader::from(DB::raw("penerimaantruckingheader with (readuncommitted)"))
                ->select('penerimaantruckingheader.bank_id', 'bank.namabank as bank', 'penerimaantruckingheader.penerimaan_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'penerimaantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idBBM' => $penerimaan->bank_id,
                'bankBBM' => $penerimaan->bank,
                'nobuktiBBM' => $penerimaan->penerimaan_nobukti,
                'nomBBM' => $total
            ];
            return $data;
        }
    }
    public function showPinjaman($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        foreach ($gajidetail as $key => $value) {
            $potongan = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))
                ->where('gajisupir_nobukti', $value->gajisupir_nobukti)
                ->get();

            $nominal = $potongan->sum('nominal');
            if ($nominal != 0) {
                $total = $total + $nominal;
            }

            $fetchBBM = GajiSupirPinjaman::from(DB::raw("gajisupirpinjaman with (readuncommitted)"))->where('gajisupir_nobukti', $value->gajisupir_nobukti)->first();
            if (isset($fetchBBM)) {
                $tes = $fetchBBM->pengeluarantrucking_nobukti;
            }
        }


        if ($tes != '') {

            $pengeluaran = PengeluaranTruckingHeader::from(DB::raw("pengeluarantruckingheader with (readuncommitted)"))
                ->select('pengeluarantruckingheader.bank_id', 'bank.namabank as bank', 'pengeluarantruckingheader.pengeluaran_nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'pengeluarantruckingheader.bank_id', 'bank.id')->where('nobukti', $tes)->first();
            $data = [
                'bank_idPinjaman' => $pengeluaran->bank_id,
                'bankPinjaman' => $pengeluaran->bank,
                'nobuktiPinjaman' => $pengeluaran->pengeluaran_nobukti,
                'nomPinjaman' => $total
            ];
            return $data;
        }
    }

    public function showUangjalan($id)
    {
        $gajidetail = ProsesGajiSupirDetail::from(DB::raw("prosesgajisupirdetail with (readuncommitted)"))->where('prosesgajisupir_id', $id)->get();
        $total = 0;
        $tes = '';
        $allSP = "";
        foreach ($gajidetail as $key => $value) {
            if ($key == 0) {
                $allSP = $allSP . "'$value->gajisupir_nobukti'";
            } else {
                $allSP = $allSP . ',' . "'$value->gajisupir_nobukti'";
            }
        }
        $getUangjalan = GajisUpirUangJalan::from(DB::raw("gajisupiruangjalan with (readuncommitted)"))
            ->select('absensisupirheader.kasgantung_nobukti')
            ->join(DB::raw("absensisupirheader with (readuncommitted)"), 'gajisupiruangjalan.absensisupir_nobukti', 'absensisupirheader.nobukti')
            ->whereRaw("gajisupir_nobukti in ($allSP)")->get();


        $allSP = "";
        foreach ($getUangjalan as $key => $value) {
            if ($key == 0) {
                $allSP = $allSP . "'$value->kasgantung_nobukti'";
            } else {
                $allSP = $allSP . ',' . "'$value->kasgantung_nobukti'";
            }
        }
        if ($allSP != '') {
            $getKasgantung = PengembalianKasGantungHeader::from(DB::raw("pengembaliankasgantungheader with (readuncommitted)"))
                ->select("pengembaliankasgantungheader.penerimaan_nobukti", 'pengembaliankasgantungheader.bank_id', 'bank.namabank')
                ->join(DB::raw("pengembaliankasgantungdetail with (readuncommitted)"), 'pengembaliankasgantungheader.nobukti', 'pengembaliankasgantungdetail.nobukti')
                ->join(DB::raw("bank with (readuncommitted)"), 'pengembaliankasgantungheader.bank_id', 'bank.id')
                ->whereRaw("pengembaliankasgantungdetail.kasgantung_nobukti in ($allSP)")
                ->first();

            $data = [
                'bank_idUangjalan' => $getKasgantung->bank_id,
                'bankUangjalan' => $getKasgantung->namabank,
                'nobuktiUangjalan' => $getKasgantung->penerimaan_nobukti
            ];
            return $data;
        }
    }

    public function getDataJurnal($nobukti)
    {

        $tempGaji = '##Tempgaji' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempGaji, function ($table) {
            $table->string('nobukti');
        });
        foreach ($nobukti as $value) {

            $fetchGajiSupir = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select('nobukti')
                ->where('nobukti', $value);

            DB::table($tempGaji)->insertUsing(['nobukti'], $fetchGajiSupir);
        }
        $tempRincian = '##Temprincian' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetchTempRincian = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.tglbukti, sum(isnull(B.gajisupir,0)+isnull(B.gajiritasi,0)) as gajisupir")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("suratpengantar as C with (readuncommitted)"), 'B.suratpengantar_nobukti', 'C.nobukti')
            ->groupBy('C.tglbukti');

        Schema::create($tempRincian, function ($table) {

            $table->date('tglbukti');
            $table->bigInteger('gajisupir');
        });

        DB::table($tempRincian)->insertUsing(['tglbukti', 'gajisupir'], $fetchTempRincian);

        $fetchTempRincian2 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.tglbukti, sum(isnull(B.gajisupir,0)+isnull(B.gajiritasi,0)) as gajisupir")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("ritasi as C with (readuncommitted)"), 'B.ritasi_nobukti', 'C.nobukti')
            ->whereRaw("isnull(B.suratpengantar_nobukti,'-')='-'")
            ->groupBy('C.tglbukti');


        DB::table($tempRincian)->insertUsing(['tglbukti', 'gajisupir'], $fetchTempRincian2);

        $tempRincianJurnal = '##Temprincianjurnal' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        $fetchTempRincianJurnal = DB::table($tempRincian)->from(DB::raw("$tempRincian with (readuncommitted)"))
            ->select(DB::raw("tglbukti, sum(gajisupir) as nominal, '' as keterangan"))
            ->groupBy('tglbukti');
        Schema::create($tempRincianJurnal, function ($table) {

            $table->date('tglbukti');
            $table->bigInteger('nominal');
            $table->string('keterangan');
        });

        DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal);

        $fetchTempRincianJurnal2 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("C.tglbukti, sum(isnull(B.komisisupir, 0)) as nominal, 'Komisi Supir' as keterangan")
            )
            ->join(DB::raw("gajisupirdetail as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->join(DB::raw("suratpengantar as C with (readuncommitted)"), 'B.suratpengantar_nobukti', 'C.nobukti')
            ->whereRaw("isnull(B.komisisupir ,0)<>0")
            ->groupBy('C.tglbukti');

        DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal2);

        $tgl = DB::table($tempRincianJurnal)->select(DB::raw("min(tglbukti) as tglbukti"))->first();

        $fetchTempRincianJurnal3 = DB::table($tempGaji)->from(DB::raw("$tempGaji as A with (readuncommitted)"))
            ->select(
                DB::raw("'$tgl->tglbukti', sum(isnull(B.uangmakanharian, 0)) as nominal, '' as keterangan")
            )
            ->join(DB::raw("gajisupirheader as B with (readuncommitted)"), 'A.nobukti', 'B.nobukti')
            ->whereRaw("isnull(B.uangmakanharian ,0)<>0");

        DB::table($tempRincianJurnal)->insertUsing(['tglbukti', 'nominal', 'keterangan'], $fetchTempRincianJurnal3);

        $data = DB::table($tempRincianJurnal)->orderBy('tglbukti')->orderBy('keterangan')->get();
        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'total' || $this->params['sortIndex'] == 'uangjalan' || $this->params['sortIndex'] == 'bbm' || $this->params['sortIndex'] == 'potonganpinjaman' || $this->params['sortIndex'] == 'potonganpinjamansemua' || $this->params['sortIndex'] == 'uangmakanharian' || $this->params['sortIndex'] == 'deposito') {

            return $query->orderBy($this->tableTotal . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'total') {
                            $query = $query->whereRaw("format($this->tableTotal.total, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'totalposting') {
                            $query = $query->whereRaw("format($this->tableTotal.totalposting, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'uangjalan') {
                            $query = $query->whereRaw("format($this->tableTotal.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'bbm') {
                            $query = $query->whereRaw("format($this->tableTotal.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'potonganpinjaman') {
                            $query = $query->whereRaw("format($this->tableTotal.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'potonganpinjamansemua') {
                            $query = $query->whereRaw("format($this->tableTotal.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'uangmakanharian') {
                            $query = $query->whereRaw("format($this->tableTotal.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'deposito') {
                            $query = $query->whereRaw("format($this->tableTotal.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'total') {
                                $query = $query->orWhereRaw("format($this->tableTotal.total, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'totalposting') {
                                $query = $query->orWhereRaw("format($this->tableTotal.totalposting, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhereRaw("format($this->tableTotal.uangjalan, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->orWhereRaw("format($this->tableTotal.bbm, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->orWhereRaw("format($this->tableTotal.potonganpinjaman, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->orWhereRaw("format($this->tableTotal.potonganpinjamansemua, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhereRaw("format($this->tableTotal.uangmakanharian, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->orWhereRaw("format($this->tableTotal.deposito, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tgldari' || $filters['field'] == 'tglsampai' || $filters['field'] == 'tglapproval' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
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

    public function getSumBoronganForValidation($nobukti)
    {
        $bukti = "";
        foreach ($nobukti as $key => $value) {
            if ($key == 0) {
                $bukti = $bukti . "'$value'";
            } else {
                $bukti = $bukti . ',' . "'$value'";
            }
        }
        $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
            ->select(DB::raw("(SUM(uangmakanharian) + SUM(total)) as borongan, SUM(potonganpinjaman) as pinjamanpribadi, SUM(potonganpinjamansemua) as pinjamansemua, SUM(deposito) as deposito, SUM(bbm) as bbm, SUM(uangjalan) as uangjalan"))
        ->whereRaw("gajisupirheader.nobukti in($bukti)");
        return $fetch->first();
    }
}
