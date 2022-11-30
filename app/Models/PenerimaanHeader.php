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

        $query = DB::table($this->table);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->selectColumns($query);
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function tarikPelunasan($id)
    {
        if ($id != 'null') {
            $penerimaan = DB::table('penerimaandetail')->select('pelunasanpiutang_nobukti')->distinct('pelunasanpiutang_nobukti')->where('penerimaan_id', $id)->get();
            $data = [];
            foreach ($penerimaan as $index => $value) {
                $tbl = substr($value->pelunasanpiutang_nobukti, 0, 3);
                if ($tbl == 'PPT') {
                    $pelunasan = DB::table('pelunasanpiutangheader')->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                        ->distinct("pelunasanpiutangheader.nobukti")
                        ->join('pelunasanpiutangdetail', 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                        ->join('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')

                        ->where('pelunasanpiutangheader.nobukti', $value->pelunasanpiutang_nobukti)
                        ->get();
                        foreach($pelunasan as $index => $value)
                        {
                            $data[] = $value;
                        }
                } else {
                    $giro = DB::table('penerimaangiroheader')
                        ->select(DB::raw("penerimaangiroheader.id,penerimaangiroheader.nobukti,penerimaangiroheader.tglbukti,pelanggan.namapelanggan as pelanggan,penerimaangirodetail.pelunasanpiutang_nobukti, (SELECT (SUM(penerimaangirodetail.nominal)) FROM penerimaangirodetail WHERE penerimaangirodetail.nobukti = penerimaangiroheader.nobukti) AS nominal"))
                        ->leftJoin('penerimaangirodetail', 'penerimaangirodetail.nobukti', 'penerimaangiroheader.nobukti')
                        ->leftJoin('pelanggan', 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
                        ->where("penerimaangiroheader.nobukti", $value->pelunasanpiutang_nobukti)
                        ->get();
                        
                        foreach($giro as $index => $value)
                        {
                            $data[] = $value;
                        }
                }
            }
            return $data;

        } else {
            $tempPelunasan = $this->createTempPelunasan();
            $tempGiro = $this->createTempGiro();

            $pelunasan = DB::table("$tempPelunasan as a")
                ->select(DB::raw("a.nobukti as nobukti, a.id as id,a.tglbukti as tglbukti, a.pelanggan as pelangggan, a.nominal as nominal,null as pelunasanpiutang_nobukti"))
                ->distinct("a.nobukti")
                ->join("$tempGiro as B", "a.nobukti", "=", "B.pelunasanpiutang_nobukti", "left outer");

            $giro = DB::table($tempGiro)
                ->select(DB::raw("nobukti,id,tglbukti,pelanggan,nominal,pelunasanpiutang_nobukti"))
                
                ->distinct("nobukti")
                ->unionAll($pelunasan);
            $data = $giro->get();
        }

        return $data;
    }
    public function createTempPelunasan()
    {
        $temp = '##tempPelunasan' . rand(1, 10000);


        $fetch = DB::table('pelunasanpiutangheader')
            ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti,pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti = pelunasanpiutangheader.nobukti) AS nominal"))
            ->join('pelunasanpiutangdetail', 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
            ->join('pelanggan', 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
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
        $temp = '##tempGiro' . rand(1, 10000);


        $fetch = DB::table('penerimaangiroheader')
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
            $data = DB::table('penerimaangirodetail')->select('id', 'nominal', 'tgljatuhtempo as tgljt', 'keterangan', 'invoice_nobukti', 'nobukti')
                ->where('penerimaangiro_id', $id)
                ->get();
        } else {
            $data = DB::table('pelunasanpiutangdetail')->select('id', 'nominal', 'tgljt', 'keterangan', 'invoice_nobukti', 'nobukti')
                ->where('pelunasanpiutang_id', $id)
                ->get();
        }



        return $data;
    }

    public function findAll($id)
    {
        $data = PenerimaanHeader::select('penerimaanheader.id', 'penerimaanheader.nobukti', 'penerimaanheader.tglbukti', 'penerimaanheader.pelanggan_id', 'pelanggan.namapelanggan as pelanggan', 'penerimaanheader.keterangan', 'penerimaanheader.diterimadari', 'penerimaanheader.tgllunas', 'penerimaanheader.cabang_id', 'cabang.namacabang as cabang', 'penerimaanheader.statuskas', 'penerimaanheader.bank_id', 'bank.namabank as bank')
            ->join('pelanggan', 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->join('bank', 'penerimaanheader.bank_id', 'bank.id')
            ->join('cabang', 'penerimaanheader.cabang_id', 'cabang.id')
            ->where('penerimaanheader.id', $id)
            ->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            pelanggan.namapelanggan as pelanggan_id,
            bank.namabank as bank_id,
            $this->table.keterangan,
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
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin('pelanggan', 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin('bank', 'penerimaanheader.bank_id', 'bank.id')
            ->leftJoin('cabang', 'penerimaanheader.cabang_id', 'cabang.id')
            ->leftJoin('parameter as statuskas', 'penerimaanheader.statuskas', 'statuskas.id')
            ->leftJoin('parameter as statusapproval', 'penerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin('parameter as statusberkas', 'penerimaanheader.statusberkas', 'statusberkas.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, 10000);
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 1000)->default('');
            $table->date('tglbukti', 1000)->default('1900/1/1');
            $table->string('pelanggan_id', 1000)->default('');
            $table->string('bank_id', 1000)->default('');
            $table->string('keterangan', 3000)->default('');
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
            'id', 'nobukti', 'tglbukti', 'pelanggan_id', 'bank_id', 'keterangan', 'postingdari',
            'diterimadari', 'tgllunas', 'cabang_id',  'statuskas', 'statusapproval', 'userapproval', 'tglapproval', 'noresi', 'statusberkas', 'userberkas', 'tglberkas', 'modifiedby', 'created_at', 'updated_at'
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
            $query->where('penerimaanheader.statusapproval','<>', request()->approve)
                  ->whereYear('penerimaanheader.tglbukti','=', request()->year)
                  ->whereMonth('penerimaanheader.tglbukti','=', request()->month);
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

        $query = DB::table($this->table)->select(
            'penerimaanheader.nobukti',
            'penerimaanheader.keterangan as keterangan_detail',
            'penerimaanheader.tglbukti',
            DB::raw('SUM(penerimaandetail.nominal) AS nominal')
        )
            ->where('penerimaanheader.bank_id', $bank)
            ->where('penerimaanheader.tglbukti', $tglbukti)
            ->whereRaw(" NOT EXISTS (
                SELECT penerimaan_nobukti
                FROM rekappenerimaandetail
                WHERE penerimaan_nobukti = penerimaanheader.nobukti   
              )")
            ->leftJoin('penerimaandetail', 'penerimaanheader.id', 'penerimaandetail.penerimaan_id')
            ->groupBy('penerimaanheader.nobukti', 'penerimaanheader.keterangan', 'penerimaanheader.tglbukti');
        $data = $query->get();

        return $data;
    }
}
