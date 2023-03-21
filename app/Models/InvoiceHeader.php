<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceHeader extends MyModel
{
    use HasFactory;

    protected $table = 'invoiceheader';

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

        $query = DB::table($this->table)->from(DB::raw("invoiceheader with (readuncommitted)"))
            ->select(
                'invoiceheader.id',
                'invoiceheader.nobukti',
                'invoiceheader.tglbukti',
                'invoiceheader.nominal',
                'invoiceheader.tglterima',
                'invoiceheader.tgljatuhtempo',
                'agen.namaagen as agen_id',
                'agen.namaagen as agen',
                'jenisorder.keterangan as jenisorder_id',
                'cabang.namacabang as cabang_id',
                'invoiceheader.piutang_nobukti',
                'statusapproval.memo as statusapproval',
                'statuscetak.memo as statuscetak',
                'invoiceheader.userapproval',
                DB::raw('(case when (year(invoiceheader.tglapproval) <= 2000) then null else invoiceheader.tglapproval end ) as tglapproval'),
                'invoiceheader.userbukacetak',
                'invoiceheader.jumlahcetak',
                DB::raw('(case when (year(invoiceheader.tglbukacetak) <= 2000) then null else invoiceheader.tglbukacetak end ) as tglbukacetak'),
                'invoiceheader.modifiedby',
                'invoiceheader.created_at',
                'invoiceheader.updated_at'
            )
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'invoiceheader.cabang_id', 'cabang.id');

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
        $query = InvoiceHeader::from(DB::raw("invoiceheader with (readuncommitted)"))
            ->select(
                'invoiceheader.*',
                'agen.namaagen as agen',
                'jenisorder.keterangan as jenisorder'
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id')
            ->where('invoiceheader.id', $id);
        $data = $query->first();
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
                $this->table.nominal,
                $this->table.tglterima,
                $this->table.tgljatuhtempo,
                'agen.namaagen as agen_id',
                'jenisorder.keterangan as jenisorder_id',
                'cabang.namacabang as cabang_id',
                $this->table.piutang_nobukti,
                'statusapproval.text as statusapproval',
                $this->table.userapproval,
                $this->table.tglapproval,
                'statuscetak.text as statuscetak',
                $this->table.userbukacetak,
                $this->table.tglbukacetak,
                $this->table.jumlahcetak,
                $this->table.modifiedby,
                $this->table.created_at,
                $this->table.updated_at
                "
                )
            )
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'invoiceheader.agen_id', 'agen.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'invoiceheader.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("parameter as statusapproval with (readuncommitted)"), 'invoiceheader.statusapproval', 'statusapproval.id')
            ->leftJoin(DB::raw("parameter as statuscetak with (readuncommitted)"), 'invoiceheader.statuscetak', 'statuscetak.id')
            ->leftJoin(DB::raw("cabang with (readuncommitted)"), 'invoiceheader.cabang_id', 'cabang.id');
    }

    public function createTemp(string $modelTable)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('nobukti', 1000)->nullable();
            $table->date('tglbukti')->nullable();
            $table->bigInteger('nominal')->nullable();
            $table->date('tglterima')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->string('agen_id')->default();
            $table->string('jenisorder_id')->default();
            $table->string('cabang_id')->default();
            $table->string('piutang_nobukti')->default();
            $table->string('statusapproval')->nullable();
            $table->string('userapproval')->default();
            $table->date('tglapproval')->nullable();
            $table->string('statuscetak', 1000)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby')->default();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->increments('position');
        });

        $this->setRequestParameters();
        $query = DB::table($modelTable);
        $query = $this->selectColumns($query);
        $this->sort($query);
        $models = $this->filter($query);
        DB::table($temp)->insertUsing(['id', 'nobukti', 'tglbukti', 'nominal', 'tglterima', 'tgljatuhtempo', 'agen_id', 'jenisorder_id', 'cabang_id', 'piutang_nobukti', 'statusapproval', 'userapproval', 'tglapproval', 'statuscetak', 'userbukacetak', 'tglbukacetak', 'jumlahcetak', 'modifiedby', 'created_at', 'updated_at'], $models);

        return $temp;
    }

    public function getSP($request)
    {
        $temp = $this->createTempSP($request);
        // dd(DB::table($temp)->get());
        $query = SuratPengantar::from(DB::raw("suratpengantar as sp with (readuncommitted)"))
            ->select(DB::raw("$temp.id,$temp.jobtrucking,sp.tglsp, sp.keterangan,jenisorder.keterangan as jenisorder_id, agen.namaagen as agen_id, sp.statuslongtrip, ot.statusperalihan, (case when ot.nocont IS NULL then '-' else ot.nocont end) as nocont, 
            (case when tarif.tujuan IS NULL then '-' else tarif.tujuan end) as tarif_id,
            ot.nominal as omset"))
            ->Join(DB::raw("$temp with (readuncommitted)"), 'sp.id', "$temp.id")
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'sp.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'ot.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->whereRaw("sp.jobtrucking not in(select orderantrucking_nobukti from invoicedetail)")
            ->orderBy("sp.jobtrucking",'asc');

        $data = $query->get();
        return $data;
    }

    public function createTempSP($request)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $fetch = SuratPengantar::from(DB::raw("suratpengantar"))
            ->select(DB::raw("min(id) as id, jobtrucking"))
            ->where('agen_id', $request->agen_id)
            ->where('jenisorder_id', $request->jenisorder_id)
            ->where('tglbukti', '>=', date('Y-m-d', strtotime($request->tgldari)))
            ->where('tglbukti', '<=', date('Y-m-d', strtotime($request->tglsampai)))
            ->groupBy('jobtrucking');
        // ->get();
        
        Schema::create($temp, function ($table) {
            $table->bigInteger('id')->nullable();
            $table->string('jobtrucking');
        });

        $tes = DB::table($temp)->insertUsing(['id', 'jobtrucking'], $fetch);

        // $data = DB::table($temp)->get();
        return $temp;
    }

    public function getEdit($id, $request)
    {
        $temp = $this->createTempSP($request);

        $query = InvoiceDetail::from(DB::raw("invoicedetail with (readuncommitted)"))
            ->select(DB::raw("$temp.id,$temp.jobtrucking,sp.tglsp, sp.keterangan,jenisorder.keterangan as jenisorder_id, agen.namaagen as agen_id, sp.statuslongtrip, ot.statusperalihan, ot.nocont, tarif.tujuan as tarif_id, ot.nominal as omset, invoicedetail.nominalretribusi"))

            ->leftJoin(DB::raw("suratpengantar as sp with (readuncommitted)"), 'invoicedetail.orderantrucking_nobukti', 'sp.jobtrucking')
            ->Join(DB::raw("$temp with (readuncommitted)"), 'sp.id', "$temp.id")
            ->leftJoin(DB::raw("orderantrucking as ot with (readuncommitted)"), 'sp.jobtrucking', 'ot.nobukti')
            ->leftJoin(DB::raw("tarif with (readuncommitted)"), 'ot.tarif_id', 'tarif.id')
            ->leftJoin(DB::raw("jenisorder with (readuncommitted)"), 'sp.jenisorder_id', 'jenisorder.id')
            ->leftJoin(DB::raw("agen with (readuncommitted)"), 'sp.agen_id', 'agen.id')
            ->whereRaw("invoicedetail.invoice_id = $id");

        $data = $query->get();
        return $data;
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
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->where('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->where('statuscetak.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'agen_id') {
                            $query = $query->where('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jenisorder_id') {
                            $query = $query->where('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->where('cabang.namacabang', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'statusapproval') {
                            $query = $query->orWhere('statusapproval.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'statuscetak') {
                            $query = $query->orWhere('statuscetak.text', '=', $filters['data']);
                        } else if ($filters['field'] == 'agen_id') {
                            $query = $query->orWhere('agen.namaagen', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'jenisorder_id') {
                            $query = $query->orWhere('jenisorder.keterangan', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'cabang_id') {
                            $query = $query->orWhere('cabang.namacabang', 'LIKE', "%$filters[data]%");
                        } else {
                            $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        if (request()->approve && request()->periode) {
            $query->where('invoiceheader.statusapproval', '<>', request()->approve)
                ->whereYear('invoiceheader.tglbukti', '=', request()->year)
                ->whereMonth('invoiceheader.tglbukti', '=', request()->month);
            return $query;
        }
        if (request()->cetak && request()->periode) {
            $query->where('invoiceheader.statuscetak', '<>', request()->cetak)
                ->whereYear('invoiceheader.tglbukti', '=', request()->year)
                ->whereMonth('invoiceheader.tglbukti', '=', request()->month);
            return $query;
        }
        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function jenisorder()
    {
        return $this->belongsTo(JenisOrder::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id');
    }
}
