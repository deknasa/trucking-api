<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanBukuBesar extends MyModel
{
    use HasFactory;

    protected $table = 'jurnalumumheader';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    
    public function getReport()
    {
        $tempsaldo = '##tempsaldo' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
        Schema::create($templaporan, function ($table) {
            $table->id();
            $table->unsignedBigInteger('kodebarang')->default(0);
            $table->string('namabarang', 1000)->default('');
            $table->dateTime('tglbukti')->default('1900/1/1');
            $table->string('nobukti', 100)->default('');
            $table->unsignedBigInteger('kategori_id')->default(0);
            $table->double('qtymasuk', 15, 2)->default(0);
            $table->double('nilaimasuk', 15, 2)->default(0);
            $table->double('qtykeluar', 15, 2)->default(0);
            $table->double('nilaikeluar', 15, 2)->default(0);
            $table->double('qtysaldo', 15, 2)->default(0);
            $table->double('nilaisaldo', 15, 2)->default(0);
            $table->string('modifiedby', 100)->default('');
        });

        // data coba coba
        $query = DB::table('jurnalumumdetail AS A')
        ->from(
            DB::raw("jurnalumumdetail AS A with (readuncommitted)")
        )
        ->select(['A.nominal as debet','b.nominal as kredit','A.nominal as saldo','A.keterangan', 'jurnalumumheader.nobukti', 'jurnalumumheader.tglbukti'])
        ->leftJoin(
            DB::raw("(SELECT baris,nobukti,nominal FROM jurnalumumdetail with (readuncommitted) WHERE nominal<0) B"),
            function ($join) {
                $join->on('A.baris', '=', 'B.baris');
            }
        )
        ->leftJoin(DB::raw("jurnalumumheader with (readuncommitted)"),'jurnalumumheader.nobukti','A.nobukti')
        ->whereRaw("A.nobukti = B.nobukti")
        ->whereRaw("A.nominal >= 0");

        $data = $query->get();
        return $data;
    }
}