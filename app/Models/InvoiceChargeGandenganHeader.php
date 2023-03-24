<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceChargeGandenganHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoicechargegandenganheader';

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

            ->whereBetween($this->table.'.tglbukti', [date('Y-m-d', strtotime(request()->tgldari)), date('Y-m-d', strtotime(request()->tglsampai))])
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoicechargegandenganheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'invoicechargegandenganheader.statusformat', 'statusformat.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoicechargegandenganheader.statuscetak', 'cetak.id')

            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoicechargegandenganheader.agen_id', 'agen.id');

        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->filter($query);
        $this->paginate($query);

        $data = $query->get();

        return $data;
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
                "$this->table.tglproses",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.statusapproval as statusapproval_id",
                "$this->table.userapproval",
                "$this->table.statusformat as statusformat_id",
                "$this->table.statuscetak as statuscetak_id",
                "$this->table.userbukacetak",
                "$this->table.tglbukacetak",
                "$this->table.jumlahcetak",
                "$this->table.modifiedby",
                "agen.namaagen as  agen",
                "parameter.memo as statusapproval",
                "cetak.memo as statuscetak",
                "statusformat.memo as  statusformat",
                "$this->table.created_at",
                "$this->table.updated_at",
                DB::raw('(case when (year(invoicechargegandenganheader.tglapproval) <= 2000) then null else invoicechargegandenganheader.tglapproval end ) as tglapproval'),



            );
    }

    public function createTemp(string $modelTable)
    {
        $this->setRequestParameters();

        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        Schema::create($temp, function ($table) {

            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->date('tglproses')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal')->nullable();
            $table->integer('statuscetak')->length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->dateTime('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->length(11)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
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
                "$this->table.tglproses",
                "$this->table.agen_id",
                "$this->table.nominal",
                "$this->table.statusapproval",
                "$this->table.userapproval",
                "$this->table.statusformat",
                "$this->table.userbukacetak",
                "$this->table.tglbukacetak",
                "$this->table.jumlahcetak",
                "$this->table.modifiedby"
            );

        $query = $this->sort($query);
        $models = $this->filter($query);
        $models =  $query->whereBetween($this->table.'.tglbukti', [date('Y-m-d', strtotime(request()->tgldariheader)), date('Y-m-d', strtotime(request()->tglsampaiheader))]);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'tglproses',
            'agen_id',
            'nominal',
            'statusapproval',
            'userapproval',
            'statusformat',
            'userbukacetak',
            'tglbukacetak',
            'jumlahcetak',
            'modifiedby',
        ], $models);

        return $temp;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)->from(
            DB::raw($this->table . " with (readuncommitted)")
        )
            ->leftJoin(DB::raw("parameter with (readuncommitted)"), 'invoicechargegandenganheader.statusapproval', 'parameter.id')
            ->leftJoin(DB::raw("parameter as statusformat with (readuncommitted)"), 'invoicechargegandenganheader.statusformat', 'statusformat.id')
            ->leftJoin(DB::raw("parameter as cetak with (readuncommitted)"), 'invoicechargegandenganheader.statuscetak', 'cetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoicechargegandenganheader.agen_id', 'agen.id');

        $data = $query->where("$this->table.id", $id)->first();
        return $data;
    }


    public function getInvoiceGandengan($id)
    {
        $query = DB::table('invoicechargegandengandetail')->from(DB::raw("invoicechargegandengandetail with (readuncommitted)"));

        $query->select(
            'invoicechargegandengandetail.id',
            'header.nobukti as nobukti_header',
            'header.tglbukti',
            'header.nominal as nominal_header',
            'invoicechargegandengandetail.jobtrucking',
            'invoicechargegandengandetail.tgltrip',
            'invoicechargegandengandetail.jumlahhari',
            'invoicechargegandengandetail.nominal as nominal_detail',
            'invoicechargegandengandetail.trado_id',
            'trado.kodetrado as nopolisi',
            'invoicechargegandengandetail.keterangan',
        )
            ->leftJoin(DB::raw("invoicechargegandenganheader as header with (readuncommitted)"), 'header.id', 'invoicechargegandengandetail.invoicechargegandengan_id')
            ->leftJoin(DB::raw("trado with (readuncommitted)"), 'trado.id', 'invoicechargegandengandetail.trado_id');


        $query->where('invoicechargegandengandetail.invoicechargegandengan_id', '=', $id);
        return $query->get();
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
                        $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }
                    break;
                case "OR":
                    $query = $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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
            $query->where('invoicechargegandenganheader.statusapproval', '<>', request()->approve)
                ->whereYear('invoicechargegandenganheader.tglbukti', '=', request()->year)
                ->whereMonth('invoicechargegandenganheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('invoicechargegandenganheader.statuscetak', '<>', request()->cetak)
                ->whereYear('invoicechargegandenganheader.tglbukti', '=', request()->year)
                ->whereMonth('invoicechargegandenganheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
