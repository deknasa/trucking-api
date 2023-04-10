<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PencairanGiroPengeluaranHeader extends MyModel
{
    use HasFactory;

    protected $table = 'PencairanGiroPengeluaranHeader';
    protected $anotherTable = 'pengeluaranheader';
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
        $periode = request()->periode ?? date('m-Y');
        $month = substr($periode, 0, 2);
        $year = substr($periode, 3);

        $alatBayar = AlatBayar::from(DB::raw("alatbayar with (readuncommitted)"))->where('kodealatbayar', 'GIRO')->first();
        $query = DB::table($this->anotherTable)->from(DB::raw("pengeluaranheader with (readuncommitted)"))
            ->select(
                DB::raw("pengeluaranheader.nobukti as pengeluaran_nobukti,pengeluaranheader.id, pengeluaranheader.dibayarke, bank.namabank as bank_id, pengeluaranheader.transferkeac, pengeluaranheader.modifiedby, pengeluaranheader.created_at,pengeluaranheader.updated_at, alatbayar.namaalatbayar as alatbayar_id, pgp.nobukti, pgp.tglbukti, parameter.memo as statusapproval, (SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
        WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti and pengeluaranheader.alatbayar_id=$alatBayar->id) as nominal")
            )
            ->distinct('pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pengeluarandetail with (readuncommitted)"), 'pengeluarandetail.nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("pencairangiropengeluaranheader as pgp with (readuncommitted)"), 'pgp.pengeluaran_nobukti', 'pengeluaranheader.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'pgp.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("bank with (readuncommitted)"), 'pengeluaranheader.bank_id', 'bank.id')
            ->leftJoin(DB::raw("alatbayar with (readuncommitted)"), 'pengeluaranheader.alatbayar_id', 'alatbayar.id')
            ->whereRaw("MONTH(pengeluaranheader.tglbukti) = $month")
            ->whereRaw("YEAR(pengeluaranheader.tglbukti) = $year")
            ->where('pengeluaranheader.alatbayar_id', $alatBayar->id);

        $this->sort($query, 'pengeluaranheader');
        $this->filter($query, 'pengeluaranheader');
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
            $this->table.keterangan,
            $this->table.pengeluaran_nobukti,
            statusapproval.text as statusapproval,
            $this->table.userapproval,
            $this->table.tglapproval,
            $this->table.modifiedby,
            $this->table.created_at,
            $this->table.updated_at"
                )
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'pencairangiropengeluaranheader.statusapproval', 'statusapproval.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti', 1000)->nullable();
            $table->string('keterangan', 1000)->nullable();
            $table->string('pengeluaran_nobukti', 1000)->nullable();
            $table->string('statusapproval', 1000)->nullable();
            $table->string('userapproval', 1000)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query, 'pencairangiropengeluaranheader');
        $models = $this->filter($query, 'pencairangiropengeluaranheader');
        DB::table($temp)->insertUsing([
            'id', 'nobukti', 'tglbukti', 'keterangan', 'pengeluaran_nobukti', 'statusapproval', 'userapproval', 'tglapproval', 'modifiedby', 'created_at', 'updated_at'
        ], $models);


        return  $temp;
    }


    public function sort($query, $table)
    {
        if ($this->params['sortIndex'] == 'bank_id') {
            return $query->orderBy('bank.namabank', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'alatbayar_id') {
            return $query->orderBy('alatbayar.keterangan', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nobukti') {
            return $query->orderBy('pgp.nobukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'tglbukti') {
            return $query->orderBy('pgp.tglbukti', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'pengeluaran_nobukti') {
            return $query->orderBy('pengeluaranheader.nobukti', $this->params['sortOrder']);
        } else {
            return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('parameter.text', '=', "$filters[data]");
                        } else if ($filters['field'] == 'bank_id') {
                            $query = $query->where('bank.namabank', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'alatbayar_id') {
                            $query = $query->where('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nobukti') {
                            $query = $query->where('pgp.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'tglbukti') {
                            $query->whereRaw("format(pgp.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'pengeluaran_nobukti') {
                            $query = $query->where('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format((SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
                            WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti), '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":

                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'statusapproval') {
                                $query = $query->orWhere('parameter.text', '=', "$filters[data]");
                            } else if ($filters['field'] == 'bank_id') {
                                $query = $query->orWhere('bank.namabank', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'alatbayar_id') {
                                $query = $query->orWhere('alatbayar.namaalatbayar', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nobukti') {
                                $query = $query->orWhere('pgp.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query->orWhereRaw("format(pgp.tglbukti, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'pengeluaran_nobukti') {
                                $query = $query->orWhere('pengeluaranheader.nobukti', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal') {
                                $query = $query->orWhereRaw("format((SELECT (SUM(pengeluarandetail.nominal)) FROM pengeluarandetail 
                            WHERE pengeluarandetail.nobukti= pengeluaranheader.nobukti), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                                $query = $query->orWhereRaw("format(" . $this->anotherTable . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->anotherTable . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
}
