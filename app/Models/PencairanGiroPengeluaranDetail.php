<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PencairanGiroPengeluaranDetail extends MyModel
{
    use HasFactory;

    protected $anotherTable = 'pengeluarandetail';
    protected $table = 'pencairangiropengeluarandetail';

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
        $nobukti = request()->nobukti;
        $cekAsal = substr($nobukti, 0, 3);
        $status = request()->status;
        $templist = '##templist' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templist, function ($table) {
            $table->integer('id')->nullable();
            $table->string('nobukti', 300)->nullable();
            $table->string('nowarkat', 300)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal')->nullable();
            $table->longText('coadebet')->nullable();
            $table->longText('coakredit')->nullable();
            $table->longText('keterangan')->nullable();
            $table->date('bulanbeban')->nullable();
        });
        if ($status == 591) {
            if ($cekAsal == 'PBT') {
                $query1 = DB::table("pindahbuku")->from(DB::raw("pindahbuku with (readuncommitted)"))
                    ->select(
                        'id',
                        'nobukti',
                        'nowarkat',
                        'tgljatuhtempo',
                        'nominal',
                        'coadebet.keterangancoa as coadebet',
                        'coakredit.keterangancoa as coakredit',
                        'keterangan'
                    )
                    ->leftJoin('akunpusat as coadebet', 'pindahbuku.coadebet', 'coadebet.coa')
                    ->leftJoin('akunpusat as coakredit', 'pindahbuku.coakredit', 'coakredit.coa')
                    ->where('nobukti', $nobukti);


                DB::table($templist)->insertUsing([
                    'id',
                    'nobukti',
                    'nowarkat',
                    'tgljatuhtempo',
                    'nominal',
                    'coadebet',
                    'coakredit',
                    'keterangan',


                ], $query1);
            } else {

                $query1 = DB::table($this->anotherTable)->from(DB::raw("pengeluarandetail with (readuncommitted)"))
                    ->select(
                        'pengeluarandetail.id',
                        'pengeluarandetail.nobukti',
                        'pengeluarandetail.nowarkat',
                        'pengeluarandetail.tgljatuhtempo',
                        'pengeluarandetail.nominal',
                        'coadebet.keterangancoa as coadebet',
                        'coakredit.keterangancoa as coakredit',
                        'pengeluarandetail.keterangan',
                        DB::raw("(case when (year(pengeluarandetail.bulanbeban) <= 2000) then null else pengeluarandetail.bulanbeban end ) as bulanbeban"),
                    )
                    ->leftJoin('akunpusat as coadebet', 'pengeluarandetail.coadebet', 'coadebet.coa')
                    ->leftJoin('akunpusat as coakredit', 'pengeluarandetail.coakredit', 'coakredit.coa')
                    ->where('pengeluarandetail.nobukti', '=', request()->nobukti);

                DB::table($templist)->insertUsing([
                    'id',
                    'nobukti',
                    'nowarkat',
                    'tgljatuhtempo',
                    'nominal',
                    'coadebet',
                    'coakredit',
                    'keterangan',
                    'bulanbeban',

                ], $query1);
                $query2 = DB::table($this->anotherTable)->from(DB::raw("saldopengeluarandetail with (readuncommitted)"))
                    ->select(
                        'saldopengeluarandetail.id',
                        'saldopengeluarandetail.nobukti',
                        'saldopengeluarandetail.nowarkat',
                        'saldopengeluarandetail.tgljatuhtempo',
                        'saldopengeluarandetail.nominal',
                        'coadebet.keterangancoa as coadebet',
                        'coakredit.keterangancoa as coakredit',
                        'saldopengeluarandetail.keterangan',
                        DB::raw("(case when (year(saldopengeluarandetail.bulanbeban) <= 2000) then null else saldopengeluarandetail.bulanbeban end ) as bulanbeban"),
                    )
                    ->leftJoin('akunpusat as coadebet', 'saldopengeluarandetail.coadebet', 'coadebet.coa')
                    ->leftJoin('akunpusat as coakredit', 'saldopengeluarandetail.coakredit', 'coakredit.coa')
                    ->where('saldopengeluarandetail.nobukti', '=', request()->nobukti);

                DB::table($templist)->insertUsing([
                    'id',
                    'nobukti',
                    'nowarkat',
                    'tgljatuhtempo',
                    'nominal',
                    'coadebet',
                    'coakredit',
                    'keterangan',
                    'bulanbeban',

                ], $query2);
            }
        } else {
            $query1 = DB::table('penerimaangirodetail')->from(DB::raw("penerimaangirodetail with (readuncommitted)"))
                ->select(
                    'penerimaangirodetail.id',
                    'penerimaangirodetail.nobukti',
                    'penerimaangirodetail.nowarkat',
                    'penerimaangirodetail.tgljatuhtempo',
                    'penerimaangirodetail.nominal',
                    'coadebet.keterangancoa as coadebet',
                    'coakredit.keterangancoa as coakredit',
                    'penerimaangirodetail.keterangan',
                    DB::raw("(case when (year(penerimaangirodetail.bulanbeban) <= 2000) then null else penerimaangirodetail.bulanbeban end ) as bulanbeban"),
                )
                ->leftJoin('akunpusat as coadebet', 'penerimaangirodetail.coadebet', 'coadebet.coa')
                ->leftJoin('akunpusat as coakredit', 'penerimaangirodetail.coakredit', 'coakredit.coa')
                ->where('penerimaangirodetail.nobukti', '=', request()->nobukti);
            DB::table($templist)->insertUsing([
                'id',
                'nobukti',
                'nowarkat',
                'tgljatuhtempo',
                'nominal',
                'coadebet',
                'coakredit',
                'keterangan',
                'bulanbeban',

            ], $query1);
        }
        $query = DB::table($templist)->from(DB::raw($templist . " AS pengeluarandetail "))
            ->select([
                'pengeluarandetail.id',
                'pengeluarandetail.nobukti',
                'pengeluarandetail.nowarkat',
                'pengeluarandetail.tgljatuhtempo',
                'pengeluarandetail.nominal',
                'pengeluarandetail.coadebet',
                'pengeluarandetail.coakredit',
                'pengeluarandetail.keterangan',
                'pengeluarandetail.bulanbeban',
            ]);

        $this->sort($query, 'pengeluarandetail');
        $this->filter($query, 'pengeluarandetail');

        $this->totalNominal = $query->sum('nominal');
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);

        return $query->get();
    }

    public function sort($query, $table)
    {
        // if ($this->params['sortIndex'] == 'coadebet') {
        //     return $query->orderBy('coadebet.keterangancoa', $this->params['sortOrder']);
        // } else if ($this->params['sortIndex'] == 'coakredit') {
        //     return $query->orderBy('coakredit.keterangancoa', $this->params['sortOrder']);
        // } else {
        return $query->orderBy($table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        // }
    }

    public function filter($query, $table, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    // $query->where(function ($query, $table) {
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'coadebet') {
                        //     $query = $query->where('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'coakredit') {
                        //     $query = $query->where('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        if ($filters['field'] == 'nominal') {
                            $query = $query->whereRaw("format($table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tgljatuhtempo') {
                            $query->whereRaw("format($table.tgljatuhtempo, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->where($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }
                    // });

                    break;
                case "OR":
                    // $query->where(function ($query,$table) {
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        // if ($filters['field'] == 'coadebet') {
                        //     $query = $query->orWhere('coadebet.keterangancoa', 'LIKE', "%$filters[data]%");
                        // } else if ($filters['field'] == 'coakredit') {
                        //     $query = $query->orWhere('coakredit.keterangancoa', 'LIKE', "%$filters[data]%");
                        if ($filters['field'] == 'nominal') {
                            $query = $query->orWhereRaw("format($table.nominal, '#,#0.00') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'tgljatuhtempo') {
                            $query->orWhereRaw("format($table.tgljatuhtempo, 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                        } else {
                            $query = $query->orWhere($table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                        }
                    }
                    // });
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
    public function processStore(PencairanGiroPengeluaranHeader $pencairanGiroPengeluaranHeader, array $data): PencairanGiroPengeluaranDetail
    {
        $pencairanGiroDetail = new PencairanGiroPengeluaranDetail();
        $pencairanGiroDetail->pencairangiropengeluaran_id = $pencairanGiroPengeluaranHeader->id;
        $pencairanGiroDetail->nobukti = $pencairanGiroPengeluaranHeader->nobukti;
        $pencairanGiroDetail->alatbayar_id = $data['alatbayar_id'];
        $pencairanGiroDetail->nowarkat = $data['nowarkat'];
        $pencairanGiroDetail->tgljatuhtempo = $data['tgljatuhtempo'];
        $pencairanGiroDetail->nominal = $data['nominal'];
        $pencairanGiroDetail->coadebet = $data['coadebet'];
        $pencairanGiroDetail->coakredit = $data['coakredit'];
        $pencairanGiroDetail->keterangan = $data['keterangan'];
        $pencairanGiroDetail->bulanbeban = $data['bulanbeban'];
        $pencairanGiroDetail->modifiedby = auth('api')->user()->name;
        $pencairanGiroDetail->info = html_entity_decode(request()->info);

        $pencairanGiroDetail->save();

        if (!$pencairanGiroDetail->save()) {
            throw new \Exception("Error storing pencairan giro pengeluaran Detail.");
        }

        return $pencairanGiroDetail;
    }
}
