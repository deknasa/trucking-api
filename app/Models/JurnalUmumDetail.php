<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JurnalUmumDetail extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumdetail';

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


        if (isset(request()->forReport) && request()->forReport) {
            $id = request()->jurnalumum_id;
            $data = JurnalUmumHeader::find($id);
            $nobukti = $data['nobukti'];


            $jurnalUmumDetail = JurnalUmumDetail::from(
                DB::raw("jurnalumumdetail with (readuncommitted)")
            )
                ->select(
                    'header.nobukti as nobukti',
                    'header.tglbukti as tglbukti',
                    'jurnalumumdetail.coa as coa',
                    'coa.keterangancoa as keterangancoa',
                    DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                    DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                    'jurnalumumdetail.keterangan as keterangan'
                )
                ->join(DB::raw("jurnalumumheader as header with (readuncommitted)"), 'header.id', 'jurnalumumdetail.jurnalumum_id')
                ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa')

                ->where([
                    ['jurnalumumdetail.nobukti', '=', $nobukti]
                ]);
        } else if (isset(request()->forExport) && request()->forExport) {
            $id = request()->jurnalumum_id;
            $data = JurnalUmumHeader::find($id);
            $nobukti = $data['nobukti'];
            $jurnalUmumDetail = JurnalUmumDetail::from(
                DB::raw("jurnalumumdetail with (readuncommitted)")
            )
                ->select(
                    'header.nobukti as nobukti',
                    'header.tglbukti as tglbukti',
                    'jurnalumumdetail.coa as coa',
                    'coa.keterangancoa as keterangancoa',
                    DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                    DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                    'jurnalumumdetail.keterangan as keterangan'
                )
                ->join(DB::raw("jurnalumumheader as header with (readuncommitted)"), 'header.id', 'jurnalumumdetail.jurnalumum_id')
                ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa')

                ->where([
                    ['jurnalumumdetail.nobukti', '=', $nobukti]
                ]);
        } else {
            $id = request()->jurnalumum_id;
            $data = JurnalUmumHeader::find($id);
            $nobukti = $data['nobukti'];

            $jurnalUmumDetail = JurnalUmumDetail::from(
                DB::raw("jurnalumumdetail with (readuncommitted)")
            )
                ->select(
                    'jurnalumumdetail.nobukti as nobukti',
                    'jurnalumumdetail.tglbukti as tglbukti',
                    'jurnalumumdetail.coa as coa',
                    'coa.keterangancoa as keterangancoa',
                    DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                    DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                    'jurnalumumdetail.keterangan as keterangan'
                )
                ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa');


            $this->sort($jurnalUmumDetail);
            $jurnalUmumDetail->where($this->table . '.jurnalumum_id', '=', request()->jurnalumum_id);
            $this->filter($jurnalUmumDetail);
            $this->totalRows = $jurnalUmumDetail->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->paginate($jurnalUmumDetail);
            $temp = $this->getNominal($nobukti);
            $tempNominal = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))->select(DB::raw("nominaldebet,nominalkredit"))->first();

            $this->totalNominalDebet = $tempNominal->nominaldebet;
            $this->totalNominalKredit = $tempNominal->nominalkredit;
        }


        return $jurnalUmumDetail->get();
    }

    public function getNominal($nobukti)
    {
        $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));

        $jurnalUmumDetail = JurnalUmumDetail::from(
            DB::raw("jurnalumumdetail with (readuncommitted)")
        )
            ->select(
                DB::raw("sum((case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end)) as nominaldebet"),
                DB::raw("sum((case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end)) as nominalkredit"),
            )
            ->where([
                ['jurnalumumdetail.nobukti', '=', $nobukti]
            ]);


        Schema::create($temp, function ($table) {
            $table->float('nominaldebet')->nullable();
            $table->float('nominalkredit')->nullable();
        });

        $tes = DB::table($temp)->insertUsing(['nominaldebet', 'nominalkredit'], $jurnalUmumDetail);

        // $data = DB::table($temp)->get();
        return $temp;
    }

    public function findAll($nobukti)
    {
        $query = JurnalUmumDetail::from(
            DB::raw("jurnalumumdetail as A with (readuncommitted)")
        )->select(['A.coa as coadebet', 'debet.keterangancoa as ketcoadebet', 'b.coa as coakredit', 'kredit.keterangancoa as ketcoakredit', 'A.nominal', 'A.keterangan'])
            ->join(
                DB::raw("(SELECT baris,coa FROM jurnalumumdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                function ($join) {
                    $join->on('A.baris', '=', 'B.baris');
                }
            )
            ->join(DB::raw("akunpusat as debet with (readuncommitted)"), 'debet.coa', 'A.coa')
            ->join(DB::raw("akunpusat as kredit with (readuncommitted)"), 'kredit.coa', 'B.coa')
            ->where([
                ['A.nobukti', '=', $nobukti],
                ['A.nominal', '>=', '0']
            ])
            ->get();

        return $query;
    }

    public function getDetail($id)
    {
        $query = JurnalUmumDetail::from(
            DB::raw("jurnalumumdetail as A with (readuncommitted)")
        )->select(['A.coa as coadebet', 'debet.keterangancoa as ketcoadebet', 'b.coa as coakredit', 'kredit.keterangancoa as ketcoakredit', 'A.nominal', 'A.keterangan'])
            ->join(
                DB::raw("(SELECT baris,coa FROM jurnalumumdetail WHERE jurnalumum_id='$id' AND nominal<0) B"),
                function ($join) {
                    $join->on('A.baris', '=', 'B.baris');
                }
            )
            ->join(DB::raw("akunpusat as debet with (readuncommitted)"), 'debet.coa', 'A.coa')
            ->join(DB::raw("akunpusat as kredit with (readuncommitted)"), 'kredit.coa', 'B.coa')
            ->where([
                ['A.jurnalumum_id', '=', $id],
                ['A.nominal', '>=', '0']
            ])
            ->get();

        return $query;
    }

    public function getJurnalFromAnotherTable($nobukti)
    {
        $this->setRequestParameters();
        $query = JurnalUmumDetail::from(
            DB::raw("jurnalumumdetail with (readuncommitted)")
        )
            ->select(
                'jurnalumumdetail.nobukti as nobukti',
                'jurnalumumdetail.tglbukti as tglbukti',
                'jurnalumumdetail.coa as coa',
                'coa.keterangancoa as keterangancoa',
                DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                'jurnalumumdetail.keterangan as keterangan'
            )
            ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa');

        $this->sort($query);
        $query->where($this->table . '.nobukti', '=', $nobukti);
        $this->filter($query);
        // dd($query->toSql());
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);
        $temp = $this->getNominal($nobukti);
        $tempNominal = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))->select(DB::raw("sum(nominaldebet) as nominaldebet,sum(nominalkredit) as nominalkredit"))->first();

        $this->totalNominalDebet = $tempNominal->nominaldebet;
        $this->totalNominalKredit = $tempNominal->nominalkredit;

        return $query->get();
    }

    public function getProsesKBTAbsensi($nobukti)
    {
        $this->setRequestParameters();
        $query = DB::table($this->table)->from(DB::raw("absensisupirapprovalproses as proses with (readuncommitted) "));

        // $query = JurnalUmumDetail::from(
        //     DB::raw("jurnalumumdetail with (readuncommitted)")
        // )
        $query
            ->select(
                'jurnalumumdetail.nobukti as nobukti',
                'jurnalumumdetail.tglbukti as tglbukti',
                'jurnalumumdetail.coa as coa',
                'coa.keterangancoa as keterangancoa',
                DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                'jurnalumumdetail.keterangan as keterangan'
            )
            ->leftJoin(DB::raw("$this->table with (readuncommitted)"), 'proses.pengeluaran_nobukti',$this->table.'.nobukti')
            ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa');

        $this->sort($query);
        $query->where( "proses.nobukti", "=", $nobukti);
        $this->filter($query);
        // dd($query->toSql());
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);
        $temp = $this->getNominal($nobukti);
        $tempNominal = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))->select(DB::raw("sum(nominaldebet) as nominaldebet,sum(nominalkredit) as nominalkredit"))->first();

        $this->totalNominalDebet = $tempNominal->nominaldebet;
        $this->totalNominalKredit = $tempNominal->nominalkredit;

        return $query->get();
    }

    public function jurnalForRetur($nobukti, $statuspotong)
    {
        $this->setRequestParameters();
        if ($statuspotong == 219) {

            $temp = '##temp' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            $pengeluaranstokbukti = db::table("pengeluaranstokheader")->from(db::raw("pengeluaranstokheader a with (readuncommitted)"))
                ->select('nobukti')->where('penerimaan_nobukti', request()->nobukti)
                ->first()->nobukti ?? '';

            $fetch1 = DB::table("jurnalumumdetail")->from(
                DB::raw("jurnalumumdetail with (readuncommitted)")
            )
                ->select(
                    'jurnalumumdetail.nobukti as nobukti',
                    'jurnalumumdetail.tglbukti as tglbukti',
                    'jurnalumumdetail.coa as coa',
                    'jurnalumumdetail.nominal',
                    'jurnalumumdetail.keterangan as keterangan'
                )
                ->where($this->table . '.nobukti', '=', $nobukti);
            Schema::create($temp, function ($table) {
                $table->string('nobukti')->nullable();
                $table->date('tglbukti')->nullable();
                $table->string('coa')->nullable();
                $table->bigInteger('nominal')->nullable();
                $table->longText('keterangan')->nullable();
            });

            $tes = DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'coa', 'nominal', 'keterangan'], $fetch1);
            if (isset($pengeluaranstokbukti)) {

                $fetch1 = DB::table("jurnalumumdetail")->from(
                    DB::raw("jurnalumumdetail with (readuncommitted)")
                )
                    ->select(
                        'jurnalumumdetail.nobukti as nobukti',
                        'jurnalumumdetail.tglbukti as tglbukti',
                        'jurnalumumdetail.coa as coa',
                        'jurnalumumdetail.nominal',
                        'jurnalumumdetail.keterangan as keterangan'
                    )
                    ->where($this->table . '.nobukti', '=', $pengeluaranstokbukti);
                DB::table($temp)->insertUsing(['nobukti', 'tglbukti', 'coa', 'nominal', 'keterangan'], $fetch1);
            }

            $query = DB::table($temp)->from(
                DB::raw("$temp as jurnalumumdetail with (readuncommitted)")
            )
                ->select(
                    'jurnalumumdetail.nobukti as nobukti',
                    'jurnalumumdetail.tglbukti as tglbukti',
                    'jurnalumumdetail.coa as coa',
                    'coa.keterangancoa as keterangancoa',
                    DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                    DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                    'jurnalumumdetail.keterangan as keterangan'
                )
                ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa');
        } else {

            $query = JurnalUmumDetail::from(
                DB::raw("jurnalumumdetail with (readuncommitted)")
            )
                ->select(
                    'jurnalumumdetail.nobukti as nobukti',
                    'jurnalumumdetail.tglbukti as tglbukti',
                    'jurnalumumdetail.coa as coa',
                    'coa.keterangancoa as keterangancoa',
                    DB::raw("(case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end) as nominaldebet"),
                    DB::raw("(case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end) as nominalkredit"),
                    'jurnalumumdetail.keterangan as keterangan'
                )
                ->join(DB::raw("akunpusat as coa with (readuncommitted)"), 'coa.coa', 'jurnalumumdetail.coa');

            $query->where($this->table . '.nobukti', '=', $nobukti);
        }

        $this->sort($query);
        $this->filter($query);
        // dd($query->toSql());
        $this->totalRows = $query->count();
        $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

        $this->paginate($query);
        $temp = $this->getNominal($nobukti);
        $tempNominal = DB::table($temp)->from(DB::raw("$temp with (readuncommitted)"))->select(DB::raw("sum(nominaldebet) as nominaldebet,sum(nominalkredit) as nominalkredit"))->first();

        $this->totalNominalDebet = $tempNominal->nominaldebet;
        $this->totalNominalKredit = $tempNominal->nominalkredit;

        return $query->get();
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'keterangancoa') {
            return $query->orderBy('coa.' . $this->params['sortIndex'], $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominaldebet') {
            return $query->orderBy('jurnalumumdetail.nominal', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'nominalkredit') {
            return $query->orderBy('jurnalumumdetail.nominal', $this->params['sortOrder']);
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
                            if ($filters['field'] == 'keterangancoa') {
                                $query = $query->where('coa.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominaldebet') {
                                $query = $query->whereRaw("format((case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalkredit') {
                                $query = $query->whereRaw("format((case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
                            } else {
                                $query = $query->where($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            }
                        }
                    });

                    break;
                case "OR":
                    $query->where(function ($query) {
                        foreach ($this->params['filters']['rules'] as $index => $filters) {
                            if ($filters['field'] == 'keterangancoa') {
                                $query = $query->orWhere('coa.keterangancoa', 'LIKE', "%$filters[data]%");
                            } else if ($filters['field'] == 'nominaldebet') {
                                $query = $query->orWhereRaw("format((case when jurnalumumdetail.nominal<=0 then 0 else jurnalumumdetail.nominal end), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'nominalkredit') {
                                $query = $query->orWhereRaw("format((case when jurnalumumdetail.nominal>=0 then 0 else abs(jurnalumumdetail.nominal) end), '#,#0.00') LIKE '%$filters[data]%'");
                            } else if ($filters['field'] == 'tglbukti') {
                                $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy') LIKE '%$filters[data]%'");
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

    public function processStore(JurnalUmumHeader $jurnalUmumHeader, array $data): JurnalUmumDetail
    {
        $jurnalUmumDetail = new JurnalUmumDetail();
        $jurnalUmumDetail->jurnalumum_id = $jurnalUmumHeader->id;
        $jurnalUmumDetail->nobukti = $jurnalUmumHeader->nobukti;
        $jurnalUmumDetail->tglbukti = $data['tglbukti'];
        $jurnalUmumDetail->coa = $data['coa'];
        $jurnalUmumDetail->nominal = $data['nominal'];
        $jurnalUmumDetail->keterangan = $data['keterangan'] ?? '';
        $jurnalUmumDetail->modifiedby = auth('api')->user()->name;
        $jurnalUmumDetail->baris = $data['baris'];

        if (!$jurnalUmumDetail->save()) {
            throw new \Exception("Error storing jurnal umum detail.");
        }

        return $jurnalUmumDetail;
    }
}
