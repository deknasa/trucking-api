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
            ->whereRaw("isnull(b.nobukti,'')=''")
            ->whereRaw("format(a.tglbukti,'MM-yyyy')='" . $periode . "'");

            // dd($querytemp->tosql());

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
                db::raw("isnull(b.kodeagen,'') as agen_id"),
                'a.nominal',
                'a.tglbayar',
                'a.bayar',
                'a.sisa',
            )
            ->leftjoin(db::raw("agen  b with (readuncommitted)"),'a.agen_id','b.id')
            ->orderBy('a.nobukti', 'asc');


            // dd($query->get());
        $this->filter($query);
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
        $this->sort($query);
        $this->paginate($query);
        $data = $query->get();
        // dd($this->totalPages);
        return $data;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] != '') {
                            if ($filters['field'] == 'agen_id') {
                                $query = $query->where('b.kodeagen', '=', $filters['data']);
                            } else {
                                // $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                                $query = $query->whereRaw("a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
                            }
                        }
                    }

                    break;
                case "OR":
                    $query = $query->where(function ($query) {

                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] != '') {
                                if ($filters['field'] == 'agen_id') {
                                    $query = $query->orWhere('b.kodeagen', '=', $filters['data']);
                                } else {
                                    $query = $query->orWhereRaw( "a.[" .  $filters['field'] . "] LIKE '%" . escapeLike($filters['data']) . "%' escape '|'");
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

        return $query;
    }

    public function sort($query)
    {
       
        return $query->orderBy('a.'.$this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function getinvoicelunas($id)
    {
        $query = db::table("invoiceheader")->from(db::raw("invoiceheader a with (readuncommitted)"))
        ->select(
            'c.id',
            'a.id as invoiceheader_id',
            'a.nobukti',
            'a.tglbukti',
            db::raw("isnull(b.kodeagen,'') as agen"),
            db::raw("isnull(b.id,0) as agen_id"),
            'a.nominal',
            db::raw("isnull(c.tglbayar,format(getdate(),'yyyy/MM/dd')) as tglbayar"),
            db::raw("isnull(c.bayar,0) as bayar"),
            db::raw("(isnull(a.nominal,0)-isnull(c.nominal,0)) as sisa"),
        )
        ->leftjoin(db::raw("agen b with (readuncommitted)"),'a.agen_id','b.id')
        ->leftjoin(db::raw("invoicelunaskepusat c with (readuncommitted)"),'a.nobukti','c.nobukti')
        ->where('a.id',$id);


        return $query->first();
    }

    public function processStore(array $data)
    {
        $InvoiceLunaskePusat = new InvoiceLunasKePusat();
        $InvoiceLunaskePusat->invoiceheader_id = $data['invoiceheader_id'];
        $InvoiceLunaskePusat->nobukti = $data['nobukti'] ?? '';
        $InvoiceLunaskePusat->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $InvoiceLunaskePusat->agen_id =$data['agen_id'];
        $InvoiceLunaskePusat->nominal =$data['nominal'];
        $InvoiceLunaskePusat->tglbayar = date('Y-m-d', strtotime($data['tglbayar']));
        $InvoiceLunaskePusat->bayar =$data['bayar'];
        $InvoiceLunaskePusat->sisa =$data['sisa'];
        $InvoiceLunaskePusat->modifiedby = auth('api')->user()->name;
        $InvoiceLunaskePusat->info = html_entity_decode(request()->info);
        // $request->sortname = $request->sortname ?? 'id';
        // $request->sortorder = $request->sortorder ?? 'asc';

        if (!$InvoiceLunaskePusat->save()) {
            throw new \Exception("Error Simpan Invoice Lunas ke Pusat");
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($InvoiceLunaskePusat->getTable()),
            'postingdari' => 'ENTRY INVOICE LUNAS KE PUSAT',
            'idtrans' => $InvoiceLunaskePusat->id,
            'nobuktitrans' => $InvoiceLunaskePusat->id,
            'aksi' => 'ENTRY',
            'datajson' => $InvoiceLunaskePusat->toArray(),
            'modifiedby' => $InvoiceLunaskePusat->modifiedby
        ]);

        return $InvoiceLunaskePusat;
    }
    public function processUpdate(InvoiceLunaskePusat $InvoiceLunaskePusat, array $data)
    {

        $InvoiceLunaskePusat->invoiceheader_id = $data['invoiceheader_id'];
        $InvoiceLunaskePusat->nobukti = $data['nobukti'] ?? '';
        $InvoiceLunaskePusat->tglbukti = date('Y-m-d', strtotime($data['tglbukti']));
        $InvoiceLunaskePusat->agen_id =$data['agen_id'];
        $InvoiceLunaskePusat->nominal =$data['nominal'];
        $InvoiceLunaskePusat->tglbayar = date('Y-m-d', strtotime($data['tglbayar']));
        $InvoiceLunaskePusat->bayar =$data['bayar'];
        $InvoiceLunaskePusat->sisa =$data['sisa'];
        $InvoiceLunaskePusat->modifiedby = auth('api')->user()->name;
        $InvoiceLunaskePusat->info = html_entity_decode(request()->info);


        if (!$InvoiceLunaskePusat->save()) {
            throw new \Exception("Error update Invoice Lunas ke Pusat.");
        }
        (new LogTrail())->processStore([
            'namatabel' => strtoupper($InvoiceLunaskePusat->getTable()),
            'postingdari' => 'EDIT INVOICE LUNAS KE PUSAT',
            'idtrans' => $InvoiceLunaskePusat->id,
            'nobuktitrans' => $InvoiceLunaskePusat->id,
            'aksi' => 'EDIT',
            'datajson' => $InvoiceLunaskePusat->toArray(),
            'modifiedby' => $InvoiceLunaskePusat->modifiedby
        ]);

        return $InvoiceLunaskePusat;
    }


    public function processDestroy($id)
    {
        // $AbsensiSupirHeader = AbsensiSupirHeader::where('id', $AbsensiSupirDetail->absensi_id)->first();
        $InvoiceLunaskePusat = new InvoiceLunasKePusat();
        $InvoiceLunaskePusat = $InvoiceLunaskePusat->lockAndDestroy($id);

        (new LogTrail())->processStore([
            'namatabel' => strtoupper($InvoiceLunaskePusat->getTable()),
            'postingdari' => 'DELETE INVOICE LUNAS KE PUSAT',
            'idtrans' => $InvoiceLunaskePusat->id,
            'nobuktitrans' => $InvoiceLunaskePusat->id,
            'aksi' => 'DELETE',
            'datajson' => $InvoiceLunaskePusat->toArray(),
            'modifiedby' => auth('api')->user()->user
        ]);
        return $InvoiceLunaskePusat;
    }

}
