<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengeluaranStokHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PengeluaranStokHeader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('gudang', 'pengeluaranstokheader.gudang_id', 'gudang.id')
            ->leftJoin('gandengan', 'pengeluaranstokheader.gandengan_id', 'gandengan.id')
            ->leftJoin('pengeluaranstok', 'pengeluaranstokheader.pengeluaranstok_id', 'pengeluaranstok.id')
            ->leftJoin('trado', 'pengeluaranstokheader.trado_id', 'trado.id')
            ->leftJoin('supplier', 'pengeluaranstokheader.supplier_id', 'supplier.id')
            ->leftJoin('kerusakan', 'pengeluaranstokheader.kerusakan_id', 'kerusakan.id')
            ->leftJoin('bank', 'pengeluaranstokheader.bank_id', 'bank.id')
            ->leftJoin('penerimaanstokheader as penerimaan', 'pengeluaranstokheader.penerimaanstok_nobukti', 'penerimaan.nobukti')
            ->leftJoin('penerimaanheader', 'pengeluaranstokheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
            ->leftJoin('hutangbayarheader', 'pengeluaranstokheader.hutangbayar_nobukti', 'hutangbayarheader.nobukti')
            ->leftJoin('pengeluaranstokheader as pengeluaran', 'pengeluaranstokheader.pengeluaranstok_nobukti', 'pengeluaran.nobukti')
            // ->leftJoin('servicein','pengeluaranstokheader.servicein_nobukti','servicein.nobukti')
            ->leftJoin('supir', 'pengeluaranstokheader.supir_id', 'supir.id');
        if (request()->tgldari) {
            $query->whereBetween('pengeluaranstokheader.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))]);
        }
        if (request()->pengeluaranheader_id) {
            $query->where('pengeluaranstokheader.pengeluaranstok_id', request()->pengeluaranheader_id);
        }

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'grp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.subgrp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        if ($this->params['sortIndex'] == 'subgrp') {
            return $query
                ->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder'])
                ->orderBy($this->table . '.grp', $this->params['sortOrder'])
                ->orderBy($this->table . '.id', $this->params['sortOrder']);
        }

        switch ($this->params['sortIndex']) {
            case 'pengeluaranstok':
                return $query->orderBy('pengeluaranstok.kodepengeluaran', $this->params['sortOrder']);
                break;
            case 'gudang':
                return $query->orderBy('gudang.gudang', $this->params['sortOrder']);
                break;
            case 'gandengan':
                return $query->orderBy('gandengan.keterangan', $this->params['sortOrder']);
                break;
            case 'trado':
                return $query->orderBy('trado.kodetrado', $this->params['sortOrder']);
                break;
            case 'supplier':
                return $query->orderBy('supplier.namasupplier', $this->params['sortOrder']);
                break;
            case 'kerusakan':
                return $query->orderBy('kerusakan.keterangan', $this->params['sortOrder']);
                break;
            case 'supir':
                return $query->orderBy('supir.namasupir', $this->params['sortOrder']);
                break;
            case 'bank':
                return $query->orderBy('bank.namabank', $this->params['sortOrder']);
                break;

            default:
                return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
                break;
        }
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                       
                        if ($filters['field'] == 'pengeluaranstok') {
                            $query = $query->where('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gudang') {
                            $query = $query->where('gudang.gudang', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'gandengan') {
                            $query = $query->where('gandengan.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'trado') {
                            $query = $query->where('trado.kodetrado', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supplier') {
                            $query = $query->where('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'kerusakan') {
                            $query = $query->where('kerusakan.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'supir') {
                            $query = $query->where('supir.namasupir', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'bank') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'pengeluaranstok') {
                                $query = $query->orWhere('pengeluaranstok.kodepengeluaran', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gudang') {
                                $query = $query->orWhere('gudang.gudang', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'gandengan') {
                                $query = $query->orWhere('gandengan.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'trado') {
                                $query = $query->orWhere('trado.kodetrado', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supplier') {
                                $query = $query->orWhere('supplier.namasupplier', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'kerusakan') {
                                $query = $query->orWhere('kerusakan.keterangan', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'supir') {
                                $query = $query->orWhere('supir.namasupir', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'bank') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");

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

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti', 50)->nullable();
            $table->unsignedBigInteger('pengeluaranstok_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('hutangbayar_nobukti', 50)->nullable();
            $table->string('servicein_nobukti', 50)->nullable();
            $table->unsignedBigInteger('kerusakan_id')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->unsignedBigInteger('statuspotongretur')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->increments('position');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $query = DB::table($modelTable);
        $query = $this->select(
            "id",
            "nobukti",
            "tglbukti",
            "pengeluaranstok_id",
            "trado_id",
            "gudang_id",
            "gandengan_id",
            "supir_id",
            "supplier_id",
            "pengeluaranstok_nobukti",
            "penerimaanstok_nobukti",
            "penerimaan_nobukti",
            "hutangbayar_nobukti",
            "servicein_nobukti",
            "kerusakan_id",
            "statusformat",
            "statuspotongretur",
            "bank_id",
            "tglkasmasuk",
            "modifiedby",
        );
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "tglbukti",
            "pengeluaranstok_id",
            "trado_id",
            "gudang_id",
            "gandengan_id",
            "supir_id",
            "supplier_id",
            "pengeluaranstok_nobukti",
            "penerimaanstok_nobukti",
            "penerimaan_nobukti",
            "hutangbayar_nobukti",
            "servicein_nobukti",
            "kerusakan_id",
            "statusformat",
            "statuspotongretur",
            "bank_id",
            "tglkasmasuk",
            "modifiedby",
        ], $models);

        return  $temp;
    }

    public function selectColumns($query)
    {
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.pengeluaranstok_id",
            "$this->table.trado_id",
            "$this->table.gandengan_id",
            "$this->table.gudang_id",
            "$this->table.supir_id",
            "$this->table.supplier_id",
            "$this->table.pengeluaranstok_nobukti",
            "$this->table.penerimaanstok_nobukti",
            "$this->table.penerimaan_nobukti",
            "$this->table.hutangbayar_nobukti",
            "$this->table.servicein_nobukti",
            "$this->table.kerusakan_id",
            "$this->table.statuscetak",
            "$this->table.statusformat",
            "$this->table.statuspotongretur",
            "$this->table.bank_id",
            "$this->table.tglkasmasuk",
            "$this->table.modifiedby",
            "$this->table.created_at",
            "$this->table.updated_at",
            "kerusakan.keterangan as kerusakan",
            "bank.namabank as bank",
            "pengeluaranstok.kodepengeluaran as pengeluaranstok",
            "trado.kodetrado as trado",
            "gudang.gudang as gudang",
            "gandengan.keterangan as gandengan",
            "supir.namasupir as supir",
            "supplier.namasupplier as supplier",
        );
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('gudang', 'pengeluaranstokheader.gudang_id', 'gudang.id')
            ->leftJoin('gandengan', 'pengeluaranstokheader.gandengan_id', 'gandengan.id')
            ->leftJoin('pengeluaranstok', 'pengeluaranstokheader.pengeluaranstok_id', 'pengeluaranstok.id')
            ->leftJoin('trado', 'pengeluaranstokheader.trado_id', 'trado.id')
            ->leftJoin('supplier', 'pengeluaranstokheader.supplier_id', 'supplier.id')
            ->leftJoin('kerusakan', 'pengeluaranstokheader.kerusakan_id', 'kerusakan.id')
            ->leftJoin('bank', 'pengeluaranstokheader.bank_id', 'bank.id')
            ->leftJoin('penerimaanstokheader as penerimaan', 'pengeluaranstokheader.penerimaanstok_nobukti', 'penerimaan.nobukti')
            ->leftJoin('penerimaanheader', 'pengeluaranstokheader.penerimaan_nobukti', 'penerimaanheader.nobukti')
            ->leftJoin('hutangbayarheader', 'pengeluaranstokheader.hutangbayar_nobukti', 'hutangbayarheader.nobukti')
            ->leftJoin('pengeluaranstokheader as pengeluaran', 'pengeluaranstokheader.pengeluaranstok_nobukti', 'pengeluaran.nobukti')
            // ->leftJoin('servicein','pengeluaranstokheader.servicein_nobukti','servicein.nobukti')
            ->leftJoin('supir', 'pengeluaranstokheader.supir_id', 'supir.id');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
