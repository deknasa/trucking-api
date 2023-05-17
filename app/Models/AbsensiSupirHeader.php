<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsensiSupirHeader extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl' => 'date:d-m-Y',
    ];

    public function absensiSupirDetail()
    {
        return $this->hasMany(AbsensiSupirDetail::class, 'absensi_id');
    }


    public function cekvalidasiaksi($nobukti)
    {
        $absensiSupir = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.absensisupir_nobukti'
            )
            ->where('a.absensisupir_nobukti', '=', $nobukti)
            ->first();
        if (isset($absensiSupir)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Absensi Supir Posting',
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

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.kasgantung_nobukti',
                DB::raw("(case when absensisupirheader.nominal IS NULL then 0 else absensisupirheader.nominal end) as nominal"),
                DB::raw('(case when (year(absensisupirheader.tglbukacetak) <= 2000) then null else absensisupirheader.tglbukacetak end ) as tglbukacetak'),
                'statuscetak.memo as statuscetak',
                'absensisupirheader.userbukacetak',
                'absensisupirheader.jumlahcetak',
                'absensisupirheader.modifiedby',
                'absensisupirheader.created_at',
                'absensisupirheader.updated_at'
            )
            // request()->tgldari ?? date('Y-m-d',strtotime('today'))
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id');
        if (request()->tgldari) {
            $query->whereBetween('tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);
        $data = $query->get();
        return $data;
    }

    public function findAll($id)
    {
        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select(
                'absensisupirheader.id',
                'absensisupirheader.nobukti',
                'absensisupirheader.kasgantung_nobukti',
                'absensisupirheader.tglbukti',
                'absensisupirheader.tglbukacetak',
                'absensisupirheader.statuscetak',
                'absensisupirheader.statusapprovaleditabsensi',
                'absensisupirheader.userbukacetak',
                'absensisupirheader.jumlahcetak',

            )
            ->where('id', $id);
        $data = $query->first();

        return $data;
    }

    public function selectColumns($query)
    {

        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )->select(
            DB::raw(
                "$this->table.id,
            $this->table.nobukti,
            $this->table.tglbukti,
            $this->table.kasgantung_nobukti,
            $this->table.nominal,
            'statuscetak.text as statuscetak',
            $this->table.userbukacetak,
            $this->table.tglbukacetak,
            $this->table.jumlahcetak,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
            )
        )
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'absensisupirheader.statuscetak', 'statuscetak.id');
    }

    public function createTemp(string $modelTable)
    {

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->string('tglbukti', 1000)->nullable();
            $table->string('kasgantung_nobukti', 1000)->nullable();
            $table->string('nominal', 1000)->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby', 1000)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'kasgantung_nobukti',
            'nominal',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at',
        ], $models);

        return $temp;
    }

    public function getAbsensi($id)
    {
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(
                'absensisupirdetail.keterangan as keterangan_detail',
                'absensisupirdetail.jam',
                'absensisupirdetail.uangjalan',
                'absensisupirdetail.absensi_id',
                'absensisupirdetail.id',
                'trado.kodetrado as trado',
                'supirutama.namasupir as supir',
                'trado.id as trado_id',
                DB::raw("(case when supirutama.id IS NULL then 0 else supirutama.id end) as supir_id"),

                'absensisupirheader.kasgantung_nobukti',
            )
            ->leftJoin(DB::raw("absensisupirheader with (readuncommitted)"), 'absensisupirdetail.absensi_id', 'absensisupirheader.id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'absensisupirdetail.trado_id', 'trado.id')
            ->leftJoin(DB::raw("supir as supirutama with (readuncommitted)"), 'absensisupirdetail.supir_id', 'supirutama.id')
            ->whereRaw("not EXISTS (
            SELECT absensisupirapprovalheader.absensisupir_nobukti
    FROM absensisupirdetail  with (readuncommitted)        
    left join absensisupirapprovalheader  with (readuncommitted)  on absensisupirapprovalheader.absensisupir_nobukti= absensisupirdetail.nobukti
    WHERE absensisupirapprovalheader.absensisupir_nobukti = absensisupirheader.nobukti 
          )")
            ->where('absensi_id', $id);
        //     $this->totalRows = $query->count();
        // $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;


        $data = $query->get();
        $this->totalUangJalan = $query->sum('uangjalan');
        return $data;
    }

    public function getTradoAbsensi($id)
    {
        $query = DB::table('absentrado')
            ->select('absentrado.kodeabsen', DB::raw('COUNT(absensisupirdetail.absen_id) as jumlah'))
            ->leftJoin('absensisupirdetail', function ($join) use ($id) {
                $join->on('absensisupirdetail.absen_id', '=', 'absentrado.id')
                    ->where('absensisupirdetail.absensi_id', '=', $id);
            })
            ->groupBy('absentrado.kodeabsen')
            ->orderBy("absentrado.kodeabsen", "asc")
            ->get();

        return $query;
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
                            $query = $query->where('statuscetak.text', '=', "$filters[data]");
                        }else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        }  else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statuscetak') {
                                $query = $query->orWhere('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function todayValidation($id)
    {
        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select('tglbukti')
            ->where('id', $id)
            ->first();
        $tglbukti = strtotime($query->tglbukti);
        $today = strtotime('today');
        if ($tglbukti === $today) return true;
        return false;
    }
    public function isApproved($nobukti)
    {
        $query = DB::table('absensisupirapprovalheader')
            ->from(
                DB::raw("absensisupirapprovalheader as a with (readuncommitted)")
            )
            ->select(
                'a.absensisupir_nobukti'
            )
            ->where('a.absensisupir_nobukti', '=', $nobukti)
            ->first();
        //jika ada return false
        if (empty($absensiSupir)) return true;
        return false;
    }
    public function isEditAble($id)
    {
        $tidakBolehEdit = DB::table('absensisupirheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUS EDIT ABSENSI')->where('default', 'YA')->first();

        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select('statusapprovaleditabsensi as statusedit')
            ->where('id', $id)
            ->first();

        if ($query->statusedit != $tidakBolehEdit->id) return true;
        return false;
    }

    public function printValidation($id)
    {

        $statusCetak = DB::table('absensisupirheader')->from(DB::raw("parameter with (readuncommitted)"))->where('grp', 'STATUSCETAK')->where('text', 'CETAK')->first();

        $query = DB::table('absensisupirheader')->from(DB::raw("absensisupirheader with (readuncommitted)"))
            ->select('statuscetak')
            ->where('id', $id)
            ->first();

        if ($query->statuscetak != $statusCetak->id) return true;
        return false;
    }
}
