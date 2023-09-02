<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class HutangBayarDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangbayardetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    public function getAll($id)
    {

        $query = DB::table('hutangbayardetail')->from(DB::raw("hutangbayardetail with (readuncommitted)"))
            ->select(
                'hutangbayardetail.nominal',
                'hutangbayardetail.hutang_nobukti',
                'hutangbayardetail.cicilan',
                'hutangbayardetail.potongan',
                'hutangbayardetail.keterangan',

            )

            ->where('hutangbayar_id', '=', $id);

        $data = $query->get();

        return $data;
    }
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));



        if (isset(request()->forReport) && request()->forReport) {

            $temphutangbayar = '##temhutangbayar' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphutangbayar, function ($table) {
                $table->string('hutang_nobukti', 1000)->nullable();
                $table->string('spb_nobukti', 1000)->nullable();
            });

            $querydata = DB::table('hutangbayardetail')->from(
                DB::raw("hutangbayardetail a with (readuncommitted)")
            )
                ->select(
                    'a.hutang_nobukti',
                    db::raw("(case when isnull(d.nobukti,'')<>'' then d.nobukti 
                                   when isnull(e.nobukti,'')<>'' then e.nobukti 
                                   else '' end) as spb_nobukti
                    ")
                )
                ->leftjoin(DB::raw("penerimaanstokheader as d with (readuncommitted)"), 'a.hutang_nobukti', 'd.hutang_nobukti')
                ->leftjoin(DB::raw("hutangextraheader as e with (readuncommitted)"), 'a.hutang_nobukti', 'e.hutang_nobukti')
                ->where('hutangbayar_id', '=', request()->hutangbayar_id);


            DB::table($temphutangbayar)->insertUsing([
                'hutang_nobukti',
                'spb_nobukti',
            ], $querydata);


            $temphutang = '##temhutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temphutang, function ($table) {
                $table->string('hutang_nobukti', 1000)->nullable();
                $table->date('tgljatuhtempo')->nullable();
                $table->float('nominal', 15, 2)->nullable();
                $table->string('spb_nobukti', 1000)->nullable();
            });

            $queryrekap = DB::table('hutangdetail')->from(
                DB::raw("hutangdetail a with (readuncommitted)")
            )
                ->select(
                    'b.nobukti as hutang_nobukti',
                    db::raw("max(isnull(a.tgljatuhtempo,'1900/1/1')) as tgljatuhtempo"),
                    db::raw("sum(isnull(a.total,0)) as nominal"),
                    db::raw("max(isnull(c.spb_nobukti,'')) as spb_nobukti")
                )
                ->join(DB::raw("hutangheader as b with (readuncommitted)"), 'a.hutang_id', 'b.id')
                ->join(DB::raw($temphutangbayar . " c "), 'b.nobukti', 'c.hutang_nobukti')
                ->groupby('b.nobukti');

            DB::table($temphutang)->insertUsing([
                'hutang_nobukti',
                'tgljatuhtempo',
                'nominal',
                'spb_nobukti',
            ], $queryrekap);


            // 

            $temppelunasanhutang = '##temppelunasanhutang' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temppelunasanhutang, function ($table) {
                $table->string('hutang_nobukti', 1000)->nullable();
                $table->float('nominal')->nullable();
            });

            $querydatapelunasan = DB::table('hutangbayardetail')->from(
                DB::raw("hutangbayardetail a with (readuncommitted)")
            )
                ->select(
                    'a.hutang_nobukti',
                    DB::raw("sum(isnull(a.nominal,0)+isnull(a.potongan,0)) as nominal"),
                )
                ->join(DB::raw($temphutang . " as b "), 'a.hutang_nobukti', 'b.hutang_nobukti')
                ->where('a.hutangbayar_id', '<=', request()->hutangbayar_id)
                ->groupby('a.hutang_nobukti');

            DB::table($temppelunasanhutang)->insertUsing([
                'hutang_nobukti',
                'nominal',
            ], $querydatapelunasan);


            // 

            $temprekap = '##temrekap' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            Schema::create($temprekap, function ($table) {
                $table->string('hutang_nobukti', 1000)->nullable();
                $table->float('nominalhutang')->nullable();
                $table->float('nominalpelunasan')->nullable();
                $table->float('nominalsisa')->nullable();
            });

            $queryrekap = DB::table($temphutang)->from(
                DB::raw($temphutang . " a ")
            )
                ->select(
                    'a.hutang_nobukti',
                    DB::raw("isnull(a.nominal,0) as nominalhutang"),
                    DB::raw("isnull(b.nominal,0) as nominalpelunasan"),
                    DB::raw("(isnull(a.nominal,0)-isnull(b.nominal,0)) as nominalsisa"),
                )
                ->leftjoin(DB::raw($temppelunasanhutang . " as b "), 'a.hutang_nobukti', 'b.hutang_nobukti');


            DB::table($temprekap)->insertUsing([
                'hutang_nobukti',
                'nominalhutang',
                'nominalpelunasan',
                'nominalsisa',
            ], $queryrekap);
            //  dd( DB::table($temphutang)->get());

            $query->select(
                $this->table . '.nominal as nominaLbayar',
                $this->table . '.keterangan',
                $this->table . '.hutang_nobukti',
                DB::raw("isnull(b.spb_nobukti,'') as spb_nobukti"),
                DB::raw("isnull(B.nominal,0) as nominalhutang"),
                DB::raw("isnull(" . $this->table . ".potongan,0) as diskon"),
                DB::raw("(case when isnull(" . $this->table . ".potongan,0)=0 then '' else 'POTONGAN HUTANG' END)  as keterangandiskon"),
                DB::raw("isnull(c.nominalsisa,0) as sisahutang"),
                DB::raw("(case when year(isnull(b.tgljatuhtempo,'1900/1/1'))=1900 then null else isnull(b.tgljatuhtempo,'1900/1/1') end)  as tgljatuhtempo"),
            )
                ->leftJoin(DB::raw($temphutang . " as b"), $this->table . '.hutang_nobukti', 'b.hutang_nobukti')
                ->leftjoin(DB::raw($temprekap . " as c "), $this->table . '.hutang_nobukti', 'c.hutang_nobukti')
                ->where($this->table . '.hutangbayar_id', '=', request()->hutangbayar_id);
        } else {
            $query->select(
                $this->table . '.nobukti',
                $this->table . '.nominal',
                $this->table . '.keterangan',
                DB::raw("isnull($this->table.potongan,0) as potongan"),
                $this->table . '.hutang_nobukti'
            );
            $this->sort($query);
            $query->where($this->table . '.hutangbayar_id', '=', request()->hutangbayar_id);
            $this->filter($query);

            $this->totalNominal = $query->sum('nominal');
            $this->totalPotongan = $query->sum('potongan');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($query);
        }

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
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {

                            $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
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

        return $query;
    }
    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(HutangBayarHeader $hutangBayarHeader, array $data): HutangBayarDetail
    {


        $hutangBayarDetail = new HutangBayarDetail();
        $hutangBayarDetail->hutangbayar_id = $data['hutangbayar_id'];
        $hutangBayarDetail->nobukti = $data['nobukti'];
        $hutangBayarDetail->nominal = $data['nominal'];
        $hutangBayarDetail->hutang_nobukti = $data['hutang_nobukti'];
        $hutangBayarDetail->cicilan = $data['cicilan'];
        $hutangBayarDetail->potongan = $data['potongan'];
        $hutangBayarDetail->keterangan = $data['keterangan'];
        $hutangBayarDetail->modifiedby = $hutangBayarHeader->modifiedby;


        if (!$hutangBayarDetail->save()) {
            throw new \Exception("Error storing Pengeluaran Detail.");
        }

        return $hutangBayarDetail;
    }
}
