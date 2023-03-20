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
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->string('bank', 255)->default('');
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
                'prosesgajisupirheader.modifiedby',
                'prosesgajisupirheader.created_at',
                'prosesgajisupirheader.updated_at',
                DB::raw("(case when (year(prosesgajisupirheader.tglapproval) <= 2000) then null else prosesgajisupirheader.tglapproval end ) as tglapproval"),
                DB::raw("(case when (year(prosesgajisupirheader.tglbukacetak) <= 2000) then null else prosesgajisupirheader.tglbukacetak end ) as tglbukacetak"),
                $this->tableTotal . '.total',
                $this->tableTotal . '.uangjalan',
                $this->tableTotal . '.bbm',
                $this->tableTotal . '.uangmakanharian',
                $this->tableTotal . '.potonganpinjaman',
                $this->tableTotal . '.potonganpinjamansemua',
                $this->tableTotal . '.deposito'
            )
            ->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
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
            (SELECT SUM(gajisupirheader.total)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS total,
                (SELECT SUM(gajisupirheader.uangjalan)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangjalan,
                (SELECT SUM(gajisupirheader.bbm)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS bbm,  
                (SELECT SUM(gajisupirheader.potonganpinjaman)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS potonganpinjaman,  
                (SELECT SUM(gajisupirheader.potonganpinjamansemua)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS potonganpinjamansemua,  
                (SELECT SUM(gajisupirheader.uangmakanharian)
                FROM gajisupirheader 
                WHERE gajisupirheader.nobukti in (select gajisupir_nobukti from prosesgajisupirdetail where prosesgajisupirheader.id = prosesgajisupirdetail.prosesgajisupir_id)) AS uangmakanharian,
                 
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
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'total', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito'], $fetch);

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
                $tempRIC . '.tolsupir',
                $tempRIC . '.pinjamanpribadi'
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
        $this->totalPinjaman = $query->sum('pinjamanpribadi');
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
                $tempRIC . '.tolsupir',
                $tempRIC . '.pinjamanpribadi'
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
        $this->totalPinjaman = $query->sum('pinjamanpribadi');
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
                'supir.namasupir as supir_id',
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
                'gajisupirheader.tolsupir',
                'gajisupirheader.pinjamanpribadi'
            )
            ->leftJoin(DB::raw("gajisupirheader with (readuncommitted)"), 'prosesgajisupirdetail.gajisupir_nobukti', 'gajisupirheader.nobukti')
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('prosesgajisupirdetail.prosesgajisupir_id', $gajiId);

        Schema::create($temp, function ($table) {
            $table->bigInteger('idric');
            $table->string('nobuktiric');
            $table->date('tglbuktiric')->default('');
            $table->string('supir_id');
            $table->date('tgldariric')->default('');
            $table->date('tglsampairic')->default('');
            $table->bigInteger('borongan')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('pinjamanpribadi')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir', 'pinjamanpribadi'], $fetch);

        if ($aksi != '') {

            $fetch = GajiSupirHeader::from(DB::raw("gajisupirheader with (readuncommitted)"))
                ->select(
                    'gajisupirheader.id as idric',
                    'gajisupirheader.nobukti as nobuktiric',
                    'gajisupirheader.tglbukti as tglbuktiric',
                    'supir.namasupir as supir_id',
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
                    'gajisupirheader.tolsupir',
                    'gajisupirheader.pinjamanpribadi'
                )
                ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
                ->where('gajisupirheader.tglbukti', '>=', $dari)
                ->where('gajisupirheader.tglbukti', '<=', $sampai)
                ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)");

            $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir', 'pinjamanpribadi'], $fetch);
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
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->date('tgldari')->default('');
            $table->date('tglsampai')->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->date('tglapproval')->default('');
            $table->string('statuscetak', 1000)->default('');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->date('periode')->default('');
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
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
                $getRIC . '.tolsupir',
                $getRIC . '.pinjamanpribadi'
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
        $this->totalPinjaman = $query->sum('pinjamanpribadi');
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
                'supir.namasupir as supir_id',
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
                'gajisupirheader.tolsupir',
                'gajisupirheader.pinjamanpribadi'
            )
            ->leftJoin(DB::raw("supir with (readuncommitted)"), 'gajisupirheader.supir_id', 'supir.id')
            ->where('gajisupirheader.tglbukti', '>=', $dari)
            ->where('gajisupirheader.tglbukti', '<=', $sampai)
            ->whereRaw("gajisupirheader.nobukti not in(select gajisupir_nobukti from prosesgajisupirdetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('idric');
            $table->string('nobuktiric');
            $table->date('tglbuktiric')->default('');
            $table->string('supir_id');
            $table->date('tgldariric')->default('');
            $table->date('tglsampairic')->default('');
            $table->bigInteger('borongan')->nullable();
            $table->bigInteger('uangjalan')->nullable();
            $table->bigInteger('bbm')->nullable();
            $table->bigInteger('uangmakanharian')->nullable();
            $table->bigInteger('potonganpinjaman')->nullable();
            $table->bigInteger('potonganpinjamansemua')->nullable();
            $table->bigInteger('deposito')->nullable();
            $table->bigInteger('komisisupir')->nullable();
            $table->bigInteger('tolsupir')->nullable();
            $table->bigInteger('pinjamanpribadi')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['idric', 'nobuktiric', 'tglbuktiric', 'supir_id', 'tgldariric', 'tglsampairic', 'borongan', 'uangjalan', 'bbm', 'uangmakanharian', 'potonganpinjaman', 'potonganpinjamansemua', 'deposito', 'komisisupir', 'tolsupir', 'pinjamanpribadi'], $fetch);

        return $temp;
    }

    public function filterTrip($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
                            $query = $query->where($this->tableTotal . '.total', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'uangjalan') {
                            $query = $query->where($this->tableTotal . '.uangjalan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bbm') {
                            $query = $query->where($this->tableTotal . '.bbm', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'potonganpinjaman') {
                            $query = $query->where($this->tableTotal . '.potonganpinjaman', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'potonganpinjamansemua') {
                            $query = $query->where($this->tableTotal . '.potonganpinjamansemua', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'uangmakanharian') {
                            $query = $query->where($this->tableTotal . '.uangmakanharian', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'deposito') {
                            $query = $query->where($this->tableTotal . '.deposito', 'LIKE', "%$filters[data]%");
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
                                $query = $query->orWhere($this->tableTotal . '.total', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangjalan') {
                                $query = $query->orWhere($this->tableTotal . '.uangjalan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bbm') {
                                $query = $query->orWhere($this->tableTotal . '.bbm', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potonganpinjaman') {
                                $query = $query->orWhere($this->tableTotal . '.potonganpinjaman', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'potonganpinjamansemua') {
                                $query = $query->orWhere($this->tableTotal . '.potonganpinjamansemua', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'uangmakanharian') {
                                $query = $query->orWhere($this->tableTotal . '.uangmakanharian', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'deposito') {
                                $query = $query->orWhere($this->tableTotal . '.deposito', 'LIKE', "%$filters[data]%");
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
}
