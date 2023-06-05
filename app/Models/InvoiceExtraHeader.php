<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceExtraHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceextraheader';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoiceextraheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoiceextraheader.statuscetak', 'cetak.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'invoiceextraheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceextraheader.agen_id', 'agen.id');

        if (request()->tgldari && request()->tglsampai) {
            $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
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
        $hutangBayar = DB::table('invoiceextraheader')
            ->from(
                DB::raw("invoiceextraheader as a with (readuncommitted)")
            )
            ->select(
                'a.nobukti'
            )
            ->join(DB::raw("jurnalumumpusatheader b with (readuncommitted)"),'a.piutang_nobukti','b.nobukti')
            ->where('a.nobukti', '=', $nobukti)
            ->first();
        if (isset($hutangBayar)) {
            $data = [
                'kondisi' => true,
                'keterangan' => 'Approval Jurnal',
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

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal')->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.pelanggan_id",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.statusapproval",
                "$this->table.userapproval",
                "$this->table.tglapproval",
                "$this->table.statusformat",
                "$this->table.modifiedby"
            );

        $query = $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table . '.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'agen_id',
            'nominal',
            'statusapproval',
            'userapproval',
            'tglapproval',
            'statusformat',
            'modifiedby'
        ], $models);

        return $temp;
    }

    public function selectColumns($query)
    {
        return $query->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "$this->table.id",
                "$this->table.nobukti",
                "$this->table.tglbukti",
                "$this->table.pelanggan_id",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.piutang_nobukti",
                'parameter.memo as statusapproval',
                "$this->table.userapproval",
                DB::raw('(case when (year(invoiceextraheader.tglapproval) <= 2000) then null else invoiceextraheader.tglapproval end ) as tglapproval'),
                "$this->table.userbukacetak",
                DB::raw('(case when (year(invoiceextraheader.tglbukacetak) <= 2000) then null else invoiceextraheader.tglbukacetak end ) as tglbukacetak'),
                "$this->table.created_at",
                "$this->table.updated_at",
                "cetak.memo as statuscetak",

                "$this->table.modifiedby",
                "pelanggan.namapelanggan as  pelanggan",
                "agen.namaagen as  agen",
            );
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'agen') {
            return $query->orderBy('agen.namaagen', $this->params['sortOrder']);
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
                                $query = $query->where('parameter.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'statuscetak') {
                                $query = $query->where('cetak.text', '=', $filters['data']);
                            } else if ($filters['field'] == 'agen') {
                                $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->whereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->whereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                            }
                        }
                    }
                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'statusapproval') {
                                    $query = $query->orWhere('parameter.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'statuscetak') {
                                    $query = $query->orWhere('cetak.text', '=', $filters['data']);
                                } else if ($filters['field'] == 'agen') {
                                    $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                                } else if ($filters['field'] == 'nominal') {
                                    $query = $query->orWhereRaw("format($this->table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'tglbukti' || $filters['field'] == 'tglbukacetak' || $filters['field'] == 'tglapproval') {
                                    $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                                } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                    $query = $query->orWhereRaw("format(".$this->table . "." . $filters['field'].", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                                } else {
                                    // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                    $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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
            $query->where('invoiceextraheader.statusapproval', request()->approve)
                ->whereYear('invoiceextraheader.tglbukti', '=', request()->year)
                ->whereMonth('invoiceextraheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('invoiceextraheader.statuscetak', '<>', request()->cetak)
                ->whereYear('invoiceextraheader.tglbukti', '=', request()->year)
                ->whereMonth('invoiceextraheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoiceextraheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'invoiceextraheader.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoiceextraheader.statuscetak', 'cetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceextraheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'invoiceextraheader.statusformat', 'statusformat.id');
        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
