<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaDebetHeader extends MyModel
{
    use HasFactory;

    protected $table = 'notadebetheader';

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
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
            )
            ->leftJoin(DB::raw("pelunasanpiutangheader as pelunasanpiutang with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti')
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'notadebetheader.statuscetak', 'statuscetak.id');


        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->string('pelunasanpiutang_nobukti', 50)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('postingdari', 50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->date('tgllunas')->default('1900/1/1');
            $table->string('userapproval', 50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->increments('position');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
        });

        $query = DB::table($modelTable);
        $query = $this->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->select(
                "id",
                "nobukti",
                "pelunasanpiutang_nobukti",
                "tglbukti",
                "postingdari",
                "statusapproval",
                "tgllunas",
                "userapproval",
                "tglapproval",
                "statusformat",
                "modifiedby",
            );
        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            "id",
            "nobukti",
            "pelunasanpiutang_nobukti",
            "tglbukti",
            "postingdari",
            "statusapproval",
            "tgllunas",
            "userapproval",
            "tglapproval",
            "statusformat",
            "modifiedby",
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
                "$this->table.pelunasanpiutang_nobukti",
                "$this->table.tglbukti",
                "$this->table.postingdari",
                "$this->table.statusapproval",
                "$this->table.tgllunas",
                "$this->table.userapproval",
                "$this->table.tglapproval",
                "$this->table.statusformat",
                "$this->table.statuscetak",
                "statuscetak.memo as statuscetak_memo",
                "$this->table.modifiedby",
                "parameter.memo as  statusapproval_memo",

            );
    }


    public function getNotaDebet($id)
    {
        $this->setRequestParameters();

        $query = DB::table('pelunasanpiutangdetail')->from(
            DB::raw("pelunasanpiutangdetail with (readuncommitted)")
        )
            ->select(DB::raw('
        pelunasanpiutangdetail.id as detail_id,
        pelunasanpiutangdetail.nobukti,
        pelunasanpiutangdetail.tglcair,
        pelunasanpiutangdetail.nominal as nominalbayar,
        pelunasanpiutangdetail.nominal as nominal,
        pelunasanpiutangdetail.piutang_nobukti,
        pelunasanpiutangdetail.invoice_nobukti,
        notadebetheader.keterangan,
        pelunasanpiutangdetail.coalebihbayar,
        COALESCE (pelunasanpiutangdetail.nominallebihbayar, 0) as lebihbayar '))

            ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'piutangheader.nobukti', 'pelunasanpiutangdetail.piutang_nobukti')
            ->leftJoin(DB::raw("notadebetheader with (readuncommitted)"), 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutangdetail.nobukti')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'pelunasanpiutangdetail.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'pelunasanpiutangdetail.agen_id', 'agen.id')
            ->whereRaw(" EXISTS (
            SELECT notadebetheader.pelunasanpiutang_nobukti
            FROM notadebetdetail with (readuncommitted) 
			left join notadebetheader  with (readuncommitted) on notadebetdetail.notadebet_id = notadebetheader.id
            WHERE notadebetheader.pelunasanpiutang_nobukti = pelunasanpiutangdetail.nobukti   
          )")
            ->where('pelunasanpiutangdetail.nominallebihbayar', '>', 0)
            ->where('notadebetheader.id', $id);

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

        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        switch ($filters['field']) {
                            case 'statusapproval_memo':
                                $query = $query->where('parameter.memo', 'LIKE', "%$filters[data]%");
                                break;
                            default:
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                break;
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        switch ($filters['field']) {
                            case 'statusapproval_memo':
                                $query = $query->where('parameter.memo', 'LIKE', "%$filters[data]%");
                                break;

                            default:
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                break;
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }
        if (request()->cetak && request()->periode) {
            $query->where('notadebetheader.statuscetak', '<>', request()->cetak)
                ->whereYear('notadebetheader.tglbukti', '=', request()->year)
                ->whereMonth('notadebetheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }
    public function findAll($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
            ->leftJoin('parameter', 'notadebetheader.statusapproval', 'parameter.id')
            ->leftJoin('parameter as statuscetak', 'notadebetheader.statuscetak', 'statuscetak.id')
            ->leftJoin('pelunasanpiutangheader as pelunasanpiutang', 'notadebetheader.pelunasanpiutang_nobukti', 'pelunasanpiutang.nobukti');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
