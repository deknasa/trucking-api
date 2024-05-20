<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PelunasanPiutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pelunasanpiutangdetail';

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

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        //  

        // 

        if (isset(request()->forReport) && request()->forReport) {

            $temppiutang = '##tempiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppiutang, function ($table) {
                $table->string('piutang_nobukti', 1000)->nullable();
                $table->float('nominal')->nullable();
            });

            $querydata = DB::table('pelunasanpiutangdetail')->from(
                DB::raw("pelunasanpiutangdetail a with (readuncommitted)")
            )
                ->select(
                    'a.piutang_nobukti',
                    'b.nominal',
                )
                ->join(DB::raw("piutangheader as b with (readuncommitted)"), 'a.piutang_nobukti', 'b.nobukti')
                ->where('a.pelunasanpiutang_id', '=', request()->pelunasanpiutang_id);


            DB::table($temppiutang)->insertUsing([
                'piutang_nobukti',
                'nominal',
            ], $querydata);

            $temppelunasanpiutang = '##tempelunasanpiutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppelunasanpiutang, function ($table) {
                $table->string('piutang_nobukti', 1000)->nullable();
                $table->float('nominal')->nullable();
            });

            $querydatapelunasan = DB::table('pelunasanpiutangdetail')->from(
                DB::raw("pelunasanpiutangdetail a with (readuncommitted)")
            )
                ->select(
                    'a.piutang_nobukti',
                    DB::raw("sum(isnull(a.nominal,0)+isnull(a.potongan,0)+isnull(a.potonganpph,0)) as nominal"),
                )
                ->join(DB::raw($temppiutang . " as b "), 'a.piutang_nobukti', 'b.piutang_nobukti')
                ->where('a.pelunasanpiutang_id', '<=', request()->pelunasanpiutang_id)
                ->groupby('a.piutang_nobukti');

            DB::table($temppelunasanpiutang)->insertUsing([
                'piutang_nobukti',
                'nominal',
            ], $querydatapelunasan);

            $temprekap = '##temrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temprekap, function ($table) {
                $table->string('piutang_nobukti', 1000)->nullable();
                $table->float('nominalpiutang')->nullable();
                $table->float('nominalpelunasan')->nullable();
                $table->float('nominalsisa')->nullable();
            });

            $queryrekap = DB::table($temppiutang)->from(
                DB::raw($temppiutang . " a ")
            )
                ->select(
                    'a.piutang_nobukti',
                    DB::raw("isnull(a.nominal,0) as nominalpiutang"),
                    DB::raw("isnull(b.nominal,0) as nominalpelunasan"),
                    DB::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominalsisa"),
                )
                ->leftjoin(DB::raw($temppelunasanpiutang . " as b "), 'a.piutang_nobukti', 'b.piutang_nobukti');


            DB::table($temprekap)->insertUsing([
                'piutang_nobukti',
                'nominalpiutang',
                'nominalpelunasan',
                'nominalsisa',
            ], $queryrekap);

            $query->select(
                $this->table . '.piutang_nobukti',
                $this->table . '.invoice_nobukti',
                $this->table . '.keteranganpotongan',
                $this->table . '.keteranganpotonganpph',
                'akunpusat.keterangancoa as coapotongan',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                $this->table . '.nominallebihbayar',
                $this->table . '.potongan',
                $this->table . '.potonganpph',
                DB::raw("isnull(b.nominalpiutang,0) as nominalpiutang"),
                DB::raw("isnull(b.nominalsisa,0) as sisapiutang"),
            )
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), $this->table . '.coapotongan', 'akunpusat.coa')
                ->leftjoin(DB::raw($temprekap . " as b "), $this->table . '.piutang_nobukti', 'b.piutang_nobukti');
            $query->where($this->table . '.pelunasanpiutang_id', '=', request()->pelunasanpiutang_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                $this->table . '.piutang_nobukti',
                $this->table . '.nominallebihbayar',
                $this->table . '.potongan',
                $this->table . '.keteranganpotongan',
                'akunpusat.keterangancoa as coapotongan',
                $this->table . '.potonganpph',
                $this->table . '.keteranganpotonganpph',
                'akunpusatpph.keterangancoa as coapotonganpph',
                $this->table . '.invoice_nobukti',
                db::raw("cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceheader"),
                db::raw("cast(cast(format((cast((format(invoice.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceheader"), 
                db::raw("cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderinvoiceextraheader"),
                db::raw("cast(cast(format((cast((format(invoiceextra.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderinvoiceextraheader"), 
                db::raw("cast((format(piutangheader.tglbukti,'yyyy/MM')+'/1') as date) as tgldariheaderpiutangheader"),
                db::raw("cast(cast(format((cast((format(piutangheader.tglbukti,'yyyy/MM')+'/1') as datetime)+32),'yyyy/MM')+'/01' as datetime)-1 as date) as tglsampaiheaderpiutangheader"), 
            )
                ->leftJoin(DB::raw("invoiceheader as invoice with (readuncommitted)"), 'pelunasanpiutangdetail.invoice_nobukti', '=', 'invoice.nobukti')
                ->leftJoin(DB::raw("invoiceextraheader as invoiceextra with (readuncommitted)"), 'pelunasanpiutangdetail.invoice_nobukti', '=', 'invoiceextra.nobukti')
                ->leftJoin(DB::raw("piutangheader with (readuncommitted)"), 'pelunasanpiutangdetail.piutang_nobukti', '=', 'piutangheader.nobukti')
                ->leftJoin(DB::raw("akunpusat with (readuncommitted)"), $this->table . '.coapotongan', 'akunpusat.coa')
                ->leftJoin(DB::raw("akunpusat as akunpusatpph with (readuncommitted)"), $this->table . '.coapotonganpph', 'akunpusatpph.coa');

            $this->sort($query);
            $query->where($this->table . '.pelunasanpiutang_id', '=', request()->pelunasanpiutang_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('pelunasanpiutangdetail.nominal');
            $this->totalPotongan = $query->sum('pelunasanpiutangdetail.potongan');
            $this->totalPotonganPPH = $query->sum('pelunasanpiutangdetail.potonganpph');
            $this->totalNominalLebih = $query->sum('pelunasanpiutangdetail.nominallebihbayar');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'coapotongan') {
            return $query->orderBy('akunpusat.keterangancoa', $this->params['sortOrder']);
        } else {
            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }
    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coapotongan') {
                                $query = $query->where('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else  if ($filters['field'] == 'coapotonganpph') {
                                $query = $query->where('akunpusatpph.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal' || $filters['field'] == 'potongan' || $filters['field'] == 'potonganpph' || $filters['field'] == 'nominallebihbayar') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'coapotongan') {
                                $query = $query->orWhere('akunpusat.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'coapotonganpph') {
                                $query = $query->orWhere('akunpusatpph.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominal' || $filters['field'] == 'potongan' || $filters['field'] == 'potonganpph' || $filters['field'] == 'nominallebihbayar') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", '#,#0.00') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
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

    public function processStore(PelunasanPiutangHeader $pelunasanPiutangHeader, array $data): PelunasanPiutangDetail
    {
        $pelunasanPiutangDetail = new PelunasanPiutangDetail();
        $pelunasanPiutangDetail->pelunasanpiutang_id = $pelunasanPiutangHeader->id;
        $pelunasanPiutangDetail->nobukti = $pelunasanPiutangHeader->nobukti;
        $pelunasanPiutangDetail->nominal = $data['nominal'];
        $pelunasanPiutangDetail->piutang_nobukti = $data['piutang_nobukti'];
        $pelunasanPiutangDetail->keterangan = $data['keterangan'];
        $pelunasanPiutangDetail->potongan = $data['potongan'];
        $pelunasanPiutangDetail->potonganpph = $data['potonganpph'];
        $pelunasanPiutangDetail->coapotongan = $data['coapotongan'];
        $pelunasanPiutangDetail->coapotonganpph = $data['coapotonganpph'];
        $pelunasanPiutangDetail->invoice_nobukti = $data['invoice_nobukti'];
        $pelunasanPiutangDetail->keteranganpotongan = $data['keteranganpotongan'];
        $pelunasanPiutangDetail->keteranganpotonganpph = $data['keteranganpotonganpph'];
        $pelunasanPiutangDetail->nominallebihbayar = $data['nominallebihbayar'];
        $pelunasanPiutangDetail->coalebihbayar = $data['coalebihbayar'];
        $pelunasanPiutangDetail->statusnotadebet = $data['statusnotadebet'];
        $pelunasanPiutangDetail->statusnotakredit = $data['statusnotakredit'];

        $pelunasanPiutangDetail->modifiedby = auth('api')->user()->name;
        $pelunasanPiutangDetail->info = html_entity_decode(request()->info);

        if (!$pelunasanPiutangDetail->save()) {
            throw new \Exception("Error storing pelunasan piutang detail.");
        }

        return $pelunasanPiutangDetail;
    }
}
