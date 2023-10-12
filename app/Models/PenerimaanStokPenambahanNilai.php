<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanStokPenambahanNilai extends MyModel
{
    use HasFactory;

    protected $table = 'penerimaanstokpenambahannilai';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function processStore(PenerimaanStokHeader $penerimaanStokHeader, array $data): PenerimaanStokPenambahanNilai
    {
        $stok= Stok::where('id', $data['stok_id'])->first();
        $stokreuse = Parameter::where('grp', 'STATUS REUSE')->where('subgrp', 'STATUS REUSE')->where('text', 'REUSE')->first();
        
        $reuse=false;
        if ($stok->statusreuse==$stokreuse->id) {
            $reuse=true;
        } 

        if (!$reuse) {
            throw ValidationException::withMessages(["qty"=>"bukan stok reuse"]);                

        }
        $spb = PenerimaanStokDetail::where('nobukti',$data['penerimaanstok_nobukti'])
                ->where('stok_id',$data['stok_id'])
                ->first();

        $penambahan = $data['qty'];
        $asli = $spb->qty;
        for ($i = 0; $i < $asli-$penambahan ; $i++) {
            $penambahanNilai = new PenerimaanStokPenambahanNilai();
            $penambahanNilai->penerimaanstokheader_id = $data['penerimaanstokheader_id'];
            $penambahanNilai->nobukti = $data['nobukti'];
            $penambahanNilai->stok_id = $data['stok_id'];
            $penambahanNilai->qty = 1;
            $penambahanNilai->harga = 0;
            $penambahanNilai->penerimaanstok_nobukti = $data['penerimaanstok_nobukti'];
            $penambahanNilai->modifiedby = auth('api')->user()->name;
            $penambahanNilai->info = html_entity_decode(request()->info);
            if (!$penambahanNilai->save()) {
                throw new \Exception("Error storing Penambahan Nilai");
            }
        }
        for ($i = 0; $i < $data['qty']; $i++) {
            $penambahanNilai = new PenerimaanStokPenambahanNilai();
            $penambahanNilai->penerimaanstokheader_id = $data['penerimaanstokheader_id'];
            $penambahanNilai->nobukti = $data['nobukti'];
            $penambahanNilai->stok_id = $data['stok_id'];
            $penambahanNilai->qty = 1;
            $penambahanNilai->harga = $data['harga'];
            $penambahanNilai->penerimaanstok_nobukti = $data['penerimaanstok_nobukti'];
            $penambahanNilai->modifiedby = auth('api')->user()->name;
            $penambahanNilai->info = html_entity_decode(request()->info);
            if (!$penambahanNilai->save()) {
                throw new \Exception("Error storing Penambahan Nilai");
            }
        }

        return $penambahanNilai;
    }
}
