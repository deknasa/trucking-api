<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PiutangHeader extends MyModel
{
    use HasFactory;

    protected $table = 'piutangheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {

        $temppelunasan = '##temppelunasan' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temppelunasan, function ($table) {
            $table->string('piutang_nobukti', 100)->nullable();
            $table->double('nominal', 15, 2)->nullable();
        });

        $query = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
        )
            ->select(
                'a.piutang_nobukti',
                DB::raw("sum(a.nominal+a.potongan) as nominal")
            )
            ->groupby('piutang_nobukti');

        DB::table($temppelunasan)->insertUsing([
            'piutang_nobukti',
            'nominal',
        ], $query);


        $this->setRequestParameters();

        $query = DB::table($this->table)->from(
            DB::raw("piutangheader with (readuncommitted)")
        )->select(
            'piutangheader.id',
            'piutangheader.nobukti',
            'piutangheader.tglbukti',
            'piutangheader.postingdari',
            'piutangheader.nominal',
            DB::raw("isnull(c.nominal,0) as nominalpelunasan"),
            DB::raw("piutangheader.nominal-isnull(c.nominal,0) as sisapiutang"),
            'piutangheader.invoice_nobukti',
            'piutangheader.modifiedby',
            'piutangheader.updated_at',
            'piutangheader.created_at',
            'parameter.memo as statuscetak',
            DB::raw('(case when (year(piutangheader.tglbukacetak) <= 2000) then null else piutangheader.tglbukacetak end ) as tglbukacetak'),
            'piutangheader.userbukacetak',
            'agen.namaagen as agen_id',
        )
            ->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'piutangheader.statuscetak', 'parameter.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'piutangheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw($temppelunasan . " as c"), 'piutangheader.nobukti', 'c.piutang_nobukti');

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
        $pelunasanPiutang = DB::table('pelunasanpiutangdetail')
            ->from(
                DB::raw("pelunasanpiutangdetail as a with (readuncommitted)")
            )
            ->select(
                'a.piutang_nobukti'
            )
            ->where('a.piutang_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang',
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

    public function getPiutang($id)
    {
        $this->setRequestParameters();

        $temp = $this->createTempPiutang($id);

        $query = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader with (readuncommitted)")
            )
            ->select(DB::raw("piutangheader.nobukti as nobukti,piutangheader.tglbukti, piutangheader.invoice_nobukti, piutangheader.nominal, piutangheader.agen_id," . $temp . ".sisa"))
            ->leftJoin(DB::raw("$temp with (readuncommitted)"), 'piutangheader.agen_id', $temp . ".agen_id")
            ->whereRaw("piutangheader.agen_id = $id")
            ->whereRaw("piutangheader.nobukti = $temp.nobukti")
            ->where(function ($query) use ($temp) {
                $query->whereRaw("$temp.sisa != 0")
                    ->orWhereRaw("$temp.sisa is null");
            });


        $data = $query->get();

        return $data;
    }

    public function createTempPiutang($id)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));


        $fetch = DB::table('piutangheader')
            ->from(
                DB::raw("piutangheader with (readuncommitted)")
            )
            ->select(DB::raw("piutangheader.nobukti,piutangheader.agen_id, sum(pelunasanpiutangdetail.nominal) as nominalbayar, (SELECT (piutangheader.nominal - coalesce(SUM(pelunasanpiutangdetail.nominal),0)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.piutang_nobukti= piutangheader.nobukti) AS sisa"))
            ->leftJoin(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangdetail.piutang_nobukti', 'piutangheader.nobukti')
            ->whereRaw("piutangheader.agen_id = $id")
            ->groupBy('piutangheader.nobukti', 'piutangheader.agen_id', 'piutangheader.nominal');
        // ->get();
        Schema::create($temp, function ($table) {
            $table->string('nobukti');
            $table->bigInteger('agen_id')->nullable();
            $table->bigInteger('nominalbayar')->nullable();
            $table->bigInteger('sisa')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nobukti', 'agen_id', 'nominalbayar', 'sisa'], $fetch);


        return $temp;
    }

    public function findUpdate($id)
    {
        $data = PiutangHeader::from(DB::raw("piutangheader with (readuncommitted)"))->select(
            'piutangheader.id',
            'piutangheader.nobukti',
            'piutangheader.tglbukti',
            'piutangheader.postingdari',
            'piutangheader.nominal',
            'piutangheader.invoice_nobukti',
            'piutangheader.agen_id',
            'piutangheader.statuscetak',
            'piutangheader.modifiedby',
            'piutangheader.updated_at',
            'agen.namaagen as agen'
        )->leftJoin('agen', 'piutangheader.agen_id', 'agen.id')
            ->where('piutangheader.id', $id)->first();

        return $data;
    }

    public function selectColumns($query)
    {
        return $query->select(
            DB::raw(
                "$this->table.id,
                 $this->table.nobukti,
                 $this->table.tglbukti,
                 $this->table.postingdari,
                 $this->table.nominal,
                 $this->table.invoice_nobukti,
                 'agen.namaagen as agen_id',
                 $this->table.modifiedby,
                 $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'piutangheader.agen_id', 'agen.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->float('nominal')->nullable();
            $table->string('invoice_nobukti')->nullable();
            $table->string('agen_id')->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'postingdari', 'nominal', 'invoice_nobukti', 'agen_id', 'modifiedby', 'updated_at'], $models);

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
                        if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'agen_id') {
                                $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
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

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function piutangDetails()
    {
        return $this->hasMany(PiutangDetail::class, 'piutang_id');
    }
}
