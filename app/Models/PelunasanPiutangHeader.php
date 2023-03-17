<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class PelunasanPiutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'pelunasanpiutangheader';

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
            $table->unsignedBigInteger('alatbayar_id')->default(0);
            $table->string('alatbayar', 255)->default('');
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

        $statusdefault = Parameter::from(
            db::Raw("parameter with (readuncommitted)")
        )
            ->select(
                'id'
            )
            ->where('grp', '=', 'STATUS DEFAULT')
            ->where('subgrp', '=', 'STATUS DEFAULT')
            ->where('text', '=', 'DEFAULT')
            ->first();

        $alatbayardefault = $statusdefault->id ?? 0;

        $alatbayar = DB::table('alatbayar')->from(
            DB::raw('alatbayar with (readuncommitted)')
        )
            ->select(
                'id as alatbayar_id',
                'namaalatbayar as alatbayar',

            )
            ->where('statusdefault', '=', $alatbayardefault)
            ->first();


        DB::table($tempdefault)->insert(
            ["bank_id" => $bank->bank_id, "bank" => $bank->bank, "alatbayar_id" => $alatbayar->alatbayar_id, "alatbayar" => $alatbayar->alatbayar]
        );

        $query = DB::table($tempdefault)->from(
            DB::raw($tempdefault)
        )
            ->select(
                'bank_id',
                'bank',
                'alatbayar_id',
                'alatbayar',
            );

        $data = $query->first();

        return $data;
    }
    
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(
                'pelunasanpiutangheader.id',
                'pelunasanpiutangheader.nobukti',
                'pelunasanpiutangheader.tglbukti',
                'pelunasanpiutangheader.modifiedby',
                'pelunasanpiutangheader.updated_at',
                'pelunasanpiutangheader.created_at',
                'pelunasanpiutangheader.penerimaan_nobukti',
                'pelunasanpiutangheader.penerimaangiro_nobukti',
                'pelunasanpiutangheader.notadebet_nobukti',
                'pelunasanpiutangheader.notakredit_nobukti',

                'bank.namabank as bank_id',
                'agen.namaagen as agen_id',
                'alatbayar.namaalatbayar as alatbayar_id'
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pelunasanpiutangheader.alatbayar_id', 'alatbayar.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function getPelunasanPiutang($id, $agenid)
    {
        $this->setRequestParameters();

        $tempPiutang = $this->createTempPiutang($id, $agenid);
        $tempPelunasan = $this->createTempPelunasan($id, $agenid);


        $piutang = DB::table("$tempPiutang as A")->from(DB::raw("$tempPiutang as A with (readuncommitted)"))
            ->select(DB::raw("null as pelunasanpiutang_id,A.nobukti as piutang_nobukti, A.tglbukti as tglbukti, A.invoice_nobukti as invoice_nobukti,null as nominal, null as keterangan, null as potongan, null as coapotongan, null as keteranganpotongan, null as nominallebihbayar, A.nominalpiutang, A.sisa as sisa"))
            ->distinct("A.nobukti")
            ->leftJoin(DB::raw("$tempPelunasan as B with (readuncommitted)"), "A.nobukti", "B.piutang_nobukti")
            ->whereRaw("isnull(b.piutang_nobukti,'') = ''")
            ->whereRaw("a.sisa > 0");


        $pelunasan = DB::table($tempPelunasan)->from(DB::raw("$tempPelunasan with (readuncommitted)"))
            ->select(DB::raw("pelunasanpiutang_id,piutang_nobukti,tglbukti,invoice_nobukti,nominal,keterangan,potongan, coapotongan,keteranganpotongan,nominallebihbayar,nominalpiutang,sisa"))
            ->unionAll($piutang);

        // $this->totalRows = $pelunasan->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        // $this->sort($pelunasan);
        // $this->filter($pelunasan);
        // $this->paginate($pelunasan);

        $data = $pelunasan->get();

        return $data;
    }

    public function createTempPiutang($id, $agenid)
    {
        $temp = '##tempPiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('piutangheader')->from(DB::raw("piutangheader with (readuncommitted)"))
            ->select(DB::raw("piutangheader.nobukti,piutangheader.tglbukti,piutangheader.nominal as nominalpiutang,piutangheader.invoice_nobukti, (SELECT (piutangheader.nominal - COALESCE(SUM(pelunasanpiutangdetail.nominal),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->whereRaw("piutangheader.agen_id = $agenid")
            ->groupBy('piutangheader.id', 'piutangheader.nobukti', 'piutangheader.agen_id', 'piutangheader.nominal', 'piutangheader.tglbukti', 'piutangheader.invoice_nobukti');
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('nominalpiutang');
            $table->string('invoice_nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'nominalpiutang', 'invoice_nobukti', 'sisa'], $fetch);
       
        return $temp;
    }

    public function createTempPelunasan($id, $agenid)
    {
        $tempo = '##tempPelunasan' . rand(1, 10000);

        $fetch = DB::table('pelunasanpiutangdetail as ppd')->from(DB::raw("pelunasanpiutangdetail as ppd with (readuncommitted)"))
            ->select(DB::raw("ppd.pelunasanpiutang_id,ppd.piutang_nobukti,piutangheader.tglbukti,ppd.nominal,ppd.keterangan,ppd.potongan,ppd.coapotongan,ppd.keteranganpotongan,ppd.nominallebihbayar, piutangheader.nominal as nominalpiutang,ppd.invoice_nobukti, (SELECT (piutangheader.nominal - SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->join(DB::raw("piutangheader with (readuncommitted)"), 'ppd.piutang_nobukti', 'piutangheader.nobukti')
            ->whereRaw("ppd.pelunasanpiutang_id = $id");

        Schema::create($tempo, function ($table) {
            $table->bigInteger('pelunasanpiutang_id')->default('0');
            $table->string('piutang_nobukti');
            $table->date('tglbukti')->default('');
            $table->bigInteger('nominal')->nullable();
            $table->string('keterangan');
            $table->bigInteger('potongan')->default('0');
            $table->string('coapotongan');
            $table->string('keteranganpotongan');
            $table->bigInteger('nominallebihbayar')->default('0');
            $table->bigInteger('nominalpiutang');
            $table->string('invoice_nobukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($tempo)->insertUsing(['pelunasanpiutang_id', 'piutang_nobukti', 'tglbukti', 'nominal', 'keterangan', 'potongan', 'coapotongan', 'keteranganpotongan', 'nominallebihbayar', 'nominalpiutang', 'invoice_nobukti', 'sisa'], $fetch);

        return $tempo;
    }

    public function getDeletePelunasanPiutang($id, $agenId)
    {

       
        $tempPelunasan = $this->createTempPelunasan($id, $agenId);
        
        $data = DB::table($tempPelunasan)->get();
        return $data;
    }

    public function getPelunasanNotaKredit($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"))
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        pelunasanpiutangdetail.keterangan,
        pelunasanpiutangdetail.coapotongan,
        COALESCE (pelunasanpiutangdetail.potongan, 0) as potongan '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" NOT EXISTS (
            SELECT notakreditheader.pelunasanpiutang_nobukti
            FROM notakreditdetail with (readuncommitted)
			left join notakreditheader with (readuncommitted) on notakreditdetail.notakredit_id = notakreditheader.id
            WHERE notakreditheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.potongan', '>', 0)
            ->where('pelunasanpiutangdetail.pelunasanpiutang_id', $id);




        $data = $query->get();

        return $data;
    }

    public function getPelunasanNotaDebet($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(DB::raw("pelunasanpiutangdetail with (readuncommitted)"))
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        pelunasanpiutangdetail.keterangan,
        pelunasanpiutangdetail.coalebihbayar,
        COALESCE (pelunasanpiutangdetail.nominallebihbayar, 0) as lebihbayar '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" NOT EXISTS (
            SELECT notadebetheader.pelunasanpiutang_nobukti
            FROM notadebetdetail with (readuncommitted)
			left join notadebetheader with (readuncommitted) on notadebetdetail.notadebet_id = notadebetheader.id
            WHERE notadebetheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.nominallebihbayar', '>', 0)
            ->where('pelunasanpiutangdetail.pelunasanpiutang_id', $id);




        $data = $query->get();

        return $data;
    }

    public function findAll($id)
    {

        $query = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
            ->select(
                'pelunasanpiutangheader.id',
                'pelunasanpiutangheader.nobukti',
                'pelunasanpiutangheader.tglbukti',
                'pelunasanpiutangheader.bank_id',
                'pelunasanpiutangheader.alatbayar_id',
                'pelunasanpiutangheader.agen_id',
                'pelunasanpiutangheader.penerimaan_nobukti',
                'pelunasanpiutangheader.penerimaangiro_nobukti',
                'pelunasanpiutangheader.notakredit_nobukti',
                'pelunasanpiutangheader.notadebet_nobukti',
                'pelunasanpiutangheader.nowarkat',

                'bank.namabank as bank',
                'alatbayar.namaalatbayar as alatbayar',
                'agen.namaagen as agen',
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pelunasanpiutangheader.alatbayar_id', 'alatbayar.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangheader.agen_id', 'agen.id')
            ->where('pelunasanpiutangheader.id', $id);

        $data = $query->first();

        return $data;
    }


    public function pelunasanpiutangdetail()
    {
        return $this->hasMany(PelunasanPiutangDetail::class, 'pelunasanpiutang_id');
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                DB::raw("
            $this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            'bank.namabank as bank_id',
            $this->table.modifiedby,
            $this->table.updated_at
            ")
            )
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pelunasanpiutangheader.bank_id', 'bank.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti')->default('');
            $table->string('bank_id')->default('');
            $table->string('modifiedby')->default();
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'bank_id', 'modifiedby', 'updated_at'], $models);

        return $temp;
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
                        if ($filters['field'] == 'bank_id') {
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
                        if ($filters['field'] == 'bank_id') {
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

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
