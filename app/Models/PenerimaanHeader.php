<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



class PenerimaanHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function penerimaandetail()
    {
        return $this->hasMany(penerimaandetail::class, 'penerimaan_id');
    }

    public function get()
    {
        $this->setRequestParameters();


        $query = DB::table($this->table)->from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select(
                'penerimaanheader.id',
                'penerimaanheader.nobukti',
                'penerimaanheader.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'bank.namabank as bank_id',
                'penerimaanheader.postingdari',
                'penerimaanheader.diterimadari',
                DB::raw('(case when (year(penerimaanheader.tgllunas) <= 2000) then null else penerimaanheader.tgllunas end ) as tgllunas'),
                'cabang.namacabang as cabang_id',
                'statuskas.memo as statuskas',
                'penerimaanheader.userapproval',
                DB::raw('(case when (year(penerimaanheader.tglapproval) <= 2000) then null else penerimaanheader.tglapproval end ) as tglapproval'),
                'penerimaanheader.noresi',
                'statusberkas.memo as statusberkas',
                'penerimaanheader.userberkas',
                DB::raw('(case when (year(penerimaanheader.tglberkas) <= 2000) then null else penerimaanheader.tglberkas end ) as tglberkas'),

                'statuscetak.memo as statuscetak',
                'penerimaanheader.userbukacetak',
                DB::raw('(case when (year(penerimaanheader.tglbukacetak) <= 2000) then null else penerimaanheader.tglbukacetak end ) as tglberkas'),
                'penerimaanheader.jumlahcetak',
                'penerimaanheader.modifiedby',
                'penerimaanheader.created_at',
                'penerimaanheader.updated_at',
                'statusapproval.memo as statusapproval',
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("parameter as statuskas with (readuncommitted)"), 'penerimaanheader.statuskas', 'statuskas.id')
            ->leftJoin(DB::raw("parameter as statusberkas with (readuncommitted)"), 'penerimaanheader.statusberkas', 'statusberkas.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'penerimaanheader.cabang_id', 'cabang.id');


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function tarikPelunasan($id)
    {
        if ($id != 'null') {
            $penerimaan = DB::table('penerimaandetail')->from(DB::raw("penerimaandetail with (readuncommitted)"))
                ->select('pelunasanpiutang_nobukti')->distinct('pelunasanpiutang_nobukti')->where('penerimaan_id', $id)->get();
            $data = [];
            foreach ($penerimaan as $index => $value) {
                $tbl = substr($value->pelunasanpiutang_nobukti, 0, 3);
                if ($tbl == 'PPT') {
                    $pelunasan = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                        ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                        ->distinct("pelunasanpiutangheader.nobukti")
                        ->leftJoin(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                        ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')

                        ->where('pelunasanpiutangheader.nobukti', $value->pelunasanpiutang_nobukti)
                        ->get();
                    foreach ($pelunasan as $index => $value) {
                        $data[] = $value;
                    }
                } else {
                    $giro = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
                        ->select(DB::raw("penerimaangiroheader.id,penerimaangiroheader.nobukti,penerimaangiroheader.tglbukti,pelanggan.namapelanggan as pelanggan,penerimaangirodetail.pelunasanpiutang_nobukti, (SELECT (SUM(penerimaangirodetail.nominal)) FROM penerimaangirodetail WHERE penerimaangirodetail.nobukti = penerimaangiroheader.nobukti) AS nominal"))
                        ->leftJoin(DB::raw("penerimaangirodetail with (readuncommitted)"), 'penerimaangirodetail.nobukti', 'penerimaangiroheader.nobukti')
                        ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
                        ->where("penerimaangiroheader.nobukti", $value->pelunasanpiutang_nobukti)
                        ->get();

                    foreach ($giro as $index => $value) {
                        $data[] = $value;
                    }
                }
            }
            return $data;
        } else {
            $tempPelunasan = $this->createTempPelunasan();
            $tempGiro = $this->createTempGiro();

            $pelunasan = DB::table("$tempPelunasan as a")->from(DB::raw("$tempPelunasan as a with (readuncommitted)"))
                ->select(DB::raw("a.nobukti as nobukti, a.id as id,a.tglbukti as tglbukti, a.pelanggan as pelangggan, a.nominal as nominal,null as pelunasanpiutang_nobukti"))
                ->distinct("a.nobukti")
                ->join(DB::raw("$tempGiro as B with (readuncommitted)"), "a.nobukti", "=", "B.pelunasanpiutang_nobukti", "left outer");

            $giro = DB::table($tempGiro)->from(DB::raw("$tempGiro with (readuncommitted)"))
                ->select(DB::raw("nobukti,id,tglbukti,pelanggan,nominal,pelunasanpiutang_nobukti"))

                ->distinct("nobukti")
                ->unionAll($pelunasan);
            $data = $giro->get();
        }

        return $data;
    }
    public function createTempPelunasan()
    {
        $temp = '##tempPelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti,pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti = pelunasanpiutangheader.nobukti) AS nominal"))
            ->join(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
            ->join(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
            ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaangirodetail)");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobukti');
            $table->date('tglbukti')->default('');
            $table->string('pelanggan');
            $table->bigInteger('nominal')->default(0);
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pelanggan', 'nominal'], $fetch);

        return $temp;
    }

    public function createTempGiro()
    {
        $temp = '##tempGiro' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('penerimaangiroheader')->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(DB::raw("penerimaangiroheader.id,penerimaangiroheader.nobukti,penerimaangiroheader.tglbukti,pelanggan.namapelanggan as pelanggan,penerimaangirodetail.pelunasanpiutang_nobukti, (SELECT (SUM(penerimaangirodetail.nominal)) FROM penerimaangirodetail WHERE penerimaangirodetail.nobukti = penerimaangiroheader.nobukti) AS nominal"))
            ->leftJoin('penerimaangirodetail', 'penerimaangirodetail.nobukti', 'penerimaangiroheader.nobukti')
            ->leftJoin('pelanggan', 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->whereRaw("penerimaangiroheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
            ->whereRaw("penerimaangirodetail.pelunasanpiutang_nobukti != '-'");

        Schema::create($temp, function ($table) {
            $table->bigInteger('id');
            $table->string('nobukti');
            $table->date('tglbukti')->default('');
            $table->string('pelanggan');
            $table->string('pelunasanpiutang_nobukti');
            $table->bigInteger('nominal')->default(0);
        });

        $tes = DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'pelanggan', 'pelunasanpiutang_nobukti', 'nominal'], $fetch);

        return $temp;
    }

    public function getPelunasan($id, $table)
    {
        if ($table == 'giro') {
            $data = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                ->select('id', 'nominal', 'tgljatuhtempo as tgljt', 'invoice_nobukti', 'nobukti')
                ->where('penerimaangiro_id', $id)
                ->get();
        } else {
            $data = DB::table('pelunasanpiutangdetail')->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"))
                ->select('id', 'nominal', 'tgljt', 'invoice_nobukti', 'nobukti')
                ->where('pelunasanpiutang_id', $id)
                ->get();
        }



        return $data;
    }

    public function findAll($id)
    {
        $data = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select('penerimaanheader.id', 'penerimaanheader.nobukti', 'penerimaanheader.tglbukti', 'penerimaanheader.pelanggan_id', 'pelanggan.namapelanggan as pelanggan', 'penerimaanheader.statuscetak', 'penerimaanheader.diterimadari', 'penerimaanheader.tgllunas', 'penerimaanheader.cabang_id', 'cabang.namacabang as cabang', 'penerimaanheader.statuskas', 'penerimaanheader.bank_id', 'bank.namabank as bank')
            ->join(DB::raw("pelanggan with (readuncommitted)"), 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->join(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->join(DB::raw("cabang with (readuncommitted)"), 'penerimaanheader.cabang_id', 'cabang.id')
            ->where('penerimaanheader.id', $id)
            ->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw(
                    "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            pelanggan.namapelanggan as pelanggan_id,
            bank.namabank as bank_id,
            $this->table.postingdari,
            $this->table.diterimadari,
            $this->table.tgllunas,
            cabang.namacabang as cabang_id,
            statuskas.text as statuskas,
            statusapproval.text as statusapproval,
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.noresi,
            statusberkas.text as statusberkas,
            $this->table.userberkas,
            $this->table.tglberkas,
            statuscetak.text as statuscetak,
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'penerimaanheader.cabang_id', 'cabang.id')
            ->leftJoin(DB::raw("parameter as statuskas with (readuncommitted)"), 'penerimaanheader.statuskas', 'statuskas.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusberkas with (readuncommitted)"), 'penerimaanheader.statusberkas', 'statusberkas.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti', 1000)->default('1900/1/1');
            $table->string('pelanggan_id', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('postingdari', 1000)->default('');
            $table->string('diterimadari', 1000)->default('');
            $table->date('tgllunas', 1000)->default('1900/1/1');
            $table->string('cabang_id', 1000)->default('');
            $table->string('statuskas', 1000)->default('');
            $table->string('statusapproval', 1000)->default('');
            $table->string('userapproval', 1000)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('noresi', 1000)->default('');
            $table->string('statusberkas', 1000)->default('')->nullable();
            $table->string('userberkas', 1000)->default('');
            $table->dateTime('tglberkas')->default('1900/1/1');
            $table->string('statuscetak', 1000)->default('');
            $table->string('userbukacetak', 50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'pelanggan_id', 'bank_id', 'postingdari', 'diterimadari', 'tgllunas', 'cabang_id',  'statuskas', 'statusapproval', 'userapproval', 'tglapproval', 'noresi', 'statusberkas', 'userberkas', 'tglberkas', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuskas') {
                            $query = $query->where('statuskas.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusberkas') {
                            $query = $query->where('statusberkas.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->where('cabang.namacabang', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuskas') {
                            $query = $query->orWhere('statuskas.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'statusberkas') {
                            $query = $query->orWhere('statusberkas.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'pelanggan_id') {
                            $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->orWhere('cabang.namacabang', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->approve && request()->periode) {
            $query->where('penerimaanheader.statusapproval', '<>', request()->approve)
                ->whereYear('penerimaanheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaanheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('penerimaanheader.statuscetak', '<>', request()->cetak)
                ->whereYear('penerimaanheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaanheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('penerimaanheader.statuscetak', '<>', request()->cetak)
                ->whereYear('penerimaanheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaanheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getRekapPenerimaanHeader($bank, $tglbukti)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            'penerimaanheader.id',
            'penerimaanheader.nobukti',
            'penerimaanheader.tglbukti',
            DB::raw('SUM(penerimaandetail.nominal) AS nominal')
        )
            ->where('penerimaanheader.bank_id', $bank)
            ->where('penerimaanheader.tglbukti', $tglbukti)
            ->whereRaw(" NOT EXISTS (
                SELECT penerimaan_nobukti
                FROM rekappenerimaandetail with (readuncommitted)
                WHERE penerimaan_nobukti = penerimaanheader.nobukti   
              )")
            ->leftJoin(DB::raw("penerimaandetail with (readuncommitted)"), 'penerimaanheader.id', 'penerimaandetail.penerimaan_id')
            ->groupBy('penerimaanheader.nobukti', 'penerimaanheader.id', 'penerimaanheader.tglbukti');
        $data = $query->get();

        return $data;
    }
}
