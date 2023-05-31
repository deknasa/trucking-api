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



        $data = $query->first();

        return $data;
    }

    public function penerimaandetail()
    {
        return $this->hasMany(penerimaandetail::class, 'penerimaan_id');
    }

    public function cekvalidasiaksi($nobukti)
    {
        $rekap = DB::table('rekappenerimaandetail')
            ->from(
                DB::raw("rekappenerimaandetail as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($rekap)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Rekap Penerimaan',
                'kodeerror' => 'SATL'
            ];
            goto selesai;
        }

        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $penerimaanTrucking = DB::table('penerimaantruckingheader')
            ->from(
                DB::raw("penerimaantruckingheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($penerimaanTrucking)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'penerimaan trucking',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $pengembalianKasgantung = DB::table('pengembaliankasgantungheader')
            ->from(
                DB::raw("pengembaliankasgantungheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengembalianKasgantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'pengembalian kas gantung',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $prosesUangjalan = DB::table('prosesuangjalansupirdetail')
            ->from(
                DB::raw("prosesuangjalansupirdetail as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaantrucking_nobukti'
            )
            ->where('a.penerimaantrucking_nobukti', '=', $nobukti)
            ->first();
        if (isset($prosesUangjalan)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'proses uang jalan supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }

        $pengeluaranStok = DB::table('pengeluaranstokheader')
            ->from(
                DB::raw("pengeluaranstokheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengeluaranStok)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'pengeluaran stok',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        
        $pemutihanSupir = DB::table('pemutihansupirheader')
            ->from(
                DB::raw("pemutihansupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaan_nobukti'
            )
            ->where('a.penerimaan_nobukti', '=', $nobukti)
            ->first();
        if (isset($pemutihanSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'pemutihan supir',
                'kodeerror' => 'TDT'
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

    public function get()
    {
        $this->setRequestParameters();


        $query = DB::table($this->table)->from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select(
                'penerimaanheader.id',
                'penerimaanheader.nobukti',
                'penerimaanheader.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'agen.namaagen as agen_id',
                'bank.namabank as bank_id',
                'penerimaanheader.postingdari',
                'penerimaanheader.diterimadari',
                DB::raw('(case when (year(penerimaanheader.tgllunas) <= 2000) then null else penerimaanheader.tgllunas end ) as tgllunas'),
                'penerimaanheader.userapproval',
                DB::raw('(case when (year(penerimaanheader.tglapproval) <= 2000) then null else penerimaanheader.tglapproval end ) as tglapproval'),

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
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaanheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
                ->where('penerimaanheader.bank_id', request()->bank);
        }

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
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan');
            $table->bigInteger('nominal')->nullable();
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
            $table->date('tglbukti')->nullable();
            $table->string('pelanggan');
            $table->string('pelunasanpiutang_nobukti');
            $table->bigInteger('nominal')->nullable();
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
        // dd($id);
        $data = PenerimaanHeader::from(DB::raw("penerimaanheader with (readuncommitted)"))
            ->select(
                'penerimaanheader.id',
                'penerimaanheader.nobukti',
                'penerimaanheader.tglbukti',
                DB::raw("(case when penerimaanheader.pelanggan_id=0 then null else penerimaanheader.pelanggan_id end) as pelanggan_id"),
                'pelanggan.namapelanggan as pelanggan',
                'penerimaanheader.statuscetak',
                'penerimaanheader.diterimadari',
                'penerimaanheader.tgllunas',
                'penerimaanheader.bank_id',
                'bank.namabank as bank'
            )
            ->leftjoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaanheader.pelanggan_id', 'pelanggan.id')
            ->join(DB::raw("bank with (readuncommitted)"), 'penerimaanheader.bank_id', 'bank.id')
            ->where('penerimaanheader.id', '=', $id)
            ->first();

        // dd($data);
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
            statusapproval.text as statusapproval,
            $this->table.userapproval,
            $this->table.tglapproval,
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
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaanheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaanheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti', 1000)->nullable();
            $table->string('pelanggan_id', 1000)->nullable()->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('diterimadari', 1000)->nullable();
            $table->date('tgllunas', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
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
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))])->where($this->table . '.bank_id', request()->bankheader);
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'pelanggan_id', 'bank_id', 'postingdari', 'diterimadari', 'tgllunas',  'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'agen_id') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pelanggan_id') {
            return $query->orderBy('pelanggan.namapelanggan', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->where('statusapproval.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'agen_id') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at') {
                                $query = $query->whereRaw("format($this->table.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format($this->table.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format($this->table.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgllunas') {
                                $query = $query->whereRaw("format($this->table.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format($this->table.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'pelanggan_id') {
                                    $query = $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'bank_id') {
                                    $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'created_at') {
                                    $query = $query->whereRaw("format($this->table.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format($this->table.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query = $query->orWhereRaw("format($this->table.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tgllunas') {
                                    $query = $query->orWhereRaw("format($this->table.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format($this->table.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else {
                                    $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
        if (request()->approve && request()->periode) {
            $query->where('penerimaanheader.statusapproval', request()->approve)
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
            'penerimaandetail.keterangan as keterangan_detail',
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
            ->groupBy('penerimaanheader.nobukti', 'penerimaanheader.id', 'penerimaanheader.tglbukti', 'penerimaandetail.keterangan');
        $data = $query->get();

        return $data;
    }
}
