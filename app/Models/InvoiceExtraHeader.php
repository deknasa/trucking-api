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
        $query = $this->selectColumns($query)
        ->leftJoin('pelanggan','invoiceextraheader.pelanggan_id','pelanggan.id')
        ->leftJoin('agen','invoiceextraheader.agen_id','agen.id')
        ->leftJoin('parameter as statusformat','invoiceextraheader.statusformat','statusformat.id');

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

        $temp = '##temp' . rand(1, 10000);

        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->default('0');
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->double('nominal')->default('0');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->dateTime('created_at')->default('1900/1/1');
            $table->dateTime('updated_at')->default('1900/1/1');
            $table->increments('position');
        });

        $query = DB::table($modelTable);
        $query = $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.pelanggan_id",
            "$this->table.agen_id",
            "$this->table.nominal",
            "$this->table.keterangan",
            "$this->table.statusformat",
            "$this->table.modifiedby");

        $query = $this->sort($query);
        $models = $this->filter($query);

        DB::table($temp)->insertUsing([
            'id',
            'nobukti',
            'tglbukti',
            'pelanggan_id',
            'agen_id',
            'nominal',
            'keterangan',
            'statusformat',
            'modifiedby'
        ], $models);

        return $temp;
    }

    public function selectColumns($query)
    {        
        return $query->select(
            "$this->table.id",
            "$this->table.nobukti",
            "$this->table.tglbukti",
            "$this->table.pelanggan_id",
            "$this->table.agen_id",
            "$this->table.nominal",
            "$this->table.keterangan",
            "$this->table.statusformat",
            "$this->table.modifiedby",
            "statusformat.memo as  statusformat_memo",
            "pelanggan.namapelanggan as  pelanggan",
            "agen.namaagen as  agen",
        );
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
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                    }
                    break;
                default:
                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function find($id)
    {
        $this->setRequestParameters();

        $query = DB::table($this->table);
        $query = $this->selectColumns($query)
        ->leftJoin('parameter as statusformat','absensisupirapprovalheader.statusformat','statusformat.id');
        $data = $query->where("$this->table.id",$id)->first();
        return $data;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
