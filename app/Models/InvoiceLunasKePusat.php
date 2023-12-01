<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceLunasKePusat extends MyModel
{
    use HasFactory;

    protected $table = 'invoicelunaskepusat';

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

        $periode = request()->periode ?? '01-1900';
        $this->setRequestParameters();

        $tempinvoice = '##tempinvoice' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($tempinvoice, function ($table) {
            $table->id();
            $table->integer('invoiceheader_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->date('tglbayar')->nullable();
            $table->double('bayar', 15, 2)->nullable();
            $table->double('sisa', 15, 2)->nullable();
        });

        $querytemp = db::table("invoicelunaskepust")->from(db::raw("invoicelunaskepusat a with (readuncommitted)"))
            ->select(
                'a.invoiceheader_id',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                'a.sisa',
            )
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'");

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'sisa',
        ], $querytemp);

        $querytemp = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
            ->select(
                'a.id as invoiceheader',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                db::raw("null as tglbayar"),
                db::raw("0 as bayar"),
                db::raw("a.nominal as sisa"),
            )
            ->leftjoin(db::raw($tempinvoice . " b "), 'a.nobukti', 'b.nobukti')
            ->whereRaw("isnull(a.nobukti,'')=''")
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'");

        DB::table($tempinvoice)->insertUsing([
            'invoiceheader_id',
            'nobukti',
            'tglbukti',
            'agen_id',
            'nominal',
            'tglbayar',
            'bayar',
            'sisa',
        ], $querytemp);

        $query = db::table($tempinvoice)->from(db::raw($tempinvoice . " a"))
            ->select(
                'a.invoiceheader_id',
                'a.nobukti',
                'a.tglbukti',
                'a.agen_id',
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                'a.sisa',
            )
            ->orderBy('a.nobukti', 'asc');


            dd($query->get());
        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        // dd($this->totalPages);
        return $data;
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {

                        // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            $query = $query->orWhereRaw( "a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

    public function sort($query)
    {
       
        return $query->orderBy('a.'.$this->params['sortIndex'], $this->params['sortOrder']);
    }
}
