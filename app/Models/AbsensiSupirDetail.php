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
                'absensisupirdetail.trado_id',
                'trado.keterangan as trado',
                'absensisupirdetail.supir_id',
                'supir.namasupir as supir',
                'absensisupirdetail.keterangan',
                'absensisupirdetail.absen_id',
                'absentrado.keterangan as absen',
                'absensisupirdetail.jam',
                'absensisupirdetail.uangjalan'
            )
            ->join(DB::raw("trado with (readuncommitted)"),'absensisupirdetail.trado_id','trado.id')
            ->join(DB::raw("supir with (readuncommitted)"),'absensisupirdetail.supir_id','supir.id')
            ->leftJoin(DB::raw("absentrado with (readuncommitted)"),'absensisupirdetail.absen_id','absentrado.id')
            ->where('absensisupirdetail.absensi_id',$id);

        $detail = $query->get();
        return $detail;
    }
}
