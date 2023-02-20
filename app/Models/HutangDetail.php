<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class HutangDetail extends MyModel
{
    use HasFactory;

    protected $table = 'hutangdetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];  
    public function getAll($id)
    {
       

        $query = DB::table('hutangdetail')->from(DB::raw("hutangdetail with (readuncommitted)"))
        ->select(
            'hutangdetail.total',
            'hutangdetail.cicilan',
            'hutangdetail.totalbayar',            
            'hutangdetail.tgljatuhtempo',
            'hutangdetail.keterangan',
        )
            ->where('hutang_id', '=', $id);
            

        $data = $query->get();

        return $data;
    } 
    
    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                'header.nobukti',
                'header.tglbukti',
                'pelanggan.namapelanggan as pelanggan_id',
                'header.coa',
                'header.keterangan as keteranganheader',
                'header.total as totalheader',
                'supplier.namasupplier as supplier_id',
                $this->table .'.tgljatuhtempo',
                $this->table . '.total',
                $this->table . '.keterangan'
            )->leftJoin(DB::raw("hutangheader as header with (readuncommitted)"),'header.id',$this->table . '.hutang_id')
            ->leftJoin(DB::raw("pelanggan with (readuncommitted)"), 'header.pelanggan_id', 'pelanggan.id')
            ->leftJoin(DB::raw("supplier with (readuncommitted)"), 'header.supplier_id', 'supplier.id');

            $query->where($this->table . '.hutang_id', '=', request()->hutang_id);
        } else {
            $query->select(
                $this->table .'.nobukti',
                $this->table .'.tgljatuhtempo',
                $this->table .'.total',
                $this->table .'.keterangan',
            );

            $query->where($this->table . '.hutang_id', '=', request()->hutang_id);

            $this->totalNominal = $query->sum('total');
            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }

        return $query->get();
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
