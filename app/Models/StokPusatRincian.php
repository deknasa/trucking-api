<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StokPusatRincian extends Model
{
    use HasFactory;
    protected $table = 'stokpusatrincian';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(StokPusat $stokPusat, array $data): StokPusatRincian
    {
        $stokPusatRincian = new StokPusatRincian();
        $stokPusatRincian->stokpusat_id = $stokPusat->id;
        $stokPusatRincian->namastok = $data['namastok'];
        $stokPusatRincian->kelompok_id = $data['kelompok_id'];
        $stokPusatRincian->stok_id = $data['stok_id'];
        $stokPusatRincian->cabang_id = $data['cabang_id'];
        $stokPusatRincian->gambar = $data['gambar'];
        $stokPusatRincian->modifiedby = auth('api')->user()->user;
        $stokPusatRincian->info = html_entity_decode(request()->info);

        if (!$stokPusatRincian->save()) {
            throw new \Exception("Error storing stok pusat rincian.");
        }

        return $stokPusatRincian;
    }

    public function findMdn($id)
    {
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MDN')->first();
        $query = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian"))
            ->where('cabang_id', $getCabang->id)
            ->where('stokpusat_id', $id)
            ->first();

        return $query;
    }
    public function findJkt($id)
    {
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'JKT')->first();
        $query = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian"))
            ->where('cabang_id', $getCabang->id)
            ->where('stokpusat_id', $id)
            ->first();

        return $query;
    }
    public function findJktTnl($id)
    {
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'TNL')->first();
        $query = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian"))
            ->where('cabang_id', $getCabang->id)
            ->where('stokpusat_id', $id)
            ->first();

        return $query;
    }
    public function findSby($id)
    {
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'SBY')->first();
        $query = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian"))
            ->where('cabang_id', $getCabang->id)
            ->where('stokpusat_id', $id)
            ->first();

        return $query;
    }
    public function findMks($id)
    {
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'MKS')->first();
        $query = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian"))
            ->where('cabang_id', $getCabang->id)
            ->where('stokpusat_id', $id)
            ->first();

        return $query;
    }
    public function findBtg($id)
    {
        $getCabang = DB::table("cabang")->from(DB::raw("cabang with (readuncommitted)"))->where('kodecabang', 'BTG')->first();
        $query = DB::table("stokpusatrincian")->from(DB::raw("stokpusatrincian"))
            ->where('cabang_id', $getCabang->id)
            ->where('stokpusat_id', $id)
            ->first();

        return $query;
    }
}
