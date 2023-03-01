<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

            
            $jurnalUmumDetail=JurnalUmumDetail::from(
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
            $jurnalUmumDetail=JurnalUmumDetail::from(
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

            $jurnalUmumDetail=JurnalUmumDetail::from(
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


            // $this->totalNominal = $query->sum('nominal');
            $this->totalRows = $jurnalUmumDetail->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($jurnalUmumDetail);
            $this->paginate($jurnalUmumDetail);
        }

        return $jurnalUmumDetail->get();
    }

    public function findAll($nobukti){
        $query = JurnalUmumDetail::from(
            DB::raw("jurnalumumdetail as A with (readuncommitted)")
        )->select(['A.coa as coadebet','debet.keterangancoa as ketcoadebet', 'b.coa as coakredit','kredit.keterangancoa as ketcoakredit', 'A.nominal', 'A.keterangan'])
            ->join(
                DB::raw("(SELECT baris,coa FROM jurnalumumdetail WHERE nobukti='$nobukti' AND nominal<0) B"),
                function ($join) {
                    $join->on('A.baris', '=', 'B.baris');
                }
            )
            ->join(DB::raw("akunpusat as debet with (readuncommitted)"), 'debet.coa','A.coa')
            ->join(DB::raw("akunpusat as kredit with (readuncommitted)"), 'kredit.coa','B.coa')
            ->where([
                ['A.nobukti', '=', $nobukti],
                ['A.nominal', '>=', '0']
            ])
            ->get();

        return $query;
    }
    public function sort($query)
    {
        return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }
}
