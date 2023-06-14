<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenerimaanGiroHeader extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaangiroheader';

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


        $pelunasanPiutang = DB::table('pelunasanpiutangheader')
            ->from(
                DB::raw("pelunasanpiutangheader as a with (readuncommitted)")
            )
            ->select(
                'a.penerimaangiro_nobukti'
            )
            ->where('a.penerimaangiro_nobukti', '=', $nobukti)
            ->first();
        if (isset($pelunasanPiutang)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Pelunasan Piutang',
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
        $periode = request()->periode ?? '';
        $statusCetak = request()->statuscetak ?? '';
        $query = DB::table($this->table)->from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                'penerimaangiroheader.id',
                'penerimaangiroheader.nobukti',
                'penerimaangiroheader.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'agen.namaagen as agen_id',
                'penerimaangiroheader.postingdari',
                'penerimaangiroheader.diterimadari',
                'penerimaangiroheader.tgllunas',
                'statusapproval.memo as statusapproval',
                DB::raw('(case when (year(penerimaangiroheader.tglapproval) <= 2000) then null else penerimaangiroheader.tglapproval end ) as tglapproval'),
                'penerimaangiroheader.userapproval',
                'penerimaangiroheader.created_at',
                'statuscetak.memo as statuscetak',
                DB::raw('(case when (year(penerimaangiroheader.tglbukacetak) <= 2000) then null else penerimaangiroheader.tglbukacetak end ) as tglbukacetak'),
                'penerimaangiroheader.userbukacetak',
                'penerimaangiroheader.created_at',
                'penerimaangiroheader.modifiedby',
                'penerimaangiroheader.updated_at'
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'penerimaangiroheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaangiroheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaangiroheader.statusapproval', 'statusapproval.id');
        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        
        if ($periode != '') {
            $periode = explode("-", $periode);
            $query->whereRaw("MONTH(penerimaangiroheader.tglbukti) ='" . $periode[0] . "'")
                ->whereRaw("year(penerimaangiroheader.tglbukti) ='" . $periode[1] . "'");
        }
        if ($statusCetak != '') {
            $query->where("penerimaangiroheader.statuscetak", $statusCetak);
        }

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $data = $query->get();

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
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'penerimaangiroheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'penerimaangiroheader.statusapproval', 'statusapproval.id');
    }

    public function findAll($id)
    {
        $data = PenerimaanGiroHeader::from(DB::raw("penerimaangiroheader with (readuncommitted)"))
            ->select(
                'penerimaangiroheader.id',
                'penerimaangiroheader.nobukti',
                'penerimaangiroheader.tglbukti',
                'penerimaangiroheader.pelanggan_id',
                'pelanggan.namapelanggan as pelanggan',
                'penerimaangiroheader.diterimadari',
                'penerimaangiroheader.tgllunas',
                'penerimaangiroheader.statuscetak'
            )
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'penerimaangiroheader.pelanggan_id', 'pelanggan.id')
            ->where('penerimaangiroheader.id', $id)
            ->first();

        return $data;
    }

    public function tarikPelunasan($id)
    {
        if ($id != 'null') {
            $penerimaan = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                ->select('pelunasanpiutang_nobukti')->where('penerimaangiro_id', $id)->first();
            $data = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                ->distinct("pelunasanpiutangheader.nobukti")
                ->join(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                ->join(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')

                ->where('pelunasanpiutangheader.nobukti', $penerimaan->pelunasanpiutang_nobukti)
                ->get();
        } else {

            $data = DB::table('pelunasanpiutangheader')->from(DB::raw("pelunasanpiutangheader with (readuncommitted)"))
                ->select(DB::raw("pelunasanpiutangheader.id,pelunasanpiutangheader.nobukti,pelunasanpiutangheader.tglbukti, pelanggan.namapelanggan as pelanggan, (SELECT (SUM(pelunasanpiutangdetail.nominal)) FROM pelunasanpiutangdetail WHERE pelunasanpiutangdetail.nobukti= pelunasanpiutangheader.nobukti) AS nominal"))
                ->distinct("pelunasanpiutangheader.nobukti")
                ->join(DB::raw("pelunasanpiutangdetail with (readuncommitted)"), 'pelunasanpiutangheader.id', 'pelunasanpiutangdetail.pelunasanpiutang_id')
                ->join(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
                ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaangirodetail)")
                ->whereRaw("pelunasanpiutangheader.nobukti not in (select pelunasanpiutang_nobukti from penerimaandetail)")
                ->get();
        }

        return $data;
    }

    public function getPelunasan($id)
    {

        $data = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail with (readuncommitted)")
        )
            ->select('id', 'nominal', 'tgljt', 'keterangan', 'invoice_nobukti', 'nobukti')
            ->where('pelunasanpiutang_id', $id)
            ->get();

        return $data;
    }
    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti', 1000)->nullable();
            $table->string('pelanggan_id', 1000)->nullable();
            $table->string('postingdari', 1000)->nullable();
            $table->string('diterimadari', 1000)->nullable();
            $table->date('tgllunas', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 1000)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
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
        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'postingdari',
            'diterimadari',
            'tgllunas',
            'statusapproval',
            'userapproval',
            'tglapproval',
            'statuscetak',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
            'created_at',
            'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'pelanggan_id') {
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
                            } else if ($filters['field'] == 'agen_id') {
                                $query = $query->where('agen.namaagen', '=', "$filters[data]");
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('statuscetak.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'pelanggan_id') {
                                $query = $query->where('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'created_at') {
                                $query = $query->whereRaw("format($this->table.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format($this->table.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tgllunas') {
                                $query = $query->whereRaw("format($this->table.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format($this->table.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format($this->table.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
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
                                if ($filters['field'] == 'statusapproval') {
                                    $query->orWhere('statusapproval.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query->orWhere('statuscetak.text', '=', "$filters[data]");
                                } else if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('agen.namaagen', '=', "$filters[data]");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query->orWhere($this->table . '.tglbukti', '=', date('Y-m-d', strtotime($filters['data'])));
                                } else if ($filters['field'] == 'pelanggan_id') {
                                    $query->orWhere('pelanggan.namapelanggan', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'created_at') {
                                    $query = $query->orWhereRaw("format($this->table.created_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format($this->table.updated_at,'dd-MM-yyyy HH:mm:ss') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tgllunas') {
                                    $query = $query->orWhereRaw("format($this->table.tgllunas,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti') {
                                    $query = $query->orWhereRaw("format($this->table.tglbukti,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format($this->table.tglapproval,'dd-MM-yyyy') like '%$filters[data]%'");
                                } else {
                                    // $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('penerimaangiroheader.statuscetak', request()->cetak)
                ->whereYear('penerimaangiroheader.tglbukti', '=', request()->year)
                ->whereMonth('penerimaangiroheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
