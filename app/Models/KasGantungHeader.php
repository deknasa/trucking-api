<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KasGantungHeader extends MyModel
{
    use HasFactory;

    protected $table = 'kasgantungheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // protected $casts = [
    //     'tgl' => 'date:d-m-Y',
    //     'tglkaskeluar' => 'date:d-m-Y',
    //     'created_at' => 'date:d-m-Y H:i:s',
    //     'updated_at' => 'date:d-m-Y H:i:s'
    // ];     

    public function kasgantungDetail()
    {
        return $this->hasMany(KasGantungDetail::class, 'kasgantung_id');
    }

    // public function bank() {
    //     return $this->belongsTo(Bank::class, 'bank_id');
    // }

    // public function penerima() {
    //     return $this->belongsTo(Penerima::class, 'penerima_id');
    // }


    public function cekvalidasiaksi($nobukti)
    {
        $absensiSupir = DB::table('absensisupirheader')
            ->from(
                DB::raw("absensisupirheader as a with (readuncommitted)")
            )
            ->select(
                'a.kasgantung_nobukti'
            )
            ->where('a.kasgantung_nobukti', '=', $nobukti)
            ->first();
        if (isset($absensiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir',
                'kodeerror' => 'TDT'
            ];
            goto selesai;
        }
        $pengembalianKasgantung = DB::table('pengembaliankasgantungdetail')
            ->from(
                DB::raw("pengembaliankasgantungdetail as a with (readuncommitted)")
            )
            ->select(
                'a.kasgantung_nobukti'
            )
            ->where('a.kasgantung_nobukti', '=', $nobukti)
            ->first();
        if (isset($pengembalianKasgantung)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pengembalian Kas Gantung',
                'kodeerror' => 'SATL'
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

        $query = DB::table($this->table)->from(DB::raw("kasgantungheader with (readuncommitted)"))
            ->select(
                'kasgantungheader.id',
                'kasgantungheader.nobukti',
                'kasgantungheader.tglbukti',
                'penerima.namapenerima as penerima_id',
                'bank.namabank as bank_id',
                'kasgantungheader.pengeluaran_nobukti',
                'kasgantungheader.coakaskeluar',
                db::raw("(case when year(isnull(kasgantungheader.tglkaskeluar,'1900/1/1'))=1900 then null else kasgantungheader.tglkaskeluar end) as tglkaskeluar"),
                db::raw("(case when year(isnull(kasgantungheader.tglbukacetak,'1900/1/1'))=1900 then null else kasgantungheader.tglbukacetak end) as tglbukacetak"),
                'kasgantungheader.postingdari',
                'kasgantungheader.userbukacetak',
                'kasgantungheader.jumlahcetak',
                'statuscetak.memo as statuscetak',
                'kasgantungheader.modifiedby',
                'kasgantungheader.created_at',
                'kasgantungheader.updated_at'
            )
            ->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'kasgantungheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'kasgantungheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function findUpdate($id)
    {
        $query = KasGantungHeader::from(DB::raw("kasgantungheader with (readuncommitted)"))
            ->select(
                'kasgantungheader.id',
                'kasgantungheader.nobukti',
                'kasgantungheader.tglbukti',
                DB::raw("(case when kasgantungheader.penerima_id=0 then null else kasgantungheader.penerima_id end) as penerima_id"),
                'penerima.namapenerima as penerima',
                'kasgantungheader.bank_id',
                'bank.namabank as bank',
                'kasgantungheader.pengeluaran_nobukti',
                'kasgantungheader.statuscetak',
                'kasgantungheader.coakaskeluar',
                'kasgantungheader.tglkaskeluar',
                'kasgantungheader.tglbukacetak',
                'kasgantungheader.statuscetak',
                'kasgantungheader.userbukacetak',
                'kasgantungheader.jumlahcetak',
                'kasgantungheader.modifiedby',
                'kasgantungheader.created_at',
                'kasgantungheader.updated_at'
            )
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id')
            ->where('kasgantungheader.id', $id);

        $data = $query->first();

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
            'penerima.namapenerima as penerima_id',
            'bank.namabank as bank_id',
            $this->table.pengeluaran_nobukti,
            $this->table.coakaskeluar,
            $this->table.tglkaskeluar,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("penerima with (readuncommitted)"), 'kasgantungheader.penerima_id', 'penerima.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'kasgantungheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'kasgantungheader.bank_id', 'bank.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('penerima_id', 1000)->nullable();
            $table->string('bank_id', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('coakaskeluar', 1000)->nullable();
            $table->date('tglkaskeluar')->nullable();
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
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'penerima_id', 'bank_id', 'pengeluaran_nobukti', 'coakaskeluar', 'tglkaskeluar', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);


        return  $temp;
    }

    


    public function getKasGantung($dari, $sampai)
    {
        
        $tempPribadi = $this->createTempKasGantung($dari, $sampai);
        $query = DB::table($tempPribadi)->from(DB::raw("$tempPribadi with (readuncommitted)"))
        ->select(DB::raw("row_number() Over(Order By $tempPribadi.nobukti) as id,$tempPribadi.tglbukti,$tempPribadi.nobukti,$tempPribadi.sisa "))
        ->where(function ($query) use ($tempPribadi) {
            $query->whereRaw("$tempPribadi.sisa != 0")
                ->orWhereRaw("$tempPribadi.sisa is null");
        });
        return $query->get();
    }

    public function createTempKasGantung($dari, $sampai)
    {
        
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("kasgantungdetail.nobukti,kasgantungheader.tglbukti,(SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) FROM pengembaliankasgantungdetail WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa")) 
            ->leftJoin('kasgantungheader', 'kasgantungheader.id', 'kasgantungdetail.kasgantung_id')
            ->whereBetween('kasgantungheader.tglbukti', [$dari, $sampai])                                                                                     
            ->groupBy('kasgantungdetail.nobukti','kasgantungheader.tglbukti')
            ->orderBy('kasgantungheader.tglbukti', 'asc')
            ->orderBy('kasgantungdetail.nobukti', 'asc');

        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->date('tglbukti');
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti','tglbukti', 'sisa'], $fetch); 
        //dd($tes);
        return $temp;
    }

    public function sort($query)
    {
        if($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if($this->params['sortIndex'] == 'penerima_id') {
            return $query->orderBy('penerima.namapenerima', $this->params['sortOrder']);
        } else{
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'penerima_id') {
                            $query = $query->where('penerima.namapenerima', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'penerima_id') {
                                $query = $query->orWhere('penerima.namapenerima', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . ".[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
            $query->where('kasgantungheader.statuscetak', '<>', request()->cetak)
                ->whereYear('kasgantungheader.tglbukti', '=', request()->year)
                ->whereMonth('kasgantungheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function getSisaPengembalianForValidasi($nobukti){
        
        $fetch = DB::table('kasgantungdetail')
            ->from(
                DB::raw("kasgantungdetail with (readuncommitted)")
            )
            ->select(DB::raw("kasgantungdetail.nobukti,(SELECT (sum(kasgantungdetail.nominal) - coalesce(SUM(pengembaliankasgantungdetail.nominal),0)) FROM pengembaliankasgantungdetail WHERE pengembaliankasgantungdetail.kasgantung_nobukti= kasgantungdetail.nobukti) AS sisa")) 
            ->whereRaw("kasgantungdetail.nobukti = '$nobukti'")
            ->groupBy('kasgantungdetail.nobukti');
            // ->first();
        
            return $fetch->first();
            
    }
}
