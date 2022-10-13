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
        $query = DB::table('absensisupirdetail')
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
            ->join('trado','absensisupirdetail.trado_id','trado.id')
            ->join('supir','absensisupirdetail.supir_id','supir.id')
            ->join('absentrado','absensisupirdetail.absen_id','absentrado.id')
            ->where('absensisupirdetail.absensi_id',$id);

        $detail = $query->get();
        return $detail;
    }
}
