<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsensiSupirDetail extends MyModel
{
    use HasFactory;

    protected $table = 'absensisupirdetail';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'jam' => 'date:H:i:s',
    ];


    public function get()
    {
        $this->setRequestParameters();

        $query = DB::table($this->table)->from(DB::raw("$this->table with (readuncommitted)"));

        if (isset(request()->forReport) && request()->forReport) {
            $query->select(
                "header.id as id_header",
                "header.nobukti as nobukti_header",
                "header.tglbukti as tgl_header",
                "header.kasgantung_nobukti as kasgantung_nobukti_header",
                "header.nominal as nominal_header",
                "trado.keterangan as trado",
                "supir.namasupir as supir",
                // "absentrado.kodeabsen as status",
                "$this->table.keterangan as keterangan_detail",
                "$this->table.jam",
                "$this->table.uangjalan",
                "$this->table.absensi_id"
            )
                ->leftjoin(DB::raw("absensisupirheader as header with (readuncommitted)"), "header.id", "$this->table.absensi_id")
                ->leftjoin(DB::raw("trado with (readuncommitted)"), "trado.id","$this->table.trado_id")
                ->leftjoin(DB::raw("supir with (readuncommitted)"), "supir.id","$this->table.supir_id");
                // ->leftjoin(DB::raw("absentrado with (readuncommitted)"), "absentrado.id","$this->table.absen_id");
                $query->where($this->table . '.absensi_id', '=', request()->absensi_id);

        } else {
            $query->select(
                "trado.keterangan as trado",
                "supir.namasupir as supir",
                "absentrado.kodeabsen as status",
                "$this->table.keterangan as keterangan_detail",
                "$this->table.jam",
                "$this->table.id",
                DB::raw("isnull($this->table.trado_id,0) as trado_id"),
                DB::raw("isnull($this->table.supir_id,0) as supir_id"),
                "$this->table.uangjalan",
                DB::raw("isnull($this->table.absensi_id,0) as absensi_id"),
                DB::raw("isnull($this->table.absen_id,0) as absen_id"),
            )
            ->leftjoin(DB::raw("trado with (readuncommitted)"), "trado.id","$this->table.trado_id")
            ->leftjoin(DB::raw("supir with (readuncommitted)"), "supir.id","$this->table.supir_id")
            ->leftjoin(DB::raw("absentrado with (readuncommitted)"), "absentrado.id","$this->table.absen_id");

            $query->where($this->table . '.absensi_id', '=', request()->absensi_id);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;

            $this->sort($query);
            $this->paginate($query);
        }
        return $query->get();
    }
    

    public function getAll($id)
    {
 
        $query = DB::table('trado')->from(DB::raw("trado with (readuncommitted)"))
            ->select(
                'trado.id as trado_id',
                'trado.keterangan as trado',
                DB::raw("isnull(absensisupirdetail.supir_id,0) as supir_id"),
                DB::raw("isnull(supir.namasupir,'') as supir"),
                DB::raw("isnull(absensisupirdetail.keterangan,'') as keterangan"),
                DB::raw("isnull(absensisupirdetail.absen_id,0) as absen_id"),
                DB::raw("isnull(absentrado.keterangan,'') as absen"),
                DB::raw("isnull(absensisupirdetail.jam,'') as jam"),
                DB::raw("isnull(absensisupirdetail.uangjalan,0) as uangjalan"),               
            )
            ->leftJoin('absensisupirdetail', function ($join)  use ($id) {
                $join->on('absensisupirdetail.trado_id', '=', 'trado.id')
                    ->where('absensisupirdetail.absensi_id', '=', $id);
            })
            ->leftjoin(DB::raw("supir with (readuncommitted)"),'absensisupirdetail.supir_id','supir.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"),'absensisupirdetail.absen_id','absentrado.id');
   


        $data = $query->get();

        return $data;
    }    

    public function absensiSupirHeader()
    {
        return $this->belongsToMany(AbsensiSupirHeader::class);
    }

    public function trado() {
        return $this->belongsTo(Trado::class, 'trado_id');
    }

    public function supir() {
        return $this->belongsTo(Supir::class, 'supir_id');
    }

    public function absenTrado() {
        return $this->belongsTo(AbsenTrado::class, 'absen_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function find($id) 
    {
        $query = DB::table('absensisupirdetail')->from(DB::raw("absensisupirdetail with (readuncommitted)"))
            ->select(
                DB::raw("isnull(absensisupirdetail.trado_id,0) as trado_id"),
                DB::raw("isnull(trado.keterangan,'') as trado"),
                DB::raw("isnull(absensisupirdetail.supir_id,0) as supir_id"),
                DB::raw("isnull(supir.namasupir,'') as supir"),
                DB::raw("isnull(absensisupirdetail.keterangan,'') as keterangan"),
                DB::raw("isnull(absensisupirdetail.absen_id,0) as absen_id"),
                DB::raw("isnull(absentrado.keterangan,'') as absen"),
                DB::raw("isnull(absensisupirdetail.jam,'') as jam"),
                DB::raw("isnull(absensisupirdetail.uangjalan,0) as uangjalan"),
            )
            ->join(DB::raw("trado with (readuncommitted)"),'absensisupirdetail.trado_id','trado.id')
            ->join(DB::raw("supir with (readuncommitted)"),'absensisupirdetail.supir_id','supir.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"),'absensisupirdetail.absen_id','absentrado.id')
            ->where('absensisupirdetail.absensi_id',$id);

        $detail = $query->get();
        return $detail;
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
